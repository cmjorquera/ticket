<?php
declare(strict_types=1);

$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';

$usuarioId = (int) Sesion::get('id', 0);
$usuarioNombre = (string) Sesion::get('nombre', 'Usuario');
$categorias = [];
$colegios = [];
$misTickets = [];
$errorCarga = null;
$errorListado = null;

try {
    $categorias = $db->fetchAll(
        'SELECT id_categoria, nombre_categoria FROM categoria_de_ticket WHERE estado = 1 ORDER BY orden ASC, nombre_categoria ASC'
    );
    if (es_administrador_global($usuarioId, $db)) {
        $colegios = $db->fetchAll('SELECT id_colegio, nom_colegio FROM colegio WHERE estado = 1 ORDER BY nom_colegio ASC');
    } else {
        $colegios = $db->fetchAll(
            'SELECT DISTINCT c.id_colegio, c.nom_colegio
               FROM usuario_colegio uc
               JOIN colegio c ON c.id_colegio = uc.id_colegio AND c.estado = 1
              WHERE uc.id_usuario = ? AND uc.estado = 1
           ORDER BY c.nom_colegio ASC',
            [$usuarioId]
        );
    }
} catch (Throwable $ex) {
    error_log('Error al cargar formulario de ticket: ' . $ex->getMessage());
    $errorCarga = 'No fue posible cargar categorías y colegios. Verifica la instalación de las tablas de tickets.';
}

try {
    $misTickets = $db->fetchAll(
        "SELECT t.id_ticket, t.asunto, t.descripcion, t.estado, t.fecha_creacion,
                t.fecha_respuesta, t.prioridad,
                c.nombre_categoria AS categoria_nombre,
                col.nom_colegio AS colegio_nombre,
                CONCAT_WS(' ', tec.nombre, tec.apellido_paterno) AS tecnico_nombre
           FROM tickets t
           JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria
           JOIN colegio col ON col.id_colegio = t.id_colegio
      LEFT JOIN usuarios tec ON tec.id = t.id_tecnico_asignado
          WHERE t.id_usuario = ?
       ORDER BY FIELD(t.estado, 'nuevo', 'en_proceso', 'atrasado', 'resuelto', 'cerrado'),
                t.fecha_creacion DESC",
        [$usuarioId]
    );
} catch (Throwable $ex) {
    error_log('Error al listar tickets del solicitante: ' . $ex->getMessage());
    $errorListado = 'No fue posible consultar tus solicitudes en este momento.';
}

$etiquetasEstado = [
    'nuevo' => 'Nuevo',
    'en_proceso' => 'En proceso',
    'atrasado' => 'Atrasado',
    'resuelto' => 'Resuelto',
    'cerrado' => 'Cerrado',
];

$csrf = ticket_csrf_token();
iniciar_layout_configuracion('Crear ticket', 'Tickets', 'ticket');
?>
<div class="page-header">
  <div>
    <h1>Mesa de ayuda</h1>
    <p>Crea y consulta solicitudes de soporte desde un solo lugar.</p>
  </div>
  <div class="page-header-actions">
    <a class="btn btn-outline" href="ticket_asignados.php"><i class="bi bi-list-check"></i> Tickets asignados</a>
  </div>
</div>

<section class="card ticket-launch">
  <div class="ticket-launch__mark"><i class="bi bi-ticket-perforated-fill"></i><span>Soporte SEDUC</span></div>
  <div class="ticket-launch__content">
    <span class="ticket-launch__eyebrow">Nueva solicitud</span>
    <h2>¿Necesitas ayuda técnica?</h2>
    <p>Describe el problema y lo enviaremos al técnico adecuado según su categoría. El proceso toma menos de dos minutos.</p>
    <?php if ($errorCarga): ?><div class="ticket-message error" style="display:block"><?= e($errorCarga) ?></div><?php endif; ?>
    <button class="btn btn-primary ticket-launch__button" type="button" onclick="abrirModalTicket()" <?= ($errorCarga || !$categorias || !$colegios) ? 'disabled' : '' ?>>
      <i class="bi bi-plus-circle"></i> Nuevo ticket
    </button>
  </div>
  <div class="ticket-launch__flow" aria-label="Proceso de atención">
    <div><span>01</span><strong>Describe</strong><small>Cuéntanos qué ocurre</small></div>
    <div><span>02</span><strong>Asignamos</strong><small>Buscamos al especialista</small></div>
    <div><span>03</span><strong>Resolvemos</strong><small>Sigue el avance del caso</small></div>
  </div>
