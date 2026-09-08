<?php
declare(strict_types=1);

$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';

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

        $sql = "SELECT t.id_ticket, t.asunto, t.descripcion_ticket AS descripcion,
                       t.id_estado, e.nombre AS estado_nombre, t.id_prioridad, uc_ticket.id_colegio, t.id_tecnico,
                       COALESCE(CONCAT(pt.fecha_creacion_inicio, ' ', COALESCE(pt.hora_creacion_inicio, '00:00:00')), '') AS fecha_creacion,
                       COALESCE(CONCAT(pt.fecha_asignacion_tecnico, ' ', COALESCE(pt.hora_asignacion_tecnico, '00:00:00')), '') AS fecha_respuesta,
                       CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS usuario_nombre,
                       c.nombre_categoria AS categoria_nombre, col.nom_colegio AS colegio_nombre,
                       CONCAT_WS(' ', tec.nombre, tec.apellido_paterno) AS tecnico_nombre
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
             LEFT JOIN usuarios tec ON tec.id = t.id_tecnico
             LEFT JOIN estados_ticket e ON e.id = t.id_estado
             LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
                 WHERE t.estado = 1";
        $params = [];
        if (!$esGlobal) {
            if (!$idsColegio) {
                $sql .= ' AND 1 = 0';
            } else {
                $sql .= ' AND uc_ticket.id_colegio IN (' . implode(',', array_fill(0, count($idsColegio), '?')) . ')';
                array_push($params, ...$idsColegio);
            }
        }
        if ($estadoFiltro > 0) { $sql .= ' AND t.id_estado = ?'; $params[] = $estadoFiltro; }
        if ($colegioFiltro > 0) { $sql .= ' AND uc_ticket.id_colegio = ?'; $params[] = $colegioFiltro; }
        if ($tecnicoFiltro > 0) { $sql .= ' AND t.id_tecnico = ?'; $params[] = $tecnicoFiltro; }
        $sql .= ' ORDER BY t.id_estado ASC, pt.fecha_creacion_inicio DESC, pt.hora_creacion_inicio DESC';
        ticket_debug_sql('ADMIN usuario=' . $usuarioId . ' pagina=' . $idPagActual, $sql, $params);
        $tickets = $db->fetchAll($sql, $params);
        $tickets = array_map('ticket_normalizar_fila', $tickets);
    }
} catch (Throwable $ex) {
    error_log('Error en administración de tickets: ' . $ex->getMessage());
    ticket_debug_sql('ADMIN ERROR', $sql ?? 'SQL no construida', $params ?? [], $ex);
    $errorCarga = 'No fue posible consultar los tickets. Verifica la estructura de base de datos del módulo.';
}

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
