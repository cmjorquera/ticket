<?php
require_once __DIR__ . '/componentes/boot.php';

$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoloadPath)) {
    require_once $autoloadPath;
}

if (!class_exists('FPDF')) {
    $fpdfPaths = [
        dirname(__DIR__) . '/vendor/setasign/fpdf/fpdf.php',
        dirname(__DIR__) . '/PDF/PDF/fpdf.php',
        dirname(__DIR__) . '/PDF/fpdf.php',
    ];
    foreach ($fpdfPaths as $fpdfPath) {
        if (is_file($fpdfPath)) {
            require_once $fpdfPath;
            break;
        }
    }
}

if (!class_exists('FPDF')) {
    inventario_responder_error('No se encontro la libreria FPDF disponible para generar el PDF de inventario.', 500);
}

ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_DEPRECATED);

function inv_pdf_equipos_text($texto)
{
    $texto = (string)$texto;
    $convertido = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
    return $convertido !== false ? $convertido : $texto;
}

function inv_pdf_equipos_truncar(FPDF $pdf, $texto, $anchoDisponible)
{
    $texto = (string)$texto;
    if ($pdf->GetStringWidth($texto) <= $anchoDisponible) {
        return $texto;
    }
    while ($texto !== '' && $pdf->GetStringWidth($texto . '...') > $anchoDisponible) {
        $texto = substr($texto, 0, -1);
    }
    return $texto . '...';
}

class InventarioEquiposPdf extends FPDF
{
    public $subtituloFiltros = '';
    public $generadoPor = '';

    function Header()
    {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 8, inv_pdf_equipos_text('Inventario de equipos'), 0, 1, 'L');
        $this->SetFont('Arial', '', 8.5);
        $this->SetTextColor(90, 105, 125);
        $this->Cell(0, 5, inv_pdf_equipos_text('Generado el ' . date('d-m-Y H:i') . ' por ' . $this->generadoPor), 0, 1, 'L');
        if ($this->subtituloFiltros !== '') {
            $this->SetFont('Arial', 'I', 8);
            $this->MultiCell(0, 4.5, inv_pdf_equipos_text($this->subtituloFiltros), 0, 'L');
        }
        $this->SetTextColor(0, 0, 0);
        $this->Ln(1);
    }

    function Footer()
    {
        $this->SetY(-12);
        $this->SetFont('Arial', 'I', 7.5);
        $this->SetTextColor(120, 130, 145);
        $this->Cell(0, 8, inv_pdf_equipos_text('Pagina ' . $this->PageNo() . ' de {nb}'), 0, 0, 'C');
    }
}

try {
    $alcanceInventario = $inventario->obtenerAlcanceInventario($idUsuarioSession);
    $filtros = [
        'id_colegio'          => (int)($_GET['id_colegio'] ?? 0),
        'id_estado'           => (int)($_GET['id_estado'] ?? 0),
        'tipo_pc'             => '',
        'id_ubicacion'        => (int)($_GET['id_ubicacion'] ?? 0),
        'id_usuario_asignado' => (int)($_GET['id_usuario_asignado'] ?? 0),
        'busqueda'            => trim((string)($_GET['busqueda'] ?? '')),
    ];
    $filtros = $inventario->normalizarFiltrosPorAlcance($filtros, $alcanceInventario);
    $equipos = $inventario->listarEquipos($filtros);
    $resumen = $inventario->obtenerResumen($filtros);
} catch (Throwable $e) {
    inventario_responder_error('No fue posible generar el PDF de inventario: ' . $e->getMessage(), 422);
}

// --- Resolver nombres de los filtros aplicados para mostrarlos en el encabezado ---
$partesFiltro = [];

if ($filtros['id_colegio'] > 0) {
    $nombreColegio = '';
    foreach (($alcanceInventario['colegios'] ?? []) as $colegio) {
        if ((int)$colegio['id_colegio'] === $filtros['id_colegio']) {
            $nombreColegio = (string)$colegio['nom_colegio'];
            break;
        }
    }
    if ($nombreColegio === '' && !empty($equipos)) {
        $nombreColegio = (string)($equipos[0]['nom_colegio'] ?? '');
    }
    $partesFiltro[] = 'Colegio: ' . ($nombreColegio !== '' ? $nombreColegio : ('#' . $filtros['id_colegio']));
}

if ($filtros['id_estado'] > 0) {
    $nombreEstado = '';
    foreach ($equipos as $fila) {
        if ((int)($fila['id_estado'] ?? 0) === $filtros['id_estado']) {
            $nombreEstado = (string)($fila['nombre_estado'] ?? '');
            break;
        }
    }
    $partesFiltro[] = 'Estado: ' . ($nombreEstado !== '' ? $nombreEstado : ('#' . $filtros['id_estado']));
}

if ($filtros['id_ubicacion'] > 0) {
    $nombreUbicacion = '';
    foreach ($equipos as $fila) {
        if (!empty($fila['nombre_ubicacion'])) {
            $nombreUbicacion = (string)$fila['nombre_ubicacion'];
            break;
        }
    }
    $partesFiltro[] = 'Ubicacion: ' . ($nombreUbicacion !== '' ? $nombreUbicacion : ('#' . $filtros['id_ubicacion']));
}

