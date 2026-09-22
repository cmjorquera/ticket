<?php
require_once dirname(__DIR__, 2) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if ($idUsuarioSession <= 0) {
        throw new RuntimeException('Sesión no válida.');
    }

    $idUsuario = (int)($_POST['id_usuario'] ?? 0);
    $permisos = isset($_POST['permisos']) ? json_decode($_POST['permisos'], true) : [];

    if ($idUsuario <= 0) {
        throw new RuntimeException('ID de usuario no válido.');
    }

    require_once dirname(__DIR__) . '/class/GestorPermisosUsuarios.php';
    $gestorPermisos = new GestorPermisosUsuarios($bdato);

    $resultado = $gestorPermisos->guardarPermisosUsuario($idUsuarioSession, $idUsuario, $permisos);

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Permisos guardados correctamente.',
        'usuario_id' => $idUsuario,
        'permisos_procesados' => $resultado['procesados'],
        'errores' => $resultado['errores']
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
?>
