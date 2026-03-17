<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $idHerramienta = $inventario->guardarHerramienta($_POST, $_FILES, $idUsuarioSession);
    echo json_encode([
        'ok' => true,
        'mensaje' => 'Herramienta registrada correctamente.',
        'id_herramienta' => $idHerramienta,
        'redirect' => 'ver_herramienta.php?id_herramienta=' . $idHerramienta,
    ]);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
