<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../helpers/tickets.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

$datos = entrada_ajax();
$usuarioId = (int) ($_SESSION['id'] ?? 0);
$ticketId = (int) ($datos['ticket_id'] ?? $datos['id_ticket'] ?? 0);
$puntaje = (int) ($datos['calificacion'] ?? 0);
$comentario = trim((string) ($datos['comentario'] ?? ''));
$csrf = (string) ($datos['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

if (!ticket_csrf_valido($csrf)) {
    responder_json(['ok' => false, 'error' => 'La sesión venció. Recarga la página.'], 419);
}
$largoComentario = function_exists('mb_strlen') ? mb_strlen($comentario, 'UTF-8') : strlen($comentario);
if ($ticketId <= 0 || $puntaje < 1 || $puntaje > 5 || $largoComentario > 1000) {
    responder_json(['ok' => false, 'error' => 'Revisa la calificación y su comentario.'], 422);
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $ticket = $db->fetchOne(
        'SELECT id_ticket, id_usuario, id_tecnico AS id_tecnico_asignado, id_estado FROM tickets WHERE id_ticket = ? AND id_usuario = ? AND estado = 1 LIMIT 1',
        [$ticketId, $usuarioId]
    );
    if (!$ticket) {
        responder_json(['ok' => false, 'error' => 'Solo el solicitante puede calificar este ticket.'], 403);
    }
    if ((int) $ticket['id_estado'] !== 5) {
        responder_json(['ok' => false, 'error' => 'Puedes calificar el ticket cuando esté resuelto.'], 422);
    }
    if ((int) ($ticket['id_tecnico_asignado'] ?? 0) <= 0) {
        responder_json(['ok' => false, 'error' => 'El ticket no tiene un técnico asignado.'], 422);
    }

    $tabla = 'calificacion_ticket';
    $columnas = ticket_columnas_tabla($tabla, $db);
    if (!$columnas) {
        $tabla = 'calificacion_tickett';
        $columnas = ticket_columnas_tabla($tabla, $db);
    }
    if (!isset($columnas['id_ticket'], $columnas['id_usuario'])) {
        responder_json(['ok' => false, 'error' => 'Las calificaciones todavía no están habilitadas en la base de datos.'], 503);
    }
    if ($db->fetchOne("SELECT id_ticket FROM {$tabla} WHERE id_ticket = ? LIMIT 1", [$ticketId])) {
        responder_json(['ok' => false, 'error' => 'Este ticket ya fue calificado.'], 409);
    }

    $campoPuntaje = isset($columnas['calificacion']) ? 'calificacion' : null;
    if ($campoPuntaje === null && isset($columnas['id_calificacion']) && !str_contains($columnas['id_calificacion']['extra'], 'auto_increment')) {
        $campoPuntaje = 'id_calificacion';
    }
    if ($campoPuntaje === null) {
        responder_json(['ok' => false, 'error' => 'La estructura de calificaciones no contiene un campo de puntaje compatible.'], 503);
    }

    $campos = ['id_ticket', 'id_usuario', $campoPuntaje];
    $valores = ['?', '?', '?'];
    $params = [$ticketId, $usuarioId, $puntaje];
    if (isset($columnas['id_tecnico'])) {
        $campos[] = 'id_tecnico';
        $valores[] = '?';
        $params[] = (int) $ticket['id_tecnico_asignado'];
    }
    if (isset($columnas['comentario'])) {
        $campos[] = 'comentario';
        $valores[] = '?';
        $params[] = $comentario !== '' ? $comentario : null;
    }
    if (isset($columnas['fecha_calificacion'])) {
        $campos[] = 'fecha_calificacion';
        $valores[] = 'NOW()';
    }
    if (isset($columnas['ip_usuario'])) {
        $campos[] = 'ip_usuario';
        $valores[] = '?';
        $params[] = substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    }

    $db->execute(
        "INSERT INTO {$tabla} (" . implode(', ', $campos) . ') VALUES (' . implode(', ', $valores) . ')',
        $params
    );
    ticket_registrar_cambio($db, $ticketId, $usuarioId, 'calificar', $campoPuntaje, null, (string) $puntaje);
    responder_json(['ok' => true, 'mensaje' => 'Calificación guardada.'], 201);
} catch (Throwable $ex) {
    error_log('Error en guardar_calificacion.php: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible guardar la calificación.'], 500);
}
