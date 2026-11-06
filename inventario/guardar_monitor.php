<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Método no permitido.');
    }

    $colegioUsuario = $inventario->obtenerColegioDelUsuario($idUsuarioSession);
    $idColegio      = (int)($colegioUsuario['id_colegio'] ?? 0);

    $idMonitor = $inventario->guardarMonitor($_POST, $_FILES, $idUsuarioSession, $idColegio);

    echo json_encode([
        'ok'       => true,
        'mensaje'  => 'Monitor registrado correctamente.',
        'redirect' => 'ver_monitor.php?id_monitor=' . $idMonitor,
        'id'       => $idMonitor,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
