<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $idMonitor = (int)($_POST['id_monitor'] ?? 0);
    $idFoto    = (int)($_POST['id_foto'] ?? 0);
    if ($idMonitor <= 0 || $idFoto <= 0) {
        throw new RuntimeException('Parámetros inválidos.');
    }
    $inventario->marcarFotoPrincipalMonitor($idMonitor, $idFoto);
    echo json_encode(['ok' => true, 'mensaje' => 'Foto principal actualizada.']);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
