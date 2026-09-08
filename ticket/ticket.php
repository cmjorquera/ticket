<?php
declare(strict_types=1);

$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';

$usuarioId = (int) Sesion::get('id', 0);
$usuarioNombre = (string) Sesion::get('nombre', 'Usuario');
$categorias = [];
$colegioSesion = false;
$puedeElegirSolicitante = false;
$misTickets = [];
$errorCarga = null;
$errorListado = null;
$idPagActual = FuncionesTicket::ROL_USUARIO;


try {
    $categorias = $db->fetchAll(
        'SELECT id_categoria, nombre_categoria FROM categoria_de_ticket WHERE estado = 1 ORDER BY orden ASC, nombre_categoria ASC'
    );
    $colegioSesion = $db->fetchOne(
        'SELECT c.id_colegio, c.nom_colegio
           FROM usuario_colegio uc
           JOIN colegio c ON c.id_colegio = uc.id_colegio AND c.estado = 1
          WHERE uc.id_usuario = ? AND uc.estado = 1
       ORDER BY uc.id_colegio ASC LIMIT 1',
        [$usuarioId]
    );
    if ($colegioSesion) {
        $_SESSION['ticket_colegio_id'] = (int) $colegioSesion['id_colegio'];
    }
    $puedeElegirSolicitante = es_administrador_global($usuarioId, $db)
        || es_admin_colegio($usuarioId, null, $db);
} catch (Throwable $ex) {
    error_log('Error al cargar formulario de ticket: ' . $ex->getMessage());
    $errorCarga = 'No fue posible cargar los datos del formulario.';
}

try {
    $sqlUsuario = "SELECT t.id_ticket, t.asunto, t.descripcion_ticket AS descripcion,
                t.id_estado, e.nombre AS estado_nombre, t.id_prioridad, t.id_tecnico,
                COALESCE(CONCAT(pt.fecha_creacion_inicio, ' ', COALESCE(pt.hora_creacion_inicio, '00:00:00')), '') AS fecha_creacion,
                COALESCE(CONCAT(pt.fecha_asignacion_tecnico, ' ', COALESCE(pt.hora_asignacion_tecnico, '00:00:00')), '') AS fecha_respuesta,
                c.nombre_categoria AS categoria_nombre,
                col.nom_colegio AS colegio_nombre,
                CONCAT_WS(' ', tec.nombre, tec.apellido_paterno) AS tecnico_nombre
           FROM tickets t
           JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria_ticket
      LEFT JOIN (
                    SELECT id_usuario, MIN(id_colegio) AS id_colegio
                      FROM usuario_colegio
                     WHERE estado = 1
                  GROUP BY id_usuario
                ) uc_ticket ON uc_ticket.id_usuario = t.id_usuario
      LEFT JOIN colegio col ON col.id_colegio = uc_ticket.id_colegio
      LEFT JOIN usuarios tec ON tec.id = t.id_tecnico
      LEFT JOIN estados_ticket e ON e.id = t.id_estado
      LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
          WHERE t.id_usuario = ? AND t.estado = 1
       ORDER BY t.id_estado ASC, pt.fecha_creacion_inicio DESC, pt.hora_creacion_inicio DESC";
    $paramsUsuario = [$usuarioId];
    ticket_debug_sql('USUARIO', $sqlUsuario, $paramsUsuario);
    $misTickets = $db->fetchAll($sqlUsuario, $paramsUsuario);
    $misTickets = array_map('ticket_normalizar_fila', $misTickets);
} catch (Throwable $ex) {
    error_log('Error al listar tickets del solicitante: ' . $ex->getMessage());
    ticket_debug_sql('USUARIO ERROR', $sqlUsuario ?? 'SQL no construida', $paramsUsuario ?? [$usuarioId], $ex);
    $errorListado = 'No fue posible consultar tus solicitudes en este momento.';
}
$etiquetasEstado = [
    'nuevo' => 'Nuevo',
    'asignado' => 'Asignado',
    'en_proceso' => 'En proceso',
    'borrador' => 'Borrador',
    'atrasado' => 'Demorado',
    'resuelto' => 'Terminado',
];

$funcionesTicket = new FuncionesTicket($db);
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

