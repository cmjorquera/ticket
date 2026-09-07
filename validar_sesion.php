<?php
/**
 * ============================================================================
 *  VALIDAR SESIÓN  ·  SEDUC Chile - Panel Central
 * ============================================================================
 *  Guard de acceso. Inclúyelo al inicio de CADA página protegida, ANTES de
 *  imprimir HTML:
 *
 *      require_once __DIR__ . '/validar_sesion.php';       // desde la raíz (dashboard.php)
 *      require_once __DIR__ . '/../validar_sesion.php';    // desde pages/
 *
 *  NO lo incluyas en index.php (ahí vive el login -> provocaría un loop).
 * ============================================================================
 */

// Fix sesiones: la carpeta de cPanel puede no existir en algunos planes
$_vs_sp = '/var/cpanel/php/sessions/ea-php83';
if (!is_dir($_vs_sp)) { session_save_path(sys_get_temp_dir()); }
session_start();

require_once __DIR__ . '/clases/Conexion.php';

if (is_file(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

if (!function_exists('_vs_url_login')) {
    function _vs_url_login(): string
    {
        if (defined('BASE_URL')) {
            return rtrim((string) BASE_URL, '/') . '/index.php';
        }
        $path = parse_url($_SERVER['PHP_SELF'] ?? '', PHP_URL_PATH) ?: '';
        $prof = max(0, substr_count(trim($path, '/'), '/') - 1);
        return str_repeat('../', $prof) . 'index.php';
    }
}

/* -- 1. ¿Hay sesión? ------------------------------------------------------- */
if (!isset($_SESSION['id']) || (int) $_SESSION['id'] <= 0) {
    session_destroy();
    header('Location: ' . _vs_url_login());
    exit;
}

/* -- 2. ¿El usuario existe y no está bloqueado/inactivo? --------------- */
try {
    $db = Conexion::getInstance('sistema_panel_central');

    $usuario_actual = $db->fetchOne(
        "SELECT id, nombre, email, estado
           FROM usuarios
          WHERE id = ?
          LIMIT 1",
        [(int) $_SESSION['id']]
    );
} catch (\Throwable $e) {
    die('Error validando la sesión. Intenta más tarde.');
}

if ($usuario_actual === false
    || $usuario_actual['estado'] === 'bloqueado'
    || $usuario_actual['estado'] === 'inactivo') {

    session_destroy();
    header('Location: ' . _vs_url_login());
    exit;
}

/* -- 3. Datos frescos para la página --------------------------------- */
$_SESSION['nombre'] = $usuario_actual['nombre'];
$_SESSION['email']  = $usuario_actual['email'];