if ($filtros['id_usuario_asignado'] > 0) {
    $nombreResponsable = '';
    foreach ($equipos as $fila) {
        if (!empty($fila['usuario_asignado'])) {
            $nombreResponsable = trim((string)$fila['usuario_asignado']);
            break;
        }
    }
    $partesFiltro[] = 'Responsable: ' . ($nombreResponsable !== '' ? $nombreResponsable : ('#' . $filtros['id_usuario_asignado']));
}

if ($filtros['busqueda'] !== '') {
    $partesFiltro[] = 'Busqueda: "' . $filtros['busqueda'] . '"';
}

$subtituloFiltros = $partesFiltro
    ? ('Filtros aplicados: ' . implode('  |  ', $partesFiltro))
    : 'Sin filtros aplicados (todos los equipos visibles segun permisos)';

// --- Totales para el bloque de resumen (sin nuevas consultas SQL) ---
$totalExportados = count($equipos);
$totalActivos = (int)($resumen['total_activos'] ?? 0);
$totalReparacion = (int)($resumen['total_reparacion'] ?? 0);
$totalBaja = (int)($resumen['total_baja'] ?? 0);
$totalSinResponsable = 0;
$totalSinUbicacion = 0;
foreach ($equipos as $fila) {
    if (empty($fila['id_usuario_asignado'])) {
        $totalSinResponsable++;
    }
    if (empty($fila['nombre_ubicacion'])) {
        $totalSinUbicacion++;
    }
}

$pdf = new InventarioEquiposPdf('L', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->subtituloFiltros = $subtituloFiltros;
$pdf->generadoPor = $nombreUsuarioSession !== '' ? trim($nombreUsuarioSession) : ('Usuario #' . $idUsuarioSession);
$pdf->SetTitle(inv_pdf_equipos_text('Inventario de equipos'));
$pdf->SetAuthor(inv_pdf_equipos_text('SISTEMA TICKET'));
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 14);
$pdf->AddPage();

// --- Bloque de resumen ---
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(238, 245, 251);
$pdf->SetDrawColor(216, 226, 239);
$resumenTexto = sprintf(
    'Exportados: %d   |   Activos: %d   |   En reparacion: %d   |   Dados de baja: %d   |   Sin responsable: %d   |   Sin ubicacion: %d',
    $totalExportados,
    $totalActivos,
    $totalReparacion,
    $totalBaja,
    $totalSinResponsable,
    $totalSinUbicacion
);
$pdf->Cell(0, 8, inv_pdf_equipos_text($resumenTexto), 1, 1, 'L', true);
$pdf->Ln(3);

// --- Tabla principal ---
$columnas = [
    ['label' => 'ID', 'width' => 10],
    ['label' => 'Equipo', 'width' => 45],
    ['label' => 'N. Serie', 'width' => 28],
    ['label' => 'Colegio', 'width' => 32],
    ['label' => 'Tipo', 'width' => 18],
    ['label' => 'Fabricante', 'width' => 24],
    ['label' => 'Modelo', 'width' => 30],
    ['label' => 'Estado', 'width' => 20],
    ['label' => 'Ubicacion', 'width' => 28],
    ['label' => 'Responsable', 'width' => 24],
];

$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(33, 37, 41);
$pdf->SetTextColor(255, 255, 255);
foreach ($columnas as $col) {
    $pdf->Cell($col['width'], 7, inv_pdf_equipos_text($col['label']), 1, 0, 'L', true);
}
$pdf->Ln();
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 7.6);

if (empty($equipos)) {
    $pdf->SetFillColor(255, 255, 255);
    $anchoTotal = array_sum(array_column($columnas, 'width'));
    $pdf->Cell($anchoTotal, 8, inv_pdf_equipos_text('No hay equipos para los filtros seleccionados.'), 1, 1, 'C');
} else {
    $fill = false;
    foreach ($equipos as $fila) {
        $nombrePersonalizado = trim((string)($fila['nombre_personalizado'] ?? ''));
        $nombreEquipo = $nombrePersonalizado !== '' ? $nombrePersonalizado : (string)($fila['nombre_equipo'] ?? '-');
        $responsable = trim((string)($fila['usuario_asignado'] ?? ''));
        $ubicacion = trim((string)($fila['nombre_ubicacion'] ?? ''));

        $valores = [
            (string)(int)$fila['id_equipo'],
            $nombreEquipo,
            (string)($fila['numero_serie'] ?: 'No informado'),
            (string)($fila['nom_colegio'] ?: 'No informado'),
            (string)($fila['tipo_pc'] ?: 'No informado'),
            (string)($fila['fabricante'] ?: 'No informado'),
            (string)($fila['producto'] ?: 'No informado'),
            (string)($fila['nombre_estado'] ?: 'Sin estado'),
            $ubicacion !== '' ? $ubicacion : 'Sin ubicacion',
            $responsable !== '' ? $responsable : 'Sin asignar',
        ];

        if ($fill) {
            $pdf->SetFillColor(247, 250, 253);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        foreach ($valores as $i => $valor) {
            $texto = inv_pdf_equipos_truncar($pdf, inv_pdf_equipos_text($valor), $columnas[$i]['width'] - 2);
            $pdf->Cell($columnas[$i]['width'], 6.5, $texto, 1, 0, 'L', true);
        }
        $pdf->Ln();
        $fill = !$fill;
    }
}

$nombreArchivo = 'inventario_equipos_' . date('Ymd_His') . '.pdf';
$pdf->Output('D', $nombreArchivo);
exit;