<?php $funcionesTicket->renderizarContenedores($usuarioId, $idPagActual); ?>

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
          <label class="form-label" for="ticket-solicitante">Solicitante</label>
          <?php if ($puedeElegirSolicitante): ?>
            <select class="form-input" id="ticket-solicitante" name="solicitante_id" data-dynamic-user>
              <option value="<?= $usuarioId ?>"><?= e($usuarioNombre) ?></option>
            </select>
          <?php else: ?>
            <input class="form-input" id="ticket-solicitante" value="<?= e($usuarioNombre) ?>" readonly>
            <input type="hidden" name="solicitante_id" value="<?= $usuarioId ?>">
          <?php endif; ?>
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
      </div>

      <div class="form-group">
        <label class="form-label" for="ticket-asunto">Asunto *</label>
        <input class="form-input" id="ticket-asunto" name="asunto" minlength="5" maxlength="180" placeholder="Ej.: La impresora de secretaría no responde" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="ticket-descripcion">Descripción *</label>
        <div class="ticket-editor" data-ticket-editor>
          <div class="ticket-editor__toolbar" role="toolbar" aria-label="Formato de descripción">
            <select class="ticket-editor__format" aria-label="Formato del texto" data-editor-format>
              <option value="p">Normal</option>
              <option value="h3">Título</option>
            </select>
            <span class="ticket-editor__separator"></span>
            <button type="button" data-editor-command="bold" aria-label="Negrita"><strong>B</strong></button>
            <button type="button" data-editor-command="italic" aria-label="Cursiva"><em>I</em></button>
            <button type="button" data-editor-command="underline" aria-label="Subrayado"><u>U</u></button>
            <span class="ticket-editor__separator"></span>
            <button type="button" data-editor-command="insertOrderedList" aria-label="Lista numerada"><i class="bi bi-list-ol"></i></button>
            <button type="button" data-editor-command="insertUnorderedList" aria-label="Lista con viñetas"><i class="bi bi-list-ul"></i></button>
            <button type="button" data-editor-link aria-label="Agregar enlace"><i class="bi bi-link-45deg"></i></button>
          </div>
          <div class="ticket-editor__surface" id="ticket-descripcion" contenteditable="true" role="textbox" aria-multiline="true" data-placeholder="Indica qué ocurrió, desde cuándo y qué intentaste hacer."></div>
        </div>
        <input type="hidden" id="ticket-descripcion-value" name="descripcion">
        <div class="ticket-editor__meta"><span>Formato básico permitido</span><span id="ticket-description-count">0/10000</span></div>
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

      <div class="ticket-account-note"><i class="bi bi-building-check"></i><span>El colegio se asignará automáticamente: <strong id="ticket-colegio-contexto"><?= e((string) ($colegioSesion['nom_colegio'] ?? 'sin colegio asociado')) ?></strong>.</span></div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline" id="ticket-limpiar" type="reset">Limpiar</button>
        <div class="ticket-create-actions">
          <button class="btn btn-outline ticket-draft-button" id="ticket-borrador" type="button" <?= ($errorCarga || !$categorias || (!$colegioSesion && !$puedeElegirSolicitante)) ? 'disabled' : '' ?>><i class="bi bi-file-earmark"></i> Guardar borrador</button>
          <button class="btn btn-primary" id="ticket-guardar" type="submit" <?= ($errorCarga || !$categorias || (!$colegioSesion && !$puedeElegirSolicitante)) ? 'disabled' : '' ?>><i class="bi bi-send-fill"></i> Crear ticket</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="js/crear_ticket_dinamico.js"></script>
<script>
(() => {
  if (window.ticketDynamicCreateEnabled) return;
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
      setTimeout(() => {
        closeModal('modal-crear-ticket');
        window.location.reload();
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

function filtrarMisTickets() {
  const input = document.getElementById('mis-tickets-buscar');
  const select = document.getElementById('mis-tickets-estado');
  const date = document.getElementById('mis-tickets-fecha');
  const responseDate = document.getElementById('mis-tickets-respuesta');
  const rows = Array.from(document.querySelectorAll('#mis-tickets-tabla tbody tr'));
  if (!input || !select || !date || !responseDate || !rows.length) return;
  const query = input.value.trim().toLowerCase();
  let visibles = 0;
  let tieneFilaVacia = false;
  rows.forEach(row => {
    if (typeof row.dataset.search === 'undefined') {
      row.hidden = false;
      tieneFilaVacia = true;
      return;
    }
    const visible = row.dataset.search.includes(query)
      && (!select.value || row.dataset.estado === select.value)
      && (!date.value || row.dataset.fecha === date.value)
      && (!responseDate.value || row.dataset.fechaRespuesta === responseDate.value);
    row.hidden = !visible;
    if (visible) visibles++;
  });
  const empty = document.getElementById('mis-tickets-sin-resultados');
  if (empty) empty.hidden = visibles > 0 || tieneFilaVacia;
}

function enlazarFiltrosMisTickets() {
  document.getElementById('mis-tickets-buscar')?.addEventListener('input', filtrarMisTickets);
  document.getElementById('mis-tickets-estado')?.addEventListener('change', filtrarMisTickets);
  document.getElementById('mis-tickets-fecha')?.addEventListener('change', filtrarMisTickets);
  document.getElementById('mis-tickets-respuesta')?.addEventListener('change', filtrarMisTickets);
}

enlazarFiltrosMisTickets();

(() => {
  const table = document.getElementById('mis-tickets-tabla');
  const count = document.querySelector('#ticket-tabla-usuario .ticket-list-count');
  if (!table?.tBodies.length) return;

  const normalizar = html => html.replace(/\s+/g, ' ').trim();
  let ultimaHuella = normalizar(table.tBodies[0].innerHTML);
  let consultando = false;

  async function actualizarBandeja() {
    if (consultando || document.hidden) return;
    consultando = true;
    try {
      const response = await fetch('componentes/ajax/bloque_tabla_usuario.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        cache: 'no-store'
      });
      if (!response.ok) return;

      const html = await response.text();
      const template = document.createElement('template');
      template.innerHTML = `<table>${html}</table>`;
      const nuevoTbody = template.content.querySelector('tbody');
      if (!nuevoTbody) return;

      const nuevaHuella = normalizar(nuevoTbody.innerHTML);
      if (nuevaHuella === ultimaHuella) return;

      ultimaHuella = nuevaHuella;
      table.tBodies[0].replaceWith(nuevoTbody);
      const total = Number(response.headers.get('X-Ticket-Count') || 0);
      if (count) count.textContent = `${total} ticket${total === 1 ? '' : 's'}`;
      filtrarMisTickets();
    } catch (_) {
      // La bandeja visible se conserva cuando falla una actualización silenciosa.
    } finally {
      consultando = false;
    }
  }

  const polling = window.setInterval(actualizarBandeja, 3000);
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden) actualizarBandeja();
  });
  window.addEventListener('pagehide', () => window.clearInterval(polling), {once: true});
})();
</script>

<?php require __DIR__ . '/componentes/chat_ticket_panel.php'; ?>
<?php finalizar_layout_configuracion(); ?>
