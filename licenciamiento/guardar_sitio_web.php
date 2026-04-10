<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $idSitio = $inventario->guardarSitioWeb($_POST, $idUsuarioSession);
    echo json_encode(['ok' => true, 'mensaje' => 'Sitio registrado correctamente.', 'id_sitio' => $idSitio, 'redirect' => 'ver_sitio_web.php?id_sitio=' . $idSitio], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
