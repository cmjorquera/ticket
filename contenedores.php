<?php
declare(strict_types=1);

/** PÁGINA DE USUARIO → MIS CONTENEDORES */

require_once __DIR__ . '/configuracion/_inicio.php';

$depth = '';
$usuarioId = (int) Sesion::get('id', 0);
$usuarioNombre = (string) Sesion::get('nombre', 'Usuario');
$contenedores = [];
$error = null;

if (empty($_SESSION['csrf_contenedores'])) {
    $_SESSION['csrf_contenedores'] = bin2hex(random_bytes(32));
}
$csrf = (string) $_SESSION['csrf_contenedores'];

function contenedor_imagen_url(string $imagen): string
{
    $imagen = str_replace('\\', '/', trim($imagen));
    if ($imagen === '' || str_contains($imagen, '..') || str_starts_with($imagen, '/')) {
        $imagen = 'google.png';
    }

    $base = __DIR__ . '/imagenes';
    $candidatos = [$imagen];
    if (!str_starts_with($imagen, 'imagenes/')) {
        $candidatos[] = 'imagenes/' . $imagen;
    }
    $candidatos[] = 'imagenes/google.png';
    $candidatos[] = 'google.png';

    foreach (array_unique($candidatos) as $relativa) {
        $partes = array_values(array_filter(explode('/', $relativa), static fn (string $parte): bool => $parte !== ''));
        $ruta = $base . '/' . implode('/', $partes);
        if (is_file($ruta)) {
            return 'imagenes/' . implode('/', array_map('rawurlencode', $partes));
        }
    }
    return '';
}

function contenedor_url(string $url): string
{
    $url = trim($url);
    if ($url === '') {
        return '#';
    }
    if (!preg_match('~^https?://~i', $url)) {
        $url = 'https://' . ltrim($url, '/');
    }
    return filter_var($url, FILTER_VALIDATE_URL) ? $url : '#';
}

try {
    $contenedores = $db->fetchAll(
        'SELECT id, nombre, url_, imagen FROM contenedor WHERE id_usuario = ? ORDER BY id ASC',
        [$usuarioId]
    );
} catch (Throwable $ex) {
    error_log('Error al obtener contenedores: ' . $ex->getMessage());
    $error = 'No fue posible cargar tus contenedores en este momento.';
}

iniciar_layout_configuracion('Mis contenedores', 'Contenedores', 'contenedores');
?>

<div class="page-header">
  <div><h1><i class="bi bi-grid" aria-hidden="true"></i> Mis contenedores</h1><p>Accesos directos configurados para <?= e($usuarioNombre) ?>.</p></div>
  <div class="page-header-actions"><button type="button" class="btn btn-primary" id="btn-nuevo-contenedor"><i class="bi bi-plus-circle"></i> Nuevo contenedor</button></div>
</div>

<?php if ($error): ?>
  <div class="card"><div class="card-body cont-alert cont-alert--danger"><i class="bi bi-exclamation-circle"></i><span><?= e($error) ?></span></div></div>
