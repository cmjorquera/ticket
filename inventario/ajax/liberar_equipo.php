<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $idEquipo = (int)($_POST['id_equipo'] ?? 0);
    if ($idEquipo <= 0) {
        throw new RuntimeException('Equipo no válido.');
    }
    $inventario->liberarEquipo($idEquipo, $idUsuarioSession);
    echo json_encode(['ok' => true, 'mensaje' => 'Equipo liberado correctamente.']);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
