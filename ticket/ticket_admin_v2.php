<?php
declare(strict_types=1);

$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';

$usuarioId = (int) Sesion::get('id', 0);
$esGlobal = false;
$autorizado = false;
$tickets = [];
$colegios = [];
$tecnicos = [];
$errorCarga = null;
$estadoFiltro = strtolower(trim((string) ($_GET['estado'] ?? '')));
$colegioFiltro = (int) ($_GET['colegio'] ?? 0);
if (!in_array($estadoFiltro, ['', 'nuevo', 'en_proceso', 'atrasado', 'resuelto', 'cerrado'], true)) $estadoFiltro = '';

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

        $sql = "SELECT t.id_ticket, t.asunto, t.descripcion, t.estado, t.fecha_creacion, t.fecha_respuesta,
                       t.prioridad, t.id_colegio, t.id_tecnico_asignado,
                       CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS usuario_nombre,
                       c.nombre_categoria AS categoria_nombre, col.nom_colegio AS colegio_nombre,
                       CONCAT_WS(' ', tec.nombre, tec.apellido_paterno) AS tecnico_nombre
                  FROM tickets t
                  JOIN usuarios u ON u.id = t.id_usuario
                  JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria
                  JOIN colegio col ON col.id_colegio = t.id_colegio
             LEFT JOIN usuarios tec ON tec.id = t.id_tecnico_asignado
                 WHERE 1 = 1";
        $params = [];
        if (!$esGlobal) {
            if (!$idsColegio) {
                $sql .= ' AND 1 = 0';
            } else {
                $sql .= ' AND t.id_colegio IN (' . implode(',', array_fill(0, count($idsColegio), '?')) . ')';
                array_push($params, ...$idsColegio);
            }
        }
        if ($estadoFiltro !== '') { $sql .= ' AND t.estado = ?'; $params[] = $estadoFiltro; }
        if ($colegioFiltro > 0) { $sql .= ' AND t.id_colegio = ?'; $params[] = $colegioFiltro; }
        $sql .= " ORDER BY FIELD(t.estado, 'nuevo','en_proceso','atrasado','resuelto','cerrado'), t.fecha_creacion DESC";
        $tickets = $db->fetchAll($sql, $params);
        $tecnicos = $db->fetchAll("SELECT id, CONCAT_WS(' ', nombre, apellido_paterno) AS nombre FROM usuarios WHERE id_area_trabajo = 1 AND LOWER(estado) = 'activo' ORDER BY nombre, apellido_paterno");
    }
} catch (Throwable $ex) {
    error_log('Error en administración de tickets: ' . $ex->getMessage());
    $errorCarga = 'No fue posible consultar los tickets. Verifica la estructura de base de datos del módulo.';
}

