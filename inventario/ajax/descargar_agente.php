<?php
require_once dirname(__DIR__) . '/componentes/boot.php';

$baseDir = dirname(__DIR__) . '/agente/';
$exePath = $baseDir . 'seduc_inventario_agent.exe';
$pyPath  = $baseDir . 'seduc_inventario_agent.py';

if (is_file($exePath)) {
    $archivo = $exePath;
    $nombre  = 'seduc_inventario_agent.exe';
    $mime    = 'application/octet-stream';
} elseif (is_file($pyPath)) {
    $archivo = $pyPath;
    $nombre  = 'seduc_inventario_agent.py';
    $mime    = 'text/plain; charset=utf-8';
} else {
    http_response_code(404);
    echo json_encode(['ok' => false, 'mensaje' => 'El archivo del agente no esta disponible.']);
    exit;
}

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $nombre . '"');
header('Content-Length: ' . filesize($archivo));
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
readfile($archivo);
