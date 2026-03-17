<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $filtros = ['id_colegio' => (int)($_GET['id_colegio'] ?? 0), 'id_estado' => (int)($_GET['id_estado'] ?? 0), 'categoria' => trim((string)($_GET['categoria'] ?? '')), 'id_usuario_asignado' => (int)($_GET['id_usuario_asignado'] ?? 0), 'busqueda' => trim((string)($_GET['busqueda'] ?? ''))];
    $herramientas = $inventario->listarHerramientas($filtros);
    $resumen = $inventario->obtenerResumen($filtros);
    ob_start();
    require dirname(__DIR__) . '/componentes/resumen.php';
    $resumenHtml = ob_get_clean();
    echo json_encode(['ok' => true, 'data' => $herramientas, 'resumen_html' => $resumenHtml], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'Error al listar herramientas: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
