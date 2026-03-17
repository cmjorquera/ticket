<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $idProducto = $inventario->guardarProducto($_POST, $idUsuarioSession);
    echo json_encode([
        'ok' => true,
        'mensaje' => 'Producto de aseo registrado correctamente.',
        'id_producto' => $idProducto,
        'redirect' => 'ver_producto.php?id_producto=' . $idProducto
    ]);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
