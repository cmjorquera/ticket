<?php
/**
 * Arranque del módulo Inventario dentro del sistema nuevo.
 * - Usa la sesión del sistema (clases/Session.php).
 * - Exige sesión iniciada (salvo páginas públicas que definan
 *   INVENTARIO_PUBLICO antes de incluir este archivo).
 * - Conexión con credenciales de config_privada (class/InventarioConexion.php).
 */

ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once dirname(__DIR__, 3) . '/clases/Session.php';
Session::iniciar();

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('[inventario] ' . $error['message'] . ' en ' . $error['file'] . ':' . $error['line']);
    }
});

function inventario_es_ajax(): bool
{
    return strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/inventario/pc/ajax/') !== false;
}

function inventario_responder_error($mensaje, $codigo = 500)
{
    http_response_code((int)$codigo);
    if (inventario_es_ajax()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Inventario</title>';
    echo '<style>body{font-family:Arial,sans-serif;background:#f4f7fb;color:#17324d;padding:32px}.box{max-width:900px;margin:0 auto;background:#fff;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.08)}h1{margin-top:0}</style>';
    echo '</head><body><div class="box"><h1>Inventario no pudo cargar</h1><p>' . htmlspecialchars((string)$mensaje, ENT_QUOTES, 'UTF-8') . '</p></div></body></html>';
    exit;
}

$idUsuarioSession = (int)($_SESSION['id'] ?? 0);
$nombreUsuarioSession = trim((string)($_SESSION['nombre'] ?? '') . ' ' . (string)($_SESSION['apellido_paterno'] ?? ''));
$idPagActual = '9';

if ($idUsuarioSession <= 0 && !defined('INVENTARIO_PUBLICO')) {
    if (inventario_es_ajax()) {
        inventario_responder_error('Sesión expirada. Vuelve a iniciar sesión.', 401);
    }
    header('Location: ../../index.php');
    exit;
}

require_once __DIR__ . '/../class/Inventario.php';

try {
    $inventario = new Inventario();
} catch (Throwable $e) {
    error_log('[inventario] inicio: ' . $e->getMessage());
    inventario_responder_error('No fue posible iniciar el módulo de inventario. Intenta nuevamente o contacta al administrador.');
}

// Si las tablas del módulo no están instaladas, avisar en vez de un error 500.
if (!$inventario->tablaExiste('equipos') || !$inventario->tablaExiste('usuario_colegio')) {
    inventario_responder_error('El módulo de inventario aún no está instalado en esta base de datos. Ejecuta sql/inventario_instalacion.sql y luego sql/inventario_asignar_usuarios.sql.', 503);
}

/**
 * URL (desde la raíz del sitio) de la carpeta inventario/, respetando una
 * posible subcarpeta de instalación. Ej: "/inventario" o "/sistema/inventario".
 */
function inventario_url_carpeta(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $pos = strpos($scriptName, '/inventario/');
    return $pos !== false ? substr($scriptName, 0, $pos) . '/inventario' : '/inventario';
}

/** URL de una página del módulo Inventario PC (inventario/pc/...). */
function inventario_url($ruta = '')
{
    $base = inventario_url_carpeta() . '/pc';
    return $ruta === '' ? $base : $base . '/' . ltrim($ruta, '/');
}

/**
 * URL de una foto guardada. Las fotos siguen en inventario/uploads/
 * (no se movieron con el módulo). En la BD las de equipos se guardan como
 * "/uploads/equipos/..." y las de monitores como "inventario/uploads/monitores/...".
 */
function inventario_foto_url($ruta = '')
{
    $ruta = ltrim((string)$ruta, '/');
    if (strpos($ruta, 'inventario/') === 0) {
        $ruta = substr($ruta, strlen('inventario/'));
    }
    return inventario_url_carpeta() . '/' . $ruta;
}

function inventario_url_absoluta($ruta = '')
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . inventario_url($ruta);
}

function inventario_sistema_url($ruta = '')
{
    $ruta = ltrim((string)$ruta, '/');
    return '../../' . $ruta;
}

function inventario_sistema_url_absoluta($ruta = '')
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    return $scheme . '://' . $host . '/' . ltrim((string)$ruta, '/');
}

/**
 * URL del logo de un colegio, o null si no hay archivo.
 * Busca primero img/colegios/colegio_{id}.png (sistema antiguo) y luego los
 * escudos del sistema nuevo assets/images/escudo/{id}.png|jpg.
 */
function inventario_logo_colegio(int $idColegio): ?string
{
    if ($idColegio <= 0) {
        return null;
    }
    $raiz = dirname(__DIR__, 3);
    $candidatos = [
        'img/colegios/colegio_' . $idColegio . '.png',
        'assets/images/escudo/' . $idColegio . '.png',
        'assets/images/escudo/' . $idColegio . '.jpg',
    ];
    foreach ($candidatos as $relativa) {
        if (is_file($raiz . '/' . $relativa)) {
            return inventario_sistema_url($relativa);
        }
    }
    return null;
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
