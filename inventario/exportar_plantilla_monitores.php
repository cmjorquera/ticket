<?php
require_once __DIR__ . '/componentes/boot.php';

ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

require_once dirname(__DIR__) . '/excel/Classes/PHPExcel.php';

// ── Información del colegio del usuario ──────────────────────────────────────
$colegioUsuario = $inventario->obtenerColegioDelUsuario($idUsuarioSession);
$idColegio      = (int)($colegioUsuario['id_colegio']    ?? 0);
$nombreColegio  = $colegioUsuario['nombre_colegio']      ?? 'SEDUC';
$ubicaciones    = $inventario->obtenerUbicacionesPorColegio($idColegio);

// ── Estados ──────────────────────────────────────────────────────────────────
$estados = $inventario->obtenerEstados();

// ── Libro Excel ──────────────────────────────────────────────────────────────
$excel = new PHPExcel();
$excel->getProperties()
    ->setCreator('SEDUC Sistema Panel')
    ->setTitle('Plantilla Carga Masiva Monitores')
    ->setSubject('Inventario Monitores SEDUC');

// ── Hoja 1: Monitores ─────────────────────────────────────────────────────────
$sheet = $excel->getActiveSheet();
$sheet->setTitle('Monitores');

// Columnas A-R
$columnas = [
    'A' => ['id_usuario_asignado',  'ID Usuario Asignado',   22],
    'B' => ['id_ubicacion',          'ID Ubicacion *',         18],
    'C' => ['id_estado',             'ID Estado *',            14],
    'D' => ['nombre_monitor',        'Nombre Monitor *',       28],
    'E' => ['marca',                 'Marca',                  18],
    'F' => ['modelo',                'Modelo',                 22],
    'G' => ['numero_serie',          'Numero de Serie *',      24],
    'H' => ['codigo_interno',        'Codigo Interno',         18],
    'I' => ['tamano_monitor',        'Tamano Monitor',         16],
    'J' => ['resolucion_monitor',    'Resolucion Monitor',     18],
    'K' => ['tipo_panel',            'Tipo Panel',             16],
    'L' => ['tipo_conexion',         'Tipo Conexion',          16],
    'M' => ['observacion',           'Observacion',            28],
    'N' => ['valor_monitor',         'Valor Monitor',          16],
    'O' => ['proveedor',             'Proveedor',              22],
    'P' => ['numero_factura',        'Numero Factura',         18],
    'Q' => ['fecha_compra',          'Fecha Compra (YYYY-MM-DD)', 22],
    'R' => ['observacion_compra',    'Observacion Compra',    28],
];

// ── Filas 1-4: encabezado institucional ──────────────────────────────────────
// Fila 1 — Logo (imagen si existe)
$idColegioLogo = $idColegio;
$logoPath      = dirname(__DIR__) . '/img/colegios/colegio_' . $idColegioLogo . '.png';
if (is_file($logoPath)) {
    $logo = new PHPExcel_Worksheet_Drawing();
    $logo->setPath($logoPath);
    $logo->setCoordinates('A1');
    $logo->setWidth(100);
    $logo->setHeight(40);
    $logo->setOffsetX(4);
    $logo->setOffsetY(4);
    $logo->setWorksheet($sheet);
}

// Fila 2 — Nombre del colegio
$sheet->mergeCells('A2:R2');
$sheet->setCellValue('A2', $nombreColegio);
$sheet->getStyle('A2')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF0F4C81']],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
]);

// Fila 3 — Título
$sheet->mergeCells('A3:R3');
$sheet->setCellValue('A3', 'Plantilla de Carga Masiva — Inventario Monitores');
$sheet->getStyle('A3')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 11],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
]);

// Fila 4 — Fecha
$sheet->mergeCells('A4:R4');
$sheet->setCellValue('A4', 'Generado: ' . date('d/m/Y H:i'));
$sheet->getStyle('A4')->applyFromArray([
    'font'      => ['color' => ['argb' => 'FF64748B'], 'size' => 9],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
]);

// Fila 5 — separador vacío
$sheet->mergeCells('A5:R5');
$sheet->getRowDimension(5)->setRowHeight(4);

// ── Fila 6: encabezados de columna (azul) ────────────────────────────────────
$styleHeader = [
    'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
    'fill'      => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => ['argb' => 'FF0F4C81']],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, 'wrap' => true],
    'borders'   => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => 'FF1E3A5F']]],
];
$sheet->getRowDimension(6)->setRowHeight(28);
foreach ($columnas as $col => [$campo, $etiqueta, $ancho]) {
    $sheet->setCellValue($col . '6', $etiqueta);
    $sheet->getColumnDimension($col)->setWidth($ancho);
}
$sheet->getStyle('A6:R6')->applyFromArray($styleHeader);

