<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Método no permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}
try {
    $idSitio = (int)($_POST['id_sitio'] ?? 0);
    if ($idSitio <= 0) {
        throw new RuntimeException('No se recibio el identificador del sitio.');
    }
    $inventario->eliminarLogicoSitioWeb($idSitio);
    echo json_encode(['ok' => true, 'mensaje' => 'Sitio marcado como inactivo.'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => inventario_mensaje_error($e)], JSON_UNESCAPED_UNICODE);
}
