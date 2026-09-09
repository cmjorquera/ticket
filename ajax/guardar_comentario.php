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
$comentario = trim((string) ($datos['comentario'] ?? ''));
$csrf = (string) ($datos['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

if (!ticket_csrf_valido($csrf)) {
    responder_json(['ok' => false, 'error' => 'La sesión venció. Recarga la página.'], 419);
}
$largoComentario = function_exists('mb_strlen') ? mb_strlen($comentario, 'UTF-8') : strlen($comentario);
if ($ticketId <= 0 || $largoComentario < 3 || $largoComentario > 5000) {
    responder_json(['ok' => false, 'error' => 'El comentario debe tener entre 3 y 5000 caracteres.'], 422);
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $ticket = ticket_buscar_para_acceso($ticketId, $db);
    if (!$ticket) {
        responder_json(['ok' => false, 'error' => 'Ticket no encontrado.'], 404);
    }
    if (!puede_ver_ticket($usuarioId, $ticket, $db)) {
        responder_json(['ok' => false, 'error' => 'No tienes permiso para comentar este ticket.'], 403);
    }
    if (ticket_estado_sin_escritura((int) ($ticket['id_estado'] ?? 0))) {
        responder_json(['ok' => false, 'error' => 'El ticket está cerrado.'], 409);
    }

    $columnas = ticket_columnas_tabla('comentarios_ticket', $db);
    foreach (['id_ticket', 'id_usuario', 'comentario'] as $requerida) {
        if (!isset($columnas[$requerida])) {
            responder_json(['ok' => false, 'error' => 'La conversación todavía no está habilitada en la base de datos.'], 503);
        }
    }

    $campos = ['id_ticket', 'id_usuario', 'comentario'];
    $valores = ['?', '?', '?'];
    $params = [$ticketId, $usuarioId, $comentario];
    if (isset($columnas['id_tecnico'])) {
        $campos[] = 'id_tecnico';
        $valores[] = '?';
        $params[] = (int) ($ticket['id_tecnico_asignado'] ?? 0) === $usuarioId ? $usuarioId : null;
    }
    if (isset($columnas['fecha_comentario'])) {
        $campos[] = 'fecha_comentario';
        $valores[] = 'NOW()';
    }
    if (isset($columnas['hora_comentario'])) {
        $campos[] = 'hora_comentario';
        $valores[] = 'CURTIME()';
    }
    if (isset($columnas['estado'])) {
        $campos[] = 'estado';
        $valores[] = '1';
    }

    $db->execute(
        'INSERT INTO comentarios_ticket (' . implode(', ', $campos) . ') VALUES (' . implode(', ', $valores) . ')',
        $params
    );
    ticket_registrar_cambio($db, $ticketId, $usuarioId, 'comentar');

    responder_json(['ok' => true, 'mensaje' => 'Comentario enviado.'], 201);
} catch (Throwable $ex) {
    error_log('Error en guardar_comentario.php: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible guardar el comentario.'], 500);
}
