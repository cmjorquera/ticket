<?php
declare(strict_types=1);

$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';

$usuarioId = (int) Sesion::get('id', 0);
$tickets = [];
$errorCarga = null;
try {
    $tickets = $db->fetchAll(
        "SELECT t.id_ticket, t.asunto, t.descripcion, t.estado, t.fecha_creacion,
                t.fecha_respuesta, t.prioridad,
                CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS usuario_nombre,
                c.nombre_categoria AS categoria_nombre, col.nom_colegio AS colegio_nombre
           FROM tickets t
           JOIN usuarios u ON u.id = t.id_usuario
           JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria
           JOIN colegio col ON col.id_colegio = t.id_colegio
          WHERE t.id_tecnico_asignado = ?
       ORDER BY FIELD(t.estado, 'nuevo', 'en_proceso', 'resuelto', 'cerrado'), t.fecha_creacion DESC",
        [$usuarioId]
    );
} catch (Throwable $ex) {
    error_log('Error al listar tickets asignados: ' . $ex->getMessage());
    $errorCarga = 'No fue posible consultar los tickets. Verifica que las tablas del módulo estén instaladas.';
}

$conteos = ['nuevo' => 0, 'en_proceso' => 0, 'resuelto' => 0, 'cerrado' => 0];
foreach ($tickets as $ticket) {
    $estado = strtolower((string) $ticket['estado']);
    if (isset($conteos[$estado])) $conteos[$estado]++;
}
$csrf = ticket_csrf_token();
iniciar_layout_configuracion('Tickets asignados', 'Tickets', 'ticket_asignados');
?>
<link rel="stylesheet" href="<?= e($depth) ?>ticket/tickets.css">

<div class="page-header">
  <div><h1>Mis tickets asignados</h1><p>Casos que esperan tu revisión y seguimiento.</p></div>
  <a class="btn btn-outline" href="ticket.php"><i class="bi bi-plus-circle"></i> Crear ticket</a>
</div>

<div class="ticket-metrics">
  <div class="ticket-metric"><span>Nuevos</span><strong><?= $conteos['nuevo'] ?></strong></div>
  <div class="ticket-metric"><span>En proceso</span><strong><?= $conteos['en_proceso'] ?></strong></div>
  <div class="ticket-metric"><span>Resueltos</span><strong><?= $conteos['resuelto'] ?></strong></div>
  <div class="ticket-metric"><span>Total asignados</span><strong><?= count($tickets) ?></strong></div>
</div>

<section class="card">
  <div class="card-header"><div><h2 class="card-title">Bandeja técnica</h2><p class="card-desc">Abre un caso para revisar su descripción y cambiar el estado.</p></div></div>
  <div class="ticket-toolbar">
    <div class="form-group ticket-search">
      <label class="form-label" for="ticket-buscar">Buscar</label>
      <input class="form-input" id="ticket-buscar" placeholder="Folio, asunto, solicitante o colegio">
    </div>
    <div class="form-group">
      <label class="form-label" for="ticket-estado">Estado</label>
      <select class="form-input" id="ticket-estado">
        <option value="">Todos</option><option value="nuevo">Nuevo</option><option value="en_proceso">En proceso</option><option value="resuelto">Resuelto</option><option value="cerrado">Cerrado</option>
      </select>
    </div>
  </div>
  <?php if ($errorCarga): ?>
    <div class="ticket-empty"><i class="bi bi-exclamation-circle"></i><?= e($errorCarga) ?></div>
  <?php elseif (!$tickets): ?>
    <div class="ticket-empty"><i class="bi bi-inbox"></i>No tienes tickets asignados en este momento.</div>
  <?php else: ?>
    <div class="table-wrap">
      <table id="tabla-tickets">
        <thead><tr><th>Folio / fecha</th><th>Caso</th><th>Solicitante</th><th>Estado</th><th>Prioridad</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($tickets as $ticket):
          $estado = strtolower((string) $ticket['estado']);
          $prioridad = str_replace(['í', 'Í'], 'i', strtolower((string) $ticket['prioridad']));
          $datosTicket = json_encode($ticket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        ?>
          <tr data-search="<?= e(strtolower(implode(' ', [(string) $ticket['id_ticket'], $ticket['asunto'], $ticket['usuario_nombre'], $ticket['colegio_nombre'], $ticket['categoria_nombre']]))) ?>" data-estado="<?= e($estado) ?>">
            <td><span class="ticket-id">#<?= (int) $ticket['id_ticket'] ?></span><div class="text-xs text-muted"><?= e(date('d/m/Y H:i', strtotime((string) $ticket['fecha_creacion']))) ?></div></td>
            <td class="ticket-subject"><strong><?= e($ticket['asunto']) ?></strong><small><?= e($ticket['categoria_nombre']) ?> · <?= e($ticket['colegio_nombre']) ?></small></td>
            <td class="ticket-person"><strong><?= e($ticket['usuario_nombre']) ?></strong></td>
            <td><span class="ticket-badge ticket-badge--<?= e($estado) ?>"><?= e(str_replace('_', ' ', $estado)) ?></span></td>
            <td><span class="ticket-priority ticket-priority--<?= e($prioridad) ?>"><?= e($ticket['prioridad']) ?></span></td>
            <td><div class="ticket-actions"><button class="btn btn-outline btn-sm" type="button" data-ticket="<?= e($datosTicket) ?>" onclick="abrirTicket(this)"><i class="bi bi-eye"></i> Ver</button></div></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>

<div class="modal-overlay" id="modal-ticket" onclick="closeModalOutside(event,'modal-ticket')">
  <div class="modal" style="max-width:640px">
    <div class="modal-header"><h3 id="detalle-titulo">Detalle del ticket</h3><p id="detalle-folio"></p></div>
    <div class="modal-body">
      <div class="ticket-detail-grid" id="detalle-grid"></div>
      <div class="ticket-description" id="detalle-descripcion"></div>
      <div class="ticket-message" id="detalle-mensaje" role="status"></div>
      <div class="form-group" style="margin-top:16px">
        <label class="form-label" for="detalle-estado">Actualizar estado</label>
        <select class="form-input" id="detalle-estado"><option value="nuevo">Nuevo</option><option value="en_proceso">En proceso</option><option value="resuelto">Resuelto</option><option value="cerrado">Cerrado</option></select>
      </div>
    </div>
    <div class="modal-footer"><button class="btn btn-outline" onclick="closeModal('modal-ticket')">Cerrar</button><button class="btn btn-primary" id="detalle-guardar"><i class="bi bi-check2"></i> Guardar estado</button></div>
  </div>
</div>

<script>
const ticketCsrf = <?= json_encode($csrf) ?>;
const ticketLabels = {nuevo:'Nuevo', en_proceso:'En proceso', resuelto:'Resuelto', cerrado:'Cerrado'};

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
  document.getElementById('detalle-estado').value = ticket.estado;
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
