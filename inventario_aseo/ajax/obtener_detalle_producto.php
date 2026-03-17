<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

$idProducto = (int) ($_GET['id_producto'] ?? 0);
$producto = $inventario->obtenerProductoCompleto($idProducto);

if (!$producto) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'mensaje' => 'Producto no encontrado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['ok' => true, 'producto' => $producto], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
