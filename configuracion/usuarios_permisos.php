<?php
declare(strict_types=1);

require_once __DIR__ . '/_inicio.php';

$depth = '../';
$usuarioObjetivo = max(0, (int) ($_GET['usuario_id'] ?? 0));
$usuarioActual = (int) Sesion::get('id', 0);
$error = null;
$schemaPreparado = false;
$usuario = false;
$menus = [];
$submenusPorMenu = [];
$permisosMenus = [];
$permisosSubmenus = [];

if (empty($_SESSION['csrf_usuarios_permisos'])) {
    $_SESSION['csrf_usuarios_permisos'] = bin2hex(random_bytes(32));
}
$csrf = (string) $_SESSION['csrf_usuarios_permisos'];

function usuario_puede_administrar_permisos(Conexion $db, int $idUsuario): bool
{
    $perfilSesion = strtolower(trim((string) Sesion::get('perfil', '')));
    if (in_array($perfilSesion, ['administrador', 'admin'], true)) {
        return true;
    }
    return (bool) $db->fetchOne(
        "SELECT 1
          WHERE EXISTS (
                    SELECT 1 FROM usuario_perfil up
                    JOIN perfiles p ON p.id_perfil = up.id_perfil
                    WHERE up.id_usuario = ? AND LOWER(p.nombre) IN ('administrador', 'admin')
                )
             OR EXISTS (
                    SELECT 1 FROM usuario_colegio uc
                    JOIN perfiles p ON p.id_perfil = uc.id_perfil
                    WHERE uc.id_usuario = ? AND uc.estado = 1
                      AND LOWER(p.nombre) IN ('administrador', 'admin')
                )",
        [$idUsuario, $idUsuario]
    );
}

try {
    if (!usuario_puede_administrar_permisos($db, $usuarioActual)) {
        http_response_code(403);
        $error = 'No tienes autorización para modificar permisos de usuarios.';
    } elseif ($usuarioObjetivo <= 0) {
        http_response_code(422);
        $error = 'Selecciona un usuario desde el listado de usuarios.';
    } else {
        $usuario = $db->fetchOne(
            "SELECT id, nombre, apellido_paterno, apellido_materno, email, estado
               FROM usuarios WHERE id = ? LIMIT 1",
            [$usuarioObjetivo]
        );
        if (!$usuario) {
            http_response_code(404);
            $error = 'El usuario indicado no existe.';
        }
    }

    if (!$error) {
        $columna = $db->fetchOne(
            "SELECT COUNT(*) AS total
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'permisos_menu_1'
                AND COLUMN_NAME = 'id_submenu'"
        );
        $schemaPreparado = (int) ($columna['total'] ?? 0) > 0;

        $menus = $db->fetchAll(
            "SELECT id_menu, nombre, archivo, icono, orden
               FROM menu_1
              WHERE id_menu > 0
           ORDER BY CAST(orden AS UNSIGNED) ASC, nombre ASC"
        );
        $submenus = $db->fetchAll(
            "SELECT id_submenu, id_menu, nombre, archivo, icono, orden
               FROM menu_1_sub
           ORDER BY id_menu ASC, orden ASC, nombre ASC"
        );
        foreach ($submenus as $submenu) {
            $submenusPorMenu[(int) $submenu['id_menu']][] = $submenu;
        }

        if ($schemaPreparado) {
            $permisos = $db->fetchAll(
                "SELECT id_menu1, id_submenu
                   FROM permisos_menu_1
                  WHERE id_usuario = ? AND id_tipo_permiso = 1",
                [$usuarioObjetivo]
            );
            foreach ($permisos as $permiso) {
                if ($permiso['id_submenu'] === null) {
                    $permisosMenus[(int) $permiso['id_menu1']] = true;
                } else {
                    $permisosSubmenus[(int) $permiso['id_submenu']] = true;
                }
            }
        }
    }
} catch (Throwable $ex) {
    error_log('Error en usuarios_permisos.php: ' . $ex->getMessage());
    $error = 'No fue posible cargar la configuración de permisos.';
}

$nombreUsuario = $usuario
    ? trim(implode(' ', array_filter([$usuario['nombre'], $usuario['apellido_paterno'], $usuario['apellido_materno']])))
    : 'Usuario';

iniciar_layout_configuracion('Permisos de usuario', 'Permisos', 'usuarios_permisos');
?>

<div class="page-header">
  <div><h1><i class="bi bi-shield-lock" aria-hidden="true"></i> Permisos de usuario</h1><p>Controla los menús y submenús visibles para <?= e($nombreUsuario) ?>.</p></div>
  <div class="page-header-actions"><a href="listado_usuarios.php" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Volver a usuarios</a></div>
</div>

<?php if ($error): ?>
  <div class="card"><div class="card-body up-alert up-alert--danger"><i class="bi bi-exclamation-circle"></i><span><?= e($error) ?></span></div></div>
