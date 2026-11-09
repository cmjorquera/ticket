<?php
/**
 * Mensajes no leídos por ticket, para el número sobre el botón "Chat".
 * POST JSON: {csrf, vista: usuario|tecnico, ids: [id_ticket, ...]}
 * → {ok, no_leidos: {id_ticket: cantidad}, total}
 * Lógica: ticket/logica/LOGICA_MENSAJERIA.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/_ticket_chat.php';

$datos = entrada_ajax();
try {
    [, $conv, $usuarioId] = chat_inicio($datos);
    $conteo = $conv->noLeidos($usuarioId, (string) ($datos['vista'] ?? ''), (array) ($datos['ids'] ?? []));
    responder_json(['ok' => true, 'no_leidos' => (object) $conteo, 'total' => array_sum($conteo)]);
} catch (Throwable $ex) {
    error_log('[ticket_no_leidos] ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible consultar los mensajes.'], 500);
}
