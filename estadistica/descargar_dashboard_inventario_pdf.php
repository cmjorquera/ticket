<?php
session_start();

require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';
require_once __DIR__ . '/../PDF/PDF/fpdf.php';
require_once __DIR__ . '/dashboard_inventario_data.php';

$idUsuarioSession = (int)($_SESSION['id'] ?? 0);
if ($idUsuarioSession <= 0) {
    http_response_code(401);
    exit('No autorizado');
}

$db = new MySQL('', '', '');
$db->set_charset('utf8mb4');
$funciones = new Funciones();
$filters = di_parse_filters($_GET);
$data = di_dashboard_data($db, $funciones, $idUsuarioSession, $filters);
$ctx = $data['ctx'];
$kpis = $data['kpis'];

class DashboardInventarioPdf extends FPDF
{
    public function Header()
    {
        $this->SetFillColor(15, 76, 129);
        $this->Rect(0, 0, 297, 18, 'F');
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 12);
        $this->SetXY(12, 5);
        $this->Cell(0, 8, $this->txt('Reporte Ejecutivo de Inventario Tecnologico'), 0, 0, 'L');
        $this->Ln(18);
        $this->SetTextColor(23, 50, 77);
    }

    public function Footer()
    {
        $this->SetY(-12);
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(100, 116, 139);
        $this->Cell(0, 8, $this->txt('SEDUC - Pagina ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    public function txt($text)
    {
        return utf8_decode((string)$text);
    }

    public function sectionTitle($title)
    {
        $this->Ln(3);
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(15, 76, 129);
        $this->Cell(0, 8, $this->txt($title), 0, 1);
        $this->SetTextColor(23, 50, 77);
    }

    public function row(array $widths, array $cells, bool $header = false, array $align = [])
    {
        $this->SetFont('Arial', $header ? 'B' : '', $header ? 8 : 7);
        if ($header) {
            $this->SetFillColor(239, 246, 255);
            $this->SetTextColor(15, 76, 129);
        } else {
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor(51, 65, 85);
        }
        foreach ($widths as $i => $w) {
            $this->Cell($w, 7, $this->txt($cells[$i] ?? ''), 1, 0, $align[$i] ?? 'L', $header);
        }
        $this->Ln();
    }
}

$pdf = new DashboardInventarioPdf('L', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->SetMargins(12, 12, 12);
$pdf->AddPage();

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(51, 65, 85);
$pdf->Cell(0, 6, $pdf->txt('Fecha de generacion: ' . date('d-m-Y H:i')), 0, 1);
$pdf->Cell(0, 6, $pdf->txt('Colegio: ' . $ctx['colegio_label']), 0, 1);
$pdf->Cell(0, 6, $pdf->txt('Periodo: ' . date('d-m-Y', strtotime($filters['desde'])) . ' al ' . date('d-m-Y', strtotime($filters['hasta']))), 0, 1);

$pdf->sectionTitle('KPIs principales');
$kpiWidths = [42, 42, 42, 42, 42, 58];
$pdf->row($kpiWidths, ['Total PC', 'Total monitores', 'Activos', 'Reparacion', 'Baja', 'Valor total'], true, ['C','C','C','C','C','C']);
$pdf->row($kpiWidths, [
    number_format($kpis['total_pc'], 0, ',', '.'),
    number_format($kpis['total_monitores'], 0, ',', '.'),
    number_format($kpis['activos'], 0, ',', '.'),
    number_format($kpis['reparacion'], 0, ',', '.'),
    number_format($kpis['baja'], 0, ',', '.'),
    di_money($kpis['valor']),
], false, ['C','C','C','C','C','R']);

$pdf->sectionTitle('Resumen por colegio');
$widths = [76, 22, 26, 24, 28, 22, 42];
$pdf->row($widths, ['Colegio', 'PC', 'Monitores', 'Activos', 'Reparacion', 'Baja', 'Valor inventario'], true, ['L','R','R','R','R','R','R']);
foreach (array_slice($data['resumen_colegio'], 0, 18) as $row) {
    $pdf->row($widths, [
        $row['nom_colegio'],
        number_format((int)$row['pc'], 0, ',', '.'),
        number_format((int)$row['monitores'], 0, ',', '.'),
        number_format((int)$row['activos'], 0, ',', '.'),
        number_format((int)$row['reparacion'], 0, ',', '.'),
        number_format((int)$row['baja'], 0, ',', '.'),
        di_money($row['valor']),
    ], false, ['L','R','R','R','R','R','R']);
}

$pdf->sectionTitle('Top 10 ubicaciones con mas activos');
$widthsUbic = [100, 100, 32];
$pdf->row($widthsUbic, ['Ubicacion', 'Colegio', 'Activos'], true, ['L','L','R']);
foreach (array_slice($data['top_ubicaciones'], 0, 10) as $row) {
    $pdf->row($widthsUbic, [$row['ubicacion'], $row['nom_colegio'], number_format((int)$row['total'], 0, ',', '.')], false, ['L','L','R']);
}

$pdf->sectionTitle('Ultimos 10 movimientos');
$widthsMov = [32, 22, 55, 48, 48, 40, 36];
$pdf->row($widthsMov, ['Fecha', 'Tipo', 'Activo', 'Origen', 'Destino', 'Usuario', 'Motivo'], true);
foreach (array_slice($data['movimientos'], 0, 10) as $row) {
    $pdf->row($widthsMov, [
        $row['fecha'] ? date('d-m-Y H:i', strtotime($row['fecha'])) : '',
        $row['tipo_activo'],
        $row['activo'],
        $row['origen'],
        $row['destino'],
        trim((string)$row['usuario']) ?: 'Sin usuario',
        $row['motivo'],
    ]);
}

$pdf->Ln(4);
$pdf->SetFont('Arial', 'I', 8);
$pdf->SetTextColor(100, 116, 139);
$pdf->MultiCell(0, 5, $pdf->txt('Este reporte resume informacion ejecutiva. No incluye todos los registros individuales para mantener un PDF breve y legible.'));

$pdf->Output('I', 'reporte_ejecutivo_inventario_' . date('Ymd_His') . '.pdf');
exit;
