<?php
require_once __DIR__ . '/componentes/boot.php';

// PHPExcel genera avisos de deprecacion en PHP 8.x — suprimirlos para no corromper la descarga binaria
ini_set('display_errors', 0);
error_reporting(E_ERROR | E_PARSE);

if ($idUsuarioSession <= 0) {
    http_response_code(401);
    exit('No autorizado.');
}

require_once dirname(__DIR__) . '/excel/Classes/PHPExcel.php';

// ── Datos del colegio del usuario de sesión ───────────────────────────────────
// El id_colegio no se guarda en $_SESSION; se obtiene de la tabla usuario_colegio.
$colegioSession = $inventario->obtenerColegioDelUsuario($idUsuarioSession);
$idColegioLogo  = (int)($colegioSession['id_colegio'] ?? 0);
$nomColegio     = $colegioSession['nom_colegio'] ?? 'Colegio no asignado';

// Logo: /img/colegios/colegio_{id}.png (relativo a la raíz del proyecto)
$rutaLogo = dirname(__DIR__) . '/img/colegios/colegio_' . $idColegioLogo . '.png';
$tienelogo = ($idColegioLogo > 0 && is_file($rutaLogo));

// ── Mapeo campo interno => etiqueta legible (31 columnas, sin id_colegio/nombre/QR) ────
$columnas = [
    'A'  => ['campo' => 'id_usuario_asignado',    'etiqueta' => 'Usuario asignado'],
    'B'  => ['campo' => 'fabricante',              'etiqueta' => 'Fabricante'],
    'C'  => ['campo' => 'producto',                'etiqueta' => 'Producto / modelo'],
    'D'  => ['campo' => 'numero_serie',            'etiqueta' => 'Numero de serie'],
    'E'  => ['campo' => 'tipo_pc',                 'etiqueta' => 'Tipo de PC'],
    'F'  => ['campo' => 'id_estado',               'etiqueta' => 'Estado'],
    'G'  => ['campo' => 'valor_equipo',            'etiqueta' => 'Valor equipo'],
    'H'  => ['campo' => 'proveedor',               'etiqueta' => 'Proveedor'],
    'I'  => ['campo' => 'numero_factura',          'etiqueta' => 'Numero factura'],
    'J'  => ['campo' => 'fecha_compra',            'etiqueta' => 'Fecha compra'],
    'K'  => ['campo' => 'observacion_compra',      'etiqueta' => 'Observacion compra'],
    'L'  => ['campo' => 'almacenamiento_modelo',   'etiqueta' => 'Almacenamiento modelo'],
    'M'  => ['campo' => 'almacenamiento_capacidad','etiqueta' => 'Almacenamiento capacidad'],
    'N'  => ['campo' => 'almacenamiento_tamano',   'etiqueta' => 'Almacenamiento tipo/tamano'],
    'O'  => ['campo' => 'procesador_fabricante',   'etiqueta' => 'Procesador fabricante'],
    'P'  => ['campo' => 'procesador_modelo',       'etiqueta' => 'Procesador modelo'],
    'Q'  => ['campo' => 'procesador_velocidad',    'etiqueta' => 'Procesador velocidad'],
    'R'  => ['campo' => 'windows',                 'etiqueta' => 'Windows'],
    'S'  => ['campo' => 'office',                  'etiqueta' => 'Office'],
    'T'  => ['campo' => 'antivirus',               'etiqueta' => 'Antivirus'],
    'U'  => ['campo' => 'memoria_designacion',     'etiqueta' => 'Memoria slot/designacion'],
    'V'  => ['campo' => 'memoria_formato',         'etiqueta' => 'Memoria formato'],
    'W'  => ['campo' => 'memoria_tipo',            'etiqueta' => 'Memoria tipo'],
    'X'  => ['campo' => 'memoria_tamano',          'etiqueta' => 'Memoria tamano'],
    'Y'  => ['campo' => 'memoria_frecuencia',      'etiqueta' => 'Memoria frecuencia'],
    'Z'  => ['campo' => 'memoria_marca',           'etiqueta' => 'Memoria marca'],
    'AA' => ['campo' => 'monitor_modelo',          'etiqueta' => 'Monitor modelo'],
    'AB' => ['campo' => 'monitor_codigo',          'etiqueta' => 'Monitor codigo'],
    'AC' => ['campo' => 'monitor_serie',           'etiqueta' => 'Monitor serie'],
    'AD' => ['campo' => 'monitor_tamano',          'etiqueta' => 'Monitor tamano'],
    'AE' => ['campo' => 'monitor_resolucion',      'etiqueta' => 'Monitor resolucion'],
];

$ultimaCol   = 'AE';     // última columna de datos
$rangoData   = 'A:' . $ultimaCol;

// ── Crear workbook ────────────────────────────────────────────────────────────
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()
    ->setCreator('SEDUC TICKET')
    ->setTitle('Plantilla carga masiva inventario')
    ->setSubject('Inventario equipos')
    ->setDescription('Plantilla para importacion masiva de equipos computacionales.');

// ── Hoja 1: Equipos ───────────────────────────────────────────────────────────
$sheet = $objPHPExcel->getActiveSheet();
$sheet->setTitle('Equipos');

