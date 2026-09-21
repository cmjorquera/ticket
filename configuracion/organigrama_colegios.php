<?php
declare(strict_types=1);

require_once __DIR__ . '/_inicio.php';

$depth = '../';
$usuarioActual = (int) Sesion::get('id', 0);
$autorizado = false;
$accesoDenegado = false;
$error = null;
$colegios = [];

try {
    $autorizado = (bool) $db->fetchOne(
        'SELECT 1
           FROM usuario_perfil up
          WHERE up.id_usuario = ? AND up.id_perfil = 3
          LIMIT 1',
        [$usuarioActual]
    );

    if (!$autorizado) {
        http_response_code(403);
        $accesoDenegado = true;
        $error = 'No tienes permisos para consultar el organigrama de colegios.';
    } else {
        $filasColegios = $db->fetchAll(
            "SELECT c.id_colegio, c.nom_colegio
               FROM colegio c
              WHERE c.estado = 1
                AND c.id_colegio <> 15
                AND UPPER(TRIM(c.nom_colegio)) <> 'SEDUC'
           ORDER BY c.nom_colegio ASC"
        );

        foreach ($filasColegios as $colegio) {
            $idColegio = (int) $colegio['id_colegio'];

            $administradores = $db->fetchAll(
                "SELECT u.id,
                        CONCAT_WS(' ', u.nombre, u.apellido_paterno, u.apellido_materno) AS nombre,
                        u.email,
                        GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.id_perfil SEPARATOR ', ') AS perfil
                   FROM usuarios u
                   JOIN jefatura_departamento jd
                     ON jd.id_usuario = u.id
                    AND jd.estado = 1
                   JOIN usuario_perfil up ON up.id_usuario = u.id
                   JOIN perfiles p
                     ON p.id_perfil = up.id_perfil
                    AND p.estado = 1
                  WHERE jd.id_colegio = ?
                    AND jd.tipo_jefatura = 'Admin_Colegio'
                    AND jd.id_departamento_colegio IS NULL
               GROUP BY u.id, u.nombre, u.apellido_paterno, u.apellido_materno, u.email
               ORDER BY u.nombre ASC, u.apellido_paterno ASC",
                [$idColegio]
            );

            $departamentos = $db->fetchAll(
                "SELECT dc.id, dc.nombre_departamento, dc.sigla,
                        (
                            SELECT CONCAT_WS(' ', ud.nombre, ud.apellido_paterno, ud.apellido_materno)
                              FROM jefatura_departamento jd_depto
                              JOIN usuarios ud ON ud.id = jd_depto.id_usuario
                             WHERE jd_depto.id_departamento_colegio = dc.id
                               AND jd_depto.id_colegio = dc.id_colegio
                               AND jd_depto.tipo_jefatura = 'Admin_Departamento'
                               AND jd_depto.estado = 1
                          ORDER BY ud.nombre ASC, ud.apellido_paterno ASC
                             LIMIT 1
                        ) AS admin_depto
                   FROM departamentos_colegio dc
                  WHERE dc.id_colegio = ? AND dc.estado = 1
               ORDER BY dc.nombre_departamento ASC",
                [$idColegio]
            );

            $usuarios = $db->fetchAll(
                "SELECT u.id,
                        CONCAT_WS(' ', u.nombre, u.apellido_paterno, u.apellido_materno) AS nombre,
                        u.email,
                        u.estado,
                        GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.id_perfil SEPARATOR ', ') AS perfiles,
                        MAX(dc.nombre_departamento) AS nombre_departamento
                   FROM usuarios u
                   JOIN jefatura_departamento jd
                     ON jd.id_usuario = u.id
                    AND jd.id_colegio = ?
                    AND jd.estado = 1
              LEFT JOIN usuario_perfil up ON up.id_usuario = u.id
              LEFT JOIN perfiles p
                     ON p.id_perfil = up.id_perfil
                    AND p.estado = 1
              LEFT JOIN departamentos_colegio dc
                     ON dc.id = jd.id_departamento_colegio
                    AND dc.estado = 1
               GROUP BY u.id, u.nombre, u.apellido_paterno, u.apellido_materno, u.email, u.estado
               ORDER BY u.nombre ASC, u.apellido_paterno ASC",
                [$idColegio]
            );

            $colegios[] = [
                'id_colegio' => $idColegio,
                'nom_colegio' => (string) $colegio['nom_colegio'],
                'administradores' => $administradores,
                'departamentos' => $departamentos,
                'usuarios' => $usuarios,
            ];
        }
    }
} catch (Throwable $ex) {
    error_log('Error en organigrama_colegios.php: ' . $ex->getMessage());
    http_response_code(500);
    $error = 'No fue posible cargar el organigrama. Revisa la estructura de usuarios y vuelve a intentarlo.';
}

