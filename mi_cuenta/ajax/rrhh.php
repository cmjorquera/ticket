<?php
/**
 * Mi cuenta · lectura de RRHH (JSON, GET). Siempre de la persona de la sesión.
 *   accion=liquidacion&id=…   detalle (haberes y descuentos)
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
try {
    $persona = (new MiCuenta(Conexion::getInstance('sistema_panel_central')))->persona($id);
    if (($_GET['accion'] ?? '') === 'liquidacion') {
        $idLiq = (string) ($_GET['id'] ?? '');
        if (!preg_match('/^[\w\-.]{1,80}$/', $idLiq)) {
            mc_json(['ok' => false, 'mensaje' => 'Liquidación no válida.'], 400);
        }
        $l = Rrhh::fuente()->liquidacion($persona, $idLiq);
        if (!$l) {
            mc_json(['ok' => false, 'mensaje' => 'Liquidación no encontrada.'], 404);
        }
        unset($l['pdf_url']);   // el enlace firmado de Buk no sale al navegador
        mc_json(['ok' => true, 'liquidacion' => $l]);
    }
    mc_json(['ok' => false, 'mensaje' => 'Acción no válida.'], 400);
} catch (RuntimeException $e) {
    mc_json(['ok' => false, 'mensaje' => $e->getMessage()], 502);
} catch (Throwable $e) {
    error_log('[mi_cuenta] rrhh: ' . $e->getMessage());
    mc_json(['ok' => false, 'mensaje' => 'Ocurrió un error inesperado.'], 500);
}
