<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    // Enforce server-side: users linked to a colegio can only see their colegio's equipment.
    // Internal/admin users (no colegio in usuario_colegio) see everything.
    $colegioUsuario     = $inventario->obtenerColegioDelUsuario($idUsuarioSession);
    $idColegioForzado   = (int)($colegioUsuario['id_colegio'] ?? 0);

    $filtros = [
        'id_colegio'          => $idColegioForzado > 0 ? $idColegioForzado : (int)($_GET['id_colegio'] ?? 0),
        'id_estado'           => (int)($_GET['id_estado'] ?? 0),
        'tipo_pc'             => trim((string)($_GET['tipo_pc'] ?? '')),
        'id_usuario_asignado' => (int)($_GET['id_usuario_asignado'] ?? 0),
        'id_ubicacion'        => (int)($_GET['id_ubicacion'] ?? 0),
        'busqueda'            => trim((string)($_GET['busqueda'] ?? '')),
    ];

    $equipos = $inventario->listarEquipos($filtros);
    $resumen = $inventario->obtenerResumen($filtros);

    ob_start();
    require dirname(__DIR__) . '/componentes/resumen.php';
    $htmlResumen = ob_get_clean();

    echo json_encode([
        'ok' => true,
        'resumen_html' => $htmlResumen,
        'data' => $equipos
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'mensaje' => 'Error al listar equipos: ' . $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}
