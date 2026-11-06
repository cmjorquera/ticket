<?php
/**
 * Arranque del módulo Inventario de Herramientas dentro del sistema nuevo.
 * - Usa la sesión del sistema (clases/Session.php) y exige sesión iniciada.
 * - Conexión con credenciales de config_privada, reutilizando
 *   inventario/class/InventarioConexion.php (sin contraseñas en el código).
 * - Si faltan las tablas, muestra un aviso (503) en vez de un error 500.
 */

ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once dirname(__DIR__, 2) . '/clases/Session.php';
Session::iniciar();

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('[inventario_herramientas] ' . $error['message'] . ' en ' . $error['file'] . ':' . $error['line']);
    }
});

function inventario_es_ajax(): bool
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (strpos($script, '/inventario_herramintas/ajax/') !== false) {
        return true;
    }
    // Los endpoints de guardado responden JSON aunque no estén en ajax/
    return (bool) preg_match('#/(guardar|actualizar|eliminar_logico)_herramienta\.php$#', $script);
}

function inventario_responder_error($mensaje, $codigo = 500)
{
    http_response_code((int)$codigo);
    if (inventario_es_ajax()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Inventario de herramientas</title>';
    echo '<style>body{font-family:Arial,sans-serif;background:#f4f7fb;color:#17324d;padding:32px}.box{max-width:900px;margin:0 auto;background:#fff;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.08)}h1{margin-top:0}a{color:#1d4675}</style>';
    echo '</head><body><div class="box"><h1>Inventario de herramientas no pudo cargar</h1><p>' . htmlspecialchars((string)$mensaje, ENT_QUOTES, 'UTF-8') . '</p><p><a href="../principal.php">Volver al inicio</a></p></div></body></html>';
    exit;
}

/** Mensaje para el usuario: los errores de base de datos no se muestran en detalle. */
function inventario_mensaje_error(Throwable $e): string
{
    if ($e instanceof mysqli_sql_exception) {
        error_log('[inventario_herramientas] BD: ' . $e->getMessage());
        if ((int)$e->getCode() === 1062) {
            return 'Ya existe una herramienta con ese número de serie o código QR.';
        }
        return 'Error al guardar en la base de datos. Intenta nuevamente o contacta al administrador.';
    }
    return $e->getMessage();
}

$idUsuarioSession = (int)($_SESSION['id'] ?? 0);
$nombreUsuarioSession = trim((string)($_SESSION['nombre'] ?? '') . ' ' . (string)($_SESSION['apellido_paterno'] ?? ''));
$idPagActual = '9';

if ($idUsuarioSession <= 0) {
    if (inventario_es_ajax()) {
        inventario_responder_error('Sesión expirada. Vuelve a iniciar sesión.', 401);
    }
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../class/Inventario.php';

try {
    $inventario = new Inventario();
} catch (Throwable $e) {
    error_log('[inventario_herramientas] inicio: ' . $e->getMessage());
    inventario_responder_error('No fue posible iniciar el inventario de herramientas. Intenta nuevamente o contacta al administrador.');
}

$instalacionHerramientas = $inventario->obtenerEstadoInstalacion();
if (!$instalacionHerramientas['instalado']) {
    inventario_responder_error('El inventario de herramientas aún no está instalado en esta base de datos. Ejecuta sql/herramientas_instalacion.sql. Tablas faltantes: ' . implode(', ', $instalacionHerramientas['faltantes']) . '.', 503);
}

function inventario_url($ruta = '')
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base = '/inventario_herramintas';

    $pos = strpos($scriptName, '/inventario_herramintas');
    if ($pos !== false) {
        $base = substr($scriptName, 0, $pos) . '/inventario_herramintas';
    }

    return $ruta === '' ? $base : $base . '/' . ltrim($ruta, '/');
}

function inventario_sistema_url($ruta = '')
{
    return '../' . ltrim((string)$ruta, '/');
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