</section>

<?php require __DIR__ . '/componentes/bloque_tabla_usuario.php'; ?>

<div class="modal-overlay" id="modal-crear-ticket" onclick="closeModalOutside(event,'modal-crear-ticket')">
  <div class="modal ticket-create-modal" role="dialog" aria-modal="true" aria-labelledby="ticket-modal-title">
    <form id="form-ticket" novalidate>
      <div class="modal-header ticket-modal-header">
        <div>
          <span class="ticket-modal-kicker">Mesa de ayuda</span>
          <h3 id="ticket-modal-title">Crear nuevo ticket</h3>
          <p>Registra un problema o una solicitud para el equipo de soporte.</p>
        </div>
        <button class="ticket-modal-close" type="button" onclick="closeModal('modal-crear-ticket')" aria-label="Cerrar modal"><i class="bi bi-x-lg"></i></button>
      </div>

      <div class="modal-body ticket-form">
      <input type="hidden" name="accion" value="crear">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
      <div class="ticket-message" id="ticket-mensaje" role="status" aria-live="polite"></div>

      <div class="ticket-form-grid">
        <div class="form-group">
          <label class="form-label" for="ticket-usuario">Solicitante</label>
          <input class="form-input" id="ticket-usuario" value="<?= e($usuarioNombre) ?>" disabled>
        </div>
        <div class="form-group">
          <label class="form-label" for="ticket-prioridad">Prioridad</label>
          <select class="form-input" id="ticket-prioridad" name="prioridad">
            <option value="baja">Baja</option>
            <option value="media" selected>Media</option>
            <option value="alta">Alta</option>
            <option value="crítica">Crítica</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="ticket-categoria">Categoría *</label>
          <select class="form-input" id="ticket-categoria" name="categoria_id" required <?= !$categorias ? 'disabled' : '' ?>>
            <option value="">Seleccionar categoría</option>
            <?php foreach ($categorias as $categoria): ?>
              <option value="<?= (int) $categoria['id_categoria'] ?>"><?= e($categoria['nombre_categoria']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="ticket-colegio">Colegio *</label>
          <select class="form-input" id="ticket-colegio" name="colegio_id" required <?= !$colegios ? 'disabled' : '' ?>>
            <option value="">Seleccionar colegio</option>
            <?php foreach ($colegios as $colegio): ?>
              <option value="<?= (int) $colegio['id_colegio'] ?>"><?= e($colegio['nom_colegio']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="ticket-asunto">Asunto *</label>
        <input class="form-input" id="ticket-asunto" name="asunto" minlength="5" maxlength="180" placeholder="Ej.: La impresora de secretaría no responde" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="ticket-descripcion">Descripción *</label>
        <textarea class="form-input" id="ticket-descripcion" name="descripcion" minlength="10" maxlength="10000" rows="4" placeholder="Indica qué ocurrió, desde cuándo y qué intentaste hacer." required></textarea>
      </div>
      <div class="form-group ticket-files">
        <label class="form-label">Adjuntos</label>
        <input type="file" id="ticket-archivos" name="archivos[]" multiple hidden accept="image/jpeg,image/png,image/webp,application/pdf,.doc,.docx,.xls,.xlsx">
        <button class="ticket-file-picker" type="button" onclick="document.getElementById('ticket-archivos').click()">
          <i class="bi bi-paperclip"></i><span><strong>Elegir archivos</strong><small id="ticket-file-count">Sin archivos seleccionados</small></span>
        </button>
        <div class="ticket-file-list" id="ticket-file-list"></div>
        <span class="ticket-note">Hasta 5 archivos de 5 MB cada uno. Imágenes, PDF, Word o Excel.</span>
      </div>

      <div class="ticket-account-note"><i class="bi bi-shield-check"></i><span>Solo se muestran colegios asociados a tu cuenta.</span></div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline" id="ticket-limpiar" type="reset">Limpiar</button>
        <button class="btn btn-primary" id="ticket-guardar" type="submit" <?= ($errorCarga || !$categorias || !$colegios) ? 'disabled' : '' ?>><i class="bi bi-send-fill"></i> Crear ticket</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="modal-mi-ticket" onclick="closeModalOutside(event,'modal-mi-ticket')">
  <div class="modal ticket-detail-modal" role="dialog" aria-modal="true" aria-labelledby="mi-ticket-titulo">
    <div class="modal-header ticket-modal-header">
      <div><span class="ticket-modal-kicker">Detalle de solicitud</span><h3 id="mi-ticket-titulo">Ticket</h3><p id="mi-ticket-folio"></p></div>
      <button class="ticket-modal-close" type="button" onclick="closeModal('modal-mi-ticket')" aria-label="Cerrar detalle"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <div class="ticket-detail-grid" id="mi-ticket-grid"></div>
      <div class="ticket-description" id="mi-ticket-descripcion"></div>
    </div>
    <div class="modal-footer"><button class="btn btn-outline" type="button" onclick="closeModal('modal-mi-ticket')">Cerrar</button></div>
  </div>
</div>

<script>
(() => {
  const form = document.getElementById('form-ticket');
  const message = document.getElementById('ticket-mensaje');
  const button = document.getElementById('ticket-guardar');
  const files = document.getElementById('ticket-archivos');
  const fileCount = document.getElementById('ticket-file-count');
  const fileList = document.getElementById('ticket-file-list');
  if (!form || !button) return;

  window.abrirModalTicket = () => {
    message.className = 'ticket-message';
    message.textContent = '';
    openModal('modal-crear-ticket');
    setTimeout(() => document.getElementById('ticket-asunto').focus(), 80);
  };

  function renderFiles() {
    const selected = Array.from(files.files || []);
    fileCount.textContent = selected.length ? `${selected.length} archivo${selected.length === 1 ? '' : 's'} seleccionado${selected.length === 1 ? '' : 's'}` : 'Sin archivos seleccionados';
    fileList.replaceChildren();
    selected.forEach(file => {
      const item = document.createElement('div');
      item.className = 'ticket-file-item';
      const icon = document.createElement('i'); icon.className = 'bi bi-file-earmark';
      const name = document.createElement('span'); name.textContent = file.name;
      const size = document.createElement('small'); size.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
      item.append(icon, name, size); fileList.appendChild(item);
    });
  }

  files.addEventListener('change', () => {
    const selected = Array.from(files.files || []);
    if (selected.length > 5 || selected.some(file => file.size > 5 * 1024 * 1024)) {
      files.value = '';
      renderFiles();
      message.className = 'ticket-message error';
      message.textContent = selected.length > 5 ? 'Puedes adjuntar un máximo de 5 archivos.' : 'Cada archivo debe pesar como máximo 5 MB.';
      return;
    }
    message.className = 'ticket-message';
    renderFiles();
  });

  form.addEventListener('reset', () => setTimeout(renderFiles, 0));

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    let creado = false;
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-hourglass-split"></i> Creando…';
    message.className = 'ticket-message';
    try {
      const response = await fetch('../ajax/guardar_ticket.php', {method: 'POST', body: new FormData(form), credentials: 'same-origin'});
      const data = await response.json();
      if (!response.ok || !data.ok) throw new Error(data.error || 'No fue posible crear el ticket.');
      message.className = 'ticket-message ok';
      message.textContent = `${data.mensaje} Folio #${data.ticket_id}.`;
      creado = true;
      form.reset();
      setTimeout(async () => {
        closeModal('modal-crear-ticket');
        await recargarTablaUsuario();
      }, 900);
    } catch (error) {
      message.className = 'ticket-message error';
      message.textContent = error.message;
    } finally {
      if (!creado) {
        button.disabled = false;
        button.innerHTML = '<i class="bi bi-send-fill"></i> Crear ticket';
      }
    }
  });
})();