// ---- Filas 1–5: encabezado institucional ------------------------------------

// Fila 1 col B: Colegio
$sheet->setCellValue('B1', 'Colegio:');
$sheet->setCellValue('C1', $nomColegio);
$sheet->mergeCells('C1:' . $ultimaCol . '1');
$sheet->getStyle('B1')->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('0F4C81');
$sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('0F4C81');
$sheet->getStyle('C1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

// Fila 2 vacía (espacio para imagen)
$sheet->getRowDimension(1)->setRowHeight(45);
$sheet->getRowDimension(2)->setRowHeight(40);

// Fila 3: título
$sheet->setCellValue('B3', 'Plantilla de carga masiva de inventario de equipos');
$sheet->mergeCells('B3:' . $ultimaCol . '3');
$sheet->getStyle('B3')->getFont()->setSize(11)->setBold(false)->getColor()->setRGB('334155');
$sheet->getRowDimension(3)->setRowHeight(20);

// Fila 4: fecha generación
$sheet->setCellValue('B4', 'Generado el: ' . date('d-m-Y'));
$sheet->mergeCells('B4:' . $ultimaCol . '4');
$sheet->getStyle('B4')->getFont()->setSize(9)->getColor()->setRGB('94A3B8');
$sheet->getRowDimension(4)->setRowHeight(16);

// Fila 5: separador vacío
$sheet->getRowDimension(5)->setRowHeight(10);

// Fondo degradado aproximado en cabecera (filas 1-4) — fondo azul claro
$estiloHeader = [
    'fill' => [
        'type'       => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => ['rgb' => 'EFF6FF'],
    ],
    'borders' => [
        'bottom' => ['style' => PHPExcel_Style_Border::BORDER_NONE],
    ],
];
$sheet->getStyle('A1:' . $ultimaCol . '4')->applyFromArray($estiloHeader);

// Línea divisoria al final del encabezado (fila 4 bottom)
$sheet->getStyle('A4:' . $ultimaCol . '4')->getBorders()->getBottom()
    ->setBorderStyle(PHPExcel_Style_Border::BORDER_MEDIUM)
    ->getColor()->setRGB('0F4C81');

// ---- Logo (imagen flotante sobre A1) ----------------------------------------
if ($tienelogo) {
    $drawing = new PHPExcel_Worksheet_Drawing();
    $drawing->setName('Logo colegio');
    $drawing->setDescription($nomColegio);
    $drawing->setPath($rutaLogo);
    $drawing->setCoordinates('A1');
    $drawing->setOffsetX(8);
    $drawing->setOffsetY(6);
    $drawing->setWidth(110);
    $drawing->setHeight(72);
    $drawing->setWorksheet($sheet);
}

// ---- Fila 6: encabezados de columna -----------------------------------------
$estiloEncabezado = [
    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
    'fill'      => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => ['rgb' => '0F4C81']],
    'alignment' => ['horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, 'wrapText' => true, 'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER],
    'borders'   => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => '1A5C96']]],
];

foreach ($columnas as $col => $info) {
    $sheet->setCellValue($col . '6', $info['etiqueta']);
}
$sheet->getStyle('A6:' . $ultimaCol . '6')->applyFromArray($estiloEncabezado);
$sheet->getRowDimension(6)->setRowHeight(34);

// ---- Fila 7: ejemplo en gris (el usuario la elimina antes de subir) ----------
$ejemplo = [
    'A'  => '0 - Sin asignar',
    'B'  => 'Dell',
    'C'  => 'OptiPlex 3080',
    'D'  => 'SN-12345ABC',
    'E'  => 'Desktop',
    'F'  => '1 - Activo',
    'G'  => '350000',
    'H'  => 'TechShop Ltda',
    'I'  => 'FAC-2024-001',
    'J'  => '2024-01-15',
    'K'  => 'Adquisicion primer semestre',
    'L'  => 'Samsung 860 EVO',
    'M'  => '256GB',
    'N'  => '2.5"',
    'O'  => 'Intel',
    'P'  => 'Core i5-10500',
    'Q'  => '3.1 GHz',
    'R'  => 'Windows 10 Pro',
    'S'  => 'Office 2021',
    'T'  => 'Windows Defender',
    'U'  => 'DIMM1',
    'V'  => 'DIMM',
    'W'  => 'DDR4',
    'X'  => '8GB',
    'Y'  => '2666 MHz',
    'Z'  => 'Kingston',
    'AA' => 'Dell P2219H',
    'AB' => 'MON-001',
    'AC' => 'SN-MON-001',
    'AD' => '22"',
    'AE' => '1920x1080',
];

foreach ($ejemplo as $col => $val) {
    $sheet->setCellValueExplicit($col . '7', $val, PHPExcel_Cell_DataType::TYPE_STRING);
}

