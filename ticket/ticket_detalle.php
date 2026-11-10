<?php
declare(strict_types=1);

$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';
require_once __DIR__ . '/../clases/Tickets/ConversacionTicket.php';
require_once __DIR__ . '/../clases/Tickets/BitacoraTicket.php';
require_once __DIR__ . '/../clases/Notificaciones.php';

$usuarioId = (int) Sesion::get('id', 0);
$ticketId = (int) ($_GET['id'] ?? 0);
$ticket = false;
$comentarios = [];
$adjuntos = [];
$calificacion = null;
$conversacionDisponible = false;
$calificacionDisponible = false;
$errorCarga = null;

try {
    $ticket = $ticketId > 0 ? $db->fetchOne(
        "SELECT t.id_ticket, t.id_usuario, t.id_tecnico, uc_ticket.id_colegio,
                t.asunto, t.descripcion_ticket AS descripcion, t.id_estado,
                e.nombre AS estado_nombre, e.color AS estado_color,
                e.color_degradado AS estado_degradado, t.id_prioridad,
                COALESCE(CONCAT(pt.fecha_creacion_inicio, ' ', COALESCE(pt.hora_creacion_inicio, '00:00:00')), '') AS fecha_creacion,
                COALESCE(CONCAT(pt.fecha_asignacion_tecnico, ' ', COALESCE(pt.hora_asignacion_tecnico, '00:00:00')), '') AS fecha_respuesta,
                pt.fecha_estimada_admin AS fecha_estimada,
                COALESCE(CONCAT(pt.fecha_comienzo_ticket, ' ', COALESCE(pt.hora_comienzo_ticket, '00:00:00')), '') AS fecha_comienzo,
                COALESCE(CONCAT(pt.fecha_termino_ticket, ' ', COALESCE(pt.hora_termino_ticket, '00:00:00')), '') AS fecha_termino,
                c.nombre_categoria AS categoria_nombre, col.nom_colegio AS colegio_nombre,
                CONCAT_WS(' ', sol.nombre, sol.apellido_paterno) AS usuario_nombre, sol.email AS usuario_email,
                CONCAT_WS(' ', tec.nombre, tec.apellido_paterno) AS tecnico_nombre
           FROM tickets t
           JOIN usuarios sol ON sol.id = t.id_usuario
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
          WHERE t.id_ticket = ? AND t.estado = 1 LIMIT 1",
        [$ticketId]
    ) : false;
    if ($ticket) {
        $ticket = ticket_normalizar_fila($ticket);
    }
} catch (Throwable $ex) {
    error_log('Error al abrir detalle de ticket: ' . $ex->getMessage());
    $errorCarga = 'No fue posible consultar el ticket.';
}

$autorizado = $ticket && puede_ver_ticket($usuarioId, $ticket, $db);
$esPropietario = $autorizado && (int) $ticket['id_usuario'] === $usuarioId;
$esTecnico = $autorizado && (int) ($ticket['id_tecnico_asignado'] ?? 0) === $usuarioId;
$esAdministrador = $autorizado && puede_administrar_ticket($usuarioId, (int) $ticket['id_colegio'], $db);
// Bandeja desde la que se abrió (?vista=usuario|tecnico|admin). Define qué se puede hacer:
//   usuario → lo que ve quien pidió el ticket: chat, ciclo, QR y calificación (sin bitácora ni estados)
//   tecnico → además bitácora, Comenzar / Terminar y cambiar estado
//   admin   → bitácora como administración y cambiar estado
$vistasPosibles = array_keys(array_filter(['usuario' => $esPropietario, 'tecnico' => $esTecnico, 'admin' => $esAdministrador]));
$vista = in_array($_GET['vista'] ?? '', $vistasPosibles, true) ? (string) $_GET['vista'] : ($vistasPosibles[0] ?? 'usuario');
$puedeCambiarEstado = ($vista === 'tecnico' && $esTecnico) || ($vista === 'admin' && $esAdministrador);

