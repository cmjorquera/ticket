<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../class/conexion.php';

date_default_timezone_set('America/Santiago');

function responderRecuperar($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

$idUsuarioSession = (int) ($_SESSION['id'] ?? 0);
if ($idUsuarioSession <= 0) {
    responderRecuperar(['success' => false, 'message' => 'Sesion no valida.'], 401);
}

$ticketId = (int) ($_POST['ticket_id'] ?? 0);
if ($ticketId <= 0) {
    responderRecuperar(['success' => false, 'message' => 'Debes indicar un ticket valido.'], 422);
}

$db = new MySQL("", "", "");
$db->set_charset('utf8mb4');

$resultadoTicket = $db->consulta("SELECT id_ticket, id_estado FROM tickets WHERE id_ticket = {$ticketId} LIMIT 1");
if ($db->num_rows($resultadoTicket) === 0) {
    responderRecuperar(['success' => false, 'message' => 'El ticket indicado no existe.'], 404);
}

$ticket = $db->fetch_assoc($resultadoTicket);
if ((int) $ticket['id_estado'] !== 2) {
    responderRecuperar(['success' => false, 'message' => 'El ticket no se encuentra en estado eliminado.'], 422);
}

$resultado = $db->guardar("UPDATE tickets SET id_estado = 1 WHERE id_ticket = {$ticketId} LIMIT 1");
if ($resultado !== 0) {
    responderRecuperar(['success' => false, 'message' => 'No se pudo actualizar el estado del ticket.'], 500);
}

responderRecuperar([
    'success'   => true,
    'message'   => 'El ticket fue recuperado correctamente.',
    'ticket_id' => $ticketId,
    'id_estado' => 1
]);
