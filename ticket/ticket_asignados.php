<?php
declare(strict_types=1);

$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';

$usuarioId = (int) Sesion::get('id', 0);
$idPagActual = FuncionesTicket::ROL_TECNICO;
$tickets = [];
$errorCarga = null;
try {
    $sqlTecnico = "SELECT t.id_ticket, t.asunto, t.descripcion_ticket AS descripcion,
                t.id_estado, e.nombre AS estado_nombre, t.id_prioridad, t.id_tecnico,
                COALESCE(CONCAT(pt.fecha_creacion_inicio, ' ', COALESCE(pt.hora_creacion_inicio, '00:00:00')), '') AS fecha_creacion,
                COALESCE(CONCAT(pt.fecha_asignacion_tecnico, ' ', COALESCE(pt.hora_asignacion_tecnico, '00:00:00')), '') AS fecha_respuesta,
                CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS usuario_nombre,
                c.nombre_categoria AS categoria_nombre, col.nom_colegio AS colegio_nombre
           FROM tickets t
           JOIN usuarios u ON u.id = t.id_usuario
           JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria_ticket
      LEFT JOIN (
                    SELECT id_usuario, MIN(id_colegio) AS id_colegio
                      FROM usuario_colegio
                     WHERE estado = 1
                  GROUP BY id_usuario
                ) uc_ticket ON uc_ticket.id_usuario = t.id_usuario
      LEFT JOIN colegio col ON col.id_colegio = uc_ticket.id_colegio
      LEFT JOIN estados_ticket e ON e.id = t.id_estado
      LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
          WHERE t.id_tecnico = ? AND t.estado = 1
       ORDER BY t.id_estado ASC, pt.fecha_creacion_inicio DESC, pt.hora_creacion_inicio DESC";
    $paramsTecnico = [$usuarioId];
    ticket_debug_sql('TECNICO usuario=' . $usuarioId . ' pagina=' . $idPagActual, $sqlTecnico, $paramsTecnico);
    $tickets = $db->fetchAll($sqlTecnico, $paramsTecnico);
    $tickets = array_map('ticket_normalizar_fila', $tickets);
} catch (Throwable $ex) {
    error_log('Error al listar tickets asignados: ' . $ex->getMessage());
    ticket_debug_sql('TECNICO ERROR', $sqlTecnico ?? 'SQL no construida', $paramsTecnico ?? [$usuarioId], $ex);
    $errorCarga = 'No fue posible consultar los tickets. Verifica que las tablas del módulo estén instaladas.';
}

$funcionesTicket = new FuncionesTicket($db);
$csrf = ticket_csrf_token();
iniciar_layout_configuracion('Tickets asignados', 'Tickets', 'ticket_asignados');
?>
<div class="page-header">
  <div><h1>Mis tickets asignados</h1><p>Casos que esperan tu revisión y seguimiento.</p></div>
  <a class="btn btn-outline" href="ticket.php"><i class="bi bi-plus-circle"></i> Crear ticket</a>
</div>

<?php $funcionesTicket->renderizarContenedores($usuarioId, $idPagActual); ?>

<?php require __DIR__ . '/componentes/bloque_tabla_tecnico.php'; ?>

<div class="modal-overlay" id="modal-ticket" onclick="closeModalOutside(event,'modal-ticket')">
  <div class="modal" style="max-width:640px">
    <div class="modal-header"><h3 id="detalle-titulo">Detalle del ticket</h3><p id="detalle-folio"></p></div>
    <div class="modal-body">
      <div class="ticket-detail-grid" id="detalle-grid"></div>
      <div class="ticket-description" id="detalle-descripcion"></div>
      <div class="ticket-message" id="detalle-mensaje" role="status"></div>
      <div class="form-group" style="margin-top:16px">
        <label class="form-label" for="detalle-estado">Actualizar estado</label>
        <select class="form-input" id="detalle-estado"><?php foreach (ticket_estados_legacy() as $idEstado => $infoEstado): ?><option value="<?= $idEstado ?>"><?= e($infoEstado['nombre']) ?></option><?php endforeach; ?></select>
      </div>
    </div>
    <div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('modal-ticket')">Cerrar</button><button class="btn btn-primary" id="detalle-guardar"><i class="bi bi-check2"></i> Guardar estado</button></div>
  </div>
</div>

<script>
const ticketCsrf = <?= json_encode($csrf) ?>;

function abrirTicket(button) {
  const ticket = JSON.parse(button.dataset.ticket);
  document.getElementById('detalle-titulo').textContent = ticket.asunto;
  document.getElementById('detalle-folio').textContent = `Ticket #${ticket.id_ticket} · ${ticket.categoria_nombre}`;
  document.getElementById('detalle-grid').innerHTML = `
    <div class="ticket-detail-block"><span>Solicitante</span><strong>${esc(ticket.usuario_nombre)}</strong></div>
    <div class="ticket-detail-block"><span>Colegio</span><strong>${esc(ticket.colegio_nombre)}</strong></div>
    <div class="ticket-detail-block"><span>Prioridad</span><strong>${esc(ticket.prioridad)}</strong></div>
    <div class="ticket-detail-block"><span>Creado</span><strong>${esc(ticket.fecha_creacion)}</strong></div>`;
  document.getElementById('detalle-descripcion').textContent = ticket.descripcion;
  document.getElementById('detalle-estado').value = ticket.id_estado;
  document.getElementById('detalle-mensaje').className = 'ticket-message';
  document.getElementById('detalle-guardar').onclick = () => guardarEstado(ticket.id_ticket);
  openModal('modal-ticket');
}

async function guardarEstado(id) {
  const button = document.getElementById('detalle-guardar');
  const message = document.getElementById('detalle-mensaje');
  button.disabled = true;
  try {
    const body = new URLSearchParams({accion:'actualizar_estado', ticket_id:id, estado:document.getElementById('detalle-estado').value, csrf:ticketCsrf});
    const response = await fetch('../ajax/guardar_ticket.php', {method:'POST', body, credentials:'same-origin'});
    const data = await response.json();
    if (!response.ok || !data.ok) throw new Error(data.error || 'No fue posible actualizar el estado.');
    message.className = 'ticket-message ok'; message.textContent = data.mensaje;
    setTimeout(() => location.reload(), 700);
  } catch (error) { message.className = 'ticket-message error'; message.textContent = error.message; }
  finally { button.disabled = false; }
}

function esc(value) { const d=document.createElement('div'); d.textContent=value ?? ''; return d.innerHTML; }
function filtrarTickets() {
  const query = document.getElementById('ticket-buscar').value.trim().toLowerCase();
  const state = document.getElementById('ticket-estado').value;
  document.querySelectorAll('#tabla-tickets tbody tr').forEach(row => row.hidden = !(row.dataset.search.includes(query) && (!state || row.dataset.estado === state)));
}
document.getElementById('ticket-buscar')?.addEventListener('input', filtrarTickets);
document.getElementById('ticket-estado')?.addEventListener('change', filtrarTickets);
</script>

<?php finalizar_layout_configuracion(); ?>
