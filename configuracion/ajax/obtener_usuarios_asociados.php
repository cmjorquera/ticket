<?php
require_once dirname(__DIR__, 2) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if ($idUsuarioSession <= 0) {
        throw new RuntimeException('Sesión no válida.');
    }

    require_once dirname(__DIR__) . '/class/GestorPermisosUsuarios.php';
    $gestorPermisos = new GestorPermisosUsuarios($bdato);

    $usuarios = $gestorPermisos->obtenerUsuariosAsociados($idUsuarioSession);

    echo json_encode([
        'ok' => true,
        'data' => $usuarios
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
?>
