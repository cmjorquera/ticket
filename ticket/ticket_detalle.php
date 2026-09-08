<?php
declare(strict_types=1);

$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';

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
                t.asunto, t.descripcion_ticket AS descripcion, t.id_estado, e.nombre AS estado_nombre, t.id_prioridad,
                COALESCE(CONCAT(pt.fecha_creacion_inicio, ' ', COALESCE(pt.hora_creacion_inicio, '00:00:00')), '') AS fecha_creacion,
                COALESCE(CONCAT(pt.fecha_asignacion_tecnico, ' ', COALESCE(pt.hora_asignacion_tecnico, '00:00:00')), '') AS fecha_respuesta,
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
$puedeCambiarEstado = $esTecnico || $esAdministrador;

if ($autorizado) {
    try {
        $columnasComentario = ticket_columnas_tabla('comentarios_ticket', $db);
        $conversacionDisponible = isset($columnasComentario['id_ticket'], $columnasComentario['id_usuario'], $columnasComentario['comentario']);
        if ($conversacionDisponible) {
            $campoFecha = isset($columnasComentario['fecha_comentario']) ? 'ct.fecha_comentario' : 'NULL';
            $campoId = isset($columnasComentario['id_comentario']) ? 'ct.id_comentario' : (isset($columnasComentario['id']) ? 'ct.id' : 'ct.id_ticket');
            $soloActivos = isset($columnasComentario['estado']) ? ' AND ct.estado = 1' : '';
            $comentarios = $db->fetchAll(
                "SELECT {$campoId} AS id_comentario, ct.id_usuario, ct.comentario,
                        {$campoFecha} AS fecha_comentario,
                        CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS autor_nombre
                   FROM comentarios_ticket ct
                   JOIN usuarios u ON u.id = ct.id_usuario
                  WHERE ct.id_ticket = ?{$soloActivos}
               ORDER BY " . (isset($columnasComentario['fecha_comentario']) ? 'ct.fecha_comentario' : $campoId) . ' ASC',
                [$ticketId]
            );
        }
    } catch (Throwable $ex) {
        error_log('Error al consultar conversación del ticket: ' . $ex->getMessage());
        $conversacionDisponible = false;
    }

    try {
        $adjuntos = $db->fetchAll(
            "SELECT nombre_archivo AS nombre_original,
                    SUBSTRING_INDEX(ruta_archivo, '/', -1) AS nombre_archivo,
                    tipo_archivo AS tipo_mime, tamaño_archivo AS tamano
               FROM archivos_adjuntos_ticket
              WHERE id_ticket = ? ORDER BY fecha_subida ASC",
            [$ticketId]
        );
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

$volver = $esPropietario ? 'ticket.php' : ($esTecnico ? 'ticket_asignados.php' : 'ticket_admin_v2.php');
$csrf = ticket_csrf_token();
$estado = $ticket ? strtolower((string) $ticket['estado']) : '';
$estadoTexto = $ticket ? (string) $ticket['estado_nombre'] : '';

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
      <a class="ticket-detail-back" href="<?= e($volver) ?>"><i class="bi bi-arrow-left"></i> Volver a la bandeja</a>
      <div class="ticket-detail-titleline">
        <div><span class="ticket-detail-kicker">Solicitud #<?= (int) $ticket['id_ticket'] ?></span><h1><?= e($ticket['asunto']) ?></h1></div>
        <span class="ticket-badge ticket-badge--<?= e($estado) ?>"><?= e($estadoTexto) ?></span>
      </div>
    </div>

    <div class="ticket-detail-layout">
      <div class="ticket-detail-main">
        <section class="card ticket-detail-section">
          <div class="card-header"><div><h2 class="card-title">Descripción del caso</h2><p class="card-desc"><?= e($ticket['categoria_nombre']) ?></p></div></div>
          <div class="ticket-detail-copy"><?= e($ticket['descripcion']) ?></div>
        </section>

        <section class="card ticket-conversation">
          <div class="card-header"><div><h2 class="card-title">Conversación</h2><p class="card-desc">Seguimiento entre solicitante y equipo de soporte.</p></div><span class="ticket-list-count"><?= count($comentarios) ?> mensaje<?= count($comentarios) === 1 ? '' : 's' ?></span></div>
          <?php if (!$conversacionDisponible): ?>
            <div class="ticket-empty"><i class="bi bi-chat-square-dots"></i>La conversación se habilitará cuando esté instalada su tabla en la base de datos.</div>
          <?php else: ?>
            <div class="ticket-thread" id="ticket-thread">
              <?php if (!$comentarios): ?><div class="ticket-thread-empty">Aún no hay mensajes. Escribe el primero para iniciar el seguimiento.</div><?php endif; ?>
              <?php foreach ($comentarios as $mensaje): $propio = (int) $mensaje['id_usuario'] === $usuarioId; ?>
                <article class="ticket-message-row <?= $propio ? 'is-own' : '' ?>">
                  <div class="ticket-message-avatar"><?= e(strtoupper(substr(trim((string) $mensaje['autor_nombre']), 0, 1)) ?: 'U') ?></div>
                  <div class="ticket-message-bubble"><div><strong><?= e($mensaje['autor_nombre']) ?></strong><time><?= $mensaje['fecha_comentario'] ? e(date('d/m/Y H:i', strtotime((string) $mensaje['fecha_comentario']))) : '' ?></time></div><p><?= nl2br(e($mensaje['comentario'])) ?></p></div>
                </article>
              <?php endforeach; ?>
            </div>
            <?php if ((int) $ticket['id_estado'] === 5): ?>
              <div class="ticket-chat__closed"><i class="bi bi-lock"></i><span>Este ticket está cerrado y no admite nuevos mensajes.</span></div>
            <?php else: ?>
              <form class="ticket-reply" id="ticket-comment-form">
                <label class="form-label" for="ticket-comment">Agregar comentario</label>
                <textarea class="form-input" id="ticket-comment" name="comentario" minlength="3" maxlength="5000" required placeholder="Escribe una actualización o responde al equipo…"></textarea>
                <div class="ticket-form-actions"><span class="ticket-note">Máximo 5000 caracteres.</span><button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Enviar comentario</button></div>
                <div class="ticket-message" id="ticket-comment-message" role="status"></div>
              </form>
            <?php endif; ?>
          <?php endif; ?>
        </section>
      </div>

      <aside class="ticket-detail-side">
        <section class="card ticket-detail-facts">
          <h2>Datos del ticket</h2>
          <dl><div><dt>Solicitante</dt><dd><?= e($ticket['usuario_nombre']) ?></dd></div><div><dt>Colegio</dt><dd><?= e($ticket['colegio_nombre'] ?: 'Sin colegio') ?></dd></div><div><dt>Técnico</dt><dd><?= e(trim((string) $ticket['tecnico_nombre']) ?: 'Sin asignar') ?></dd></div><div><dt>Prioridad</dt><dd><span class="ticket-priority ticket-priority--<?= e(str_replace('í','i',strtolower((string)$ticket['prioridad']))) ?>"><?= e($ticket['prioridad']) ?></span></dd></div><div><dt>Creado</dt><dd><?= strtotime((string)$ticket['fecha_creacion']) ? e(date('d/m/Y H:i', strtotime((string)$ticket['fecha_creacion']))) : 'Sin fecha' ?></dd></div></dl>
        </section>

        <?php if ($adjuntos): ?><section class="card ticket-detail-files"><h2>Archivos adjuntos</h2><?php foreach ($adjuntos as $archivo): ?><a href="../uploads/tickets/<?= (int)$ticketId ?>/<?= rawurlencode(basename((string)$archivo['nombre_archivo'])) ?>" target="_blank" rel="noopener"><i class="bi bi-file-earmark"></i><span><?= e($archivo['nombre_original']) ?><small><?= e(number_format(((int)$archivo['tamano']) / 1024, 0, ',', '.')) ?> KB</small></span><i class="bi bi-box-arrow-up-right"></i></a><?php endforeach; ?></section><?php endif; ?>

        <?php if ($puedeCambiarEstado): ?><section class="card ticket-detail-manage"><h2>Actualizar estado</h2><form id="ticket-state-form"><label class="form-label" for="ticket-state">Estado del caso</label><select class="form-input" id="ticket-state" name="estado"><?php foreach (ticket_estados_legacy() as $valor=>$info): ?><option value="<?= $valor ?>" <?= (int)$ticket['id_estado'] === $valor ? 'selected' : '' ?>><?= e($info['nombre']) ?></option><?php endforeach; ?></select><button class="btn btn-primary" type="submit">Guardar estado</button><div class="ticket-message" id="ticket-state-message" role="status"></div></form></section><?php endif; ?>

        <?php if ($calificacionDisponible && $esPropietario && in_array($estado, ['resuelto','cerrado'], true)): ?>
          <section class="card ticket-rating">
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
  <script src="js/chat_ticket.js"></script>
<?php endif; ?>
<?php finalizar_layout_configuracion(); ?>