<?php elseif (!$schemaPreparado): ?>
  <div class="card"><div class="card-body up-alert up-alert--warning"><i class="bi bi-database-exclamation"></i><div><strong>Falta preparar la base de datos</strong><p>Ejecuta <code>sql/permisos_submenus.sql</code> y vuelve a cargar esta página.</p></div></div></div>
<?php else: ?>
  <div class="up-user-card">
    <div class="user-avatar"><?= e(strtoupper(substr((string) $usuario['nombre'], 0, 1) . substr((string) $usuario['apellido_paterno'], 0, 1))) ?></div>
    <div><strong><?= e($nombreUsuario) ?></strong><p><?= e((string) $usuario['email']) ?> · <?= e((string) $usuario['estado']) ?></p></div>
  </div>

  <form id="form-permisos">
    <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="usuario_id" value="<?= $usuarioObjetivo ?>">
    <div class="up-toolbar"><p>Marca un menú completo o selecciona solamente los submenús necesarios.</p><span id="up-resumen" class="up-summary"></span></div>
    <div class="up-grid">
      <?php foreach ($menus as $menu): ?>
        <?php
        $idMenu = (int) $menu['id_menu'];
        $submenusMenu = $submenusPorMenu[$idMenu] ?? [];
        $menuMarcado = isset($permisosMenus[$idMenu]);
        $submenusMarcados = count(array_filter($submenusMenu, static fn (array $submenu): bool => isset($permisosSubmenus[(int) $submenu['id_submenu']])));
        $menuVisualmenteMarcado = $menuMarcado || $submenusMarcados > 0;
        ?>
        <section class="up-menu" data-menu-group="<?= $idMenu ?>">
          <label class="up-menu__parent">
            <input type="checkbox" class="menu-checkbox" name="menus[]" value="<?= $idMenu ?>" data-menu-id="<?= $idMenu ?>" <?= $menuVisualmenteMarcado ? 'checked' : '' ?>>
            <span class="up-check-ui"><i class="bi bi-check"></i></span>
            <span class="up-menu__identity"><strong><?= e((string) $menu['nombre']) ?></strong><small><?= count($submenusMenu) ?> submenú<?= count($submenusMenu) === 1 ? '' : 's' ?></small></span>
            <span class="up-menu__count"><span><?= $submenusMarcados ?></span>/<?= count($submenusMenu) ?></span>
          </label>
          <?php if ($submenusMenu): ?>
            <div class="up-submenus">
              <?php foreach ($submenusMenu as $submenu): ?>
                <?php $idSubmenu = (int) $submenu['id_submenu']; ?>
                <label class="up-submenu"><input type="checkbox" class="submenu-checkbox" name="submenus[]" value="<?= $idSubmenu ?>" data-menu-parent="<?= $idMenu ?>" <?= isset($permisosSubmenus[$idSubmenu]) ? 'checked' : '' ?>><span class="up-check-ui"><i class="bi bi-check"></i></span><span><?= e((string) $submenu['nombre']) ?></span></label>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
    </div>
    <div class="up-action-bar"><p id="up-mensaje" role="status" aria-live="polite"></p><div><a href="listado_usuarios.php" class="btn btn-outline">Cancelar</a><button type="submit" class="btn btn-primary" id="up-guardar"><i class="bi bi-floppy-fill"></i> Guardar permisos</button></div></div>
  </form>
<?php endif; ?>

