<?php
require_once __DIR__ . '/componentes/boot.php';

ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

require_once dirname(__DIR__) . '/excel/Classes/PHPExcel.php';

// ── Información del colegio del usuario ──────────────────────────────────────
$colegioUsuario = $inventario->obtenerColegioDelUsuario($idUsuarioSession);
$idColegio      = (int)($colegioUsuario['id_colegio']    ?? 0);
$nombreColegio  = $colegioUsuario['nom_colegio'] ?? $colegioUsuario['nombre_colegio'] ?? 'SEDUC';
$ubicaciones    = $inventario->obtenerUbicacionesPorColegio($idColegio);
$usuarios       = $inventario->obtenerUsuarios();

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

// Columnas A-Q (sin nombre_monitor: se genera automaticamente)
$columnas = [
    'A' => ['id_usuario_asignado',  'Usuario asignado',        34],
    'B' => ['id_ubicacion',          'Ubicacion *',             28],
    'C' => ['id_estado',             'Estado *',                22],
    'D' => ['marca',                 'Marca',                   18],
    'E' => ['modelo',                'Modelo',                  22],
    'F' => ['numero_serie',          'Numero de Serie *',       24],
    'G' => ['codigo_interno',        'Codigo Interno',          18],
    'H' => ['tamano_monitor',        'Tamano Monitor',          16],
    'I' => ['resolucion_monitor',    'Resolucion Monitor',      18],
    'J' => ['tipo_panel',            'Tipo Panel',              16],
    'K' => ['tipo_conexion',         'Tipo Conexion',           16],
    'L' => ['observacion',           'Observacion',             28],
    'M' => ['valor_monitor',         'Valor Monitor',           16],
    'N' => ['proveedor',             'Proveedor',               22],
    'O' => ['numero_factura',        'Numero Factura',          18],
    'P' => ['fecha_compra',          'Fecha Compra (YYYY-MM-DD)', 22],
    'Q' => ['observacion_compra',    'Observacion Compra',      28],
];
$ultimaCol = 'Q';

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
$sheet->mergeCells('A2:' . $ultimaCol . '2');
$sheet->setCellValue('A2', $nombreColegio);
$sheet->getStyle('A2')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 13, 'color' => ['argb' => 'FF0F4C81']],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
]);

// Fila 3 — Título
$sheet->mergeCells('A3:' . $ultimaCol . '3');
$sheet->setCellValue('A3', 'Plantilla de Carga Masiva — Inventario Monitores');
$sheet->getStyle('A3')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 11],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
]);

// Fila 4 — Fecha
$sheet->mergeCells('A4:' . $ultimaCol . '4');
$sheet->setCellValue('A4', 'Generado: ' . date('d/m/Y H:i'));
$sheet->getStyle('A4')->applyFromArray([
    'font'      => ['color' => ['argb' => 'FF64748B'], 'size' => 9],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
]);

// Fila 5 — separador vacío
$sheet->mergeCells('A5:' . $ultimaCol . '5');
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
$sheet->getStyle('A6:' . $ultimaCol . '6')->applyFromArray($styleHeader);

// ── Fila 7: fila de ejemplo (gris claro) ──────────────────────────────────────
$styleEjemplo = [
    'font'      => ['italic' => true, 'color' => ['argb' => 'FF94A3B8'], 'size' => 9],
    'fill'      => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => ['argb' => 'FFF1F5F9']],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
    'borders'   => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['argb' => 'FFCBD5E0']]],
];

$ubicacionEjemplo = '';
if (!empty($ubicaciones[0]['id_ubicacion'])) {
    $ubicacionEjemplo = (int)$ubicaciones[0]['id_ubicacion'] . ' - ' . (string)($ubicaciones[0]['nombre_ubicacion'] ?? '');
}

$ejemploData = [
    'A' => ['0 - Sin asignar', PHPExcel_Cell_DataType::TYPE_STRING],
    'B' => [$ubicacionEjemplo, PHPExcel_Cell_DataType::TYPE_STRING],
    'C' => ['1 - Activo', PHPExcel_Cell_DataType::TYPE_STRING],
    'D' => ['Samsung', PHPExcel_Cell_DataType::TYPE_STRING],
    'E' => ['LF24T350FHLXZP', PHPExcel_Cell_DataType::TYPE_STRING],
    'F' => ['HNZK321456AB', PHPExcel_Cell_DataType::TYPE_STRING],
    'G' => ['MON-001', PHPExcel_Cell_DataType::TYPE_STRING],
    'H' => ['24"', PHPExcel_Cell_DataType::TYPE_STRING],
    'I' => ['1920x1080', PHPExcel_Cell_DataType::TYPE_STRING],
    'J' => ['IPS', PHPExcel_Cell_DataType::TYPE_STRING],
    'K' => ['HDMI', PHPExcel_Cell_DataType::TYPE_STRING],
    'L' => ['Monitor aula 3', PHPExcel_Cell_DataType::TYPE_STRING],
    'M' => ['85000', PHPExcel_Cell_DataType::TYPE_STRING],
    'N' => ['TechnoStore', PHPExcel_Cell_DataType::TYPE_STRING],
    'O' => ['F-20240301', PHPExcel_Cell_DataType::TYPE_STRING],
    'P' => ['2024-03-01', PHPExcel_Cell_DataType::TYPE_STRING],
    'Q' => ['Compra directa', PHPExcel_Cell_DataType::TYPE_STRING],
];
foreach ($ejemploData as $col => [$valor, $tipo]) {
    $sheet->setCellValueExplicit($col . '7', $valor, $tipo);
}
$sheet->getStyle('A7:' . $ultimaCol . '7')->applyFromArray($styleEjemplo);

