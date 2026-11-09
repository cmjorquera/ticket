<?php
declare(strict_types=1);

$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';
require_once __DIR__ . '/../clases/Tickets/ConversacionTicket.php';
require_once __DIR__ . '/../clases/Tickets/BitacoraTicket.php';

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
                CONCAT_WS(' ', sol.nombre, sol.apellido_paterno) AS usuario_nombre,
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
    ['Terminado', 'bi-flag', $fechaCorta((string) ($ticket['fecha_termino'] ?? '')), in_array((int) $ticket['id_estado'], [5, 6], true)],
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
        <?= ticket_badge_estado($ticket) ?>
      </div>
    </div>

    <div class="ticket-detail-layout">
      <div class="ticket-detail-main">
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
            <button class="btn btn-primary js-ticket-chat" type="button" data-ticket-id="<?= (int) $ticketId ?>"><i class="bi bi-chat-dots"></i> <?= ticket_estado_sin_escritura((int) $ticket['id_estado']) ? 'Ver conversación' : 'Abrir chat' ?></button>
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

        <?php if ($adjuntos): ?><section class="card ticket-detail-files"><h2>Archivos adjuntos</h2><?php foreach ($adjuntos as $archivo): ?><a href="../ajax/ticket_archivo.php?a=<?= (int) $archivo['id'] ?>" target="_blank" rel="noopener"><i class="bi bi-file-earmark"></i><span><?= e($archivo['nombre']) ?><small><?= $archivo['tamano'] ? e(number_format($archivo['tamano'] / 1024, 0, ',', '.')) . ' KB' : e(strtoupper($archivo['extension'])) ?></small></span><i class="bi bi-box-arrow-up-right"></i></a><?php endforeach; ?></section><?php endif; ?>

        <?php if ($puedeCambiarEstado): ?><section class="card ticket-detail-manage"><h2>Actualizar estado</h2><form id="ticket-state-form" data-estado-actual="<?= (int) $ticket['id_estado'] ?>" data-ticket-id="<?= (int) $ticket['id_ticket'] ?>" data-asunto="<?= e($ticket['asunto']) ?>"><label class="form-label" for="ticket-state">Estado del caso</label><select class="form-input" id="ticket-state" name="estado"><?php foreach (ticket_estados_legacy() as $valor=>$info): ?><option value="<?= $valor ?>" <?= (int)$ticket['id_estado'] === $valor ? 'selected' : '' ?>><?= e($info['nombre']) ?></option><?php endforeach; ?></select><button class="btn btn-primary" type="submit">Guardar estado</button><div class="ticket-message" id="ticket-state-message" role="status"></div></form></section><?php endif; ?>

        <?php if ($calificacionDisponible && $vista === 'usuario' && $esPropietario && in_array($estado, ['resuelto','cerrado'], true)): ?>
          <section class="card ticket-rating" id="calificacion">
            <h2>Califica la atención</h2>
            <?php if ($calificacion): ?>
              <div class="ticket-rating-result">
                <div><?= str_repeat('★', (int)$calificacion['puntaje']) . str_repeat('☆', 5 - (int)$calificacion['puntaje']) ?></div>
                <p><?= e($calificacion['comentario'] ?: 'Sin comentario adicional.') ?></p>
              </div>
            <?php else: ?>
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
            <?php endif; ?>
          </section>
        <?php endif; ?>
      </aside>
    </div>
  </div>
  <link rel="stylesheet" href="../css/ticket_bitacora.css?v=<?= (int) @filemtime(__DIR__ . '/../css/ticket_bitacora.css') ?>">
  <script src="js/chat_ticket.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <script src="js/bitacora_ticket.js?v=<?= (int) @filemtime(__DIR__ . '/js/bitacora_ticket.js') ?>"></script>
  <?php if ($vista === 'tecnico' && $esTecnico) { require __DIR__ . '/componentes/modal_comenzar_ticket.php'; } ?>
  <?php require __DIR__ . '/componentes/chat_ticket_panel.php'; ?>
<?php endif; ?>
<?php finalizar_layout_configuracion(); ?>