$estiloEjemplo = [
    'fill'    => ['type' => PHPExcel_Style_Fill::FILL_SOLID, 'startcolor' => ['rgb' => 'F1F5F9']],
    'font'    => ['color' => ['rgb' => '94A3B8'], 'italic' => true, 'size' => 9],
    'borders' => ['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
];
$sheet->getStyle('A7:' . $ultimaCol . '7')->applyFromArray($estiloEjemplo);
$sheet->getRowDimension(7)->setRowHeight(18);

// Ajuste automático de ancho
foreach (array_keys($columnas) as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
// Columnas con listas largas
$sheet->getColumnDimension('A')->setWidth(42);
$sheet->getColumnDimension('E')->setWidth(18);
$sheet->getColumnDimension('F')->setWidth(22);

// Anclar filas 1-6 (encabezado institucional + nombres de columna)
$sheet->freezePane('A7');

// ── Hoja 2: Estados (referencia) ──────────────────────────────────────────────
$sheetEstados = $objPHPExcel->createSheet();
$sheetEstados->setTitle('Estados');

$sheetEstados->setCellValue('A1', 'id_estado');
$sheetEstados->setCellValue('B1', 'nombre_estado');
$sheetEstados->getStyle('A1:B1')->applyFromArray($estiloEncabezado);
$sheetEstados->getRowDimension(1)->setRowHeight(28);

$estados = $inventario->obtenerEstados();
$fila = 2;
foreach ($estados as $estado) {
    $sheetEstados->setCellValue('A' . $fila, (int)$estado['id_estado']);
    $sheetEstados->setCellValue('B' . $fila, $estado['nombre_estado']);
    $sheetEstados->setCellValue('C' . $fila, (int)$estado['id_estado'] . ' - ' . $estado['nombre_estado']);
    $fila++;
}
$sheetEstados->getColumnDimension('A')->setAutoSize(true);
$sheetEstados->getColumnDimension('B')->setWidth(25);
$sheetEstados->getColumnDimension('C')->setWidth(35);

// ── Hoja 3: Tipos PC (origen del desplegable de columna E) ───────────────────
$sheetTipos = $objPHPExcel->createSheet();
$sheetTipos->setTitle('Tipos PC');
$sheetTipos->setCellValue('A1', 'tipo_pc');
$sheetTipos->getStyle('A1')->applyFromArray($estiloEncabezado);
$tiposPc = $inventario->obtenerTiposPc();
$filaTipo = 2;
foreach ($tiposPc as $tipoPc) {
    $sheetTipos->setCellValue('A' . $filaTipo, $tipoPc);
    $filaTipo++;
}
$sheetTipos->getColumnDimension('A')->setWidth(25);

// ── Hoja 4: Usuarios (origen del desplegable de columna A) ───────────────────
$sheetUsuarios = $objPHPExcel->createSheet();
$sheetUsuarios->setTitle('Usuarios');
$sheetUsuarios->setCellValue('A1', 'id_usuario');
$sheetUsuarios->setCellValue('B1', 'nombre_usuario');
$sheetUsuarios->setCellValue('C1', 'seleccion_excel');
$sheetUsuarios->getStyle('A1:C1')->applyFromArray($estiloEncabezado);
$sheetUsuarios->setCellValue('A2', 0);
$sheetUsuarios->setCellValue('B2', 'Sin asignar');
$sheetUsuarios->setCellValue('C2', '0 - Sin asignar');
$usuarios = $inventario->obtenerUsuarios();
$filaUsuario = 3;
foreach ($usuarios as $usuario) {
    $idUsuario = (int)$usuario['id'];
    $nombreUsuario = trim((string)$usuario['nombre_completo']);
    $sheetUsuarios->setCellValue('A' . $filaUsuario, $idUsuario);
    $sheetUsuarios->setCellValue('B' . $filaUsuario, $nombreUsuario);
    $sheetUsuarios->setCellValue('C' . $filaUsuario, $idUsuario . ' - ' . $nombreUsuario);
    $filaUsuario++;
}
$sheetUsuarios->getColumnDimension('A')->setWidth(14);
$sheetUsuarios->getColumnDimension('B')->setWidth(42);
$sheetUsuarios->getColumnDimension('C')->setWidth(52);

// ── Desplegables en hoja Equipos ─────────────────────────────────────────────
$ultimaFilaValidacion = 1000;
$ultimaFilaUsuarios = max(2, $filaUsuario - 1);
$ultimaFilaTipos = max(2, $filaTipo - 1);
$ultimaFilaEstados = max(2, $fila - 1);

$validaciones = [
    'A' => "'Usuarios'!\$C\$2:\$C\$" . $ultimaFilaUsuarios,
    'E' => "'Tipos PC'!\$A\$2:\$A\$" . $ultimaFilaTipos,
    'F' => "'Estados'!\$C\$2:\$C\$" . $ultimaFilaEstados,
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

// Activar hoja Equipos
$objPHPExcel->setActiveSheetIndex(0);

// ── Enviar al navegador ───────────────────────────────────────────────────────
$nombreArchivo = 'plantilla_inventario_' . preg_replace('/[^A-Za-z0-9_\-]/', '_', $nomColegio) . '_' . date('Ymd') . '.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
header('Cache-Control: max-age=0');
header('Expires: 0');
header('Pragma: public');

$writer = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$writer->save('php://output');
exit;
