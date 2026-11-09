<?php
/**
 * Conversación de un ticket (el panel la consulta cada 3 s mientras está abierto).
 * POST: ticket_id, csrf, vista (usuario|tecnico|admin), marcar (1 = marcar como leído)
 * Lógica: ticket/logica/LOGICA_MENSAJERIA.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/_ticket_chat.php';

$datos = entrada_ajax();
try {
    [$db, $conv, $usuarioId] = chat_inicio($datos);
    [$ticket, $vista] = chat_ticket($db, $conv, $usuarioId, $datos);
    $ticketId = (int) $ticket['id_ticket'];

    // Al ver la conversación se marcan como leídos los mensajes de la otra parte
    // (no si la pestaña está oculta: así el contador sigue avisando).
    if ((string) ($datos['marcar'] ?? '1') === '1') {
        $conv->marcarLeidos($ticketId, $vista);
    }

    $partes = $db->fetchOne(
        "SELECT TRIM(CONCAT_WS(' ', s.nombre, s.apellido_paterno)) AS solicitante,
                TRIM(CONCAT_WS(' ', tc.nombre, tc.apellido_paterno)) AS tecnico
           FROM tickets t
           JOIN usuarios s ON s.id = t.id_usuario
      LEFT JOIN usuarios tc ON tc.id = t.id_tecnico
          WHERE t.id_ticket = ?",
        [$ticketId]
    ) ?: [];
    $cerrado = ticket_estado_sin_escritura((int) ($ticket['id_estado'] ?? 0));

    $adjuntos = array_map(static fn (array $a): array => $a + [
        'url' => '../ajax/ticket_archivo.php?a=' . $a['id'],
    ], ticket_listar_adjuntos($db, $ticketId));
    $mensajes = array_map(static fn (array $m): array => $m['adjunto'] === null ? $m : array_merge($m, [
        'adjunto' => $m['adjunto'] + ['url' => '../ajax/ticket_archivo.php?m=' . $m['id']],
    ]), $conv->mensajes($ticketId, $vista));

    responder_json([
        'ok' => true,
        'vista' => $vista,
        'ticket' => [
            'id' => $ticketId,
            'asunto' => (string) ($ticket['asunto'] ?? ''),
            'estado_id' => (int) ($ticket['id_estado'] ?? 0),
            'cerrado' => $cerrado,
            'solicitante' => (string) ($partes['solicitante'] ?? ''),
            'tecnico' => (string) ($partes['tecnico'] ?? ''),
        ],
        'puede_escribir' => !$cerrado,
        // compatibilidad con el panel anterior
        'conversacion_disponible' => true,
        'adjuntos_disponibles' => true,
        'mensajes' => $mensajes,
        'adjuntos' => $adjuntos,
    ]);
} catch (Throwable $ex) {
    error_log('[obtener_conversacion_ticket] ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible cargar la conversación.'], 500);
}
