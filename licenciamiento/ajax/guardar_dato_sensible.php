<?php
require_once __DIR__ . '/../componentes/boot.php';

header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $dato = $inventario->registrarDatoSensible($_POST['nombre'] ?? '', $_POST['descripcion'] ?? '');
    echo json_encode([
        'ok' => true,
        'mensaje' => $dato['creado'] ? 'Dato sensible agregado correctamente.' : 'El dato sensible ya existia y fue reutilizado.',
        'dato' => $dato,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => inventario_mensaje_error($e)], JSON_UNESCAPED_UNICODE);
}
