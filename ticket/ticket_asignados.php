<?php
declare(strict_types=1);

$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';
require_once __DIR__ . '/../clases/Tickets/TicketTecnico.php';

$usuarioId = (int) Sesion::get('id', 0);
$idPagActual = FuncionesTicket::ROL_TECNICO;
$resumenFiltro = ticket_estado_grupo_filtro($_GET['resumen'] ?? '');
$tickets = [];
$errorCarga = null;
try {
    $ticketData = new TicketTecnico($db, $usuarioId);
    $tickets = $ticketData->traer();
    $tickets = array_map('ticket_normalizar_fila', $tickets);
    if ($resumenFiltro !== '') {
        $tickets = array_values(array_filter(
            $tickets,
            static fn (array $ticket): bool => ticket_estado_grupo((int) $ticket['id_estado']) === $resumenFiltro
        ));
    }
} catch (Throwable $ex) {
    error_log('Error al listar tickets asignados: ' . $ex->getMessage());
    $errorCarga = 'No fue posible consultar los tickets. Verifica que las tablas del módulo estén instaladas.';
}

$ticketsPorPagina = 6;
$totalTickets = count($tickets);
$totalPaginas = (int) ceil($totalTickets / $ticketsPorPagina);
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));
$paginaActual = min($paginaActual, max(1, $totalPaginas));
$offsetTickets = ($paginaActual - 1) * $ticketsPorPagina;
$ticketsPagina = array_slice($tickets, $offsetTickets, $ticketsPorPagina);

$funcionesTicket = new FuncionesTicket($db);
$csrf = ticket_csrf_token();
iniciar_layout_configuracion('Tickets asignados', 'Tickets', 'ticket_asignados');
?>
<div class="page-header">
  <div><h1>Mis tickets asignados</h1><p>Casos que esperan tu revisión y seguimiento.</p></div>
  <a class="btn btn-outline" href="ticket.php"><i class="bi bi-plus-circle"></i> Crear ticket</a>
</div>

<?php $funcionesTicket->renderizarContenedores($usuarioId, $idPagActual); ?>
<p class="ticket-status-filter" id="filtro-estado-activo" role="status" aria-live="polite"<?= $resumenFiltro === '' ? ' hidden' : '' ?>><?php if ($resumenFiltro !== ''): ?><i class="bi bi-funnel-fill" aria-hidden="true"></i> Filtrando por: <strong><?= e(ticket_estado_grupo_nombre($resumenFiltro)) ?></strong><?php endif; ?></p>

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
const tecnicoResumenFiltro = <?= json_encode($resumenFiltro) ?>;
document.querySelectorAll('.contenedor-tickets .contenedor-ticket[data-estado]').forEach(contenedor => {
  const activo = contenedor.dataset.estado === tecnicoResumenFiltro;
  contenedor.closest('.contenedor-tickets')?.classList.add('ticket-filters-enabled');
  contenedor.classList.toggle('is-active', activo);
  contenedor.setAttribute('role', 'button');
  contenedor.setAttribute('tabindex', '0');
  contenedor.setAttribute('aria-pressed', String(activo));
  const filtrar = () => {
    const url = new URL(window.location.href);
    if (url.searchParams.get('resumen') === contenedor.dataset.estado) url.searchParams.delete('resumen');
    else url.searchParams.set('resumen', contenedor.dataset.estado);
    url.searchParams.delete('pagina');
    window.location.href = url.toString();
  };
  contenedor.addEventListener('click', filtrar);
  contenedor.addEventListener('keydown', event => {
    if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); filtrar(); }
  });
});

function abrirTicket(button) {
  const ticket = JSON.parse(button.dataset.ticket);
  document.getElementById('detalle-titulo').textContent = ticket.asunto;
  document.getElementById('detalle-folio').textContent = `Ticket #${ticket.id_ticket} · ${ticket.categoria_nombre}`;
  document.getElementById('detalle-grid').innerHTML = `
    <div class="ticket-detail-block"><span>Solicitante</span><strong>${esc(ticket.usuario_nombre)}</strong></div>
    <div class="ticket-detail-block"><span>Colegio</span><strong>${esc(ticket.colegio_nombre)}</strong></div>
    <div class="ticket-detail-block"><span>Prioridad</span><strong>${esc(ticket.prioridad)}</strong></div>
    <div class="ticket-detail-block"><span>Creado</span><strong>${esc(ticket.fecha_creacion)}</strong></div>`;
  document.getElementById('detalle-descripcion').textContent = ticket.descripcion_texto || ticket.descripcion;
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

<?php require __DIR__ . '/componentes/chat_ticket_panel.php'; ?>
<?php finalizar_layout_configuracion(); ?>