<?php else: ?>
  <div class="contenedores-toolbar">
    <div class="input-icon-wrap contenedores-search"><i class="bi bi-search icon"></i><input type="search" class="input" id="tile-search" placeholder="Buscar contenedor…" autocomplete="off"></div>
    <span class="toolbar-count"><strong id="tile-count"><?= count($contenedores) ?></strong> accesos</span>
  </div>

  <section class="contenedores-empty <?= $contenedores ? 'is-hidden' : '' ?>" id="contenedores-empty">
    <div class="contenedores-empty__icon"><i class="bi bi-window-stack"></i></div>
    <h2>Sin contenedores aún</h2><p>Agrega tu primer acceso directo con el botón Nuevo contenedor.</p>
  </section>

  <div class="tiles-grid" id="tiles-grid">
    <?php foreach ($contenedores as $contenedor): ?>
      <?php
      $id = (int) $contenedor['id'];
      $nombre = (string) ($contenedor['nombre'] ?? 'Sin nombre');
      $urlOriginal = (string) ($contenedor['url_'] ?? '');
      $imagenOriginal = (string) ($contenedor['imagen'] ?? '');
      $href = contenedor_url($urlOriginal);
      $src = contenedor_imagen_url($imagenOriginal);
      ?>
      <article class="tile-card" data-id="<?= $id ?>" data-nombre="<?= e($nombre) ?>">
        <div class="tile-actions">
          <button type="button" class="tile-menu-btn" aria-label="Opciones de <?= e($nombre) ?>" aria-expanded="false"><i class="bi bi-three-dots-vertical"></i></button>
          <div class="tile-menu">
            <button type="button" class="tile-menu-item" data-action="editar" data-id="<?= $id ?>" data-nombre="<?= e($nombre) ?>" data-url="<?= e($urlOriginal) ?>" data-imagen="<?= e($imagenOriginal) ?>" data-imagen-url="<?= e($src) ?>"><i class="bi bi-pencil"></i> Editar</button>
            <button type="button" class="tile-menu-item tile-menu-item--danger" data-action="eliminar" data-id="<?= $id ?>" data-nombre="<?= e($nombre) ?>"><i class="bi bi-trash"></i> Eliminar</button>
          </div>
        </div>
        <a href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer" class="tile-link" title="Abrir <?= e($nombre) ?>">
          <div class="tile-img-wrap">
            <?php if ($src !== ''): ?><img src="<?= e($src) ?>" alt="" loading="lazy"><?php else: ?><i class="bi bi-link-45deg tile-placeholder" aria-hidden="true"></i><?php endif; ?>
          </div>
          <h2 class="tile-name"><?= e($nombre) ?></h2>
          <span class="tile-host"><?= e((string) (parse_url($href, PHP_URL_HOST) ?: 'Acceso directo')) ?></span>
        </a>
      </article>
    <?php endforeach; ?>
  </div>
  <p class="contenedores-no-results is-hidden" id="contenedores-no-results">No hay contenedores que coincidan con la búsqueda.</p>
<?php endif; ?>

<div class="modal-overlay" id="modal-contenedor" onclick="closeModalOutside(event, 'modal-contenedor')">
  <div class="modal" style="max-width:480px" role="dialog" aria-modal="true" aria-labelledby="modal-contenedor-titulo">
    <div class="modal-header"><h3 id="modal-contenedor-titulo">Nuevo contenedor</h3><p>Agrega un acceso directo a tu panel.</p></div>
    <div class="modal-body">
      <div class="cont-form-status" id="form-status" role="alert" hidden></div>
      <form id="form-contenedor" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>"><input type="hidden" name="accion" id="form-accion" value="guardar"><input type="hidden" name="id" id="form-id">
        <div class="form-group"><label class="form-label" for="form-nombre">Nombre *</label><input class="form-input" name="nombre" id="form-nombre" placeholder="Ej: SIGE" maxlength="100" required></div>
        <div class="form-group"><label class="form-label" for="form-url">URL *</label><input class="form-input" name="url" id="form-url" type="text" inputmode="url" placeholder="https://…" maxlength="2048" required></div>
        <div class="form-group"><label class="form-label" for="form-imagen-file">Imagen</label><input class="form-input cont-file-input" name="imagen_file" id="form-imagen-file" type="file" accept="image/jpeg,image/png,image/gif,image/webp"><small class="cont-field-help">JPG, PNG, GIF o WEBP, máximo 2 MB.</small></div>
        <div class="cont-preview" id="img-preview-wrap" hidden><img id="img-preview" src="" alt="Vista previa"><span id="img-preview-name"></span></div>
      </form>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('modal-contenedor')">Cancelar</button><button type="submit" form="form-contenedor" class="btn btn-primary" id="btn-guardar-contenedor">Guardar</button></div>
  </div>
</div>

<div class="modal-overlay" id="modal-eliminar-contenedor" onclick="closeModalOutside(event, 'modal-eliminar-contenedor')">
  <div class="modal" style="max-width:420px" role="dialog" aria-modal="true" aria-labelledby="modal-eliminar-titulo">
    <div class="modal-header"><h3 id="modal-eliminar-titulo">¿Eliminar contenedor?</h3><p id="modal-eliminar-nombre"></p></div>
    <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('modal-eliminar-contenedor')">Cancelar</button><button type="button" class="btn btn-danger" id="btn-confirmar-eliminar">Sí, eliminar</button></div>
  </div>
</div>

