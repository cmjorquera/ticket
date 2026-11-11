<?php
/**
 * Colaboradores: directorio de la red SEDUC (buscar, filtrar, enviar mensaje a varios).
 * API: colaboradores/ajax/colaboradores.php · Lógica: colaboradores/logica/LOGICA.txt
 */
require_once __DIR__ . '/../validar_sesion.php';

if (empty($_SESSION['csrf_colaboradores'])) {
    $_SESSION['csrf_colaboradores'] = bin2hex(random_bytes(32));
}
$csrfColaboradores = (string) $_SESSION['csrf_colaboradores'];
$nombreRemitente = trim((string) ($_SESSION['nombre'] ?? 'Usuario'));

$pagina_titulo = 'Colaboradores';
$pagina_estilos = ['colaboradores/css/colaboradores.css?v=' . (string) @filemtime(__DIR__ . '/css/colaboradores.css')];
$pagina_scripts = ['colaboradores/js/colaboradores.js?v=' . (string) @filemtime(__DIR__ . '/js/colaboradores.js')];
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-people" aria-hidden="true"></i> Colaboradores</h1>
    <p>Directorio de las personas de la red SEDUC. Busca, contacta o envía un mensaje a varias a la vez.</p>
  </div>
</div>

<div class="colab-toolbar">
  <div class="colab-search">
    <i class="bi bi-search" aria-hidden="true"></i>
    <input type="search" id="colab-buscar" placeholder="Buscar por nombre, cargo, correo o anexo…" autocomplete="off" aria-label="Buscar colaborador">
  </div>
  <select class="colab-select" id="colab-area" aria-label="Filtrar por área"><option value="">Todas las áreas</option></select>
  <select class="colab-select" id="colab-colegio" aria-label="Filtrar por colegio"><option value="">Todos los colegios</option></select>
  <div class="colab-vista" role="group" aria-label="Cambiar vista">
    <button type="button" class="on" data-vista="tarjetas" title="Tarjetas" aria-pressed="true"><i class="bi bi-grid-3x3-gap"></i></button>
    <button type="button" data-vista="lista" title="Lista" aria-pressed="false"><i class="bi bi-list-ul"></i></button>
  </div>
</div>

<p class="colab-resumen" id="colab-resumen">Cargando colaboradores…</p>

<div class="colab-grid" id="colab-grid" aria-live="polite"></div>

<div class="colab-tabla-wrap" id="colab-tabla-wrap" hidden>
  <table class="colab-tabla">
    <thead><tr><th class="col-check"></th><th>Colaborador</th><th>Cargo</th><th>Área</th><th>Colegio</th><th>Anexo</th><th>Correo</th><th></th></tr></thead>
    <tbody id="colab-tbody"></tbody>
  </table>
</div>

<div class="colab-vacio" id="colab-vacio" hidden>
  <i class="bi bi-person-x" aria-hidden="true"></i>
  <p>No hay colaboradores que coincidan con la búsqueda.</p>
</div>

<!-- Barra de seleccionados -->
<div class="barra-sel" id="barra-sel" hidden>
  <span><span class="num" id="sel-num">0</span><span id="sel-texto">seleccionados</span></span>
  <span class="avs" id="sel-avs"></span>
  <button type="button" class="b2" id="sel-limpiar">Limpiar</button>
  <button type="button" class="b1" id="sel-enviar"><i class="bi bi-send"></i> Enviar mensaje</button>
</div>

<!-- Modal enviar mensaje -->
<div class="m-ov" id="modal-mensaje" hidden>
  <div class="m" role="dialog" aria-modal="true" aria-labelledby="m-titulo">
    <div class="m-h">
      <div class="ic"><i class="bi bi-send" aria-hidden="true"></i></div>
      <div>
        <h3 id="m-titulo">Enviar mensaje</h3>
        <p>De: <?= htmlspecialchars($nombreRemitente) ?> · <span id="m-cuantos">0 destinatarios</span></p>
      </div>
      <button type="button" class="x" id="m-cerrar" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="form-mensaje">
      <div class="m-b">
        <div>
          <div class="m-l">Para</div>
          <div class="chips" id="m-chips"></div>
        </div>
        <div>
          <label class="m-l" for="m-texto">Mensaje</label>
          <textarea id="m-texto" maxlength="1000" required placeholder="Escribe tu mensaje…"></textarea>
        </div>
        <div class="m-opc">
          <label class="sw"><input type="checkbox" id="m-urgente"><span></span> Marcar como urgente</label>
          <span class="m-cont"><span id="m-largo">0</span> / 1000</span>
        </div>
        <div class="m-error" id="m-error" role="alert" hidden></div>
      </div>
      <div class="m-f">
        <button type="button" id="m-cancelar">Cancelar</button>
        <button type="submit" class="p" id="m-enviar"><i class="bi bi-send"></i> <span>Enviar</span></button>
      </div>
    </form>
  </div>
</div>

<div class="colab-toast" id="colab-toast" role="status" hidden></div>

<script>window.COLAB_CSRF = <?= json_encode($csrfColaboradores) ?>;</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
