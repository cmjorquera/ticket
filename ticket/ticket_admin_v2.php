<?php
declare(strict_types=1);

$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';
require_once __DIR__ . '/../clases/Tickets/TicketAdministrador.php';

$usuarioId = (int) Sesion::get('id', 0);
$idPagActual = FuncionesTicket::ROL_ADMIN;
$esGlobal = false;
$autorizado = false;
$tickets = [];
$colegios = [];
$tecnicos = [];
$idsColegio = [];
$errorCarga = null;
$estadoFiltro = ticket_estado_id($_GET['estado'] ?? 0);
$resumenFiltro = ticket_estado_grupo_filtro($_GET['resumen'] ?? '');
$colegioFiltro = (int) ($_GET['colegio'] ?? 0);
$tecnicoFiltro = (int) ($_GET['tecnico'] ?? 0);

try {
    $esGlobal = es_administrador_global($usuarioId, $db);
    $autorizado = $esGlobal || es_admin_colegio($usuarioId, null, $db);
    if ($autorizado) {
        if ($esGlobal) {
            $colegios = $db->fetchAll('SELECT id_colegio, nom_colegio FROM colegio WHERE estado = 1 ORDER BY nom_colegio');
        } else {
            $colegios = $db->fetchAll(
                "SELECT DISTINCT c.id_colegio, c.nom_colegio
                   FROM usuario_colegio uc
                   JOIN colegio c ON c.id_colegio = uc.id_colegio
              LEFT JOIN perfiles p ON p.id_perfil = uc.id_perfil
                  WHERE uc.id_usuario = ? AND uc.estado = 1 AND c.estado = 1
                    AND (uc.es_admin_colegio = 1 OR LOWER(p.nombre) IN ('admin colegio','admin_colegio','administrador colegio'))
               ORDER BY c.nom_colegio",
                [$usuarioId]
            );
        }
        $idsColegio = array_map('intval', array_column($colegios, 'id_colegio'));
        if ($colegioFiltro > 0 && !in_array($colegioFiltro, $idsColegio, true)) $colegioFiltro = 0;
        $tecnicos = $db->fetchAll("SELECT id, CONCAT_WS(' ', nombre, apellido_paterno) AS nombre FROM usuarios WHERE id_area_trabajo = 1 AND LOWER(estado) = 'activo' ORDER BY nombre, apellido_paterno");
        $idsTecnico = array_map('intval', array_column($tecnicos, 'id'));
        if ($tecnicoFiltro > 0 && !in_array($tecnicoFiltro, $idsTecnico, true)) $tecnicoFiltro = 0;

        $ticketData = new TicketAdministrador($db, $usuarioId);
        $tickets = $ticketData->traer(
            $estadoFiltro > 0 ? $estadoFiltro : null,
            null,
            $esGlobal ? null : $idsColegio,
            $colegioFiltro > 0 ? $colegioFiltro : null,
            $tecnicoFiltro > 0 ? $tecnicoFiltro : null
        );
        $tickets = array_map('ticket_normalizar_fila', $tickets);
        if ($resumenFiltro !== '') {
            $tickets = array_values(array_filter(
                $tickets,
                static fn (array $ticket): bool => ticket_estado_grupo((int) $ticket['id_estado']) === $resumenFiltro
            ));
        }
    }
} catch (Throwable $ex) {
    error_log('Error en administración de tickets: ' . $ex->getMessage());
    $errorCarga = 'No fue posible consultar los tickets. Verifica la estructura de base de datos del módulo.';
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
iniciar_layout_configuracion('Administración de tickets', 'Tickets', 'ticket_admin_v2');
?>
<?php if (!$autorizado): http_response_code(403); ?>
  <div class="card ticket-denied"><i class="bi bi-shield-lock"></i><h1>Acceso restringido</h1><p class="text-muted mt-3">Esta vista está disponible para administradores generales y administradores de colegio.</p></div>
<?php else: ?>
  <div class="page-header">
    <div><h1>Administración de tickets</h1><p>Distribuye casos, revisa prioridades y controla el avance de soporte.</p></div>
    <a class="btn btn-primary" href="ticket.php"><i class="bi bi-plus-circle"></i> Nuevo ticket</a>
  </div>

  <?php $funcionesTicket->renderizarContenedores($usuarioId, $idPagActual, $esGlobal ? null : $idsColegio); ?>
  <p class="ticket-status-filter" id="filtro-estado-activo" role="status" aria-live="polite"<?= $resumenFiltro === '' ? ' hidden' : '' ?>><?php if ($resumenFiltro !== ''): ?><i class="bi bi-funnel-fill" aria-hidden="true"></i> Filtrando por: <strong><?= e(ticket_estado_grupo_nombre($resumenFiltro)) ?></strong><?php endif; ?></p>

  <?php require __DIR__ . '/componentes/bloque_tabla_admin.php'; ?>

  <div class="modal-overlay" id="modal-admin-ticket" onclick="closeModalOutside(event,'modal-admin-ticket')">
    <div class="modal" style="max-width:680px">
      <div class="modal-header"><h3 id="admin-modal-titulo">Gestionar ticket</h3><p id="admin-modal-folio"></p></div>
      <div class="modal-body">
        <div class="ticket-detail-grid" id="admin-modal-grid"></div><div class="ticket-description" id="admin-modal-descripcion"></div>
        <div class="ticket-admin-actions">
          <div class="form-group"><label class="form-label" for="admin-modal-tecnico">Técnico asignado</label><select class="form-input" id="admin-modal-tecnico"><option value="0">Sin asignar</option><?php foreach ($tecnicos as $tecnico): ?><option value="<?= (int)$tecnico['id'] ?>"><?= e($tecnico['nombre']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label" for="admin-modal-estado">Estado</label><select class="form-input" id="admin-modal-estado"><?php foreach (ticket_estados_legacy() as $idEstado => $infoEstado): ?><option value="<?= $idEstado ?>"><?= e($infoEstado['nombre']) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="ticket-message" id="admin-modal-mensaje" role="status"></div>
      </div>
      <div class="modal-footer">
        <?php if ($esGlobal): ?><button class="btn btn-danger" id="admin-modal-eliminar"><i class="bi bi-trash"></i> Eliminar</button><?php endif; ?>
        <button class="btn btn-outline" onclick="closeModal('modal-admin-ticket')">Cerrar</button><button class="btn btn-primary" id="admin-modal-guardar"><i class="bi bi-check2"></i> Guardar cambios</button>
      </div>
    </div>
  </div>

  <script>
  const adminTicketCsrf = <?= json_encode($csrf) ?>;
  let adminTicketActual = 0;
  const adminResumenFiltro = <?= json_encode($resumenFiltro) ?>;
  document.querySelectorAll('.contenedor-tickets .contenedor-ticket[data-estado]').forEach(contenedor=>{
    const activo=contenedor.dataset.estado===adminResumenFiltro;
    contenedor.closest('.contenedor-tickets')?.classList.add('ticket-filters-enabled');
    contenedor.classList.toggle('is-active',activo);contenedor.setAttribute('role','button');contenedor.setAttribute('tabindex','0');contenedor.setAttribute('aria-pressed',String(activo));
    const filtrar=()=>{const url=new URL(window.location.href);if(url.searchParams.get('resumen')===contenedor.dataset.estado)url.searchParams.delete('resumen');else url.searchParams.set('resumen',contenedor.dataset.estado);url.searchParams.delete('pagina');window.location.href=url.toString()};
    contenedor.addEventListener('click',filtrar);contenedor.addEventListener('keydown',event=>{if(event.key==='Enter'||event.key===' '){event.preventDefault();filtrar()}});
  });
  function escTicket(value){const d=document.createElement('div');d.textContent=value??'';return d.innerHTML}
  function administrarTicket(button){
    const t=JSON.parse(button.dataset.ticket); adminTicketActual=Number(t.id_ticket);
    document.getElementById('admin-modal-titulo').textContent=t.asunto;
    document.getElementById('admin-modal-folio').textContent=`Ticket #${t.id_ticket} · ${t.categoria_nombre}`;
    document.getElementById('admin-modal-grid').innerHTML=`<div class="ticket-detail-block"><span>Solicitante</span><strong>${escTicket(t.usuario_nombre)}</strong></div><div class="ticket-detail-block"><span>Colegio</span><strong>${escTicket(t.colegio_nombre)}</strong></div><div class="ticket-detail-block"><span>Prioridad</span><strong>${escTicket(t.prioridad)}</strong></div><div class="ticket-detail-block"><span>Creado</span><strong>${escTicket(t.fecha_creacion)}</strong></div>`;
    document.getElementById('admin-modal-descripcion').textContent=t.descripcion_texto||t.descripcion;
    document.getElementById('admin-modal-tecnico').value=t.id_tecnico_asignado||'0'; document.getElementById('admin-modal-estado').value=t.id_estado;
    document.getElementById('admin-modal-mensaje').className='ticket-message'; openModal('modal-admin-ticket');
  }
  async function accionTicket(accion, extra={}){
    const message=document.getElementById('admin-modal-mensaje');
    try{const body=new URLSearchParams({accion,ticket_id:adminTicketActual,csrf:adminTicketCsrf,...extra});const response=await fetch('../ajax/guardar_ticket.php',{method:'POST',body,credentials:'same-origin'});const data=await response.json();if(!response.ok||!data.ok)throw new Error(data.error||'No fue posible guardar.');message.className='ticket-message ok';message.textContent=data.mensaje;setTimeout(()=>location.reload(),650)}catch(error){message.className='ticket-message error';message.textContent=error.message}}
  document.getElementById('admin-modal-guardar').onclick=()=>accionTicket('actualizar',{tecnico_id:document.getElementById('admin-modal-tecnico').value,estado:document.getElementById('admin-modal-estado').value});
  document.getElementById('admin-modal-eliminar')?.addEventListener('click',()=>{if(confirm(`¿Eliminar definitivamente el ticket #${adminTicketActual}?`))accionTicket('eliminar')});
  document.getElementById('admin-buscar').addEventListener('input',event=>{const q=event.target.value.trim().toLowerCase();document.querySelectorAll('#admin-tabla tbody tr').forEach(row=>row.hidden=!row.dataset.search.includes(q))});
  </script>
<?php endif; ?>

<?php require __DIR__ . '/componentes/chat_ticket_panel.php'; ?>
<?php finalizar_layout_configuracion(); ?>