<style>
.up-alert{display:flex;align-items:flex-start;gap:10px}.up-alert--danger{color:var(--danger);background:var(--danger-light)}.up-alert--warning{color:var(--warning);background:var(--warning-light)}.up-alert p{margin-top:3px;font-size:12px;color:var(--fg)}.up-alert code{font-family:'DM Mono',monospace}.up-user-card{display:flex;align-items:center;gap:12px;margin-bottom:16px;padding:14px 16px;border:1px solid var(--border);border-radius:var(--radius);background:var(--card)}.up-user-card .user-avatar{width:40px;height:40px}.up-user-card strong{font-size:14px}.up-user-card p{font-size:11px;color:var(--muted)}
.up-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 16px;border:1px solid var(--border);border-bottom:0;border-radius:var(--radius) var(--radius) 0 0;background:#FAFAFA;font-size:12px;color:var(--muted)}.up-summary{font-weight:600;color:var(--blue);white-space:nowrap}.up-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;padding:16px;border:1px solid var(--border);background:var(--bg)}.up-menu{overflow:hidden;border:1px solid var(--border);border-radius:var(--radius);background:var(--card);box-shadow:var(--shadow-card)}
.up-menu__parent,.up-submenu{display:flex;align-items:center;gap:10px;cursor:pointer}.up-menu__parent{padding:13px 14px;background:#FAFAFA;border-bottom:1px solid var(--border)}.up-menu__parent>input,.up-submenu>input{position:absolute;opacity:0;pointer-events:none}.up-check-ui{width:18px;height:18px;display:grid;place-items:center;flex:0 0 auto;border:1px solid #C9CED6;border-radius:5px;background:var(--card);color:transparent;transition:all .15s ease}.up-menu__parent input:checked+.up-check-ui,.up-submenu input:checked+.up-check-ui{border-color:var(--blue);background:var(--blue);color:#fff}.up-menu__parent input:indeterminate+.up-check-ui{border-color:var(--blue);background:var(--blue-light);color:var(--blue)}.up-menu__parent input:indeterminate+.up-check-ui i:before{content:'\F2EA'}.up-menu__parent input:focus-visible+.up-check-ui,.up-submenu input:focus-visible+.up-check-ui{outline:2px solid var(--blue);outline-offset:2px}
.up-menu__identity{min-width:0}.up-menu__identity strong{display:block;font-size:13px}.up-menu__identity small{display:block;font-size:10px;color:var(--muted)}.up-menu__count{margin-left:auto;padding:3px 8px;border-radius:99px;background:var(--blue-light);color:var(--blue);font-size:10px;font-weight:700}.up-submenus{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:0 14px;padding:10px 14px}.up-submenu{min-width:0;padding:7px 0;font-size:12px;color:var(--fg)}.up-submenu>span:last-child{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.up-submenu .up-check-ui{width:16px;height:16px;border-radius:4px;font-size:11px}
.up-action-bar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 16px;border:1px solid var(--border);border-top:0;border-radius:0 0 var(--radius) var(--radius);background:#FAFAFA}.up-action-bar>p{font-size:12px;color:var(--muted)}.up-action-bar>p.is-success{color:var(--success)}.up-action-bar>p.is-error{color:var(--danger)}.up-action-bar>div{display:flex;gap:8px}
@media(max-width:900px){.up-grid{grid-template-columns:1fr}}@media(max-width:600px){.up-toolbar,.up-action-bar{align-items:stretch;flex-direction:column}.up-submenus{grid-template-columns:1fr}.up-action-bar>div{justify-content:flex-end}}
</style>

<?php if (!$error && $schemaPreparado): ?>
<script>
(() => {
  'use strict';
  const form = document.getElementById('form-permisos');
  const message = document.getElementById('up-mensaje');
  const saveButton = document.getElementById('up-guardar');

  function syncGroup(group) {
    const parent = group.querySelector('.menu-checkbox');
    const children = [...group.querySelectorAll('.submenu-checkbox')];
    const checked = children.filter(child => child.checked).length;
    if (children.length) {
      parent.checked = checked > 0;
      parent.indeterminate = checked > 0 && checked < children.length;
    }
    group.querySelector('.up-menu__count span').textContent = String(checked);
  }
  function syncSummary() {
    const menus = form.querySelectorAll('.menu-checkbox:checked').length;
    const submenus = form.querySelectorAll('.submenu-checkbox:checked').length;
    document.getElementById('up-resumen').textContent = `${menus} menús · ${submenus} submenús`;
  }
  form.querySelectorAll('[data-menu-group]').forEach(syncGroup);
  syncSummary();

  form.addEventListener('change', event => {
    const parent = event.target.closest('.menu-checkbox');
    if (parent) {
      const group = parent.closest('[data-menu-group]');
      group.querySelectorAll('.submenu-checkbox').forEach(child => { child.checked = parent.checked; });
      parent.indeterminate = false;
      syncGroup(group); syncSummary(); return;
    }
    const child = event.target.closest('.submenu-checkbox');
    if (child) { syncGroup(child.closest('[data-menu-group]')); syncSummary(); }
  });

  form.addEventListener('submit', async event => {
    event.preventDefault(); saveButton.disabled = true; saveButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando…'; message.className = ''; message.textContent = 'Aplicando cambios…';
    try {
      const response = await fetch('../ajax/guardar_permisos_usuario.php', {method:'POST', credentials:'same-origin', body:new FormData(form)});
      const data = await response.json();
      if (!response.ok || !data.ok) throw new Error(data.error || 'No fue posible guardar los permisos.');
      message.className = 'is-success'; message.textContent = data.mensaje; saveButton.innerHTML = '<i class="bi bi-check-circle"></i> Guardado';
      setTimeout(() => { saveButton.disabled = false; saveButton.innerHTML = '<i class="bi bi-floppy-fill"></i> Guardar permisos'; }, 1200);
    } catch (error) {
      message.className = 'is-error'; message.textContent = error.message || 'No fue posible guardar los permisos.'; saveButton.disabled = false; saveButton.innerHTML = '<i class="bi bi-floppy-fill"></i> Guardar permisos';
    }
  });
})();
</script>
<?php endif; ?>

<?php finalizar_layout_configuracion(); ?>
