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
$ticketId = (int) ($datos['ticket_id'] ?? 0);
$csrf = (string) ($datos['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));

if (!ticket_csrf_valido($csrf)) {
    responder_json(['ok' => false, 'error' => 'La sesión venció. Recarga la página.'], 419);
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $ticket = ticket_buscar_para_acceso($ticketId, $db);
    if (!$ticket) {
        responder_json(['ok' => false, 'error' => 'Ticket no encontrado.'], 404);
    }
    if (!puede_ver_ticket($usuarioId, $ticket, $db)) {
        responder_json(['ok' => false, 'error' => 'No tienes permiso para consultar este ticket.'], 403);
    }

    $mensajes = [];
    $columnasComentario = ticket_columnas_tabla('comentarios_ticket', $db);
    $conversacionDisponible = isset($columnasComentario['id_ticket'], $columnasComentario['id_usuario'], $columnasComentario['comentario']);
    if ($conversacionDisponible) {
        $campoId = isset($columnasComentario['id_comentario'])
            ? 'ct.id_comentario'
            : (isset($columnasComentario['id']) ? 'ct.id' : 'ct.id_ticket');
        if (isset($columnasComentario['fecha_comentario'], $columnasComentario['hora_comentario'])) {
            $campoFecha = "CONCAT(ct.fecha_comentario, ' ', COALESCE(ct.hora_comentario, '00:00:00'))";
        } elseif (isset($columnasComentario['fecha_comentario'])) {
            $campoFecha = 'ct.fecha_comentario';
        } else {
            $campoFecha = 'NULL';
        }
        $soloActivos = isset($columnasComentario['estado']) ? ' AND ct.estado = 1' : '';
        $mensajes = $db->fetchAll(
            "SELECT {$campoId} AS id_comentario, ct.id_usuario, ct.comentario,
                    {$campoFecha} AS fecha_comentario,
                    CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS autor_nombre
               FROM comentarios_ticket ct
               JOIN usuarios u ON u.id = ct.id_usuario
              WHERE ct.id_ticket = ?{$soloActivos}
           ORDER BY " . ($campoFecha !== 'NULL' ? $campoFecha : $campoId) . ' ASC, ' . $campoId . ' ASC',
            [$ticketId]
        );
    }

    $adjuntos = [];
    $columnasAdjunto = ticket_columnas_tabla('archivos_adjuntos_ticket', $db);
    $adjuntosDisponibles = isset($columnasAdjunto['id_ticket'])
        && (isset($columnasAdjunto['nombre_archivo'], $columnasAdjunto['ruta_archivo'])
            || isset($columnasAdjunto['adjunto']));
    if ($adjuntosDisponibles) {
        $campoIdAdjunto = isset($columnasAdjunto['id_archivo']) ? 'id_archivo' : 'id_ticket';
        $campoNombre = isset($columnasAdjunto['nombre_archivo'])
            ? 'nombre_archivo'
            : "SUBSTRING_INDEX(REPLACE(adjunto, '\\\\', '/'), '/', -1)";
        $campoRuta = isset($columnasAdjunto['ruta_archivo']) ? 'ruta_archivo' : 'adjunto';
        $campoTamano = isset($columnasAdjunto['tamaño_archivo']) ? '`tamaño_archivo`' : '0';
        $ordenAdjuntos = isset($columnasAdjunto['fecha_subida']) ? 'fecha_subida ASC' : $campoIdAdjunto . ' ASC';
        $adjuntos = $db->fetchAll(
            "SELECT {$campoIdAdjunto} AS id_archivo, {$campoNombre} AS nombre_archivo,
                    {$campoRuta} AS ruta_archivo,
                    {$campoTamano} AS tamano
               FROM archivos_adjuntos_ticket
              WHERE id_ticket = ?
           ORDER BY {$ordenAdjuntos}",
            [$ticketId]
        );
    }

    responder_json([
        'ok' => true,
        'ticket' => [
            'id' => (int) $ticket['id_ticket'],
            'asunto' => (string) ($ticket['asunto'] ?? ''),
            'estado_id' => (int) ($ticket['id_estado'] ?? 0),
            'cerrado' => (int) ($ticket['id_estado'] ?? 0) === 5,
        ],
        'conversacion_disponible' => $conversacionDisponible,
        'adjuntos_disponibles' => $adjuntosDisponibles,
        'mensajes' => $mensajes,
        'adjuntos' => $adjuntos,
    ]);
} catch (Throwable $ex) {
    error_log('Error al obtener conversación del ticket: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible cargar la conversación.'], 500);
}
