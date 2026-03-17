<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $filtros = [
        'id_colegio' => (int)($_GET['id_colegio'] ?? 0),
        'id_usuario_responsable' => (int)($_GET['id_usuario_responsable'] ?? 0),
        'tipo_licenciamiento' => trim((string)($_GET['tipo_licenciamiento'] ?? '')),
        'busqueda' => trim((string)($_GET['busqueda'] ?? '')),
    ];
    echo json_encode(['ok' => true, 'data' => $inventario->listarSoftwares($filtros)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error al listar software: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
