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
    $columnasAdjuntos = ticket_columnas_tabla('archivos_adjuntos_ticket', $db);
    $sqlCantidadArchivos = isset($columnasAdjuntos['id_ticket'])
        ? '(SELECT COUNT(*) FROM archivos_adjuntos_ticket aa WHERE aa.id_ticket = t.id_ticket)'
        : '0';
    $tablaCalificacion = 'calificacion_ticket';
    $columnasCalificacion = ticket_columnas_tabla($tablaCalificacion, $db);
    if (!$columnasCalificacion) {
        $tablaCalificacion = 'calificacion_tickett';
        $columnasCalificacion = ticket_columnas_tabla($tablaCalificacion, $db);
    }
    $sqlTieneCalificacion = isset($columnasCalificacion['id_ticket'])
        ? "EXISTS (SELECT 1 FROM {$tablaCalificacion} ct WHERE ct.id_ticket = t.id_ticket)"
        : '0';
    $sqlUsuario = "SELECT t.id_ticket, t.id_usuario, t.asunto, t.descripcion_ticket AS descripcion,
                t.id_estado, e.nombre AS estado_nombre, e.color AS estado_color,
                e.color_degradado AS estado_degradado,
                t.id_prioridad, t.id_tecnico, {$sqlCantidadArchivos} AS cantidad_archivos,
                {$sqlTieneCalificacion} AS tiene_calificacion,
                COALESCE(CONCAT(pt.fecha_creacion_inicio, ' ', COALESCE(pt.hora_creacion_inicio, '00:00:00')), '') AS fecha_creacion,
                COALESCE(CONCAT(pt.fecha_asignacion_tecnico, ' ', COALESCE(pt.hora_asignacion_tecnico, '00:00:00')), '') AS fecha_respuesta,
                CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS usuario_nombre,
                c.nombre_categoria AS categoria_nombre,
                col.nom_colegio AS colegio_nombre,
                CONCAT_WS(' ', tec.nombre, tec.apellido_paterno) AS tecnico_nombre
           FROM tickets t
           JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria_ticket
           JOIN usuarios u ON u.id = t.id_usuario
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
    'nuevo' => 'Recibido',
    'asignado' => 'Asignado',
    'en_proceso' => 'En proceso',
    'borrador' => 'Borrador',
    'atrasado' => 'Demorado',
    'resuelto' => 'Terminado',
    'cerrado' => 'Cerrado',
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
<p class="ticket-status-filter" id="filtro-estado-activo" role="status" aria-live="polite" hidden></p>

<?php require __DIR__ . '/componentes/bloque_tabla_usuario.php'; ?>

<?php require __DIR__ . '/componentes/modal_crear_ticket.php'; ?>

<div class="modal-overlay" id="modal-calificar-ticket" onclick="closeModalOutside(event,'modal-calificar-ticket')">
  <div class="modal ticket-rating" role="dialog" aria-modal="true" aria-labelledby="calificar-ticket-titulo">
    <form id="form-calificar-ticket">
      <div class="modal-header">
        <h3 id="calificar-ticket-titulo">Calificar atención</h3>
        <p>Selecciona de 1 a 5 estrellas y, si quieres, deja un comentario.</p>
      </div>
      <div class="modal-body">
        <input type="hidden" id="calificar-ticket-id" name="ticket_id">
        <fieldset>
          <legend>Calificación</legend>
          <div class="ticket-stars">
            <?php for ($estrella = 5; $estrella >= 1; $estrella--): ?>
              <input type="radio" id="calificar-estrella-<?= $estrella ?>" name="calificacion" value="<?= $estrella ?>" required>
              <label for="calificar-estrella-<?= $estrella ?>" aria-label="<?= $estrella ?> estrellas">★</label>
            <?php endfor; ?>
          </div>
        </fieldset>
        <label class="form-label" for="calificar-ticket-comentario">Comentario opcional</label>
        <textarea class="form-input" id="calificar-ticket-comentario" name="comentario" maxlength="1000" placeholder="Cuéntanos cómo fue la atención"></textarea>
        <div class="ticket-message" id="calificar-ticket-mensaje" role="status"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" type="button" onclick="closeModal('modal-calificar-ticket')">Cancelar</button>
        <button class="btn btn-primary" type="submit"><i class="bi bi-star-fill"></i> Guardar calificación</button>
      </div>
    </form>
  </div>
</div>

