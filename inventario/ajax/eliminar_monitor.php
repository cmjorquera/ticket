<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $idMonitor = (int)($_POST['id_monitor'] ?? 0);
    if ($idMonitor <= 0) {
        throw new RuntimeException('Monitor no válido.');
    }
    $inventario->eliminarMonitor($idMonitor);
    echo json_encode(['ok' => true, 'mensaje' => 'Monitor eliminado correctamente.']);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