function ticketEscape(value) {
  const node = document.createElement('div');
  node.textContent = value ?? '';
  return node.innerHTML;
}

function abrirMiTicket(button) {
  const ticket = JSON.parse(button.dataset.ticket);
  const labels = {nuevo:'Nuevo', en_proceso:'En proceso', atrasado:'Atrasado', resuelto:'Resuelto', cerrado:'Cerrado'};
  document.getElementById('mi-ticket-titulo').textContent = ticket.asunto;
  document.getElementById('mi-ticket-folio').textContent = `Ticket #${ticket.id_ticket} · ${ticket.categoria_nombre}`;
  document.getElementById('mi-ticket-grid').innerHTML = `
    <div class="ticket-detail-block"><span>Estado</span><strong>${ticketEscape(labels[ticket.estado] || ticket.estado)}</strong></div>
    <div class="ticket-detail-block"><span>Prioridad</span><strong>${ticketEscape(ticket.prioridad)}</strong></div>
    <div class="ticket-detail-block"><span>Colegio</span><strong>${ticketEscape(ticket.colegio_nombre)}</strong></div>
    <div class="ticket-detail-block"><span>Técnico</span><strong>${ticketEscape(ticket.tecnico_nombre?.trim() || 'Sin asignar')}</strong></div>
    <div class="ticket-detail-block"><span>Creado</span><strong>${ticketEscape(ticket.fecha_creacion)}</strong></div>
    <div class="ticket-detail-block"><span>Última respuesta</span><strong>${ticketEscape(ticket.fecha_respuesta || 'Sin respuesta')}</strong></div>`;
  document.getElementById('mi-ticket-descripcion').textContent = ticket.descripcion;
  openModal('modal-mi-ticket');
}

