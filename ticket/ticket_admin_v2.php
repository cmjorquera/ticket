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
                    AND (uc.es_admin_colegio = 1 OR LOWER(p.nombre_perfil) IN ('admin colegio','admin_colegio','administrador colegio'))
               ORDER BY c.nom_colegio",
                [$usuarioId]
            );
        }
        $idsColegio = array_map('intval', array_column($colegios, 'id_colegio'));
        if ($colegioFiltro > 0 && !in_array($colegioFiltro, $idsColegio, true)) $colegioFiltro = 0;
        // Técnicos asignables: funcionarios de Informática de Seduc (LOGICA_CATEGORIAS.txt)
        require_once __DIR__ . '/../clases/RolesUsuario.php';
        [$condTec, $paramTec] = (new RolesUsuario($db))->modeloActivo()
            ? RolesUsuario::condicionDepartamento('u')
            : ['u.id_area_trabajo = 1', []];
        $tecnicos = $db->fetchAll(
            "SELECT u.id, CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS nombre FROM usuarios u
              WHERE LOWER(u.estado) = 'activo' AND {$condTec} ORDER BY u.nombre, u.apellido_paterno",
            $paramTec
        );
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
$tickets = ticket_ordenar_lista($tickets);   // clic en las cabeceras (?orden=&dir=)
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
          <div class="form-group"><label class="form-label" for="admin-modal-tecnico">Técnico asignado <small class="text-muted">(Informática de Seduc)</small></label><select class="form-input" id="admin-modal-tecnico"><option value="0">Sin asignar</option><?php foreach ($tecnicos as $tecnico): ?><option value="<?= (int)$tecnico['id'] ?>"><?= e($tecnico['nombre']) ?></option><?php endforeach; ?></select><small class="tk-reasignar" id="admin-modal-tecnico-nota" hidden></small></div>
          <div class="form-group"><label class="form-label" for="admin-modal-estado">Estado</label><select class="form-input" id="admin-modal-estado"><?php foreach (ticket_estados_legacy() as $idEstado => $infoEstado): ?><option value="<?= $idEstado ?>"><?= e($infoEstado['nombre']) ?></option><?php endforeach; ?></select></div>
        </div>
        <div class="ticket-message" id="admin-modal-mensaje" role="status"></div>
      </div>
      <div class="modal-footer">
        <?php if ($esGlobal): ?><button class="btn btn-danger" id="admin-modal-eliminar" hidden><i class="bi bi-trash"></i> Eliminar</button><?php endif; ?>
        <button type="button" class="btn btn-outline" id="admin-modal-fusionar" hidden><i class="bi bi-intersect"></i> Fusionar</button>
        <a class="btn btn-outline" id="admin-modal-pdf" hidden href="#" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <button class="btn btn-outline js-ticket-correo" type="button" id="admin-modal-correo" hidden data-solo-yo="0"><i class="bi bi-envelope"></i> Correo</button>
        <button class="btn btn-outline" onclick="closeModal('modal-admin-ticket')">Cerrar</button><button class="btn btn-primary" id="admin-modal-guardar"><i class="bi bi-check2"></i> Guardar cambios</button>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modal-admin-eliminar" onclick="closeModalOutside(event,'modal-admin-eliminar')">
    <div class="modal" style="max-width:480px">
      <div class="modal-header"><h3>Eliminar ticket</h3><p id="admin-eliminar-folio"></p></div>
      <div class="modal-body">
        <p>El ticket dejará de aparecer en los listados. No se borrará de la base de datos.</p>
        <div class="form-group"><label class="form-label" for="admin-eliminar-motivo">Motivo obligatorio</label>
          <select class="form-input" id="admin-eliminar-motivo" required>
            <option value="">Selecciona un motivo</option>
            <option value="duplicado">Ticket duplicado</option>
            <option value="prueba_limpieza">Prueba o limpieza de registros</option>
            <option value="error_registro">Solicitud creada por error</option>
          </select>
        </div>
        <div class="ticket-message" id="admin-eliminar-mensaje" role="status"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-admin-eliminar')">Cancelar</button>
        <button type="button" class="btn btn-danger" id="admin-eliminar-confirmar"><i class="bi bi-trash"></i> Confirmar eliminación</button>
      </div>
    </div>
  </div>
  <div class="modal-overlay" id="modal-admin-fusionar" onclick="closeModalOutside(event,'modal-admin-fusionar')"><div class="modal" style="max-width:520px"><div class="modal-header"><h3>Fusionar tickets</h3><p id="fusion-folio"></p></div><div class="modal-body"><p>El ticket actual será el principal. El secundario conservará su historial y se terminará automáticamente cuando termine el principal.</p><div class="form-group"><label class="form-label" for="fusion-secundario">N° ticket secundario</label><input class="form-input" id="fusion-secundario" type="number" min="1" step="1"></div><div class="form-group"><label class="form-label" for="fusion-motivo">Motivo de fusión</label><textarea class="form-input" id="fusion-motivo" rows="3" maxlength="500"></textarea></div><div class="ticket-message" id="fusion-mensaje" role="status"></div></div><div class="modal-footer"><button class="btn btn-outline" type="button" onclick="closeModal('modal-admin-fusionar')">Cancelar</button><button class="btn btn-primary" type="button" id="fusion-confirmar">Confirmar fusión</button></div></div></div>
  <style>
    #admin-modal-eliminar[hidden],#admin-modal-fusionar[hidden],#admin-modal-pdf[hidden],#admin-modal-correo[hidden]{display:none!important}
    .ticket-more-menu{position:fixed;z-index:1100;width:224px;max-width:calc(100vw - 20px);padding:6px;background:var(--bs-body-bg,#fff);color:var(--bs-body-color,#243044);border:1px solid #d9dee6;border-radius:10px;box-shadow:0 12px 30px rgba(20,30,45,.17)}
    .ticket-more-menu[hidden]{display:none!important}
    .ticket-more-menu button,.ticket-more-menu a{display:flex;align-items:center;gap:10px;width:100%;padding:9px 11px;border:0;border-radius:6px;background:transparent;color:inherit;text-align:left;text-decoration:none;font:inherit;font-size:13px;cursor:pointer}
    .ticket-more-menu button:hover,.ticket-more-menu a:hover,.ticket-more-menu button:focus-visible,.ticket-more-menu a:focus-visible{background:#f0f3f7}
    .ticket-more-menu .ticket-more-danger{color:#b42318}
    .ticket-more-menu .ticket-more-danger:hover{background:#fff0ee}
    .ticket-more-menu .ticket-more-divider{border-top:1px solid #e3e7ed;margin:5px 2px}
    .ticket-more-menu i{width:17px;text-align:center}
    .ticket-more-menu button:disabled{opacity:.5;cursor:not-allowed}
  </style>
  <div class="ticket-more-menu" id="admin-ticket-more-menu" role="menu" aria-label="Acciones del ticket" hidden>
    <button type="button" role="menuitem" data-action="gestionar"><i class="bi bi-sliders"></i> Gestionar ticket</button>
    <button type="button" role="menuitem" data-action="fusionar"><i class="bi bi-intersect"></i> Fusionar tickets</button>
    <button type="button" role="menuitem" data-action="historial" disabled title="Disponible próximamente"><i class="bi bi-clock-history"></i> Historial (próximamente)</button>
    <div class="ticket-more-divider" role="separator"></div>
    <button type="button" role="menuitem" data-action="pdf"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</button>
    <button type="button" role="menuitem" data-action="correo"><i class="bi bi-envelope"></i> Enviar correo</button>
    <?php if ($esGlobal): ?><div class="ticket-more-divider" role="separator"></div><button type="button" role="menuitem" class="ticket-more-danger" data-action="eliminar"><i class="bi bi-trash"></i> Eliminar ticket</button><?php endif; ?>
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
  const ticketMoreMenu=document.getElementById('admin-ticket-more-menu');
  let ticketMoreTrigger=null;
  function cerrarMenuTicket(){
    ticketMoreMenu.hidden=true;
    if(ticketMoreTrigger){ticketMoreTrigger.setAttribute('aria-expanded','false');ticketMoreTrigger=null;}
  }
  function abrirMenuTicket(button,event){
    event?.stopPropagation();
    if(!ticketMoreMenu.hidden&&ticketMoreTrigger===button){cerrarMenuTicket();return;}
    cerrarMenuTicket();
    ticketMoreTrigger=button;
    button.setAttribute('aria-expanded','true');
    ticketMoreMenu.hidden=false;
    const rect=button.getBoundingClientRect();
    const width=ticketMoreMenu.offsetWidth, height=ticketMoreMenu.offsetHeight;
    const left=Math.max(10,Math.min(rect.right-width,window.innerWidth-width-10));
    const top=rect.bottom+6+height>window.innerHeight?Math.max(10,rect.top-height-6):rect.bottom+6;
    ticketMoreMenu.style.left=left+'px';ticketMoreMenu.style.top=top+'px';
    ticketMoreMenu.querySelector('button:not(:disabled)')?.focus();
  }
  ticketMoreMenu.addEventListener('click',event=>{
    const item=event.target.closest('[data-action]');
    if(!item||item.disabled||!ticketMoreTrigger)return;
    const trigger=ticketMoreTrigger;
    const action=item.dataset.action;
    cerrarMenuTicket();
    if(action==='historial')return;
    administrarTicket(trigger);
    if(action==='gestionar')return;
    // Las acciones independientes no requieren mantener visible el modal de gestión.
    closeModal('modal-admin-ticket');
    if(action==='fusionar')document.getElementById('admin-modal-fusionar')?.click();
    if(action==='eliminar')document.getElementById('admin-modal-eliminar')?.click();
    if(action==='correo')document.getElementById('admin-modal-correo')?.click();
    if(action==='pdf'){
      const url=document.getElementById('admin-modal-pdf').href;
      const nueva=window.open(url,'_blank','noopener,noreferrer');
      if(!nueva)window.location.href=url;
    }
  });
  document.addEventListener('click',event=>{if(!ticketMoreMenu.contains(event.target)&&!event.target.closest('.ticket-more-trigger'))cerrarMenuTicket()});
  document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!ticketMoreMenu.hidden){cerrarMenuTicket();event.preventDefault();}});
  window.addEventListener('resize',cerrarMenuTicket);
  window.addEventListener('scroll',cerrarMenuTicket,true);
  function escTicket(value){const d=document.createElement('div');d.textContent=value??'';return d.innerHTML}
  function administrarTicket(button){
    const t=JSON.parse(button.dataset.ticket); adminTicketActual=Number(t.id_ticket);
    document.getElementById('admin-modal-titulo').textContent=t.asunto;
    document.getElementById('admin-modal-folio').textContent=`Ticket #${t.id_ticket} · ${t.categoria_nombre}`;
    document.getElementById('admin-modal-grid').innerHTML=`<div class="ticket-detail-block"><span>Solicitante</span><strong>${escTicket(t.usuario_nombre)}</strong></div><div class="ticket-detail-block"><span>Colegio</span><strong>${escTicket(t.colegio_nombre)}</strong></div><div class="ticket-detail-block"><span>Prioridad</span><strong>${escTicket(t.prioridad)}</strong></div><div class="ticket-detail-block"><span>Creado</span><strong>${escTicket(t.fecha_creacion)}</strong></div>`;
    document.getElementById('admin-modal-descripcion').textContent=t.descripcion_texto||t.descripcion;
    const selTec=document.getElementById('admin-modal-tecnico'), notaTec=document.getElementById('admin-modal-tecnico-nota');
    selTec.querySelectorAll('option[data-externo]').forEach(o=>o.remove());
    if(t.id_tecnico_asignado && !selTec.querySelector(`option[value="${t.id_tecnico_asignado}"]`)){const o=new Option(`${t.tecnico_nombre} (fuera de Informática)`,t.id_tecnico_asignado);o.dataset.externo='1';selTec.add(o)}
    selTec.value=t.id_tecnico_asignado||'0';
    // Reasignar solo si el técnico aún no lo comienza (Recibido / Asignado)
    const reasignable=[1,2].includes(Number(t.id_estado));
    selTec.disabled=!reasignable;
    notaTec.hidden=reasignable;
    notaTec.innerHTML=reasignable?'':`<i class="bi bi-lock"></i> ${escTicket(t.tecnico_nombre||'El técnico')} ya comenzó el ticket: no se puede reasignar.`;
    document.getElementById('admin-modal-estado').value=t.id_estado;
    document.getElementById('admin-modal-pdf').href=`../ajax/ticket_pdf.php?id=${t.id_ticket}&vista=admin`;
    const btnCorreo=document.getElementById('admin-modal-correo'); btnCorreo.dataset.ticketId=t.id_ticket; btnCorreo.dataset.email=t.usuario_email||'';
    document.getElementById('admin-modal-mensaje').className='ticket-message'; openModal('modal-admin-ticket');
  }
  async function accionTicket(accion, extra={}){
    const message=document.getElementById('admin-modal-mensaje');
    try{const body=new URLSearchParams({accion,ticket_id:adminTicketActual,csrf:adminTicketCsrf,...extra});const response=await fetch('../ajax/guardar_ticket.php',{method:'POST',body,credentials:'same-origin'});const data=await response.json();if(!response.ok||!data.ok)throw new Error(data.error||'No fue posible guardar.');message.className='ticket-message ok';message.textContent=data.mensaje;setTimeout(()=>location.reload(),650)}catch(error){message.className='ticket-message error';message.textContent=error.message}}
  document.getElementById('admin-modal-guardar').onclick=()=>accionTicket('actualizar',{tecnico_id:document.getElementById('admin-modal-tecnico').value,estado:document.getElementById('admin-modal-estado').value});
  document.getElementById('admin-modal-eliminar')?.addEventListener('click',()=>{
     document.getElementById('admin-eliminar-folio').textContent=`Ticket #${adminTicketActual}`;
     document.getElementById('admin-eliminar-motivo').value='';
     const msg=document.getElementById('admin-eliminar-mensaje');msg.textContent='';msg.className='ticket-message';
     openModal('modal-admin-eliminar');
   });
   document.getElementById('admin-eliminar-confirmar')?.addEventListener('click',async()=>{
     const motivo=document.getElementById('admin-eliminar-motivo').value;
     const msg=document.getElementById('admin-eliminar-mensaje');
     if(!motivo){msg.className='ticket-message error';msg.textContent='Selecciona un motivo.';return;}
     const btn=document.getElementById('admin-eliminar-confirmar');btn.disabled=true;
     try{
       const body=new URLSearchParams({accion:'eliminar',ticket_id:adminTicketActual,csrf:adminTicketCsrf,motivo_eliminacion:motivo});
       const response=await fetch('../ajax/guardar_ticket.php',{method:'POST',body,credentials:'same-origin'});
       const data=await response.json();
       if(!response.ok||!data.ok)throw new Error(data.error||'No se pudo eliminar el ticket.');
       msg.className='ticket-message ok';msg.textContent=data.mensaje;
       setTimeout(()=>location.reload(),650);
     }catch(error){msg.className='ticket-message error';msg.textContent=error.message;btn.disabled=false;}
   });
  document.getElementById('admin-modal-fusionar').addEventListener('click',()=>{
    document.getElementById('fusion-folio').textContent='Ticket principal #'+adminTicketActual;
    document.getElementById('fusion-secundario').value='';
    document.getElementById('fusion-motivo').value='';
    document.getElementById('fusion-mensaje').textContent='';
    openModal('modal-admin-fusionar');
  });
  document.getElementById('fusion-confirmar').addEventListener('click',async()=>{
    const id=Number(document.getElementById('fusion-secundario').value);
    const motivo=document.getElementById('fusion-motivo').value.trim();
    const msg=document.getElementById('fusion-mensaje');
    if(!Number.isSafeInteger(id)||id<=0||id===adminTicketActual||motivo.length<5){msg.className='ticket-message error';msg.textContent='Indica otro ticket válido y un motivo de al menos cinco caracteres.';return;}
    if(!confirm('¿Fusionar el ticket #'+id+' con el principal #'+adminTicketActual+'?'))return;
    const btn=document.getElementById('fusion-confirmar');btn.disabled=true;
    try{
      const body=new URLSearchParams({accion:'fusionar',ticket_id:adminTicketActual,ticket_secundario_id:id,motivo_fusion:motivo,csrf:adminTicketCsrf});
      const response=await fetch('../ajax/guardar_ticket.php',{method:'POST',body,credentials:'same-origin'});
      const data=await response.json();
      if(!response.ok||!data.ok)throw new Error(data.error||'No se pudo fusionar.');
      msg.className='ticket-message ok';msg.textContent=data.mensaje;setTimeout(()=>location.reload(),800);
    }catch(error){msg.className='ticket-message error';msg.textContent=error.message;btn.disabled=false;}
  });
  document.getElementById('admin-buscar').addEventListener('input',event=>{const q=event.target.value.trim().toLowerCase();document.querySelectorAll('#admin-tabla tbody tr').forEach(row=>row.hidden=!row.dataset.search.includes(q))});
  </script>
<?php endif; ?>

<?php require __DIR__ . '/componentes/modal_correo_ticket.php'; ?>
<?php $vistaChat = 'admin'; require __DIR__ . '/componentes/chat_ticket_panel.php'; ?>
<?php require __DIR__ . '/componentes/panel_detalle.php'; ?>
<?php finalizar_layout_configuracion(); ?>
