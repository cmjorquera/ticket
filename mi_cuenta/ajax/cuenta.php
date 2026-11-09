<?php
/**
 * Mi cuenta (JSON, solo POST con CSRF). Siempre sobre el usuario de la sesión.
 *   accion=datos        telefono, anexo, fecha_nacimiento
 *   accion=clave        actual, nueva, repetir
 *   accion=foto         archivo "foto" (JPG/PNG/WEBP, máx. 3 MB)
 *   accion=quitar_foto
 *   accion=actualizar   olvida lo guardado de Buk y vuelve a leer
 */
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
ini_set('display_errors', '0');

require_once dirname(__DIR__, 2) . '/clases/Session.php';
require_once dirname(__DIR__) . '/class/MiCuenta.php';
require_once dirname(__DIR__) . '/class/Rrhh.php';
Session::iniciar();

function mc_json(array $r, int $codigo = 200): never
{
    http_response_code($codigo);
    echo json_encode($r, JSON_UNESCAPED_UNICODE);
    exit;
}

$id = (int) ($_SESSION['id'] ?? 0);
if ($id <= 0) {
    mc_json(['ok' => false, 'mensaje' => 'Sesión expirada. Vuelve a iniciar sesión.'], 401);
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    mc_json(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}
if (empty($_SESSION['csrf_mi_cuenta']) || !hash_equals((string) $_SESSION['csrf_mi_cuenta'], (string) ($_POST['csrf'] ?? ''))) {
    mc_json(['ok' => false, 'mensaje' => 'La sesión expiró. Recarga la página.'], 419);
}

try {
    $cuenta = new MiCuenta(Conexion::getInstance('sistema_panel_central'));
    switch ((string) ($_POST['accion'] ?? '')) {
        case 'datos':
            $cuenta->actualizarDatos($id, (string) ($_POST['telefono'] ?? ''), (string) ($_POST['anexo'] ?? ''), (string) ($_POST['fecha_nacimiento'] ?? ''));
            mc_json(['ok' => true, 'mensaje' => 'Datos guardados.']);

        case 'clave':
            // Freno simple: 5 intentos fallidos → esperar 10 minutos
            $f = $_SESSION['mc_clave'] ?? ['n' => 0, 't' => 0];
            if ($f['n'] >= 5 && $f['t'] > time() - 600) {
                mc_json(['ok' => false, 'mensaje' => 'Demasiados intentos. Espera unos minutos y vuelve a intentarlo.'], 429);
            }
            try {
                $cuenta->cambiarClave($id, (string) ($_POST['actual'] ?? ''), (string) ($_POST['nueva'] ?? ''), (string) ($_POST['repetir'] ?? ''));
            } catch (RuntimeException $e) {
                $_SESSION['mc_clave'] = ['n' => $f['n'] + 1, 't' => time()];
                throw $e;
            }
            unset($_SESSION['mc_clave'], $_SESSION['debe_cambiar_clave']);
            session_regenerate_id(true);
            mc_json(['ok' => true, 'mensaje' => 'Contraseña actualizada. Úsala la próxima vez que ingreses.']);

        case 'foto':
            $cuenta->guardarFoto($id, $_FILES['foto'] ?? []);
            mc_json(['ok' => true, 'mensaje' => 'Foto actualizada.', 'url' => 'foto.php?v=' . time()]);

        case 'quitar_foto':
            $cuenta->quitarFoto($id);
            mc_json(['ok' => true, 'mensaje' => 'Foto eliminada.']);

        case 'actualizar':
            Rrhh::olvidar();
            mc_json(['ok' => true]);
    }
    mc_json(['ok' => false, 'mensaje' => 'Acción no válida.'], 400);
} catch (PDOException $e) {
    error_log('[mi_cuenta] BD: ' . $e->getMessage());
    mc_json(['ok' => false, 'mensaje' => 'No fue posible guardar. Intenta nuevamente.'], 500);
} catch (RuntimeException $e) {
    mc_json(['ok' => false, 'mensaje' => $e->getMessage()], 422);
} catch (Throwable $e) {
    error_log('[mi_cuenta] ' . $e->getMessage());
    mc_json(['ok' => false, 'mensaje' => 'Ocurrió un error inesperado.'], 500);
}