// Fijar encabezados
$sheet->freezePane('A8');

// ── Hoja 2: Estados ───────────────────────────────────────────────────────────
$excel->createSheet(1);
$sheetEstados = $excel->getSheet(1);
$sheetEstados->setTitle('Estados');

$sheetEstados->setCellValue('A1', 'ID Estado');
$sheetEstados->setCellValue('B1', 'Nombre');
$sheetEstados->setCellValue('C1', 'Seleccion Excel');
$sheetEstados->getStyle('A1:C1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => ['argb' => 'FF0F4C81']],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
]);
$sheetEstados->getColumnDimension('A')->setWidth(12);
$sheetEstados->getColumnDimension('B')->setWidth(24);
$sheetEstados->getColumnDimension('C')->setWidth(34);

$rowEst = 2;
foreach ($estados as $est) {
    $idEstado = (int)($est['id_estado'] ?? $est['id'] ?? 0);
    $nombreEstado = (string)($est['nombre_estado'] ?? $est['nombre'] ?? '');
    $sheetEstados->setCellValue('A' . $rowEst, $idEstado);
    $sheetEstados->setCellValue('B' . $rowEst, $nombreEstado);
    $sheetEstados->setCellValue('C' . $rowEst, $idEstado . ' - ' . $nombreEstado);
    $rowEst++;
}

// ── Hoja 3: Ubicaciones del colegio ──────────────────────────────────────────
$excel->createSheet(2);
$sheetUbic = $excel->getSheet(2);
$sheetUbic->setTitle('Ubicaciones');

$sheetUbic->setCellValue('A1', 'ID Ubicacion');
$sheetUbic->setCellValue('B1', 'Nombre Ubicacion');
$sheetUbic->setCellValue('C1', 'Seleccion Excel');
$sheetUbic->getStyle('A1:C1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => ['argb' => 'FF0F4C81']],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
]);
$sheetUbic->getColumnDimension('A')->setWidth(14);
$sheetUbic->getColumnDimension('B')->setWidth(32);
$sheetUbic->getColumnDimension('C')->setWidth(46);

$rowUbic = 2;
foreach ($ubicaciones as $ub) {
    $idUbicacion = (int)($ub['id_ubicacion'] ?? 0);
    $nombreUbicacion = (string)($ub['nombre_ubicacion'] ?? '');
    $sheetUbic->setCellValue('A' . $rowUbic, $idUbicacion);
    $sheetUbic->setCellValue('B' . $rowUbic, $nombreUbicacion);
    $sheetUbic->setCellValue('C' . $rowUbic, $idUbicacion . ' - ' . $nombreUbicacion);
    $rowUbic++;
}

// ── Hoja 4: Usuarios ─────────────────────────────────────────────────────────
$excel->createSheet(3);
$sheetUsuarios = $excel->getSheet(3);
$sheetUsuarios->setTitle('Usuarios');

$sheetUsuarios->setCellValue('A1', 'ID Usuario');
$sheetUsuarios->setCellValue('B1', 'Nombre');
$sheetUsuarios->setCellValue('C1', 'Seleccion Excel');
$sheetUsuarios->getStyle('A1:C1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
    'fill' => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => ['argb' => 'FF0F4C81']],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER],
]);
$sheetUsuarios->getColumnDimension('A')->setWidth(14);
$sheetUsuarios->getColumnDimension('B')->setWidth(42);
$sheetUsuarios->getColumnDimension('C')->setWidth(54);

$sheetUsuarios->setCellValue('A2', 0);
$sheetUsuarios->setCellValue('B2', 'Sin asignar');
$sheetUsuarios->setCellValue('C2', '0 - Sin asignar');
$rowUsuario = 3;
foreach ($usuarios as $usuario) {
    $idUsuario = (int)($usuario['id'] ?? 0);
    $nombreUsuario = trim((string)($usuario['nombre_completo'] ?? ''));
    if ($idUsuario <= 0 || $nombreUsuario === '') {
        continue;
    }
    $sheetUsuarios->setCellValue('A' . $rowUsuario, $idUsuario);
    $sheetUsuarios->setCellValue('B' . $rowUsuario, $nombreUsuario);
    $sheetUsuarios->setCellValue('C' . $rowUsuario, $idUsuario . ' - ' . $nombreUsuario);
    $rowUsuario++;
}

// ── Desplegables ─────────────────────────────────────────────────────────────
$ultimaFilaValidacion = 1000;
$validaciones = [
    'A' => "'Usuarios'!\$C\$2:\$C\$" . max(2, $rowUsuario - 1),
    'B' => "'Ubicaciones'!\$C\$2:\$C\$" . max(2, $rowUbic - 1),
    'C' => "'Estados'!\$C\$2:\$C\$" . max(2, $rowEst - 1),
];

foreach ($validaciones as $col => $formula) {
    for ($row = 7; $row <= $ultimaFilaValidacion; $row++) {
        $validation = $sheet->getCell($col . $row)->getDataValidation();
        $validation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $validation->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_STOP);
        $validation->setAllowBlank($col === 'A');
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Valor no valido');
        $validation->setError('Seleccione un valor de la lista.');
        $validation->setPromptTitle('Seleccione de la lista');
        $validation->setPrompt('Use el desplegable para elegir un valor disponible.');
        $validation->setFormula1('=' . $formula);
    }
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
