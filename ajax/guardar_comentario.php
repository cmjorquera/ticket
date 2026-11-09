<?php
/**
 * Envía un mensaje al chat del ticket (tabla ticket_conversaciones).
 * POST: ticket_id, csrf, comentario, vista (usuario|tecnico|admin)
 * Lógica: ticket/logica/LOGICA_MENSAJERIA.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/_ticket_chat.php';
require_once __DIR__ . '/../clases/Tickets/AvisosTicket.php';

$datos = entrada_ajax();
$texto = trim(str_replace("\r\n", "\n", (string) ($datos['comentario'] ?? $datos['mensaje'] ?? '')));
$largo = mb_strlen($texto, 'UTF-8');
try {
    [$db, $conv, $usuarioId] = chat_inicio($datos);
    if ($largo < 1 || $largo > ConversacionTicket::LARGO_MAXIMO) {
        responder_json(['ok' => false, 'error' => 'Escribe un mensaje (máximo ' . ConversacionTicket::LARGO_MAXIMO . ' caracteres).'], 422);
    }
    [$ticket, $vista] = chat_ticket($db, $conv, $usuarioId, $datos);
    if (ticket_estado_sin_escritura((int) ($ticket['id_estado'] ?? 0))) {
        responder_json(['ok' => false, 'error' => 'El ticket está terminado: ya no admite mensajes.'], 409);
    }
    $id = $conv->enviar($ticket, $usuarioId, $vista, $texto);
    ticket_registrar_cambio($db, (int) $ticket['id_ticket'], $usuarioId, 'comentar');
    (new AvisosTicket($db))->mensaje((int) $ticket['id_ticket'], $usuarioId, $vista, $texto);   // campanita
    responder_json(['ok' => true, 'id' => $id, 'mensaje' => 'Mensaje enviado.'], 201);
} catch (Throwable $ex) {
    error_log('[guardar_comentario] ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible enviar el mensaje.'], 500);
}