if ($autorizado) {
    try {
        // Mensajería: ticket_conversaciones (ticket/logica/LOGICA_MENSAJERIA.txt)
        $conversacion = new ConversacionTicket($db);
        $vistaChat = $conversacion->resolverVista($usuarioId, $ticket, $vista) ?? 'admin';
        $conversacion->marcarLeidos($ticketId, $vistaChat);
        $comentarios = $conversacion->mensajes($ticketId, $vistaChat);
        $conversacionDisponible = true;
    } catch (Throwable $ex) {
        error_log('Error al consultar conversación del ticket: ' . $ex->getMessage());
        $conversacionDisponible = false;
    }

    // Abrir el detalle marca como leídos los avisos de la campanita de este ticket
    (new Notificaciones($db))->leerPorTicket($usuarioId, $ticketId);

    // Bitácora interna técnico ↔ administración (ticket/logica/LOGICA_BITACORA.txt)
    $bitacora = new BitacoraTicket($db);
    $rolBitacora = $vista === 'usuario' ? null : $bitacora->rolPara($usuarioId, $ticket, $vista);
    $bitacoraLista = $rolBitacora !== null && $bitacora->disponible();
    $entradasBitacora = [];
    if ($bitacoraLista) {
        try {
            $bitacora->marcarLeidos($ticketId, $rolBitacora, $usuarioId);
            $entradasBitacora = $bitacora->listar($ticketId, $usuarioId);
        } catch (Throwable $ex) {
            error_log('Error al consultar bitácora: ' . $ex->getMessage());
        }
    }

    try {
        $adjuntos = ticket_listar_adjuntos($db, $ticketId);
    } catch (Throwable $ex) {
        $adjuntos = [];
    }

    try {
        $tablaCalificacion = 'calificacion_ticket';
        $columnasCalificacion = ticket_columnas_tabla($tablaCalificacion, $db);
        if (!$columnasCalificacion) {
            $tablaCalificacion = 'calificacion_tickett';
            $columnasCalificacion = ticket_columnas_tabla($tablaCalificacion, $db);
        }
        $campoPuntaje = isset($columnasCalificacion['calificacion']) ? 'calificacion' : null;
        if ($campoPuntaje === null && isset($columnasCalificacion['id_calificacion']) && !str_contains($columnasCalificacion['id_calificacion']['extra'], 'auto_increment')) {
            $campoPuntaje = 'id_calificacion';
        }
        $calificacionDisponible = isset($columnasCalificacion['id_ticket'], $columnasCalificacion['id_usuario']) && $campoPuntaje !== null;
        if ($calificacionDisponible) {
            $campoComentario = isset($columnasCalificacion['comentario']) ? 'comentario' : "''";
            $calificacion = $db->fetchOne(
                "SELECT {$campoPuntaje} AS puntaje, {$campoComentario} AS comentario FROM {$tablaCalificacion} WHERE id_ticket = ? LIMIT 1",
                [$ticketId]
            ) ?: null;
        }
    } catch (Throwable $ex) {
        error_log('Error al consultar calificación del ticket: ' . $ex->getMessage());
        $calificacionDisponible = false;
    }
}

$volver = ['usuario' => 'ticket.php', 'tecnico' => 'ticket_asignados.php', 'admin' => 'ticket_admin_v2.php'][$vista] ?? 'ticket.php';
$csrf = ticket_csrf_token();
$rolBitacora = $rolBitacora ?? null;
$tecnicoNombre = $ticket ? trim((string) ($ticket['tecnico_nombre'] ?? '')) : '';
$textoChat = !$ticket ? 'Abrir chat' : (ticket_estado_sin_escritura((int) $ticket['id_estado']) ? 'Ver conversación'
    : ($vista === 'usuario' ? ($tecnicoNombre !== '' ? 'Abrir chat con el técnico' : 'Escribir al equipo de soporte')
    : ($vista === 'tecnico' ? 'Abrir chat con el solicitante' : 'Abrir chat')));
$enlaceSeguimiento = '';
if ($ticket && $autorizado && (int) $ticket['id_estado'] !== 4) {
    try {   // QR para seguir el ticket desde el celular (ticket/seguimiento.php)
        $enlaceSeguimiento = ticket_enlace_seguimiento(ticket_codigo_seguimiento($db, (int) $ticket['id_ticket']));
    } catch (Throwable $ex) {
        error_log('Error al preparar el QR del ticket: ' . $ex->getMessage());
    }
}
$cerradoTicket = $ticket ? ticket_estado_sin_escritura((int) $ticket['id_estado']) : true;
// Ciclo del ticket (como el modal del sistema antiguo)
$fechaCorta = static fn (string $f): string => strtotime($f) ? date('d/m/Y H:i', strtotime($f)) : '';
$pasosCiclo = $ticket ? [
    ['Creado', 'bi-plus-lg', $fechaCorta((string) $ticket['fecha_creacion']), true],
    ['Asignado', 'bi-person-check', $fechaCorta((string) $ticket['fecha_respuesta']), (int) $ticket['id_estado'] !== 1 && (int) $ticket['id_estado'] !== 4],
    ['En proceso', 'bi-gear', $fechaCorta((string) ($ticket['fecha_comienzo'] ?? '')), in_array((int) $ticket['id_estado'], [3, 5, 6, 7], true)],
    ['Terminado', 'bi-flag', in_array((int) $ticket['id_estado'], [5, 6], true)
        ? $fechaCorta((string) ($ticket['fecha_termino'] ?? ''))
        : (!empty($ticket['fecha_estimada']) && strtotime((string) $ticket['fecha_estimada']) > 0 ? 'Estimado: ' . date('d/m/Y', strtotime((string) $ticket['fecha_estimada'])) : ''),
        in_array((int) $ticket['id_estado'], [5, 6], true)],
] : [];
$estado = $ticket ? strtolower((string) $ticket['estado']) : '';
$estadoTexto = $ticket ? (string) $ticket['estado_nombre'] : '';

