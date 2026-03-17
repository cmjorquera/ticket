<?php
require_once __DIR__ . '/../bootstrap.php';

try {
    $usuario = eventos_requiere_login(true);
    $db = eventos_db();
    $id = (int) ($_POST['id'] ?? 0);

    if ($id <= 0) {
        eventos_responder_json(['ok' => false, 'message' => 'ID invalido'], 422);
    }

    $evento = eventos_obtener_evento($id);
    if (!$evento) {
        eventos_responder_json(['ok' => false, 'message' => 'Evento no encontrado'], 404);
    }

    $stmt = $db->prepare("UPDATE eventos SET eliminado = 1, actualizado_en = NOW() WHERE id = ?");
    if (!$stmt) {
        eventos_responder_json(['ok' => false, 'message' => 'No se pudo preparar la eliminacion del evento.'], 500);
    }

    $stmt->bind_param('i', $id);

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        eventos_responder_json(['ok' => false, 'message' => 'No fue posible eliminar el evento. ' . $error], 500);
    }
    $stmt->close();

    eventos_registrar_historial($id, 'eliminacion', 'Se elimino logicamente el evento "' . $evento['titulo'] . '".', $usuario['id']);
    eventos_responder_json(['ok' => true, 'message' => 'Evento eliminado correctamente.']);
} catch (Throwable $e) {
    eventos_responder_json([
        'ok' => false,
        'message' => 'Error interno al eliminar el evento: ' . $e->getMessage(),
    ], 500);
}
