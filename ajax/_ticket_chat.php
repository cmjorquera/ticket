<?php
/**
 * Arranque común de los endpoints del chat de tickets
 * (obtener_conversacion_ticket, guardar_comentario, guardar_adjunto_ticket, ticket_no_leidos).
 * Valida sesión y CSRF, carga el ticket y decide desde qué bandeja ("vista")
 * participa la persona. Lógica: ticket/logica/LOGICA_MENSAJERIA.txt
 */
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../helpers/tickets.php';
require_once __DIR__ . '/../clases/Tickets/ConversacionTicket.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

/** @return array{0:Conexion,1:ConversacionTicket,2:int} */
function chat_inicio(array $datos): array
{
    $csrf = (string) ($datos['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
    if (!ticket_csrf_valido($csrf)) {
        responder_json(['ok' => false, 'error' => 'La sesión venció. Recarga la página.'], 419);
    }
    $db = Conexion::getInstance('sistema_panel_central');
    return [$db, new ConversacionTicket($db), (int) ($_SESSION['id'] ?? 0)];
}

/**
 * Ticket + vista con la que participa la persona, o corta con 403/404.
 * @return array{0:array,1:string}
 */
function chat_ticket(Conexion $db, ConversacionTicket $conv, int $usuarioId, array $datos): array
{
    $ticket = ticket_buscar_para_acceso((int) ($datos['ticket_id'] ?? $datos['id_ticket'] ?? 0), $db);
    if (!$ticket) {
        responder_json(['ok' => false, 'error' => 'Ticket no encontrado.'], 404);
    }
    $vista = $conv->resolverVista($usuarioId, $ticket, (string) ($datos['vista'] ?? ''));
    if ($vista === null) {
        responder_json(['ok' => false, 'error' => 'No tienes permiso para este ticket.'], 403);
    }
    return [$ticket, $vista];
}
