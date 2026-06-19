<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Método no permitido.');
    }

    $idMonitor = (int)($_POST['id_monitor'] ?? 0);
    if ($idMonitor <= 0) {
        throw new RuntimeException('Monitor no válido.');
    }

    $inventario->actualizarMonitor($idMonitor, $_POST, $_FILES, $idUsuarioSession);

    echo json_encode([
        'ok'       => true,
        'mensaje'  => 'Monitor actualizado correctamente.',
        'redirect' => 'ver_monitor.php?id_monitor=' . $idMonitor,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
