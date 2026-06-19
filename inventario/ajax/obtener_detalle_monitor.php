<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $idMonitor = (int)($_GET['id_monitor'] ?? 0);
    if ($idMonitor <= 0) {
        throw new RuntimeException('Monitor no valido.');
    }

    $monitor = $inventario->obtenerMonitorPorId($idMonitor);
    if (!$monitor) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'Monitor no encontrado.'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    ob_start();
    $detalleOffcanvas = true;
    require dirname(__DIR__) . '/componentes/detalle_monitor.php';
    $detalleHtml = ob_get_clean();

    echo json_encode([
        'ok' => true,
        'monitor' => [
            'id_monitor' => (int)$monitor['id_monitor'],
            'nombre_monitor' => $monitor['nombre_monitor'] ?? '',
        ],
        'html' => $detalleHtml,
        'detalle_html' => $detalleHtml,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
