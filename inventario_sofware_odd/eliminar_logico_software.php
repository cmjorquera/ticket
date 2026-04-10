<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $idSoftware = (int)($_POST['id_software'] ?? 0);
    if ($idSoftware <= 0) {
        throw new RuntimeException('No se recibio el identificador del software.');
    }
    $inventario->eliminarLogicoSoftware($idSoftware, $idUsuarioSession);
    echo json_encode(['ok' => true, 'mensaje' => 'Software marcado como inactivo.'], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
