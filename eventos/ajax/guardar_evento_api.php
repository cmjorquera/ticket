<?php
require_once __DIR__ . '/../bootstrap.php';

try {
    $usuario = eventos_requiere_login(true);
    $db = eventos_db();
    $payload = eventos_recoger_payload();
    $errores = eventos_validar_datos($payload);

    if ($errores) {
        eventos_responder_json(['ok' => false, 'message' => implode(' ', $errores)], 422);
    }

    $stmt = $db->prepare("INSERT INTO eventos (
            titulo, descripcion, fecha_inicio, hora_evento, con_audio, solo_presentacion, musica_ambiental,
            cantidad_personas, creado_en, responsable_id, eliminado, fecha_fin, hora_inicio, hora_fin,
            ubicacion, tipo_evento, estado, color_evento, observaciones_logisticas, correo_enviado, actualizado_en
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 0, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW()
        )");

    if (!$stmt) {
        eventos_responder_json(['ok' => false, 'message' => 'No se pudo preparar la insercion del evento.'], 500);
    }

    $stmt->bind_param(
        'ssssiiiiissssssss',
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
        $payload['observaciones_logisticas']
    );

    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        eventos_responder_json(['ok' => false, 'message' => 'No fue posible guardar el evento. ' . $error], 500);
    }

    $eventoId = $db->insert_id();
    $stmt->close();

    $evento = eventos_obtener_evento($eventoId);
    $responsable = eventos_obtener_responsable($payload['responsable_id']);
    $correoEnviado = ($evento && $responsable) ? eventos_enviar_correo_evento('creado', $evento, $responsable) : false;
    $correoFlag = $correoEnviado ? 1 : 0;

    $stmtUpdate = $db->prepare("UPDATE eventos SET correo_enviado = ?, actualizado_en = NOW() WHERE id = ?");
    if ($stmtUpdate) {
        $stmtUpdate->bind_param('ii', $correoFlag, $eventoId);
        $stmtUpdate->execute();
        $stmtUpdate->close();
    }

    eventos_registrar_historial($eventoId, 'creacion', 'Se creo el evento "' . $payload['titulo'] . '".', $usuario['id']);

    eventos_responder_json([
        'ok' => true,
        'message' => $correoEnviado ? 'Evento creado y correo enviado correctamente.' : 'Evento creado correctamente.',
        'evento' => $evento ? eventos_formatear_calendario($evento) : null,
    ]);
} catch (Throwable $e) {
    eventos_responder_json([
        'ok' => false,
        'message' => 'Error interno al guardar el evento: ' . $e->getMessage(),
    ], 500);
}