<style>
.contenedores-toolbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px}.contenedores-search{width:min(100%,380px)}.cont-alert{display:flex;align-items:center;gap:10px}.cont-alert--danger{color:var(--danger);background:var(--danger-light)}
.tiles-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:16px}.tile-card{position:relative;min-width:0;border:1px solid var(--border);border-radius:var(--radius);background:var(--card);box-shadow:var(--shadow-card);text-align:center;transition:border-color var(--transition),box-shadow var(--transition),transform var(--transition)}.tile-card:hover{border-color:var(--blue);box-shadow:var(--shadow-elevated);transform:translateY(-2px)}.tile-link{display:flex;min-height:164px;flex-direction:column;align-items:center;padding:22px 12px 16px;color:var(--fg);text-decoration:none}.tile-img-wrap{width:60px;height:60px;margin-bottom:11px;display:grid;place-items:center;overflow:hidden;border:1px solid var(--border);border-radius:14px;background:var(--bg)}.tile-img-wrap img{width:44px;height:44px;object-fit:contain}.tile-placeholder{font-size:26px;color:var(--blue)}.tile-name{max-width:100%;font-size:13px;font-weight:600;line-height:1.3;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.tile-host{max-width:100%;margin-top:3px;font-size:10px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.tile-actions{position:absolute;right:7px;top:7px;z-index:5}.tile-menu-btn{width:28px;height:28px;display:grid;place-items:center;border:0;border-radius:6px;background:var(--card);color:var(--muted);opacity:0;transition:opacity var(--transition),background var(--transition),color var(--transition)}.tile-card:hover .tile-menu-btn,.tile-menu-btn:focus-visible,.tile-menu-btn[aria-expanded=true]{opacity:1}.tile-menu-btn:hover{background:var(--bg);color:var(--fg)}.tile-menu{position:absolute;right:0;top:34px;display:none;min-width:136px;padding:4px;border:1px solid var(--border);border-radius:var(--radius);background:var(--card);box-shadow:var(--shadow-elevated)}.tile-menu.open{display:block}.tile-menu-item{display:flex;align-items:center;gap:8px;width:100%;padding:8px 10px;border:0;border-radius:5px;background:none;color:var(--fg);font-size:12px;text-align:left}.tile-menu-item:hover{background:var(--bg)}.tile-menu-item--danger{color:var(--danger)}
.contenedores-empty{text-align:center;padding:64px 24px}.contenedores-empty__icon{width:64px;height:64px;margin:0 auto 16px;display:grid;place-items:center;border-radius:16px;background:var(--blue-light);color:var(--blue);font-size:27px}.contenedores-empty h2{font-size:15px}.contenedores-empty p,.contenedores-no-results{margin-top:6px;color:var(--muted);font-size:13px}.contenedores-no-results{text-align:center;padding:40px}.is-hidden{display:none!important}
.cont-form-status{padding:10px 12px;margin-bottom:14px;border:1px solid rgba(192,57,43,.2);border-radius:var(--radius);background:var(--danger-light);color:var(--danger);font-size:12px}.cont-file-input{padding:7px;border-style:dashed}.cont-field-help{font-size:11px;color:var(--muted)}.cont-preview{display:flex;align-items:center;gap:10px;padding:10px;border:1px solid var(--border);border-radius:var(--radius);background:var(--bg);font-size:11px;color:var(--muted)}.cont-preview[hidden]{display:none}.cont-preview img{width:52px;height:52px;border-radius:8px;object-fit:contain;background:var(--card)}
@media(max-width:600px){.tiles-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.contenedores-toolbar{align-items:flex-start;flex-direction:column}.tile-menu-btn{opacity:1}}
@media(prefers-reduced-motion:reduce){.tile-card{transition:none}.tile-card:hover{transform:none}}
</style>

<script>
(() => {
  'use strict';
  const grid = document.getElementById('tiles-grid');
  const search = document.getElementById('tile-search');
  const form = document.getElementById('form-contenedor');
  const status = document.getElementById('form-status');
  const saveButton = document.getElementById('btn-guardar-contenedor');
  const previewWrap = document.getElementById('img-preview-wrap');
  const preview = document.getElementById('img-preview');
  const previewName = document.getElementById('img-preview-name');
  let deleteId = 0;

  function showError(message) { status.textContent = message; status.hidden = false; }
  function resetForm() { form.reset(); document.getElementById('form-accion').value = 'guardar'; document.getElementById('form-id').value = ''; status.hidden = true; previewWrap.hidden = true; preview.removeAttribute('src'); }

  document.getElementById('btn-nuevo-contenedor').addEventListener('click', () => {
    resetForm(); document.getElementById('modal-contenedor-titulo').textContent = 'Nuevo contenedor'; openModal('modal-contenedor'); document.getElementById('form-nombre').focus();
  });
  search?.addEventListener('input', () => {
    const query = search.value.trim().toLocaleLowerCase('es'); let visible = 0;
    const cards = [...(grid?.querySelectorAll('.tile-card') || [])]; if (!cards.length) return;
    cards.forEach(card => { const show = !query || card.dataset.nombre.toLocaleLowerCase('es').includes(query); card.classList.toggle('is-hidden', !show); if (show) visible++; });
    document.getElementById('tile-count').textContent = String(visible); document.getElementById('contenedores-no-results').classList.toggle('is-hidden', visible > 0);
  });
  grid?.addEventListener('click', event => {
    const menuButton = event.target.closest('.tile-menu-btn');
    if (menuButton) { const menu = menuButton.nextElementSibling; document.querySelectorAll('.tile-menu.open').forEach(item => { if (item !== menu) item.classList.remove('open'); }); menu.classList.toggle('open'); menuButton.setAttribute('aria-expanded', String(menu.classList.contains('open'))); return; }
    const action = event.target.closest('[data-action]'); if (!action) return;
    if (action.dataset.action === 'editar') { resetForm(); document.getElementById('modal-contenedor-titulo').textContent = 'Editar contenedor'; document.getElementById('form-accion').value = 'actualizar'; document.getElementById('form-id').value = action.dataset.id; document.getElementById('form-nombre').value = action.dataset.nombre; document.getElementById('form-url').value = action.dataset.url; if (action.dataset.imagenUrl) { preview.src = action.dataset.imagenUrl; previewName.textContent = 'Imagen actual'; previewWrap.hidden = false; } openModal('modal-contenedor'); }
    if (action.dataset.action === 'eliminar') { deleteId = Number(action.dataset.id); document.getElementById('modal-eliminar-nombre').textContent = action.dataset.nombre; openModal('modal-eliminar-contenedor'); }
  });
  document.addEventListener('click', event => { if (!event.target.closest('.tile-actions')) document.querySelectorAll('.tile-menu.open').forEach(menu => menu.classList.remove('open')); });
  document.getElementById('form-imagen-file').addEventListener('change', event => { const file = event.target.files[0]; if (!file) { previewWrap.hidden = true; return; } if (file.size > 2 * 1024 * 1024) { event.target.value = ''; showError('La imagen no puede superar 2 MB.'); return; } preview.src = URL.createObjectURL(file); previewName.textContent = file.name; previewWrap.hidden = false; });
  form.addEventListener('submit', async event => {
    event.preventDefault(); if (!form.reportValidity()) return; saveButton.disabled = true; saveButton.textContent = 'Guardando…'; status.hidden = true;
    try { const response = await fetch('ajax/contenedores_ajax.php', {method:'POST', credentials:'same-origin', body:new FormData(form)}); const data = await response.json(); if (!response.ok || !data.ok) throw new Error(data.error || 'No fue posible guardar.'); location.reload(); }
    catch (error) { showError(error.message); saveButton.disabled = false; saveButton.textContent = 'Guardar'; }
  });
  document.getElementById('btn-confirmar-eliminar').addEventListener('click', async event => {
    const button = event.currentTarget; button.disabled = true; button.textContent = 'Eliminando…';
    const data = new FormData(); data.append('accion', 'eliminar'); data.append('id', String(deleteId)); data.append('csrf', <?= json_encode($csrf) ?>);
    try { const response = await fetch('ajax/contenedores_ajax.php', {method:'POST', credentials:'same-origin', body:data}); const result = await response.json(); if (!response.ok || !result.ok) throw new Error(result.error || 'No fue posible eliminar.'); location.reload(); }
    catch (error) { button.disabled = false; button.textContent = 'Sí, eliminar'; document.getElementById('modal-eliminar-nombre').textContent = error.message; }
  });
})();
</script>

<?php finalizar_layout_configuracion(); ?>
