<?php
declare(strict_types=1);

/** CONFIGURACIÓN → CATEGORÍAS Y TÉCNICOS */

require_once __DIR__ . '/_inicio.php';
require_once __DIR__ . '/../clases/RolesUsuario.php';

$depth = '../';
$error = null;
$categorias = [];
$tecnicos = [];
$catalogo = [];
$puedeEditar = false;

if (empty($_SESSION['csrf_admin_categorias'])) {
    $_SESSION['csrf_admin_categorias'] = bin2hex(random_bytes(32));
}
$csrf = (string) $_SESSION['csrf_admin_categorias'];

function icono_categoria(string $icono): string
{
    if (preg_match('/class\s*=\s*["\']([^"\']+)["\']/i', $icono, $coincidencia)) {
        $icono = $coincidencia[1];
    }
    $clases = preg_split('/\s+/', trim($icono), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $clases = array_values(array_filter(
        $clases,
        static fn (string $clase): bool => preg_match('/^bi(?:-[a-z0-9-]+)?$/i', $clase) === 1
    ));
    if (!$clases) {
        return 'bi bi-tag';
    }
    if (!in_array('bi', $clases, true)) {
        array_unshift($clases, 'bi');
    }
    return implode(' ', $clases);
}

function iniciales_tecnico(string $nombre, string $apellido): string
{
    $iniciales = strtoupper(substr(trim($nombre), 0, 1) . substr(trim($apellido), 0, 1));
    return $iniciales !== '' ? $iniciales : 'T';
}

try {
    $categoriasFilas = $db->fetchAll(
        "SELECT id_categoria, nombre_categoria, icono, orden
           FROM categoria_de_ticket
          WHERE estado = 1 AND id_categoria <> 10
       ORDER BY orden ASC, id_categoria ASC"
    );
    foreach ($categoriasFilas as $fila) {
        $idCategoria = (int) $fila['id_categoria'];
        $categorias[$idCategoria] = [
            'nombre' => (string) $fila['nombre_categoria'],
            'icono' => icono_categoria((string) ($fila['icono'] ?? '')),
        ];
    }

    // Técnicos = funcionarios de Seduc del departamento Informática (modelo de roles,
    // configuracion/logica/LOGICA_ROLES.txt). Antes de la migración: área antigua 1.
    [$condTecnicos, $paramTecnicos] = (new RolesUsuario($db))->modeloActivo()
        ? RolesUsuario::condicionDepartamento('u')
        : ['u.id_area_trabajo = 1 AND u.id <> 27', []];
    $tecnicosFilas = $db->fetchAll(
        "SELECT u.id, u.nombre, u.apellido_paterno,
                GROUP_CONCAT(CONCAT(ct.id_categoria, ':', ct.id_categoria_tecnico)
                             ORDER BY ct.id_categoria SEPARATOR ',') AS asignaciones
           FROM usuarios u
      LEFT JOIN categoria_tecnico ct ON ct.id_tecnico = u.id
          WHERE {$condTecnicos} AND LOWER(u.estado) = 'activo'
       GROUP BY u.id, u.nombre, u.apellido_paterno
       ORDER BY u.nombre ASC, u.apellido_paterno ASC",
        $paramTecnicos
    );
    // Catálogo completo (activas e inactivas) con sus técnicos y tickets
    $rolesActor = new RolesUsuario($db);
    $actorId = (int) Sesion::get('id', 0);
    $puedeEditar = $rolesActor->esSuperAdmin($actorId) || $rolesActor->esDeInformaticaSeduc($actorId);
    $idsTecnicos = array_map(static fn ($f) => (int) $f['id'], $tecnicosFilas);
    $ticketsPorCategoria = array_column($db->fetchAll(
        'SELECT id_categoria_ticket AS id, COUNT(*) AS n FROM tickets WHERE estado = 1 GROUP BY id_categoria_ticket'
    ), 'n', 'id');
    $tecnicosPorCategoria = [];
    foreach ($db->fetchAll('SELECT id_categoria, id_tecnico FROM categoria_tecnico') as $r) {
        if (in_array((int) $r['id_tecnico'], $idsTecnicos, true)) {
            $tecnicosPorCategoria[(int) $r['id_categoria']][] = (int) $r['id_tecnico'];
        }
    }
    foreach ($db->fetchAll(
        'SELECT id_categoria, nombre_categoria, abreviacion, icono, orden, estado
           FROM categoria_de_ticket ORDER BY orden ASC, id_categoria ASC'
    ) as $f) {
        $idC = (int) $f['id_categoria'];
        $catalogo[] = [
            'id' => $idC,
            'nombre' => (string) $f['nombre_categoria'],
            'abreviacion' => (string) $f['abreviacion'],
            'icono' => icono_categoria((string) ($f['icono'] ?? '')),
            'estado' => (int) $f['estado'],
            'tickets' => (int) ($ticketsPorCategoria[$idC] ?? 0),
            'tecnicos' => $tecnicosPorCategoria[$idC] ?? [],
            'otros' => $idC === 10,
        ];
    }

    foreach ($tecnicosFilas as $fila) {
        $asignaciones = [];
        foreach (array_filter(explode(',', (string) ($fila['asignaciones'] ?? ''))) as $asignacion) {
            [$idCategoria, $idRelacion] = array_pad(array_map('intval', explode(':', $asignacion, 2)), 2, 0);
            if ($idCategoria > 0 && $idRelacion > 0) {
                $asignaciones[$idCategoria] = $idRelacion;
            }
        }
        $tecnicos[] = [
            'id' => (int) $fila['id'],
            'nombre' => (string) $fila['nombre'],
            'apellido' => (string) ($fila['apellido_paterno'] ?? ''),
            'asignaciones' => $asignaciones,
        ];
    }
} catch (Throwable $ex) {
    error_log('Error en admin_categorias.php: ' . $ex->getMessage());
    $error = 'No fue posible cargar las categorías y los técnicos en este momento.';
    $categorias = [];
    $tecnicos = [];
}

iniciar_layout_configuracion('Categorías y técnicos', 'Categorías', 'admin_categorias');
?>

<div class="page-header">
  <?php if ($puedeEditar && !$error): ?>
  <div class="page-header-actions" style="order:2"><button type="button" class="btn btn-primary" id="btnNuevaCategoria"><i class="bi bi-plus-lg"></i> Nueva categoría</button></div>
  <?php endif; ?>
  <div><h1><i class="bi bi-person-gear" aria-hidden="true"></i> Categorías y técnicos</h1><p>Define qué tipo de tickets puede recibir cada técnico (funcionarios del departamento de Informática de Seduc).</p></div>
</div>

<?php if ($error): ?>
  <div class="card"><div class="card-body ac-alert ac-alert--danger"><i class="bi bi-exclamation-circle"></i><span><?= e($error) ?></span></div></div>
<?php else: ?>
  <div class="stat-grid ac-stats">
    <div class="stat-card"><div class="row"><div><div class="label">Categorías activas</div><div class="value"><?= count($categorias) ?></div></div><div class="stat-icon icon-blue"><i class="bi bi-tags-fill"></i></div></div></div>
    <div class="stat-card"><div class="row"><div><div class="label">Técnicos activos</div><div class="value"><?= count($tecnicos) ?></div></div><div class="stat-icon icon-green"><i class="bi bi-people-fill"></i></div></div></div>
  </div>

  <section class="card ac-panel">
    <div class="card-header">
      <div><h2 class="card-title">Asignación de categorías</h2><p class="card-desc">Selecciona los chips y guarda los cambios.</p></div>
      <span class="ac-dirty-count" id="acDirtyCount" hidden></span>
    </div>
    <?php if (!$categorias || !$tecnicos): ?>
      <div class="card-body ac-alert ac-alert--warning"><i class="bi bi-exclamation-triangle"></i><span><?= !$categorias ? 'No hay categorías activas disponibles.' : 'No hay técnicos activos disponibles.' ?></span></div>
    <?php else: ?>
      <div class="ac-legend" aria-label="Leyenda">
        <span><span class="cat-chip cat-chip--on cat-chip--legend"><i class="bi bi-check-circle-fill"></i> Asignada</span> Recibe tickets</span>
        <span><span class="cat-chip cat-chip--off cat-chip--legend"><i class="bi bi-circle"></i> Sin asignar</span> No recibe tickets</span>
      </div>
      <div class="ac-grid" id="acGrid">
        <?php foreach ($tecnicos as $tecnico): ?>
          <?php $asignadas = count(array_intersect_key($tecnico['asignaciones'], $categorias)); ?>
          <article class="ac-card" data-tecnico-id="<?= $tecnico['id'] ?>">
            <header class="ac-card__header">
              <div class="ac-card__avatar" aria-hidden="true"><?= e(iniciales_tecnico($tecnico['nombre'], $tecnico['apellido'])) ?></div>
              <div class="ac-card__identity"><h3 class="ac-card__name"><?= e(trim($tecnico['nombre'] . ' ' . $tecnico['apellido'])) ?></h3><p class="ac-card__meta">Técnico · Informática</p></div>
              <span class="ac-badge-count"><span class="cat-count"><?= $asignadas ?></span>/<?= count($categorias) ?></span>
            </header>
            <div class="ac-card__body"><div class="cat-chips">
              <?php foreach ($categorias as $idCategoria => $categoria): ?>
                <?php $asignada = isset($tecnico['asignaciones'][$idCategoria]); $idRelacion = $asignada ? $tecnico['asignaciones'][$idCategoria] : 0; ?>
                <button type="button" class="cat-chip <?= $asignada ? 'cat-chip--on' : 'cat-chip--off' ?>"
                        data-id-categoria="<?= $idCategoria ?>" data-id-categoria-tecnico="<?= $idRelacion ?>"
                        data-inicial="<?= $asignada ? '1' : '0' ?>" aria-pressed="<?= $asignada ? 'true' : 'false' ?>">
                  <i class="<?= e($categoria['icono']) ?> cat-chip__category-icon" aria-hidden="true"></i>
                  <span><?= e($categoria['nombre']) ?></span>
                  <i class="bi <?= $asignada ? 'bi-check-circle-fill' : 'bi-circle' ?> cat-chip__state-icon" aria-hidden="true"></i>
                </button>
              <?php endforeach; ?>
            </div></div>
          </article>
        <?php endforeach; ?>
      </div>
      <div class="ac-action-bar">
        <div><p class="ac-hint"><i class="bi bi-mouse2"></i> Los cambios se aplican al presionar Guardar.</p><p class="ac-save-status" id="acSaveStatus" role="status" aria-live="polite"></p></div>
        <button type="button" class="btn btn-primary" id="acGuardar" disabled><i class="bi bi-floppy-fill"></i><span>Guardar cambios</span></button>
      </div>
    <?php endif; ?>
  </section>
<!-- Catálogo de categorías: crear, editar, activar/desactivar y ordenar -->
  <section class="card cc-panel" id="ccPanel">
    <div class="card-header">
      <div><h2 class="card-title">Catálogo de categorías</h2><p class="card-desc">Las categorías activas aparecen al crear un ticket, en este orden.</p></div>
      <label class="cc-inactivas"><input type="checkbox" id="ccVerInactivas"> Mostrar inactivas</label>
    </div>
    <div class="cc-tabla-wrap">
      <table class="cc-tabla">
        <thead><tr><th class="cc-col-orden">Orden</th><th>Categoría</th><th>Abreviación</th><th>Técnicos</th><th class="cc-num">Tickets</th><th>Estado</th><?php if ($puedeEditar): ?><th class="cc-acc">Acciones</th><?php endif; ?></tr></thead>
        <tbody id="ccFilas"></tbody>
      </table>
    </div>
  </section>

  <dialog class="cc-dialog" id="dlgCategoria" aria-labelledby="ccTitulo">
    <form class="cc-dialog__box" id="formCategoria" novalidate autocomplete="off">
      <header class="cc-dialog__head">
        <h2 id="ccTitulo">Nueva categoría</h2>
        <button type="button" class="btn-icon" data-cc-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
      </header>
      <div class="cc-dialog__cuerpo">
        <div class="cc-vista" aria-hidden="true"><span class="cc-vista__chip"><i class="bi bi-tag" id="ccVistaIcono"></i> <span id="ccVistaNombre">Nueva categoría</span></span><small>Así se verá</small></div>
        <div class="cc-campos">
          <label class="cc-campo"><span>Nombre *</span><input name="nombre" maxlength="80" required placeholder="Ej: Proyectores"></label>
          <label class="cc-campo"><span>Abreviación</span><input name="abreviacion" maxlength="50" placeholder="Ej: Proyect."></label>
        </div>
        <div class="cc-campo">
          <span>Ícono</span>
          <div class="cc-iconos" id="ccIconos" role="radiogroup" aria-label="Ícono"></div>
          <input name="icono" id="ccIcono" maxlength="45" placeholder="o escribe uno de Bootstrap Icons, ej: bi-projector">
        </div>
        <div class="cc-campo">
          <span>Técnicos que la atienden</span>
          <div class="cc-tecnicos" id="ccTecnicos"></div>
          <small class="cc-ayuda" id="ccTecAyuda">Los tickets de esta categoría llegan al técnico con menos tickets abiertos.</small>
        </div>
        <p class="cc-error" id="ccError" role="alert" hidden></p>
      </div>
      <footer class="cc-dialog__pie">
        <button type="button" class="btn btn-outline" data-cc-cerrar>Cancelar</button>
        <button type="submit" class="btn btn-primary" id="ccGuardar"><i class="bi bi-check2"></i> Guardar</button>
      </footer>
    </form>
  </dialog>
<?php endif; ?>


<style>
.ac-stats{margin-bottom:16px}.ac-panel{overflow:hidden}.ac-alert{display:flex;align-items:center;gap:10px}.ac-alert--danger{color:var(--danger);background:var(--danger-light)}.ac-alert--warning{color:var(--warning);background:var(--warning-light)}
.ac-dirty-count{padding:4px 9px;border-radius:99px;background:var(--yellow-light);color:var(--warning);font-size:11px;font-weight:600}.ac-legend{display:flex;flex-wrap:wrap;gap:20px;padding:14px 20px;background:var(--bg);border-bottom:1px solid var(--border);font-size:12px;color:var(--muted)}.ac-legend>span{display:flex;align-items:center;gap:8px}
.ac-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(330px,1fr));gap:16px;padding:20px}.ac-card{border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;background:var(--card);transition:border-color var(--transition),box-shadow var(--transition)}.ac-card.is-dirty{border-color:var(--yellow);box-shadow:0 0 0 2px var(--yellow-light)}
.ac-card__header{display:flex;align-items:center;gap:12px;padding:14px 16px;background:#FAFAFA;border-bottom:1px solid var(--border)}.ac-card__avatar{width:42px;height:42px;border-radius:50%;display:grid;place-items:center;flex:0 0 auto;background:var(--blue-light);color:var(--blue);font-size:12px;font-weight:700}.ac-card__identity{min-width:0}.ac-card__name{font-size:14px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.ac-card__meta{font-size:11px;color:var(--muted)}.ac-badge-count{margin-left:auto;padding:3px 9px;border-radius:99px;background:var(--blue);color:#fff;font-size:11px;font-weight:700}.ac-card__body{padding:14px}.cat-chips{display:flex;flex-wrap:wrap;gap:7px}
.cat-chip{display:inline-flex;align-items:center;gap:6px;min-height:32px;padding:6px 10px;border:1px solid var(--border);border-radius:99px;font:500 12px/1.2 inherit;cursor:pointer;transition:transform .15s ease,box-shadow .15s ease,background .15s ease,border-color .15s ease;color:var(--fg)}.cat-chip:hover{transform:translateY(-1px);box-shadow:var(--shadow-card)}.cat-chip:focus-visible{outline:2px solid var(--blue);outline-offset:2px}.cat-chip--on{background:var(--success);border-color:var(--success);color:#fff}.cat-chip--off{background:var(--card);border-color:var(--border);color:var(--fg)}.cat-chip--legend{cursor:default;min-height:26px;padding:4px 9px}.cat-chip--legend:hover{transform:none;box-shadow:none}.cat-chip__state-icon{font-size:10px;opacity:.9}.cat-chip__category-icon{font-size:13px}
.ac-action-bar{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 20px;background:#FAFAFA;border-top:1px solid var(--border)}.ac-hint,.ac-save-status{font-size:12px;color:var(--muted)}.ac-save-status{min-height:18px;margin-top:2px}.ac-save-status.is-success{color:var(--success)}.ac-save-status.is-error{color:var(--danger)}.ac-action-bar .btn:disabled{opacity:.45;cursor:not-allowed}
@media(max-width:768px){.ac-grid{grid-template-columns:1fr;padding:14px}.ac-action-bar{align-items:stretch;flex-direction:column}.ac-action-bar .btn{justify-content:center;width:100%}}
@media(prefers-reduced-motion:reduce){.ac-card,.cat-chip{transition:none}.cat-chip:hover{transform:none}}
</style>

<script>
(() => {
  'use strict';
  const grid = document.getElementById('acGrid');
  const saveButton = document.getElementById('acGuardar');
  const dirtyCount = document.getElementById('acDirtyCount');
  const saveStatus = document.getElementById('acSaveStatus');
  if (!grid || !saveButton) return;

  const isAssigned = chip => chip.classList.contains('cat-chip--on');
  function updateCard(card) {
    const chips = [...card.querySelectorAll('.cat-chip')];
    const changed = chips.some(chip => Number(chip.dataset.inicial) !== Number(isAssigned(chip)));
    card.classList.toggle('is-dirty', changed);
    card.querySelector('.cat-count').textContent = String(chips.filter(isAssigned).length);
  }
  function updateSummary() {
    const changed = grid.querySelectorAll('.ac-card.is-dirty').length;
    saveButton.disabled = changed === 0;
    dirtyCount.hidden = changed === 0;
    dirtyCount.textContent = `${changed} técnico${changed === 1 ? '' : 's'} con cambios`;
    if (changed) saveStatus.textContent = '';
  }

  grid.addEventListener('click', event => {
    const chip = event.target.closest('.cat-chip');
    if (!chip) return;
    const assigned = !isAssigned(chip);
    chip.classList.toggle('cat-chip--on', assigned);
    chip.classList.toggle('cat-chip--off', !assigned);
    chip.setAttribute('aria-pressed', String(assigned));
    const stateIcon = chip.querySelector('.cat-chip__state-icon');
    stateIcon.classList.toggle('bi-check-circle-fill', assigned);
    stateIcon.classList.toggle('bi-circle', !assigned);
    updateCard(chip.closest('.ac-card'));
    updateSummary();
  });

  saveButton.addEventListener('click', async () => {
    const cambios = [...grid.querySelectorAll('.ac-card.is-dirty')].map(card => ({
      id_tecnico: Number(card.dataset.tecnicoId),
      categorias: [...card.querySelectorAll('.cat-chip')]
        .filter(chip => Number(chip.dataset.inicial) !== Number(isAssigned(chip)))
        .map(chip => ({id_categoria: Number(chip.dataset.idCategoria), asignada: isAssigned(chip), id_categoria_tecnico: Number(chip.dataset.idCategoriaTecnico || 0)})),
    }));
    if (!cambios.length) return;

    const label = saveButton.querySelector('span');
    const originalLabel = label.textContent;
    saveButton.disabled = true;
    label.textContent = 'Guardando…';
    saveStatus.className = 'ac-save-status';
    saveStatus.textContent = 'Aplicando cambios…';
    try {
      const response = await fetch('ajax/guardar_categorias_tecnicos.php', {
        method: 'POST', credentials: 'same-origin',
        headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
        body: JSON.stringify({csrf: <?= json_encode($csrf) ?>, cambios}),
      });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message || 'No fue posible guardar los cambios.');
      grid.querySelectorAll('.cat-chip').forEach(chip => { chip.dataset.inicial = isAssigned(chip) ? '1' : '0'; });
      grid.querySelectorAll('.ac-card').forEach(updateCard);
      updateSummary();
      saveStatus.className = 'ac-save-status is-success';
      saveStatus.textContent = data.message;
    } catch (error) {
      updateSummary();
      saveStatus.className = 'ac-save-status is-error';
      saveStatus.textContent = error.message || 'No fue posible guardar los cambios.';
    } finally {
      label.textContent = originalLabel;
      saveButton.disabled = grid.querySelectorAll('.ac-card.is-dirty').length === 0;
    }
  });
})();
</script>

<?php if (!$error): ?>
<script>window.CAT_DATA = <?= json_encode([
    'csrf' => $csrf,
    'puedeEditar' => $puedeEditar,
    'catalogo' => $catalogo,
    'tecnicos' => array_map(static fn ($t) => ['id' => $t['id'], 'nombre' => trim($t['nombre'] . ' ' . $t['apellido']),
        'iniciales' => iniciales_tecnico($t['nombre'], $t['apellido'])], $tecnicos),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<link rel="stylesheet" href="css/categorias.css?v=<?= (int) @filemtime(__DIR__ . '/css/categorias.css') ?>">
<script src="js/categorias_catalogo.js?v=<?= (int) @filemtime(__DIR__ . '/js/categorias_catalogo.js') ?>"></script>
<?php endif; ?>
<?php finalizar_layout_configuracion(); ?>
