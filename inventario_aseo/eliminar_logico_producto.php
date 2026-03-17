<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $idProducto = (int) ($_POST['id_producto'] ?? 0);
    $activo = isset($_POST['activo']) ? (int) $_POST['activo'] : 0;
    if ($idProducto <= 0) {
        throw new RuntimeException('Producto no valido.');
    }

    $inventario->cambiarEstadoLogico($idProducto, $activo, $idUsuarioSession);
    echo json_encode(['ok' => true, 'mensaje' => 'Estado del producto actualizado correctamente.']);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
