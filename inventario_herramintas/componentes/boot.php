<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

register_shutdown_function(function () {
    $error = error_get_last();
    if (!$error || !in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR), true)) {
        return;
    }

    $log = '[' . date('Y-m-d H:i:s') . '] ' . $error['message'] . ' en ' . $error['file'] . ':' . $error['line'] . PHP_EOL;
    @file_put_contents(dirname(__DIR__) . '/error_inventario_herramientas.log', $log, FILE_APPEND);
});

function inventario_responder_error($mensaje, $codigo = 500)
{
    http_response_code((int)$codigo);
    $esAjax = strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/inventario_herramintas/ajax/') !== false;

    if ($esAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'ok' => false,
            'mensaje' => $mensaje,
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Error inventario herramientas</title>';
    echo '<style>body{font-family:Arial,sans-serif;background:#f4f7fb;color:#17324d;padding:32px}.box{max-width:900px;margin:0 auto;background:#fff;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.08)}h1{margin-top:0}code{background:#f1f5f9;padding:2px 6px;border-radius:6px}</style>';
    echo '</head><body><div class="box"><h1>Inventario de herramientas no pudo cargar</h1><p>' . htmlspecialchars((string)$mensaje, ENT_QUOTES, 'UTF-8') . '</p></div></body></html>';
    exit;
}

require_once dirname(__DIR__, 2) . '/class/conexion.php';
require_once dirname(__DIR__, 2) . '/class/funciones.php';
require_once __DIR__ . '/../class/Inventario.php';

try {
    $funciones = new Funciones();
    $inventario = new Inventario();
} catch (Throwable $e) {
    inventario_responder_error('Error al iniciar el modulo de inventario de herramientas: ' . $e->getMessage());
}

$idUsuarioSession = (int)($_SESSION['id'] ?? 0);
$nombreUsuarioSession = trim((string)($_SESSION['nombre'] ?? '') . ' ' . (string)($_SESSION['apellido_paterno'] ?? ''));
$idPagActual = '9';

function inventario_url($ruta = '')
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base = '/inventario_herramintas';

    $pos = strpos($scriptName, '/inventario_herramintas');
    if ($pos !== false) {
        $resto = substr($scriptName, $pos);
        $partes = explode('/', trim($resto, '/'));
        if (!empty($partes[0])) {
            $base = '/' . $partes[0];
        }
    }

    return $ruta === '' ? $base : $base . '/' . ltrim($ruta, '/');
}

function inventario_sistema_url($ruta = '')
{
    $ruta = ltrim((string)$ruta, '/');
    return '../' . $ruta;
}

function inventario_h($valor)
{
    return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
}

function inventario_asset_version($rutaRelativa)
{
    $rutaFisica = dirname(__DIR__) . '/' . ltrim((string)$rutaRelativa, '/');
    if (is_file($rutaFisica)) {
        return (string)filemtime($rutaFisica);
    }
    return (string)time();
}
