<?php
/**
 * Arranque del módulo Licenciamiento (software y sitios web) dentro del sistema nuevo.
 * - Usa la sesión del sistema (clases/Session.php) y exige sesión iniciada.
 * - Conexión con credenciales de config_privada, reutilizando
 *   inventario/comun/InventarioConexion.php (sin contraseñas en el código).
 * - Si faltan las tablas, muestra un aviso (503) en vez de un error 500.
 */

ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once dirname(__DIR__, 2) . '/clases/Session.php';
Session::iniciar();

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('[licenciamiento] ' . $error['message'] . ' en ' . $error['file'] . ':' . $error['line']);
    }
});

function inventario_es_ajax(): bool
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    if (strpos($script, '/licenciamiento/ajax/') !== false) {
        return true;
    }
    // Los endpoints de guardado responden JSON aunque no estén en ajax/
    return (bool) preg_match('#/(guardar|actualizar|eliminar_logico)_(software|sitio_web)\.php$#', $script);
}

function inventario_responder_error($mensaje, $codigo = 500)
{
    http_response_code((int)$codigo);
    if (inventario_es_ajax()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'mensaje' => $mensaje], JSON_UNESCAPED_UNICODE);
        exit;
    }
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Licenciamiento</title>';
    echo '<style>body{font-family:Arial,sans-serif;background:#f4f7fb;color:#17324d;padding:32px}.box{max-width:900px;margin:0 auto;background:#fff;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.08)}h1{margin-top:0}a{color:#1d4675}</style>';
    echo '</head><body><div class="box"><h1>Licenciamiento no pudo cargar</h1><p>' . htmlspecialchars((string)$mensaje, ENT_QUOTES, 'UTF-8') . '</p><p><a href="../principal.php">Volver al inicio</a></p></div></body></html>';
    exit;
}

/** Mensaje para el usuario: los errores de base de datos no se muestran en detalle. */
function inventario_mensaje_error(Throwable $e): string
{
    if ($e instanceof mysqli_sql_exception) {
        error_log('[licenciamiento] BD: ' . $e->getMessage());
        if ((int)$e->getCode() === 1062) {
            return 'Ya existe un registro con esos datos.';
        }
        return 'Error al guardar en la base de datos. Intenta nuevamente o contacta al administrador.';
    }
    if ($e instanceof RuntimeException) {
        return $e->getMessage(); // mensajes de validación pensados para el usuario
    }
    error_log('[licenciamiento] ' . get_class($e) . ': ' . $e->getMessage());
    return 'Ocurrió un error inesperado. Intenta nuevamente o contacta al administrador.';
}

$idUsuarioSession = (int)($_SESSION['id'] ?? 0);
$nombreUsuarioSession = trim((string)($_SESSION['nombre'] ?? '') . ' ' . (string)($_SESSION['apellido_paterno'] ?? ''));
$idPagActual = '15';

if ($idUsuarioSession <= 0) {
    if (inventario_es_ajax()) {
        inventario_responder_error('Sesión expirada. Vuelve a iniciar sesión.', 401);
    }
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../class/InventarioSoftware.php';

try {
    $inventario = new InventarioSoftware();
} catch (Throwable $e) {
    error_log('[licenciamiento] inicio: ' . $e->getMessage());
    inventario_responder_error('No fue posible iniciar el módulo de licenciamiento. Intenta nuevamente o contacta al administrador.');
}

$instalacionLicencias = $inventario->obtenerEstadoInstalacion();
if (!$instalacionLicencias['instalado']) {
    inventario_responder_error('El módulo de licenciamiento aún no está instalado en esta base de datos. Ejecuta sql/licenciamiento_instalacion.sql. Tablas faltantes: ' . implode(', ', $instalacionLicencias['faltantes']) . '.', 503);
}

function inventario_url($ruta = '')
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $base = '/licenciamiento';

    $pos = strpos($scriptName, '/licenciamiento');
    if ($pos !== false) {
        $base = substr($scriptName, 0, $pos) . '/licenciamiento';
    }

    return $ruta === '' ? $base : $base . '/' . ltrim($ruta, '/');
}

function inventario_sistema_url($ruta = '')
{
    return '../' . ltrim((string)$ruta, '/');
}

/** Formatea cantidades de stock: sin decimales si es entero, hasta 2 si no (formato chileno). */
function inventario_num($valor): string
{
    $texto = number_format((float) $valor, 2, ',', '.');
    return rtrim(rtrim($texto, '0'), ','); // 1,50 -> 1,5 · 10,00 -> 10
}

/** Devuelve la URL solo si es http/https (evita enlaces javascript:); si no, ''. */
function inventario_url_segura($url): string
{
    $url = trim((string) $url);
    return preg_match('#^https?://#i', $url) ? $url : '';
}

/** Enlace HTML seguro a una URL externa, o el texto plano si no es http/https. */
function inventario_link_externo($url, string $vacio = '—'): string
{
    $url = trim((string) $url);
    if ($url === '') {
        return htmlspecialchars($vacio, ENT_QUOTES, 'UTF-8');
    }
    $segura = inventario_url_segura($url);
    $texto = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    return $segura !== ''
        ? '<a href="' . htmlspecialchars($segura, ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">' . $texto . '</a>'
        : $texto;
}

/** 'Y-m-d' o 'Y-m-d H:i:s' → 'dd-mm-aaaa' */
function inventario_fecha($valor, string $vacio = '—'): string
{
    if (!$valor || strpos((string) $valor, '0000-00-00') === 0) {
        return $vacio;
    }
    $t = strtotime((string) $valor);
    return $t ? date('d-m-Y', $t) : (string) $valor;
}

/** Monto con su moneda: CLP sin decimales, USD con 2. */
function inventario_monto($valor, $moneda = 'USD'): string
{
    $moneda = strtoupper(trim((string) $moneda)) ?: 'USD';
    $dec = $moneda === 'CLP' ? 0 : 2;
    return $moneda . ' ' . number_format((float) $valor, $dec, ',', '.');
}

/** Gasto separado por moneda a partir de costo_clp / costo_usd (nunca suma CLP con USD). */
function inventario_gasto(array $fila): string
{
    $partes = [];
    if ((float)($fila['costo_clp'] ?? 0) > 0) { $partes[] = inventario_monto($fila['costo_clp'], 'CLP'); }
    if ((float)($fila['costo_usd'] ?? 0) > 0) { $partes[] = inventario_monto($fila['costo_usd'], 'USD'); }
    return $partes ? implode(' · ', $partes) : '0';
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
