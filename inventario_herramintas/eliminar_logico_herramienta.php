<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $idHerramienta = (int)($_POST['id_herramienta'] ?? 0);
    $nuevoEstado = (int)($_POST['id_estado'] ?? 4);
    if ($idHerramienta <= 0) { throw new RuntimeException('Herramienta no valida.'); }
    $inventario->cambiarEstadoLogico($idHerramienta, $nuevoEstado, $idUsuarioSession);
    echo json_encode(['ok' => true, 'mensaje' => 'Estado de la herramienta actualizado correctamente.']);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
