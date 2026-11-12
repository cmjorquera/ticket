<?php
require_once dirname(__DIR__) . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $alcanceInventario = $inventario->obtenerAlcanceInventario($idUsuarioSession);

    $filtros = [
        'id_colegio'          => (int)($_GET['id_colegio'] ?? 0),
        'id_estado'           => (int)($_GET['id_estado'] ?? 0),
        'tipo_pc'             => '',
        'id_usuario_asignado' => (int)($_GET['id_usuario_asignado'] ?? 0),
        'id_ubicacion'        => (int)($_GET['id_ubicacion'] ?? 0),
        'busqueda'            => trim((string)($_GET['busqueda'] ?? '')),
    ];
    $filtros = $inventario->normalizarFiltrosPorAlcance($filtros, $alcanceInventario);

    // El indicador seleccionado filtra la tabla, pero los indicadores se
    // calculan sin él para que sigan mostrando sus propios totales.
    $kpiActivo = Inventario::normalizarKpi($_GET['kpi'] ?? '');
    $equipos = $inventario->listarEquipos($filtros + ['kpi' => $kpiActivo]);
    $resumen = $inventario->obtenerResumen($filtros);

    ob_start();
    require dirname(__DIR__) . '/componentes/resumen.php';   // usa $kpiActivo
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