<script src="js/crear_ticket_dinamico.js"></script>
<script>
let filtroContenedorMisTickets = '';

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
      && (!filtroContenedorMisTickets || row.dataset.estadoGrupo === filtroContenedorMisTickets)
      && (!date.value || row.dataset.fecha === date.value)
      && (!responseDate.value || row.dataset.fechaRespuesta === responseDate.value);
    row.hidden = !visible;
    if (visible) visibles++;
  });
  const empty = document.getElementById('mis-tickets-sin-resultados');
  if (empty) empty.hidden = visibles > 0 || tieneFilaVacia;
}

function filtrarTicketsPorEstado(estado) {
  filtroContenedorMisTickets = filtroContenedorMisTickets === estado ? '' : estado;
  const contenedores = document.querySelectorAll('.contenedor-tickets .contenedor-ticket[data-estado]');
  contenedores.forEach(contenedor => {
    const activo = contenedor.dataset.estado === filtroContenedorMisTickets;
    contenedor.classList.toggle('is-active', activo);
    contenedor.setAttribute('aria-pressed', String(activo));
  });

  const indicador = document.getElementById('filtro-estado-activo');
  const activo = Array.from(contenedores).find(contenedor => contenedor.classList.contains('is-active'));
  if (indicador) {
    indicador.hidden = !activo;
    indicador.innerHTML = activo
      ? `<i class="bi bi-funnel-fill" aria-hidden="true"></i> Filtrando por: <strong>${activo.querySelector('.contenedor-ticket-titulo')?.textContent || ''}</strong>`
      : '';
  }
  filtrarMisTickets();
}

function enlazarFiltrosMisTickets() {
  document.getElementById('mis-tickets-buscar')?.addEventListener('input', filtrarMisTickets);
  document.getElementById('mis-tickets-estado')?.addEventListener('change', filtrarMisTickets);
  document.getElementById('mis-tickets-fecha')?.addEventListener('change', filtrarMisTickets);
  document.getElementById('mis-tickets-respuesta')?.addEventListener('change', filtrarMisTickets);
  const grupo = document.querySelector('.contenedor-tickets');
  if (grupo) grupo.classList.add('ticket-filters-enabled');
  grupo?.querySelectorAll('.contenedor-ticket[data-estado]').forEach(contenedor => {
    contenedor.setAttribute('role', 'button');
    contenedor.setAttribute('tabindex', '0');
    contenedor.setAttribute('aria-pressed', 'false');
    contenedor.addEventListener('click', () => filtrarTicketsPorEstado(contenedor.dataset.estado || ''));
    contenedor.addEventListener('keydown', event => {
      if (event.key !== 'Enter' && event.key !== ' ') return;
      event.preventDefault();
      filtrarTicketsPorEstado(contenedor.dataset.estado || '');
    });
  });
}

enlazarFiltrosMisTickets();

function validacionTicketPorUsuario(idTicket, idUsuario, idTecnico) {
  if (!Number(idTicket)) return;
  const form = document.getElementById('form-calificar-ticket');
  form?.reset();
  document.getElementById('calificar-ticket-id').value = String(idTicket);
  const mensaje = document.getElementById('calificar-ticket-mensaje');
  if (mensaje) {
    mensaje.className = 'ticket-message';
    mensaje.textContent = '';
  }
  openModal('modal-calificar-ticket');
}

document.getElementById('form-calificar-ticket')?.addEventListener('submit', async event => {
  event.preventDefault();
  const form = event.currentTarget;
  if (!form.reportValidity()) return;
  const boton = form.querySelector('button[type="submit"]');
  const mensaje = document.getElementById('calificar-ticket-mensaje');
  boton.disabled = true;
  try {
    const response = await fetch('../ajax/guardar_calificacion.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: {'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest'},
      body: JSON.stringify({
        ticket_id: Number(document.getElementById('calificar-ticket-id').value),
        calificacion: Number(form.calificacion.value),
        comentario: form.comentario.value.trim(),
        csrf: <?= json_encode($csrf, JSON_UNESCAPED_SLASHES) ?>
      })
    });
    const data = await response.json();
    if (!response.ok || !data.ok) throw new Error(data.error || 'No fue posible guardar la calificación.');
    mensaje.className = 'ticket-message ok';
    mensaje.textContent = data.mensaje || 'Calificación guardada.';
    window.setTimeout(() => window.location.reload(), 450);
  } catch (error) {
    mensaje.className = 'ticket-message error';
    mensaje.textContent = error.message;
    boton.disabled = false;
  }
});

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