$conteos = ['nuevo' => 0, 'en_proceso' => 0, 'resuelto' => 0];
foreach ($tickets as $ticket) { $key = strtolower((string) $ticket['estado']); if (isset($conteos[$key])) $conteos[$key]++; }
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

  <div class="ticket-metrics">
    <div class="ticket-metric"><span>Nuevos</span><strong><?= $conteos['nuevo'] ?></strong></div>
    <div class="ticket-metric"><span>En proceso</span><strong><?= $conteos['en_proceso'] ?></strong></div>
    <div class="ticket-metric"><span>Resueltos</span><strong><?= $conteos['resuelto'] ?></strong></div>
    <div class="ticket-metric"><span>Resultados</span><strong><?= count($tickets) ?></strong></div>
  </div>

  <section class="card">
    <div class="card-header"><div><h2 class="card-title">Control de casos</h2><p class="card-desc"><?= $esGlobal ? 'Vista general de todos los colegios.' : 'Vista limitada a tus colegios administrados.' ?></p></div></div>
    <form class="ticket-toolbar" method="get">
      <div class="form-group ticket-search"><label class="form-label" for="admin-buscar">Buscar en resultados</label><input class="form-input" id="admin-buscar" placeholder="Folio, asunto, solicitante o técnico"></div>
      <div class="form-group"><label class="form-label" for="admin-estado">Estado</label><select class="form-input" id="admin-estado" name="estado"><option value="">Todos</option><?php foreach (['nuevo'=>'Nuevo','en_proceso'=>'En proceso','atrasado'=>'Atrasado','resuelto'=>'Resuelto','cerrado'=>'Cerrado'] as $valor=>$texto): ?><option value="<?= $valor ?>" <?= $estadoFiltro === $valor ? 'selected' : '' ?>><?= $texto ?></option><?php endforeach; ?></select></div>
      <div class="form-group"><label class="form-label" for="admin-colegio">Colegio</label><select class="form-input" id="admin-colegio" name="colegio"><option value="0">Todos</option><?php foreach ($colegios as $colegio): ?><option value="<?= (int) $colegio['id_colegio'] ?>" <?= $colegioFiltro === (int) $colegio['id_colegio'] ? 'selected' : '' ?>><?= e($colegio['nom_colegio']) ?></option><?php endforeach; ?></select></div>
      <button class="btn btn-outline" type="submit"><i class="bi bi-funnel"></i> Filtrar</button>
    </form>
    <?php if ($errorCarga): ?>
      <div class="ticket-empty"><i class="bi bi-exclamation-circle"></i><?= e($errorCarga) ?></div>
    <?php elseif (!$tickets): ?>
      <div class="ticket-empty"><i class="bi bi-inbox"></i>No hay tickets que coincidan con los filtros.</div>
    <?php else: ?>
      <div class="table-wrap"><table id="admin-tabla"><thead><tr><th>Folio / fecha</th><th>Caso</th><th>Solicitante</th><th>Técnico</th><th>Estado</th><th>Prioridad</th><th></th></tr></thead><tbody>
      <?php foreach ($tickets as $ticket):
        $estado = strtolower((string) $ticket['estado']);
        $prioridad = str_replace('í', 'i', strtolower((string) $ticket['prioridad']));
        $json = json_encode($ticket, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
      ?>
        <tr data-search="<?= e(strtolower(implode(' ', [(string)$ticket['id_ticket'],$ticket['asunto'],$ticket['usuario_nombre'],$ticket['tecnico_nombre'],$ticket['colegio_nombre']]))) ?>">
          <td><span class="ticket-id">#<?= (int) $ticket['id_ticket'] ?></span><div class="text-xs text-muted"><?= e(date('d/m/Y H:i', strtotime((string)$ticket['fecha_creacion']))) ?></div></td>
          <td class="ticket-subject"><strong><?= e($ticket['asunto']) ?></strong><small><?= e($ticket['categoria_nombre']) ?> · <?= e($ticket['colegio_nombre']) ?></small></td>
          <td class="ticket-person"><strong><?= e($ticket['usuario_nombre']) ?></strong></td>
          <td class="ticket-person"><strong><?= e($ticket['tecnico_nombre'] ?: 'Sin asignar') ?></strong></td>
          <td><span class="ticket-badge ticket-badge--<?= e($estado) ?>"><?= e(str_replace('_',' ',$estado)) ?></span></td>
          <td><span class="ticket-priority ticket-priority--<?= e($prioridad) ?>"><?= e($ticket['prioridad']) ?></span></td>
          <td><div class="ticket-actions"><button class="btn btn-outline btn-sm" type="button" data-ticket="<?= e($json) ?>" onclick="administrarTicket(this)"><i class="bi bi-sliders"></i> Gestionar</button></div></td>
        </tr>
      <?php endforeach; ?></tbody></table></div>
    <?php endif; ?>
  </section>

  <div class="modal-overlay" id="modal-admin-ticket" onclick="closeModalOutside(event,'modal-admin-ticket')">
    <div class="modal" style="max-width:680px">
      <div class="modal-header"><h3 id="admin-modal-titulo">Gestionar ticket</h3><p id="admin-modal-folio"></p></div>
      <div class="modal-body">
        <div class="ticket-detail-grid" id="admin-modal-grid"></div><div class="ticket-description" id="admin-modal-descripcion"></div>
        <div class="ticket-admin-actions">
          <div class="form-group"><label class="form-label" for="admin-modal-tecnico">Técnico asignado</label><select class="form-input" id="admin-modal-tecnico"><option value="0">Sin asignar</option><?php foreach ($tecnicos as $tecnico): ?><option value="<?= (int)$tecnico['id'] ?>"><?= e($tecnico['nombre']) ?></option><?php endforeach; ?></select></div>
          <div class="form-group"><label class="form-label" for="admin-modal-estado">Estado</label><select class="form-input" id="admin-modal-estado"><option value="nuevo">Nuevo</option><option value="en_proceso">En proceso</option><option value="atrasado">Atrasado</option><option value="resuelto">Resuelto</option><option value="cerrado">Cerrado</option></select></div>
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
    document.getElementById('admin-modal-descripcion').textContent=t.descripcion;
    document.getElementById('admin-modal-tecnico').value=t.id_tecnico_asignado||'0'; document.getElementById('admin-modal-estado').value=t.estado;
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

<?php finalizar_layout_configuracion(); ?>
