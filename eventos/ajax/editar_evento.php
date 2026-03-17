<?php
require_once __DIR__ . '/../bootstrap.php';

try {
    $usuario = eventos_requiere_login(true);
    $db = eventos_db();

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id <= 0) {
            eventos_responder_json(['ok' => false, 'message' => 'ID invalido'], 422);
        }

        $evento = eventos_obtener_evento($id);
        if (!$evento) {
            eventos_responder_json(['ok' => false, 'message' => 'Evento no encontrado'], 404);
        }

        eventos_responder_json(['ok' => true, 'evento' => $evento]);
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id <= 0) {
        eventos_responder_json(['ok' => false, 'message' => 'ID invalido'], 422);
    }

    $actual = eventos_obtener_evento($id);
    if (!$actual) {
        eventos_responder_json(['ok' => false, 'message' => 'Evento no encontrado'], 404);
    }

    $payload = eventos_recoger_payload();
    $errores = eventos_validar_datos($payload);
    if ($errores) {
        eventos_responder_json(['ok' => false, 'message' => implode(' ', $errores)], 422);
    }

    $stmt = $db->prepare("UPDATE eventos SET
            titulo = ?, descripcion = ?, fecha_inicio = ?, hora_evento = ?, con_audio = ?, solo_presentacion = ?,
            musica_ambiental = ?, cantidad_personas = ?, responsable_id = ?, fecha_fin = ?, hora_inicio = ?,
            hora_fin = ?, ubicacion = ?, tipo_evento = ?, estado = ?, color_evento = ?, observaciones_logisticas = ?,
            actualizado_en = NOW()
        WHERE id = ?");

    if (!$stmt) {
        eventos_responder_json(['ok' => false, 'message' => 'No se pudo preparar la actualizacion del evento.'], 500);
    }

    $stmt->bind_param(
        'ssssiiiiissssssssi',
        $payload['titulo'],
        $payload['descripcion'],
        $payload['fecha_inicio'],
        $payload['hora_inicio'],
        $payload['con_audio'],
        $payload['solo_presentacion'],
        $payload['musica_ambiental'],
        $payload['cantidad_personas'],
        $payload['responsable_id'],
        $payload['fecha_fin'],
        $payload['hora_inicio'],
        $payload['hora_fin'],
        $payload['ubicacion'],
        $payload['tipo_evento'],
        $payload['estado'],
        $payload['color_evento'],
        $payload['observaciones_logisticas'],
        $id
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        eventos_responder_json(['ok' => false, 'message' => 'No fue posible actualizar el evento. ' . $error], 500);
    }
    $stmt->close();

    $accion = ($actual['estado'] !== 'cancelado' && $payload['estado'] === 'cancelado') ? 'cancelacion' : 'edicion';
    $descripcion = $accion === 'cancelacion'
        ? 'Se cancelo el evento "' . $payload['titulo'] . '".'
        : 'Se edito el evento "' . $payload['titulo'] . '".';

    eventos_registrar_historial($id, $accion, $descripcion, $usuario['id']);

    $evento = eventos_obtener_evento($id);
    $responsable = eventos_obtener_responsable((int) ($evento['responsable_id'] ?? 0));
    if ($evento && $responsable) {
        eventos_enviar_correo_evento($accion === 'cancelacion' ? 'cancelado' : 'actualizado', $evento, $responsable, $actual);
    }

    eventos_responder_json([
        'ok' => true,
        'message' => $accion === 'cancelacion' ? 'Evento cancelado correctamente.' : 'Evento actualizado correctamente.',
        'evento' => $evento ? eventos_formatear_calendario($evento) : null,
    ]);
} catch (Throwable $e) {
    eventos_responder_json([
        'ok' => false,
        'message' => 'Error interno al actualizar el evento: ' . $e->getMessage(),
    ], 500);
}
