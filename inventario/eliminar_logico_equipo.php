<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $idEquipo = (int)($_POST['id_equipo'] ?? 0);
    $nuevoEstado = (int)($_POST['id_estado'] ?? 4);
    if ($idEquipo <= 0) {
        throw new RuntimeException('Equipo no valido.');
    }

    $inventario->cambiarEstadoLogico($idEquipo, $nuevoEstado, $idUsuarioSession);
    echo json_encode(['ok' => true, 'mensaje' => 'Estado del equipo actualizado correctamente.']);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
