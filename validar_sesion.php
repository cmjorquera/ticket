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
 *
 *  Comprueba:
 *    1. Que exista $_SESSION['id'].
 *    2. Que el usuario siga existiendo en `usuarios`.
 *    3. Que su estado NO sea 'bloqueado' ni 'inactivo'.
 *
 *  Si algo falla: destruye la sesión y redirige a index.php.
 *  Si todo va bien: deja $usuario_actual y refresca $_SESSION['nombre'|'email'].
 *
 *  Columnas REALES de `usuarios`: id, nombre, email, estado
 * ============================================================================
 */

session_start();

require_once __DIR__ . '/clases/Conexion.php';

// config.php es opcional; si define BASE_URL, se usa para un redirect absoluto.
if (is_file(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

if (!function_exists('_vs_url_login')) {
    /**
     * URL de login válida sin importar desde qué subcarpeta se incluyó.
     */
    function _vs_url_login(): string
    {
        if (defined('BASE_URL')) {
            return rtrim((string) BASE_URL, '/') . '/index.php';
        }
        // Fallback relativo: sube tantos niveles como subcarpetas tenga la URL.
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
// $usuario_actual queda disponible en el scope de la página que incluyó esto.
