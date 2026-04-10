<?php
require_once __DIR__ . '/../componentes/boot.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $tipoUsuario = $inventario->registrarTipoUsuario($_POST['nombre'] ?? '', $_POST['descripcion'] ?? '');
    echo json_encode([
        'ok' => true,
        'mensaje' => $tipoUsuario['creado'] ? 'Tipo de usuario agregado correctamente.' : 'El tipo de usuario ya existia y fue reutilizado.',
        'tipo_usuario' => $tipoUsuario,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
