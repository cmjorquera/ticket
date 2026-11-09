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
$menusConSubmenusConfigurados = [];

if (empty($_SESSION['csrf_usuarios_permisos'])) {
    $_SESSION['csrf_usuarios_permisos'] = bin2hex(random_bytes(32));
}
$csrf = (string) $_SESSION['csrf_usuarios_permisos'];

function usuario_puede_administrar_permisos(Conexion $db, int $idUsuario): bool
{
    $perfilSesion = strtolower(trim((string) Sesion::get('perfil', '')));
    $perfilSesion = preg_replace('/[\s_-]+/', ' ', $perfilSesion) ?: '';
    if (in_array($perfilSesion, ['administrador', 'admin', 'super admin', 'superadmin'], true)) {
        return true;
    }
    return (bool) $db->fetchOne(
        "SELECT 1
           FROM usuario_perfil up
          JOIN perfiles p ON p.id_perfil = up.id_perfil
          WHERE up.id_usuario = ?
            AND (
                p.id_perfil = 3
                OR LOWER(REPLACE(REPLACE(TRIM(p.nombre_perfil), '_', ' '), '-', ' '))
                   IN ('administrador', 'admin', 'super admin', 'superadmin')
            )
          LIMIT 1",
        [$idUsuario]
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
        $estructuraSubmenus = $db->fetchOne(
            "SELECT COUNT(DISTINCT COLUMN_NAME) AS total
               FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'permiso_sub_menu'
                AND COLUMN_NAME IN ('id_usuario', 'id_submenu', 'permiso')"
        );
        $schemaPreparado = (int) ($estructuraSubmenus['total'] ?? 0) === 3;

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
            $permisosMenu = $db->fetchAll(
                "SELECT DISTINCT id_menu1
                   FROM permisos_menu_1
                  WHERE id_usuario = ? AND id_tipo_permiso = 1",
                [$usuarioObjetivo]
            );
            foreach ($permisosMenu as $permiso) {
                $permisosMenus[(int) $permiso['id_menu1']] = true;
            }

            $permisosSubmenu = $db->fetchAll(
                "SELECT psm.id_submenu, psm.permiso, sm.id_menu
                   FROM permiso_sub_menu psm
                   JOIN menu_1_sub sm ON sm.id_submenu = psm.id_submenu
                  WHERE psm.id_usuario = ?",
                [$usuarioObjetivo]
            );
            foreach ($permisosSubmenu as $permiso) {
                $menusConSubmenusConfigurados[(int) $permiso['id_menu']] = true;
                if ((int) $permiso['permiso'] === 1) {
                    $permisosSubmenus[(int) $permiso['id_submenu']] = true;
                }
            }

            // Compatibilidad con permisos históricos: un menú principal que
            // aún no tenga configuración granular conserva todos sus hijos.
            foreach (array_keys($permisosMenus) as $idMenu) {
                if (isset($menusConSubmenusConfigurados[$idMenu])) {
                    continue;
                }
                foreach ($submenusPorMenu[$idMenu] ?? [] as $submenu) {
                    $permisosSubmenus[(int) $submenu['id_submenu']] = true;
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

// ?fragmento=1 → solo el formulario (lo carga el panel lateral del listado de usuarios)
$fragmento = !empty($_GET['fragmento']);
if (!$fragmento) {
    $pagina_estilos = array_merge($pagina_estilos ?? [], ['configuracion/css/permisos_usuario.css?v=' . (string) filemtime(__DIR__ . '/css/permisos_usuario.css')]);
    iniciar_layout_configuracion('Permisos de usuario', 'Permisos', 'usuarios_permisos');
}
?>
<?php if (!$fragmento): ?>

<div class="page-header">
  <div><h1><i class="bi bi-shield-lock" aria-hidden="true"></i> Permisos de usuario</h1><p>Controla los menús y submenús visibles para <?= e($nombreUsuario) ?>.</p></div>
  <div class="page-header-actions"><a href="listado_usuarios.php" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Volver a usuarios</a></div>
</div>
<?php endif; ?>

<?php if ($error): ?>
  <div class="card"><div class="card-body up-alert up-alert--danger"><i class="bi bi-exclamation-circle"></i><span><?= e($error) ?></span></div></div>
<?php elseif (!$schemaPreparado): ?>
  <div class="card"><div class="card-body up-alert up-alert--warning"><i class="bi bi-database-exclamation"></i><div><strong>Falta preparar la base de datos</strong><p>La tabla <code>permiso_sub_menu</code> no existe o no contiene las columnas requeridas.</p></div></div></div>
<?php else: ?>
  <?php if (!$fragmento): ?>
  <div class="up-user-card">
    <div class="user-avatar"><?= e(strtoupper(substr((string) $usuario['nombre'], 0, 1) . substr((string) $usuario['apellido_paterno'], 0, 1))) ?></div>
    <div><strong><?= e($nombreUsuario) ?></strong><p><?= e((string) $usuario['email']) ?> · <?= e((string) $usuario['estado']) ?></p></div>
  </div>
  <?php endif; ?>

  <form id="form-permisos" data-base="<?= $fragmento ? '' : '' ?>" data-usuario="<?= $usuarioObjetivo ?>" data-nombre="<?= e($nombreUsuario) ?>" data-email="<?= e((string) $usuario['email']) ?>">
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
        <section class="up-menu" data-permission-group-id="<?= $idMenu ?>">
          <label class="up-menu__parent">
            <input type="checkbox" class="menu-checkbox" name="menus[]" value="<?= $idMenu ?>" data-permission-menu-id="<?= $idMenu ?>" <?= $menuVisualmenteMarcado ? 'checked' : '' ?>>
            <span class="up-check-ui"><i class="bi bi-check"></i></span>
            <span class="up-menu__identity"><strong><?= e((string) $menu['nombre']) ?> <span data-menu-order-id="<?= $idMenu ?>" data-order-fallback="<?= e((string) $menu['orden']) ?>"></span></strong><small><?= count($submenusMenu) ?> submenú<?= count($submenusMenu) === 1 ? '' : 's' ?></small></span>
            <span class="up-menu__count"><span><?= $submenusMarcados ?></span>/<?= count($submenusMenu) ?></span>
          </label>
          <?php if ($submenusMenu): ?>
            <div class="up-submenus">
              <?php foreach ($submenusMenu as $submenu): ?>
                <?php $idSubmenu = (int) $submenu['id_submenu']; ?>
                <label class="up-submenu"><input type="checkbox" class="submenu-checkbox" name="submenus[]" value="<?= $idSubmenu ?>" data-permission-submenu-id="<?= $idSubmenu ?>" data-parent-menu-id="<?= $idMenu ?>" <?= isset($permisosSubmenus[$idSubmenu]) ? 'checked' : '' ?>><span class="up-check-ui"><i class="bi bi-check"></i></span><span><?= e((string) $submenu['nombre']) ?> <span data-submenu-order-id="<?= $idSubmenu ?>" data-order-fallback="<?= e((string) $submenu['orden']) ?>"></span></span></label>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      <?php endforeach; ?>
    </div>
    <div class="up-action-bar"><p id="up-mensaje" role="status" aria-live="polite"></p><div><?php if ($fragmento): ?><button type="button" class="btn btn-outline" data-oc-cerrar>Cancelar</button><?php else: ?><a href="listado_usuarios.php" class="btn btn-outline">Cancelar</a><?php endif; ?><button type="submit" class="btn btn-primary" id="up-guardar"><i class="bi bi-floppy-fill"></i> Guardar permisos</button></div></div>
  </form>
<?php endif; ?>

<?php if (!$fragmento): ?>
<script src="js/permisos_usuario.js?v=<?= (int) filemtime(__DIR__ . '/js/permisos_usuario.js') ?>"></script>
<?php finalizar_layout_configuracion(); ?>
<?php endif; ?>
