<?php
/**
 * Arranque del módulo Mensajes: sesión obligatoria, conexión PDO
 * (credenciales en config_privada) y token CSRF para las acciones.
 */
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once dirname(__DIR__, 2) . '/clases/Session.php';
require_once dirname(__DIR__, 2) . '/clases/Conexion.php';
require_once __DIR__ . '/../class/MensajesChat.php';
Session::iniciar();

function msg_h($v): string
{
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
}

function msg_asset_version(string $ruta): string
{
    $f = dirname(__DIR__) . '/' . ltrim($ruta, '/');
    return is_file($f) ? (string) filemtime($f) : (string) time();
}

/** 'Y-m-d H:i:s' → "hoy 14:05", "ayer 09:10" o "dd-mm-aaaa hh:mm". */
function msg_fecha(string $fecha): string
{
    $t = strtotime($fecha);
    if (!$t) {
        return '—';
    }
    $hoy = date('Y-m-d');
    if (date('Y-m-d', $t) === $hoy) {
        return 'Hoy ' . date('H:i', $t);
    }
    if (date('Y-m-d', $t) === date('Y-m-d', strtotime('-1 day'))) {
        return 'Ayer ' . date('H:i', $t);
    }
    return date('d-m-Y H:i', $t);
}

$idUsuarioSession = (int) ($_SESSION['id'] ?? 0);
$esAjaxMensajes = strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/mensajes/ajax/') !== false;
if ($idUsuarioSession <= 0) {
    if ($esAjaxMensajes) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'mensaje' => 'Sesión expirada. Vuelve a iniciar sesión.']);
        exit;
    }
    header('Location: ../index.php');
    exit;
}
if (empty($_SESSION['csrf_mensajes'])) {
    $_SESSION['csrf_mensajes'] = bin2hex(random_bytes(32));
}

try {
    $mensajeria = new MensajesChat(Conexion::getInstance('sistema_panel_central'), $idUsuarioSession);
    $mensajeriaInstalada = $mensajeria->instalado();
} catch (Throwable $e) {
    error_log('[mensajes] inicio: ' . $e->getMessage());
    $mensajeriaInstalada = false;
    $mensajeria = null;
}
