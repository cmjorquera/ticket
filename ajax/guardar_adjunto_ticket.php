<?php
/**
 * Adjunta archivos al ticket desde el chat. Cada archivo queda:
 *   - en uploads/ticket_conversacion/{id_ticket}/ (sin acceso directo desde la web)
 *   - en archivos_adjuntos_ticket (lista de archivos del ticket)
 *   - como mensaje en ticket_conversaciones (aparece en el chat, como en el sistema antiguo)
 * POST multipart: ticket_id, csrf, vista, archivos[]
 * Lógica: ticket/logica/LOGICA_MENSAJERIA.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/_ticket_chat.php';
require_once __DIR__ . '/../clases/Tickets/AvisosTicket.php';

$guardados = [];
try {
    [$db, $conv, $usuarioId] = chat_inicio($_POST);
    [$ticket, $vista] = chat_ticket($db, $conv, $usuarioId, $_POST);
    $ticketId = (int) $ticket['id_ticket'];
    if (ticket_estado_sin_escritura((int) ($ticket['id_estado'] ?? 0))) {
        responder_json(['ok' => false, 'error' => 'El ticket está terminado: ya no admite archivos.'], 409);
    }
    try {
        $archivos = ticket_validar_archivos($_FILES['archivos'] ?? []);
    } catch (RuntimeException $e) {
        responder_json(['ok' => false, 'error' => $e->getMessage()], 422);
    }
    $actuales = count(ticket_listar_adjuntos($db, $ticketId));
    if (!$archivos) {
        responder_json(['ok' => false, 'error' => 'No se recibieron archivos.'], 422);
    }
    if ($actuales + count($archivos) > TICKET_MAX_ADJUNTOS) {
        responder_json(['ok' => false, 'error' => 'Cada ticket admite un máximo de ' . TICKET_MAX_ADJUNTOS . ' archivos (ya tiene ' . $actuales . ').'], 422);
    }
    foreach ($archivos as $archivo) {
        $ruta = ticket_mover_archivo($ticketId, $archivo);
        $guardados[] = dirname(__DIR__) . '/' . $ruta;
        ticket_registrar_adjunto($db, $ticketId, $ruta, $archivo['original'], $archivo['tamano'], $usuarioId);
        $conv->enviar($ticket, $usuarioId, $vista, $archivo['original'], $ruta);
    }
    ticket_registrar_cambio($db, $ticketId, $usuarioId, 'adjuntar');
    (new AvisosTicket($db))->mensaje($ticketId, $usuarioId, $vista, 'adjuntó ' . implode(', ', array_column($archivos, 'original')));
    $n = count($archivos);
    responder_json(['ok' => true, 'mensaje' => $n === 1 ? 'Archivo adjuntado.' : "{$n} archivos adjuntados."], 201);
} catch (Throwable $ex) {
    error_log('[guardar_adjunto_ticket] ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible guardar los archivos.'], 500);
}