iniciar_layout_configuracion('Organigrama de Colegios', 'Organigrama de Colegios', 'organigrama_colegios');
?>

<style>
  .oc-shell{--oc-blue:#075985;--oc-ink:#17324d;--oc-sky:#e8f4fb;--oc-line:#c8dce9;--oc-soft:#f6f9fb;--oc-green:#24844b;--oc-red:#b33a3a;color:var(--oc-ink)}
  .oc-hero{position:relative;overflow:hidden;display:flex;justify-content:space-between;gap:24px;align-items:flex-end;padding:28px 30px;margin-bottom:18px;border:1px solid #cfe0eb;border-radius:16px;background:linear-gradient(112deg,#f8fcff 0%,#e9f5fb 70%,#d9edf7 100%)}
  .oc-hero:after{content:"";position:absolute;right:-52px;top:-88px;width:220px;height:220px;border:38px solid rgba(7,89,133,.08);border-radius:50%}
  .oc-kicker{margin:0 0 5px;color:var(--oc-blue);font-size:11px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}
  .oc-hero h1{margin:0;font-size:clamp(25px,3vw,38px);line-height:1.05;letter-spacing:-.035em;color:#12314b}
  .oc-hero p:last-child{max-width:660px;margin:10px 0 0;color:#567087;font-size:14px}
  .oc-count{position:relative;z-index:1;min-width:116px;padding:12px 16px;border-radius:12px;background:#fff;border:1px solid #c9dde9;text-align:center;box-shadow:0 8px 25px rgba(7,89,133,.08)}
  .oc-count strong{display:block;font-size:26px;color:var(--oc-blue);line-height:1}.oc-count span{font-size:11px;color:#688095}
  .oc-toolbar{display:grid;grid-template-columns:minmax(210px,1fr) minmax(240px,1.4fr) auto;gap:10px;align-items:center;margin-bottom:16px;padding:12px;border:1px solid #dbe5ec;border-radius:14px;background:#fff}
  .oc-field{position:relative}.oc-field i{position:absolute;left:13px;top:50%;transform:translateY(-50%);color:#678096}.oc-field .input{width:100%;padding-left:38px}
  .oc-actions{display:flex;gap:8px}.oc-actions .btn{white-space:nowrap}
  .oc-list{display:grid;gap:12px}
  .oc-school{border:1px solid #d5e1e9;border-radius:15px;background:#fff;box-shadow:0 5px 18px rgba(24,55,82,.045);overflow:hidden}
  .oc-school[open]{border-color:#9fc5da;box-shadow:0 10px 28px rgba(7,89,133,.09)}
  .oc-school summary{list-style:none;display:flex;align-items:center;gap:14px;padding:17px 20px;cursor:pointer;user-select:none}.oc-school summary::-webkit-details-marker{display:none}
  .oc-school summary:focus-visible{outline:3px solid rgba(2,132,199,.25);outline-offset:-3px}
  .oc-school-mark{display:grid;place-items:center;width:38px;height:38px;flex:none;border-radius:11px;background:var(--oc-blue);color:#fff;font-weight:800;letter-spacing:.03em}
  .oc-school-title{min-width:0;flex:1}.oc-school-title strong{display:block;font-size:15px;color:#16364f}.oc-school-title span{display:block;margin-top:3px;color:#718597;font-size:12px}
  .oc-chevron{color:#668196;transition:transform .2s ease}.oc-school[open] .oc-chevron{transform:rotate(180deg)}
  .oc-body{position:relative;padding:4px 20px 22px 72px;border-top:1px solid #edf2f5}.oc-body:before{content:"";position:absolute;left:39px;top:0;bottom:28px;width:2px;background:linear-gradient(var(--oc-blue),#dce9f0)}
  .oc-section{position:relative;margin-top:18px}.oc-section:before{content:"";position:absolute;left:-39px;top:13px;width:27px;height:2px;background:var(--oc-line)}
  .oc-section-head{display:flex;justify-content:space-between;gap:12px;align-items:center;margin-bottom:9px}.oc-section-head h2{margin:0;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#3e6078}.oc-section-head span{font-size:11px;color:#7890a2}
  .oc-admins{display:flex;flex-wrap:wrap;gap:8px}.oc-person{display:flex;gap:9px;align-items:center;padding:9px 11px;border:1px solid #d8e5ed;border-radius:10px;background:var(--oc-soft)}
  .oc-avatar{display:grid;place-items:center;width:30px;height:30px;border-radius:50%;background:#dceef7;color:var(--oc-blue);font-size:11px;font-weight:800}.oc-person strong{display:block;font-size:12px}.oc-person small{display:block;margin-top:2px;color:#6b8193;font-size:11px}
  .oc-dept-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:8px}.oc-dept{padding:11px 12px;border-left:3px solid #2d8fbd;border-radius:4px 10px 10px 4px;background:#f3f8fb}.oc-dept strong{font-size:12px}.oc-dept p{margin:4px 0 0;color:#678095;font-size:11px}.oc-sigla{color:var(--oc-blue);font-family:ui-monospace,SFMono-Regular,Consolas,monospace}
  .oc-table-wrap{overflow:auto;border:1px solid #dfe8ee;border-radius:11px}.oc-table{width:100%;border-collapse:collapse;min-width:720px}.oc-table th{padding:9px 11px;background:#edf5f9;color:#46657b;text-align:left;font-size:10px;letter-spacing:.06em;text-transform:uppercase}.oc-table td{padding:10px 11px;border-top:1px solid #e9eff3;font-size:12px;vertical-align:middle}.oc-table tbody tr:hover{background:#f8fbfd}
  .oc-user-name{font-weight:700;color:#1d3b53}.oc-muted{color:#748a9b}.oc-empty{margin:0;padding:12px;border:1px dashed #cadbe6;border-radius:9px;color:#71889a;font-size:12px;background:#fbfdfe}
  .oc-badge{display:inline-flex;align-items:center;gap:5px;padding:4px 8px;border-radius:999px;font-size:10px;font-weight:700;background:#eef2f5;color:#5e7180}.oc-badge:before{content:"";width:6px;height:6px;border-radius:50%;background:#8b9aa5}.oc-badge--active{background:#e7f5eb;color:var(--oc-green)}.oc-badge--active:before{background:#39a25e}.oc-badge--inactive{background:#fbeaea;color:var(--oc-red)}.oc-badge--inactive:before{background:#cf5555}
  .oc-denied{display:flex;gap:14px;align-items:flex-start;padding:22px;border:1px solid #efcaca;border-radius:14px;background:#fff7f7}.oc-denied i{font-size:24px;color:#b42323}.oc-denied h2{margin:0 0 4px;font-size:17px}.oc-denied p{margin:0;color:#7c5a5a;font-size:13px}
  [hidden]{display:none!important}
  @media(max-width:760px){.oc-hero{align-items:flex-start;padding:22px;flex-direction:column}.oc-count{display:flex;align-items:baseline;gap:7px;min-width:0}.oc-toolbar{grid-template-columns:1fr}.oc-actions{display:grid;grid-template-columns:1fr 1fr}.oc-body{padding:2px 12px 18px 34px}.oc-body:before{left:18px}.oc-section:before{left:-16px;width:11px}.oc-school summary{padding:14px}.oc-school-mark{width:34px;height:34px}.oc-table-wrap{border:0;overflow:visible}.oc-table{min-width:0}.oc-table thead{display:none}.oc-table,.oc-table tbody,.oc-table tr,.oc-table td{display:block;width:100%}.oc-table tr{margin-bottom:9px;padding:9px 11px;border:1px solid #dfe8ee;border-radius:10px;background:#fff}.oc-table td{display:grid;grid-template-columns:92px 1fr;gap:8px;padding:5px 0;border:0}.oc-table td:before{content:attr(data-label);color:#6b8192;font-size:9px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}}
  @media(prefers-reduced-motion:reduce){.oc-chevron{transition:none}}
</style>

<div class="oc-shell">
  <header class="oc-hero">
    <div>
      <p class="oc-kicker">Configuración · Estructura institucional</p>
      <h1>Organigrama de Colegios</h1>
      <p>Consulta responsables, departamentos y cuentas asociadas a cada establecimiento activo.</p>
    </div>
    <?php if ($autorizado && !$error): ?><div class="oc-count"><strong><?= count($colegios) ?></strong><span>colegios</span></div><?php endif; ?>
  </header>

  <?php if ($error): ?>
    <section class="oc-denied" role="alert">
      <i class="bi bi-shield-lock" aria-hidden="true"></i>
      <div><h2><?= $accesoDenegado ? 'Acceso restringido' : 'No se pudo cargar la información' ?></h2><p><?= e($error) ?></p></div>
    </section>
  <?php else: ?>
    <div class="oc-toolbar" aria-label="Herramientas del organigrama">
      <select class="input" id="oc-colegio" aria-label="Filtrar por colegio">
        <option value="">Todos los colegios</option>
        <?php foreach ($colegios as $colegio): ?>
          <option value="<?= (int) $colegio['id_colegio'] ?>"><?= e($colegio['nom_colegio']) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="oc-field"><i class="bi bi-search" aria-hidden="true"></i><input class="input" id="oc-buscar" type="search" placeholder="Buscar usuario por nombre, email o perfil" autocomplete="off"></div>
      <div class="oc-actions">
        <button class="btn btn-outline" type="button" id="oc-expandir"><i class="bi bi-arrows-expand"></i> Expandir</button>
        <button class="btn btn-outline" type="button" id="oc-contraer"><i class="bi bi-arrows-collapse"></i> Contraer</button>
      </div>
    </div>

    <div class="oc-list" id="oc-lista">
      <?php foreach ($colegios as $indice => $colegio):
        $palabras = preg_split('/\s+/', trim($colegio['nom_colegio']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $siglaColegio = '';
        foreach (array_slice($palabras, 0, 2) as $palabra) { $siglaColegio .= strtoupper(substr($palabra, 0, 1)); }
      ?>
        <details class="oc-school" data-school-id="<?= (int) $colegio['id_colegio'] ?>" <?= $indice === 0 ? 'open' : '' ?>>
          <summary>
            <span class="oc-school-mark"><?= e($siglaColegio ?: 'C') ?></span>
            <span class="oc-school-title"><strong><?= e($colegio['nom_colegio']) ?></strong><span><?= count($colegio['departamentos']) ?> departamentos · <?= count($colegio['usuarios']) ?> usuarios vinculados</span></span>
            <i class="bi bi-chevron-down oc-chevron" aria-hidden="true"></i>
          </summary>
          <div class="oc-body">
            <section class="oc-section">
              <div class="oc-section-head"><h2>Administración del colegio</h2><span><?= count($colegio['administradores']) ?> responsables</span></div>
              <?php if ($colegio['administradores']): ?><div class="oc-admins">
                <?php foreach ($colegio['administradores'] as $admin): ?>
                  <article class="oc-person">
                    <span class="oc-avatar"><?= e(strtoupper(substr((string) $admin['nombre'], 0, 1))) ?></span>
                    <div><strong><?= e($admin['nombre']) ?></strong><small><?= e($admin['email']) ?> · <?= e($admin['perfil'] ?: 'Sin perfil') ?></small></div>
                  </article>
                <?php endforeach; ?>
              </div><?php else: ?><p class="oc-empty">Este colegio no tiene un administrador general activo.</p><?php endif; ?>
            </section>

            <section class="oc-section">
              <div class="oc-section-head"><h2>Departamentos</h2><span><?= count($colegio['departamentos']) ?> áreas organizativas</span></div>
              <?php if ($colegio['departamentos']): ?><div class="oc-dept-grid">
                <?php foreach ($colegio['departamentos'] as $departamento): ?>
                  <article class="oc-dept"><strong><?= e($departamento['nombre_departamento']) ?> <span class="oc-sigla"><?= e($departamento['sigla'] ?: 'S/S') ?></span></strong><p>Admin: <?= e($departamento['admin_depto'] ?: 'Sin asignar') ?></p></article>
                <?php endforeach; ?>
              </div><?php else: ?><p class="oc-empty">No hay departamentos activos registrados.</p><?php endif; ?>
            </section>

            <section class="oc-section">
              <div class="oc-section-head"><h2>Usuarios vinculados</h2><span class="oc-visible-count"><?= count($colegio['usuarios']) ?> visibles</span></div>
              <?php if ($colegio['usuarios']): ?>
                <div class="oc-table-wrap"><table class="oc-table"><thead><tr><th>Nombre</th><th>Email</th><th>Perfiles</th><th>Departamento</th><th>Estado</th></tr></thead><tbody>
                <?php foreach ($colegio['usuarios'] as $usuario):
                  $estado = trim((string) ($usuario['estado'] ?? '')) ?: 'Sin estado';
                  $estadoNormalizado = strtolower($estado);
                  $esActivo = in_array($estadoNormalizado, ['activo', '1'], true);
                  $esInactivo = in_array($estadoNormalizado, ['inactivo', 'bloqueado', '0'], true);
                  $etiquetaEstado = $estadoNormalizado === '1' ? 'Activo' : ($estadoNormalizado === '0' ? 'Inactivo' : $estado);
                  $claseEstado = $esActivo ? 'oc-badge--active' : ($esInactivo ? 'oc-badge--inactive' : '');
                  $textoBusqueda = strtolower(implode(' ', [$usuario['nombre'], $usuario['email'], $usuario['perfiles'] ?? '', $usuario['nombre_departamento'] ?? '', $etiquetaEstado]));
                ?>
                  <tr data-user-search="<?= e($textoBusqueda) ?>">
                    <td data-label="Nombre" class="oc-user-name"><?= e($usuario['nombre']) ?></td>
                    <td data-label="Email"><?= e($usuario['email']) ?></td>
                    <td data-label="Perfiles" class="oc-muted"><?= e($usuario['perfiles'] ?: 'Sin perfil') ?></td>
                    <td data-label="Departamento" class="oc-muted"><?= e($usuario['nombre_departamento'] ?: 'Administración del colegio') ?></td>
                    <td data-label="Estado"><span class="oc-badge <?= $claseEstado ?>"><?= e($etiquetaEstado) ?></span></td>
                  </tr>
                <?php endforeach; ?>
                </tbody></table></div>
                <p class="oc-empty oc-no-users" hidden>No hay usuarios que coincidan con la búsqueda.</p>
              <?php else: ?><p class="oc-empty">No hay usuarios vinculados mediante jefatura_departamento.</p><?php endif; ?>
            </section>
          </div>
        </details>
      <?php endforeach; ?>
      <?php if (!$colegios): ?><p class="oc-empty">No hay colegios activos disponibles.</p><?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php if ($autorizado && !$error): ?>
<script>
(() => {
  'use strict';
  const schools = [...document.querySelectorAll('.oc-school')];
  const schoolFilter = document.getElementById('oc-colegio');
  const userSearch = document.getElementById('oc-buscar');

  const normalize = value => String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();

  function applyFilters() {
    const selectedSchool = schoolFilter.value;
    const query = normalize(userSearch.value);
    schools.forEach(school => {
      school.hidden = Boolean(selectedSchool && school.dataset.schoolId !== selectedSchool);
      const rows = [...school.querySelectorAll('[data-user-search]')];
      let visible = 0;
      rows.forEach(row => {
        const matches = !query || normalize(row.dataset.userSearch).includes(query);
        row.hidden = !matches;
        if (matches) visible++;
      });
      const counter = school.querySelector('.oc-visible-count');
      const empty = school.querySelector('.oc-no-users');
      if (counter) counter.textContent = `${visible} visibles`;
      if (empty) empty.hidden = visible !== 0;
      if (query && visible > 0 && !school.hidden) school.open = true;
    });
  }

  schoolFilter.addEventListener('change', applyFilters);
  userSearch.addEventListener('input', applyFilters);
  document.getElementById('oc-expandir').addEventListener('click', () => schools.forEach(school => { if (!school.hidden) school.open = true; }));
  document.getElementById('oc-contraer').addEventListener('click', () => schools.forEach(school => { school.open = false; }));
})();
</script>
<?php endif; ?>

<?php finalizar_layout_configuracion(); ?>
