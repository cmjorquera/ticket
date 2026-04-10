<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $idSitio = (int)($_POST['id_sitio'] ?? 0);
    if ($idSitio <= 0) {
        throw new RuntimeException('No se recibio el identificador del sitio.');
    }
    $inventario->eliminarLogicoSitioWeb($idSitio);
    echo json_encode(['ok' => true, 'mensaje' => 'Sitio marcado como inactivo.'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
