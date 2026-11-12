<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $idEquipo = (int)($_POST['id_equipo'] ?? 0);
    if ($idEquipo <= 0) {
        throw new RuntimeException('No se recibio el identificador del equipo.');
    }
    $alcanceInventario = $inventario->obtenerAlcanceInventario($idUsuarioSession);
    if (!$inventario->usuarioPuedeGestionarEquipoPorAlcance($idEquipo, $alcanceInventario)) {
        throw new RuntimeException('No tienes permiso para actualizar este equipo.');
    }

    $inventario->actualizarEquipo($idEquipo, $_POST, $_FILES, $idUsuarioSession);
    echo json_encode([
        'ok' => true,
        'mensaje' => 'Equipo actualizado correctamente.',
        'id_equipo' => $idEquipo,
        'redirect' => 'ver_equipo.php?id_equipo=' . $idEquipo
    ]);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
