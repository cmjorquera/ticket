<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $colegioUsuario   = $inventario->obtenerColegioDelUsuario($idUsuarioSession);
    $idColegioForzado = (int)($colegioUsuario['id_colegio'] ?? 0);

    $filtros = [
        'id_colegio'          => $idColegioForzado > 0 ? $idColegioForzado : (int)($_GET['id_colegio'] ?? 0),
        'id_estado'           => (int)($_GET['id_estado'] ?? 0),
        'id_usuario_asignado' => (int)($_GET['id_usuario_asignado'] ?? 0),
        'busqueda'            => trim((string)($_GET['busqueda'] ?? '')),
    ];

    $monitores = $inventario->listarMonitores($filtros);
    $resumen   = $inventario->obtenerResumenMonitores($filtros);

    ob_start();
    require dirname(__DIR__) . '/componentes/resumen_monitores.php';
    $htmlResumen = ob_get_clean();

    echo json_encode([
        'ok'          => true,
        'resumen_html' => $htmlResumen,
        'data'        => $monitores,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Error al listar monitores: ' . $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
