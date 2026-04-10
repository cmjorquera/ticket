<?php
require_once __DIR__ . '/../componentes/boot.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $dato = $inventario->registrarDatoSensible($_POST['nombre'] ?? '', $_POST['descripcion'] ?? '');
    echo json_encode([
        'ok' => true,
        'mensaje' => $dato['creado'] ? 'Dato sensible agregado correctamente.' : 'El dato sensible ya existia y fue reutilizado.',
        'dato' => $dato,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