function filtrarMisTickets() {
  const input = document.getElementById('mis-tickets-buscar');
  const select = document.getElementById('mis-tickets-estado');
  const date = document.getElementById('mis-tickets-fecha');
  const rows = Array.from(document.querySelectorAll('#mis-tickets-tabla tbody tr'));
  if (!input || !select || !date || !rows.length) return;
  const query = input.value.trim().toLowerCase();
  let visibles = 0;
  rows.forEach(row => {
    const visible = row.dataset.search.includes(query)
      && (!select.value || row.dataset.estado === select.value)
      && (!date.value || row.dataset.fecha === date.value);
    row.hidden = !visible;
    if (visible) visibles++;
  });
  const empty = document.getElementById('mis-tickets-sin-resultados');
  if (empty) empty.hidden = visibles > 0;
}

function enlazarFiltrosMisTickets() {
  document.getElementById('mis-tickets-buscar')?.addEventListener('input', filtrarMisTickets);
  document.getElementById('mis-tickets-estado')?.addEventListener('change', filtrarMisTickets);
  document.getElementById('mis-tickets-fecha')?.addEventListener('change', filtrarMisTickets);
}

async function recargarTablaUsuario() {
  const current = document.getElementById('ticket-tabla-usuario');
  if (!current) return;
  try {
    const response = await fetch('componentes/ajax/bloque_tabla_usuario.php', {credentials:'same-origin'});
    if (!response.ok) throw new Error('No fue posible actualizar el listado.');
    current.outerHTML = await response.text();
    enlazarFiltrosMisTickets();
  } catch (error) {
    window.location.reload();
  }
}

enlazarFiltrosMisTickets();
</script>

<?php finalizar_layout_configuracion(); ?>
