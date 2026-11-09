<?php
/**
 * Envía el ticket por correo con el PDF adjunto (como "Enviar ticket al correo"
 * del sistema antiguo). POST JSON/form: csrf, ticket_id, para (email), copia (1 = copia a mí).
 *   - El solicitante solo puede enviárselo a sí mismo.
 *   - El técnico y administración pueden indicar otro correo (por defecto, el del solicitante).
 * El PDF adjunto NUNCA lleva la bitácora interna. Lógica: ticket/logica/LOGICA_PDF_CORREO.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/_ticket_chat.php';
require_once __DIR__ . '/../clases/Correo.php';
require_once __DIR__ . '/../clases/Tickets/PdfTicket.php';

$datos = entrada_ajax();
try {
    [$db, , $usuarioId] = chat_inicio($datos);
    $ticket = ticket_buscar_para_acceso((int) ($datos['ticket_id'] ?? 0), $db);
    if (!$ticket || !puede_ver_ticket($usuarioId, $ticket, $db)) {
        responder_json(['ok' => false, 'error' => 'No tienes permiso para este ticket.'], 403);
    }
    // Límite simple: 5 envíos por minuto por sesión
    $ahora = time();
    $_SESSION['ticket_correos'] = array_values(array_filter((array) ($_SESSION['ticket_correos'] ?? []), static fn ($t) => $t > $ahora - 60));
    if (count($_SESSION['ticket_correos']) >= 5) {
        responder_json(['ok' => false, 'error' => 'Espera un momento antes de enviar otro correo.'], 429);
    }

    $ticketId = (int) $ticket['id_ticket'];
    $info = PdfTicket::datos($db, $ticketId, false);
    $yo = $db->fetchOne("SELECT email, TRIM(CONCAT_WS(' ', nombre, apellido_paterno)) AS nombre FROM usuarios WHERE id = ?", [$usuarioId]) ?: [];
    $esGestor = (new BitacoraTicket($db))->rol($usuarioId, $ticket) !== null;   // técnico o administración
    $para = strtolower(trim((string) ($datos['para'] ?? '')));
    if ($para === '' || !$esGestor) {
        $para = strtolower((string) ($esGestor ? $info['email_solicitante'] : ($yo['email'] ?? '')));
    }
    if (!filter_var($para, FILTER_VALIDATE_EMAIL)) {
        responder_json(['ok' => false, 'error' => 'El correo de destino no es válido.'], 422);
    }
    $destinos = [[$para, $para === strtolower((string) $info['email_solicitante']) ? (string) $info['solicitante'] : '']];
    if ((string) ($datos['copia'] ?? '') === '1' && !empty($yo['email']) && strtolower((string) $yo['email']) !== $para) {
        $destinos[] = [(string) $yo['email'], (string) $yo['nombre']];
    }

    $h = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
    $fila = static fn (string $l, string $v): string => '<tr><td style="padding:6px 0;color:#6b7280;font-size:13px;width:130px">' . $l
        . '</td><td style="padding:6px 0;font-size:14px;font-weight:600;color:#111827">' . $v . '</td></tr>';
    $estimada = !empty($info['estimada']) && strtotime((string) $info['estimada']) > 0 ? date('d/m/Y', strtotime((string) $info['estimada'])) : 'Por definir';
    $html = '<div style="font-family:Arial,sans-serif;max-width:560px;margin:auto;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden">'
        . '<div style="background:#16365c;color:#fff;padding:18px 22px;border-bottom:4px solid #e8a33d">'
        . '<div style="font-size:12px;opacity:.8">SEDUC · Mesa de ayuda</div><div style="font-size:20px;font-weight:700">Ticket #' . $ticketId . '</div></div>'
        . '<div style="padding:20px 22px"><p style="font-size:16px;font-weight:700;margin:0 0 12px">' . $h($info['asunto']) . '</p>'
        . '<table style="width:100%;border-collapse:collapse">'
        . $fila('Estado', $h($info['estado'])) . $fila('Solicitante', $h($info['solicitante'])) . $fila('Técnico', $h($info['tecnico'] ?: 'Por asignar'))
        . $fila('Categoría', $h($info['categoria'])) . $fila('Fecha estimada', $h($estimada)) . '</table>'
        . '<p style="margin:18px 0 8px;font-size:13px;color:#374151">Adjuntamos el detalle del ticket en PDF. Puedes ver en qué va en cualquier momento:</p>'
        . '<p style="margin:0 0 6px"><a href="' . $h($info['enlace']) . '" style="display:inline-block;background:#16365c;color:#fff;text-decoration:none;padding:11px 18px;border-radius:8px;font-weight:700;font-size:14px">Ver seguimiento del ticket</a></p>'
        . '<p style="margin:16px 0 0;font-size:12px;color:#9ca3af">Enviado por ' . $h($yo['nombre'] ?? 'SEDUC') . ' desde el Panel Central SEDUC.</p></div></div>';

    $r = Correo::enviar($destinos, 'Ticket #' . $ticketId . ': ' . $info['asunto'], $html, '',
        [[PdfTicket::generar($info), 'ticket_' . $ticketId . '.pdf', 'application/pdf']]);
    if (!$r['enviado']) {
        responder_json(['ok' => false, 'error' => $r['motivo']], 502);
    }
    $_SESSION['ticket_correos'][] = $ahora;
    (new BitacoraTicket($db))->registrarSistema($ticketId, $usuarioId, ($yo['nombre'] ?? 'Alguien') . ' envió el ticket por correo a ' . $para . '.');
    responder_json(['ok' => true, 'mensaje' => 'Ticket enviado a ' . $para . (count($destinos) > 1 ? ' (con copia para ti)' : '') . '.']);
} catch (Throwable $e) {
    error_log('[ticket_correo] ' . $e->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible enviar el correo.'], 500);
}