$layout_embebido = isset($_GET['embed']);   // dentro del panel lateral de las bandejas
iniciar_layout_configuracion('Detalle de ticket', 'Tickets', 'ticket_detalle');
?>
<?php if (!$ticket || !$autorizado): http_response_code($ticket ? 403 : 404); ?>
  <section class="card ticket-denied">
    <i class="bi <?= $ticket ? 'bi-shield-lock' : 'bi-ticket-detailed' ?>"></i>
    <h1><?= $ticket ? 'Acceso restringido' : 'Ticket no encontrado' ?></h1>
    <p class="text-muted mt-3"><?= e($errorCarga ?: ($ticket ? 'No tienes permiso para consultar esta solicitud.' : 'El folio solicitado no existe o ya no está disponible.')) ?></p>
    <a class="btn btn-outline mt-3" href="ticket.php">Volver a tickets</a>
  </section>
<?php else: ?>
  <div id="ticket-detail-app" data-ticket-id="<?= (int) $ticket['id_ticket'] ?>" data-csrf="<?= e($csrf) ?>">
    <div class="ticket-detail-heading">
      <?php if (!$layout_embebido): ?><a class="ticket-detail-back" href="<?= e($volver) ?>"><i class="bi bi-arrow-left"></i> Volver a la bandeja</a><?php endif; ?>
      <div class="ticket-detail-titleline">
        <div><span class="ticket-detail-kicker">Solicitud #<?= (int) $ticket['id_ticket'] ?></span><h1><?= e($ticket['asunto']) ?></h1></div>
        <div class="tb-cabecera-acciones">
          <?php if ($vista === 'usuario' && $esPropietario && $calificacionDisponible && (int) $ticket['id_estado'] === 5 && !$calificacion): ?>
             <button class="btn btn-sm ticket-rating-cta" type="button" id="abrir-calificacion" aria-haspopup="dialog" aria-controls="modal-calificacion-detalle"><i class="bi bi-star-fill"></i> Calificar atención <span aria-hidden="true">★★★★★</span></button>
           <?php endif; ?>
           <?php if ($calificacionDisponible && $calificacion && in_array($vista, ['admin','tecnico'], true)): ?>
             <span class="ticket-rating-summary" title="Calificación del solicitante: <?= (int) $calificacion['puntaje'] ?> de 5 estrellas"><i class="bi bi-star-fill"></i> <?= (int) $calificacion['puntaje'] ?>/5</span>
           <?php endif; ?>
           <a class="btn btn-outline btn-sm" href="../ajax/ticket_pdf.php?id=<?= (int) $ticket['id_ticket'] ?>&amp;vista=<?= e($vista) ?>" target="_blank" rel="noopener" title="Ver o descargar el ticket en PDF"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
          <button class="btn btn-outline btn-sm js-ticket-correo" type="button" data-ticket-id="<?= (int) $ticket['id_ticket'] ?>" data-email="<?= e($ticket['usuario_email'] ?? '') ?>" data-solo-yo="<?= $vista === 'usuario' ? '1' : '0' ?>" title="Enviar el ticket por correo con el PDF adjunto"><i class="bi bi-envelope"></i> Enviar por correo</button>
          <?php if ($vista === 'usuario'): ?><button class="btn btn-primary btn-sm js-ticket-chat" type="button" data-ticket-id="<?= (int) $ticket['id_ticket'] ?>" title="<?= e($tecnicoNombre !== '' ? 'Técnico: ' . $tecnicoNombre : 'Aún sin técnico') ?>"><i class="bi bi-chat-dots"></i> <?= e($textoChat) ?></button><?php endif; ?>
          <?= ticket_badge_estado($ticket) ?>
        </div>
      </div>
    </div>

    <div class="ticket-detail-layout">
      <div class="ticket-detail-main">
        <?php if ($adjuntos): ?>
          <?php
            $imagenesTicket = [];
            $documentosTicket = [];
            foreach ($adjuntos as $archivo) {
                $extensionArchivo = strtolower((string) ($archivo['extension'] ?? ''));
                if (in_array($extensionArchivo, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                    $imagenesTicket[] = $archivo;
                } else {
                    $documentosTicket[] = $archivo;
                }
            }
          ?>
          <section class="card ticket-detail-files">
            <h2>Archivos adjuntos</h2>
            <?php if ($imagenesTicket): ?>
              <div class="ticket-image-gallery" data-gallery>
                <div class="ticket-image-gallery-head"><strong>Imágenes (<?= count($imagenesTicket) ?>)</strong><span data-gallery-count>1 de <?= count($imagenesTicket) ?></span></div>
                <div class="ticket-image-gallery-stage">
                  <?php foreach ($imagenesTicket as $indice => $archivo): ?>
                    <a class="ticket-image-gallery-slide" data-gallery-slide href="../ajax/ticket_archivo.php?a=<?= (int) $archivo['id'] ?>" target="_blank" rel="noopener" <?= $indice === 0 ? '' : 'hidden' ?> title="Ampliar imagen">
                      <img src="../ajax/ticket_archivo.php?a=<?= (int) $archivo['id'] ?>" alt="<?= e($archivo['nombre']) ?>" loading="lazy" decoding="async">
                    </a>
                  <?php endforeach; ?>
                  <?php if (count($imagenesTicket) > 1): ?>
                    <button type="button" class="ticket-image-gallery-arrow ticket-image-gallery-prev" data-gallery-prev aria-label="Imagen anterior"><i class="bi bi-chevron-left"></i></button>
                    <button type="button" class="ticket-image-gallery-arrow ticket-image-gallery-next" data-gallery-next aria-label="Imagen siguiente"><i class="bi bi-chevron-right"></i></button>
                  <?php endif; ?>
                </div>
                <div class="ticket-image-gallery-thumbs">
                  <?php foreach ($imagenesTicket as $indice => $archivo): ?>
                    <button type="button" class="ticket-image-gallery-thumb <?= $indice === 0 ? 'is-active' : '' ?>" data-gallery-thumb="<?= $indice ?>" aria-label="Ver imagen <?= $indice + 1 ?>" aria-pressed="<?= $indice === 0 ? 'true' : 'false' ?>"><img src="../ajax/ticket_archivo.php?a=<?= (int) $archivo['id'] ?>" alt="" loading="lazy"></button>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
            <?php if ($documentosTicket): ?>
              <div class="ticket-image-gallery-documents">
                <?php foreach ($documentosTicket as $archivo): ?>
                  <a href="../ajax/ticket_archivo.php?a=<?= (int) $archivo['id'] ?>" target="_blank" rel="noopener"><i class="bi bi-file-earmark"></i><span><?= e($archivo['nombre']) ?><small><?= $archivo['tamano'] ? e(number_format($archivo['tamano'] / 1024, 0, ',', '.')) . ' KB' : e(strtoupper((string) $archivo['extension'])) ?></small></span><i class="bi bi-box-arrow-up-right"></i></a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </section>
        <?php endif; ?>


        <section class="card ticket-detail-section">
          <div class="card-header"><div><h2 class="card-title">Descripción del caso</h2><p class="card-desc"><?= e($ticket['categoria_nombre']) ?></p></div></div>
          <div class="ticket-detail-copy ticket-rich-content"><?= ticket_renderizar_descripcion((string) $ticket['descripcion']) ?></div>
        </section>

        <section class="card ticket-conversation" id="conversacion">
          <div class="card-header"><div><h2 class="card-title">Conversación</h2><p class="card-desc">Seguimiento entre solicitante y equipo de soporte.</p></div><span class="ticket-list-count"><?= count($comentarios) ?> mensaje<?= count($comentarios) === 1 ? '' : 's' ?></span></div>
          <div class="ticket-thread" id="ticket-thread">
            <?php if (!$comentarios): ?><div class="ticket-thread-empty">Aún no hay mensajes.</div><?php endif; ?>
            <?php foreach ($comentarios as $mensaje): ?>
              <article class="ticket-message-row <?= $mensaje['propio'] ? 'is-own' : '' ?>">
                <div class="ticket-message-avatar"><?= e(mb_strtoupper(mb_substr($mensaje['autor'], 0, 1)) ?: 'U') ?></div>
                <div class="ticket-message-bubble"><div><strong><?= e($mensaje['autor']) ?></strong><time><?= $mensaje['fecha'] ? e(date('d/m/Y H:i', strtotime($mensaje['fecha']))) : '' ?></time></div>
                  <?php if ($mensaje['adjunto']): ?><p><a href="../ajax/ticket_archivo.php?m=<?= (int) $mensaje['id'] ?>" target="_blank" rel="noopener"><i class="bi bi-paperclip"></i> <?= e($mensaje['adjunto']['nombre']) ?></a></p>
                  <?php else: ?><p><?= nl2br(e($mensaje['texto'])) ?></p><?php endif; ?>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
          <div class="ticket-reply" style="display:flex;justify-content:flex-end">
            <button class="btn btn-primary js-ticket-chat" type="button" data-ticket-id="<?= (int) $ticketId ?>"><i class="bi bi-chat-dots"></i> <?= e($textoChat) ?></button>
          </div>
        </section>

        <?php if ($rolBitacora !== null): ?>
        <section class="card tb-card" id="bitacora" data-rol="<?= e($rolBitacora) ?>" data-cerrado="<?= $cerradoTicket ? '1' : '0' ?>">
          <div class="card-header">
            <div><h2 class="card-title"><i class="bi bi-journal-text"></i> Bitácora del técnico</h2>
              <p class="card-desc">Avances del trabajo y conversación interna con administración. <strong>El solicitante no la ve.</strong></p></div>
            <span class="ticket-list-count" id="tb-total"><?= count($entradasBitacora) ?> registro<?= count($entradasBitacora) === 1 ? '' : 's' ?></span>
          </div>
          <?php if (!$bitacoraLista): ?>
            <div class="ticket-empty"><i class="bi bi-database-exclamation"></i>Falta instalar la bitácora en la base de datos: ejecutar <code>ticket/sql/02_bitacora.sql</code>.</div>
          <?php else: ?>
            <ol class="tb-list" id="tb-list" aria-live="polite"></ol>
            <?php if (!$cerradoTicket): ?>
              <form class="tb-form" id="tb-form">
                <label class="tc-sr" for="tb-texto">Nuevo avance</label>
                <textarea class="form-input" id="tb-texto" maxlength="3000" rows="3" required placeholder="<?= $rolBitacora === 'tecnico' ? 'Escribe el nuevo avance… (ej.: se cambió el tóner y se probó la impresión)' : 'Escribe una indicación o pregunta para el técnico…' ?>"></textarea>
                <div class="tb-form__pie"><small>Ctrl + Enter para registrar · como <strong><?= $rolBitacora === 'tecnico' ? 'técnico' : 'administración' ?></strong></small>
                  <button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> <?= $rolBitacora === 'tecnico' ? 'Registrar avance' : 'Enviar al técnico' ?></button></div>
                <div class="ticket-message" id="tb-mensaje" role="status"></div>
              </form>
            <?php else: ?>
              <div class="ticket-chat__closed"><i class="bi bi-lock"></i><span>Ticket terminado: la bitácora queda como registro.</span></div>
            <?php endif; ?>
            <script type="application/json" id="tb-datos"><?= json_encode($entradasBitacora, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>
          <?php endif; ?>
        </section>
        <?php endif; ?>
      </div>

      <aside class="ticket-detail-side">
        <section class="card tb-ciclo">
          <h2>Ciclo del ticket</h2>
          <?php $datosPlazo = ['id_estado' => $ticket['id_estado'], 'fecha_creacion' => $ticket['fecha_creacion'] ?? null, 'fecha_comienzo_ticket' => $ticket['fecha_comienzo'] ?? null, 'fecha_estimada_admin' => $ticket['fecha_estimada'] ?? null, 'fecha_termino_ticket' => $ticket['fecha_termino'] ?? null]; ?>
          <?= $vista === 'usuario' ? ticket_plazo($datosPlazo) : ticket_avance($datosPlazo) ?>
          <ol>
            <?php foreach ($pasosCiclo as [$nombrePaso, $iconoPaso, $fechaPaso, $hecho]): ?>
              <li class="<?= $hecho ? 'is-done' : '' ?>"><span><i class="bi <?= $hecho ? 'bi-check-lg' : $iconoPaso ?>"></i></span><div><strong><?= e($nombrePaso) ?></strong><small><?= e($fechaPaso ?: '—') ?></small></div></li>
            <?php endforeach; ?>
          </ol>
          <?php if ($vista === 'tecnico' && $esTecnico && in_array((int) $ticket['id_estado'], [1, 2], true)): ?>
            <button class="btn btn-primary tb-accion js-comenzar-ticket" type="button" data-ticket-id="<?= (int) $ticket['id_ticket'] ?>" data-asunto="<?= e($ticket['asunto']) ?>"><i class="bi bi-play-fill"></i> Comenzar ticket</button>
            <small class="tb-ciclo__ayuda">Al comenzar defines la prioridad y la fecha estimada.</small>
          <?php elseif ($vista === 'tecnico' && $esTecnico && in_array((int) $ticket['id_estado'], [3, 7], true)): ?>
            <button class="btn btn-primary tb-accion" type="button" data-estado="5" data-confirmar="¿Marcar el ticket como terminado? La conversación y la bitácora quedarán como registro."><i class="bi bi-flag-fill"></i> Terminar ticket</button>
          <?php endif; ?>
        </section>
        <?php if ($enlaceSeguimiento !== ''): ?>
        <section class="card tb-qr">
          <h2><i class="bi bi-qr-code"></i> Seguimiento en el celular</h2>
          <div class="tb-qr__codigo" id="tb-qr" data-enlace="<?= e($enlaceSeguimiento) ?>" role="img" aria-label="Código QR para seguir el ticket #<?= (int) $ticket['id_ticket'] ?>"></div>
          <p>Escanéalo con la cámara para ver en qué va el ticket, sin iniciar sesión.</p>
          <div class="tb-qr__botones">
            <button class="btn btn-outline btn-sm" type="button" id="tb-qr-copiar"><i class="bi bi-link-45deg"></i> Copiar enlace</button>
            <a class="btn btn-outline btn-sm" href="<?= e($enlaceSeguimiento) ?>" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Abrir</a>
          </div>
        </section>
        <?php endif; ?>

        <section class="card ticket-detail-facts">
          <h2>Datos del ticket</h2>
          <dl><div><dt>Solicitante</dt><dd><?= e($ticket['usuario_nombre']) ?></dd></div><div><dt>Colegio</dt><dd><?= e($ticket['colegio_nombre'] ?: 'Sin colegio') ?></dd></div><div><dt>Técnico</dt><dd><?= e(trim((string) $ticket['tecnico_nombre']) ?: 'Sin asignar') ?></dd></div><div><dt>Fecha estimada</dt><dd><?= !empty($ticket['fecha_estimada']) && strtotime((string) $ticket['fecha_estimada']) > 0 ? e(date('d/m/Y', strtotime((string) $ticket['fecha_estimada']))) : 'La define el técnico al comenzar' ?></dd></div><div><dt>Prioridad</dt><dd><span class="ticket-priority ticket-priority--<?= e(ticket_prioridad_clase((string) $ticket['prioridad'])) ?>"><?= e($ticket['prioridad']) ?></span></dd></div><div><dt>Creado</dt><dd><?= strtotime((string)$ticket['fecha_creacion']) ? e(date('d/m/Y H:i', strtotime((string)$ticket['fecha_creacion']))) : 'Sin fecha' ?></dd></div></dl>
        </section>

        <?php if ($puedeCambiarEstado): ?><section class="card ticket-detail-manage"><h2>Actualizar estado</h2><form id="ticket-state-form" data-estado-actual="<?= (int) $ticket['id_estado'] ?>" data-ticket-id="<?= (int) $ticket['id_ticket'] ?>" data-asunto="<?= e($ticket['asunto']) ?>"><label class="form-label" for="ticket-state">Estado del caso</label><select class="form-input" id="ticket-state" name="estado"><?php foreach (ticket_estados_legacy() as $valor=>$info): ?><option value="<?= $valor ?>" <?= (int)$ticket['id_estado'] === $valor ? 'selected' : '' ?>><?= e($info['nombre']) ?></option><?php endforeach; ?></select><button class="btn btn-primary" type="submit">Guardar estado</button><div class="ticket-message" id="ticket-state-message" role="status"></div></form></section><?php endif; ?>

        <?php if ($calificacionDisponible && $calificacion && (($vista === 'usuario' && $esPropietario) || in_array($vista, ['admin','tecnico'], true))): ?>
          <section class="card ticket-rating" id="calificacion">
            <h2><?= $vista === 'usuario' ? 'Califica la atención' : 'Calificación del solicitante' ?></h2>
            <?php if ($calificacion): ?>
              <div class="ticket-rating-result">
                <div class="ticket-rating-readonly" aria-label="<?= (int) $calificacion['puntaje'] ?> de 5 estrellas"><?= str_repeat('★', max(0, min(5, (int) $calificacion['puntaje']))) . str_repeat('☆', 5 - max(0, min(5, (int) $calificacion['puntaje']))) ?></div>
                <p><?= e($calificacion['comentario'] ?: 'Sin comentario adicional.') ?></p>
              </div>
            <?php else: ?>
              <?php /* El formulario se abre desde el modal superior. */ ?>
            <?php endif; ?>
          </section>
        <?php endif; ?>
      </aside>
    </div>
  </div>
  <style>
    .ticket-rating-cta{background:#f4c84b!important;color:#493500!important;border:1px solid #dfaa19!important;animation:ticket-rating-flash 1.25s ease-in-out infinite;white-space:nowrap;font-weight:700}
    .ticket-rating-cta span{letter-spacing:1px;font-size:12px;color:#795000}
    @keyframes ticket-rating-flash{0%,100%{background:#f4c84b;filter:brightness(1);box-shadow:0 0 0 0 rgba(238,178,23,.25)}50%{background:#ffe58c;filter:brightness(1.15);box-shadow:0 0 0 6px rgba(238,178,23,.08)}}
    @media(prefers-reduced-motion:reduce){.ticket-rating-cta{animation:none}}
    .ticket-rating-summary{display:inline-flex;align-items:center;gap:6px;color:#9a6400;background:#fff4d8;border:1px solid #ead9ac;border-radius:7px;padding:7px 10px;font-weight:600;font-size:13px;white-space:nowrap}
    .ticket-rating-readonly{font-size:27px;color:#c58a14;letter-spacing:2px}
    .ticket-rating-dialog{position:fixed;inset:0;margin:auto;width:min(440px,calc(100vw - 32px));max-height:calc(100vh - 32px);overflow:auto;padding:24px;border:1px solid #e0e4eb;border-radius:14px;background:#fff;color:#233047;box-shadow:0 18px 70px rgba(10,24,45,.24)}
    .ticket-rating-dialog::backdrop{background:rgba(10,22,38,.48)}
    .ticket-rating-dialog-head{display:flex;justify-content:space-between;align-items:flex-start;gap:14px;margin-bottom:18px}
    .ticket-rating-dialog-head h2{font-size:19px;margin:0 0 5px}
    .ticket-rating-dialog-head p{font-size:13px;color:#64748b;margin:0}
    .ticket-rating-dialog-close{border:0;background:#f1f4f8;border-radius:8px;padding:8px 10px;cursor:pointer}
    .ticket-rating-dialog .ticket-stars label{color:#e0ad27}
    .ticket-rating-dialog form{display:grid;gap:12px}
    .ticket-rating-dialog textarea{min-height:80px}
    @media(prefers-reduced-motion:reduce){.ticket-rating-cta{animation:none}}
  </style>
        <?php if ($calificacionDisponible && $vista === 'usuario' && $esPropietario && (int) $ticket['id_estado'] === 5 && !$calificacion): ?>
        <dialog id="modal-calificacion-detalle" class="ticket-rating-dialog" aria-labelledby="titulo-calificacion-detalle">
          <div class="ticket-rating-dialog-head">
            <div><h2 id="titulo-calificacion-detalle">Califica la atención</h2><p>¿Cómo fue la atención que recibiste?</p></div>
            <button type="button" class="ticket-rating-dialog-close" id="cerrar-calificacion" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
          </div>
                        <form id="ticket-rating-form">
                <fieldset>
                  <legend>Selecciona de 1 a 5 estrellas</legend>
                  <div class="ticket-stars">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                      <input type="radio" id="rating-<?= $i ?>" name="calificacion" value="<?= $i ?>" required>
                      <label for="rating-<?= $i ?>" aria-label="<?= $i ?> estrellas">★</label>
                    <?php endfor; ?>
                  </div>
                </fieldset>
                <label class="form-label" for="ticket-rating-comment">Comentario opcional</label>
                <textarea class="form-input" id="ticket-rating-comment" name="comentario" maxlength="1000" placeholder="Cuéntanos cómo fue la atención…"></textarea>
                <button class="btn btn-primary" type="submit"><i class="bi bi-star"></i> Guardar calificación</button>
                <div class="ticket-message" id="ticket-rating-message" role="status"></div>
              </form>
        </dialog>
        <?php endif; ?>
  <script>
    (() => {
      const dialog=document.getElementById('modal-calificacion-detalle');
      const trigger=document.getElementById('abrir-calificacion');
      if(!dialog||!trigger)return;
      trigger.addEventListener('click',()=>{if(!dialog.open)dialog.showModal();});
      document.getElementById('cerrar-calificacion')?.addEventListener('click',()=>dialog.close());
      dialog.addEventListener('click',event=>{if(event.target===dialog)dialog.close();});
    })();
  </script>
  <style>
    .ticket-image-gallery{width:100%;min-width:0;margin-top:12px}
    .ticket-image-gallery-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:10px;font-size:12px;color:#64748b}
    .ticket-image-gallery-head strong{color:#334155}
    .ticket-image-gallery-stage{position:relative;display:flex;align-items:center;justify-content:center;min-height:180px;background:#f1f4f8;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden}
    .ticket-image-gallery-slide{display:flex;align-items:center;justify-content:center;width:100%;height:240px}
    .ticket-image-gallery-slide[hidden]{display:none!important}
    .ticket-image-gallery-slide img{display:block;max-width:100%;max-height:100%;object-fit:contain}
    .ticket-image-gallery-arrow{position:absolute;top:50%;transform:translateY(-50%);z-index:1;display:grid;place-items:center;width:34px;height:34px;border:1px solid #d9e0e9;border-radius:50%;background:rgba(255,255,255,.94);color:#19375b;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.12)}
    .ticket-image-gallery-prev{left:8px}.ticket-image-gallery-next{right:8px}
    .ticket-image-gallery-thumbs{display:flex;gap:8px;overflow-x:auto;padding:10px 2px 4px}
    .ticket-image-gallery-thumb{flex:0 0 54px;width:54px;height:54px;border:2px solid transparent;border-radius:8px;background:#eef2f7;padding:2px;cursor:pointer;overflow:hidden}
    .ticket-image-gallery-thumb.is-active{border-color:#194a80}
    .ticket-image-gallery-thumb img{width:100%;height:100%;object-fit:cover;border-radius:4px}
    .ticket-image-gallery-documents{margin-top:12px}
    @media(max-width:640px){.ticket-image-gallery-slide{height:190px}.ticket-image-gallery-thumb{flex-basis:46px;width:46px;height:46px}}
  </style>
  <script>
    (() => {
      document.querySelectorAll('#ticket-detail-app [data-gallery]').forEach(gallery => {
        const slides=Array.from(gallery.querySelectorAll('[data-gallery-slide]'));
        const thumbs=Array.from(gallery.querySelectorAll('[data-gallery-thumb]'));
        const count=gallery.querySelector('[data-gallery-count]');
        if(!slides.length)return;
        let index=0;
        function mostrar(next){
          index=(next+slides.length)%slides.length;
          slides.forEach((slide,i)=>{slide.hidden=i!==index;});
          thumbs.forEach((thumb,i)=>{thumb.classList.toggle('is-active',i===index);thumb.setAttribute('aria-pressed',String(i===index));});
          if(count)count.textContent=(index+1)+' de '+slides.length;
          thumbs[index]?.scrollIntoView({block:'nearest',inline:'nearest',behavior:'smooth'});
        }
        gallery.querySelector('[data-gallery-prev]')?.addEventListener('click',()=>mostrar(index-1));
        gallery.querySelector('[data-gallery-next]')?.addEventListener('click',()=>mostrar(index+1));
        thumbs.forEach((thumb,i)=>thumb.addEventListener('click',()=>mostrar(i)));
        let touchStart=null;
        gallery.querySelector('.ticket-image-gallery-stage')?.addEventListener('touchstart',event=>{touchStart=event.changedTouches[0]?.clientX??null},{passive:true});
        gallery.querySelector('.ticket-image-gallery-stage')?.addEventListener('touchend',event=>{
          if(touchStart===null)return;
          const distance=(event.changedTouches[0]?.clientX??touchStart)-touchStart;
          touchStart=null;
          if(Math.abs(distance)>45)mostrar(index+(distance<0?1:-1));
        },{passive:true});
      });
    })();
  </script>
  <link rel="stylesheet" href="../css/ticket_bitacora.css?v=<?= (int) @filemtime(__DIR__ . '/../css/ticket_bitacora.css') ?>">
  <script src="js/chat_ticket.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="js/bitacora_ticket.js?v=<?= (int) @filemtime(__DIR__ . '/js/bitacora_ticket.js') ?>"></script>
  <?php if ($vista === 'tecnico' && $esTecnico) { require __DIR__ . '/componentes/modal_comenzar_ticket.php'; } ?>
  <?php require __DIR__ . '/componentes/modal_correo_ticket.php'; ?>
  <?php require __DIR__ . '/componentes/chat_ticket_panel.php'; ?>
<?php endif; ?>
<?php finalizar_layout_configuracion(); ?>
