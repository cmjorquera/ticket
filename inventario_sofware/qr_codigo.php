<?php
require_once __DIR__ . '/../vendor/autoload.php';

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

function inv_qr_base_url()
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (int)($_SERVER['SERVER_PORT'] ?? 80) === 443;
    $protocolo = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/inventario_sofware/qr_codigo.php');
    $baseDir = rtrim(str_replace('/qr_codigo.php', '', $script), '/');
    return $protocolo . '://' . $host . $baseDir;
}

$tipo = ($_GET['tipo'] ?? '') === 'sitio' ? 'sitio' : 'software';
$id = $tipo === 'sitio' ? (int)($_GET['id_sitio'] ?? $_GET['id'] ?? 0) : (int)($_GET['id_software'] ?? $_GET['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    header('Content-Type: text/plain; charset=utf-8');
    echo 'QR invalido';
    exit;
}

$url = inv_qr_base_url() . '/ficha_qr.php?tipo=' . rawurlencode($tipo) . '&id=' . $id;
$options = new QROptions([
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel' => QRCode::ECC_M,
    'scale' => 8,
    'imageBase64' => false,
]);

header('Content-Type: image/png');
echo (new QRCode($options))->render($url);
exit;
