<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../clases/Session.php';
require_once __DIR__ . '/../clases/Conexion.php';

if (!class_exists('Sesion', false)) {
    final class Sesion extends Session
    {
        public static function requerir(): void
        {
            self::iniciar();
            if (!isset($_SESSION['id']) || (int) $_SESSION['id'] <= 0) {
                responder_json(['ok' => false, 'mensaje' => 'Sesión expirada.'], 401);
            }
        }
    }
}

function responder_json(array $respuesta, int $estado = 200): never
{
    http_response_code($estado);
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function entrada_ajax(): array
{
    $tipo = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
    if (str_contains($tipo, 'application/json')) {
        $datos = json_decode((string) file_get_contents('php://input'), true);
        return is_array($datos) ? $datos : [];
    }
    return $_POST;
}

