<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if ($idUsuarioSession <= 0) {
        throw new RuntimeException('Sesion no valida.');
    }

    $idEquipo = (int)($_POST['id_equipo'] ?? 0);
    if ($idEquipo <= 0) {
        throw new RuntimeException('Equipo no valido.');
    }

    $alcanceInventario = $inventario->obtenerAlcanceInventario($idUsuarioSession);
    if (!$inventario->usuarioPuedeGestionarEquipoPorAlcance($idEquipo, $alcanceInventario)) {
        throw new RuntimeException('No tienes permiso para eliminar este equipo.');
    }

    $inventario->eliminarEquipo($idEquipo, $idUsuarioSession);

    echo json_encode([
        'ok' => true,
        'mensaje' => 'Equipo eliminado correctamente.',
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'mensaje' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
