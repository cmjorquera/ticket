<?php
@ini_set('display_errors', '0');
error_reporting(E_ALL);

ob_start();
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
    $mime    = 'text/x-python; charset=utf-8';
} else {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(404);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'No existe ningun agente disponible para descargar. Compile seduc_inventario_agent.exe o verifique que exista seduc_inventario_agent.py en inventario/agente/.';
    exit;
}

if (!is_readable($archivo) || filesize($archivo) === false) {
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'El agente existe, pero no se puede leer desde el servidor. Revise los permisos del archivo en inventario/agente/.';
    exit;
}

if (ob_get_level() > 0) {
    ob_end_clean();
}

header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $nombre . '"; filename*=UTF-8\'\'' . rawurlencode($nombre));
header('Content-Length: ' . filesize($archivo));
header('Content-Transfer-Encoding: binary');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
readfile($archivo);
exit;
