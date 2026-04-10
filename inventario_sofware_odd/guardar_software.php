<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $idSoftware = $inventario->guardarSoftware($_POST, $idUsuarioSession);
    echo json_encode(['ok' => true, 'mensaje' => 'Software registrado correctamente.', 'id_software' => $idSoftware, 'redirect' => 'ver_software.php?id_software=' . $idSoftware], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