// ── Fila 7: fila de ejemplo (gris claro) ──────────────────────────────────────
$styleEjemplo = [
    'font'      => ['italic' => true, 'color' => ['argb' => 'FF94A3B8'], 'size' => 9],
    'fill'      => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => ['argb' => 'FFF1F5F9']],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
    'borders'   => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E0']]],
];

$ejemploData = [
    'A' => ['', PHPExcel_Cell_DataType::TYPE_STRING],
    'B' => ['1', PHPExcel_Cell_DataType::TYPE_STRING],
    'C' => ['1', PHPExcel_Cell_DataType::TYPE_STRING],
    'D' => ['Monitor Samsung 24"', PHPExcel_Cell_DataType::TYPE_STRING],
    'E' => ['Samsung', PHPExcel_Cell_DataType::TYPE_STRING],
    'F' => ['LF24T350FHLXZP', PHPExcel_Cell_DataType::TYPE_STRING],
    'G' => ['HNZK321456AB', PHPExcel_Cell_DataType::TYPE_STRING],
    'H' => ['MON-001', PHPExcel_Cell_DataType::TYPE_STRING],
    'I' => ['24"', PHPExcel_Cell_DataType::TYPE_STRING],
    'J' => ['1920x1080', PHPExcel_Cell_DataType::TYPE_STRING],
    'K' => ['IPS', PHPExcel_Cell_DataType::TYPE_STRING],
    'L' => ['HDMI', PHPExcel_Cell_DataType::TYPE_STRING],
    'M' => ['Monitor aula 3', PHPExcel_Cell_DataType::TYPE_STRING],
    'N' => ['85000', PHPExcel_Cell_DataType::TYPE_STRING],
    'O' => ['TechnoStore', PHPExcel_Cell_DataType::TYPE_STRING],
    'P' => ['F-20240301', PHPExcel_Cell_DataType::TYPE_STRING],
    'Q' => ['2024-03-01', PHPExcel_Cell_DataType::TYPE_STRING],
    'R' => ['Compra directa', PHPExcel_Cell_DataType::TYPE_STRING],
];
foreach ($ejemploData as $col => [$valor, $tipo]) {
    $sheet->setCellValueExplicit($col . '7', $valor, $tipo);
}
$sheet->getStyle('A7:R7')->applyFromArray($styleEjemplo);

// Fijar encabezados
$sheet->freezePane('A8');

// ── Hoja 2: Estados ───────────────────────────────────────────────────────────
$excel->createSheet(1);
$sheetEstados = $excel->getSheet(1);
$sheetEstados->setTitle('Estados');

$sheetEstados->setCellValue('A1', 'ID Estado');
$sheetEstados->setCellValue('B1', 'Nombre');
$sheetEstados->getStyle('A1:B1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => ['argb' => 'FF0F4C81']],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
]);
$sheetEstados->getColumnDimension('A')->setWidth(12);
$sheetEstados->getColumnDimension('B')->setWidth(24);

$rowEst = 2;
foreach ($estados as $est) {
    $sheetEstados->setCellValue('A' . $rowEst, $est['id_estado'] ?? $est['id'] ?? '');
    $sheetEstados->setCellValue('B' . $rowEst, $est['nombre_estado'] ?? $est['nombre'] ?? '');
    $rowEst++;
}

// ── Hoja 3: Ubicaciones del colegio ──────────────────────────────────────────
$excel->createSheet(2);
$sheetUbic = $excel->getSheet(2);
$sheetUbic->setTitle('Ubicaciones');

$sheetUbic->setCellValue('A1', 'ID Ubicacion');
$sheetUbic->setCellValue('B1', 'Nombre Ubicacion');
$sheetUbic->getStyle('A1:B1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => ['argb' => 'FF0F4C81']],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
]);
$sheetUbic->getColumnDimension('A')->setWidth(14);
$sheetUbic->getColumnDimension('B')->setWidth(32);

$rowUbic = 2;
foreach ($ubicaciones as $ub) {
    $sheetUbic->setCellValue('A' . $rowUbic, $ub['id_ubicacion'] ?? '');
    $sheetUbic->setCellValue('B' . $rowUbic, $ub['nombre_ubicacion'] ?? '');
    $rowUbic++;
}

// ── Activar hoja 1 y exportar ─────────────────────────────────────────────────
$excel->setActiveSheetIndex(0);

$nombreArchivo = 'plantilla_monitores_' . date('Ymd') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');

$writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
$writer->save('php://output');
exit;
