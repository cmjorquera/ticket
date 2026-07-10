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

// ── Mapeo campo interno => etiqueta legible (33 columnas, sin colegio, fotos ni QR manual) ────
$columnas = [
    'A'  => ['campo' => 'id_ubicacion',            'etiqueta' => 'Ubicación'],
    'B'  => ['campo' => 'id_usuario_asignado',     'etiqueta' => 'Usuario asignado'],
    'C'  => ['campo' => 'nombre_personalizado',    'etiqueta' => 'Nombre del equipo'],
    'D'  => ['campo' => 'numero_serie',            'etiqueta' => 'Número de serie'],
    'E'  => ['campo' => 'tipo_pc',                 'etiqueta' => 'Tipo de PC'],
    'F'  => ['campo' => 'id_estado',               'etiqueta' => 'Estado'],
    'G'  => ['campo' => 'fabricante',              'etiqueta' => 'Fabricante'],
    'H'  => ['campo' => 'producto',                'etiqueta' => 'Producto / modelo'],
    'I'  => ['campo' => 'valor_equipo',            'etiqueta' => 'Valor equipo'],
    'J'  => ['campo' => 'proveedor',               'etiqueta' => 'Proveedor'],
    'K'  => ['campo' => 'numero_factura',          'etiqueta' => 'Número factura'],
    'L'  => ['campo' => 'fecha_compra',            'etiqueta' => 'Fecha compra'],
    'M'  => ['campo' => 'observacion_compra',      'etiqueta' => 'Observación compra'],
    'N'  => ['campo' => 'almacenamiento_modelo',   'etiqueta' => 'Almacenamiento modelo'],
    'O'  => ['campo' => 'almacenamiento_capacidad','etiqueta' => 'Almacenamiento capacidad'],
    'P'  => ['campo' => 'almacenamiento_tamano',   'etiqueta' => 'Almacenamiento tipo/tamaño'],
    'Q'  => ['campo' => 'procesador_fabricante',   'etiqueta' => 'Procesador fabricante'],
    'R'  => ['campo' => 'procesador_modelo',       'etiqueta' => 'Procesador modelo'],
    'S'  => ['campo' => 'procesador_velocidad',    'etiqueta' => 'Procesador velocidad'],
    'T'  => ['campo' => 'windows',                 'etiqueta' => 'Windows'],
    'U'  => ['campo' => 'office',                  'etiqueta' => 'Office'],
    'V'  => ['campo' => 'antivirus',               'etiqueta' => 'Antivirus'],
    'W'  => ['campo' => 'memoria_designacion',     'etiqueta' => 'Memoria slot/designacion'],
    'X'  => ['campo' => 'memoria_formato',         'etiqueta' => 'Memoria formato'],
    'Y'  => ['campo' => 'memoria_tipo',            'etiqueta' => 'Memoria tipo'],
    'Z'  => ['campo' => 'memoria_tamano',          'etiqueta' => 'Memoria tamaño'],
    'AA' => ['campo' => 'memoria_frecuencia',      'etiqueta' => 'Memoria frecuencia'],
    'AB' => ['campo' => 'memoria_marca',           'etiqueta' => 'Memoria marca'],
    'AC' => ['campo' => 'monitor_modelo',          'etiqueta' => 'Monitor modelo'],
    'AD' => ['campo' => 'monitor_codigo',          'etiqueta' => 'Monitor código'],
    'AE' => ['campo' => 'monitor_serie',           'etiqueta' => 'Monitor serie'],
    'AF' => ['campo' => 'monitor_tamano',          'etiqueta' => 'Monitor tamaño'],
    'AG' => ['campo' => 'monitor_resolucion',      'etiqueta' => 'Monitor resolución'],
];

$ultimaCol   = 'AG';     // última columna de datos
$rangoData   = 'A:' . $ultimaCol;

// ── Crear workbook ────────────────────────────────────────────────────────────
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()
    ->setCreator('SEDUC TICKET')
    ->setTitle('Plantilla carga masiva inventario')
    ->setSubject('Inventario equipos')
    ->setDescription('Plantilla para importacion masiva de equipos computacionales.');

// ── Hoja 1: Inventario ────────────────────────────────────────────────────────
$sheet = $objPHPExcel->getActiveSheet();
$sheet->setTitle('Inventario');

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
$ubicacionesPlantilla = $inventario->obtenerUbicacionesPorColegio($idColegioLogo);
$ubicacionEjemplo = '';
if (!empty($ubicacionesPlantilla[0])) {
    $ubEjemplo = $ubicacionesPlantilla[0];
    $tipoUbEjemplo = trim((string)($ubEjemplo['tipo_ubicacion'] ?? ''));
    $nombreUbEjemplo = trim((string)($ubEjemplo['nombre_ubicacion'] ?? ''));
    $labelUbEjemplo = $tipoUbEjemplo !== '' ? $tipoUbEjemplo . ' - ' . $nombreUbEjemplo : $nombreUbEjemplo;
    $ubicacionEjemplo = (int)$ubEjemplo['id_ubicacion'] . ' - ' . $labelUbEjemplo;
}

$ejemplo = [
    'A'  => $ubicacionEjemplo,
    'B'  => '0 - Sin asignar',
    'C'  => 'PC Direccion',
    'D'  => 'SN-12345ABC',
    'E'  => 'Desktop',
    'F'  => '1 - Activo',
    'G'  => 'Dell',
    'H'  => 'OptiPlex 3080',
    'I'  => '350000',
    'J'  => 'TechShop Ltda',
    'K'  => 'FAC-2024-001',
    'L'  => '2024-01-15',
    'M'  => 'Adquisicion primer semestre',
    'N'  => 'Samsung 860 EVO',
    'O'  => '256GB',
    'P'  => '2.5"',
    'Q'  => 'Intel',
    'R'  => 'Core i5-10500',
    'S'  => '3.1 GHz',
    'T'  => 'Windows 10 Pro',
    'U'  => 'Office 2021',
    'V'  => 'Windows Defender',
    'W'  => 'DIMM1',
    'X'  => 'DIMM',
    'Y'  => 'DDR4',
    'Z'  => '8GB',
    'AA' => '2666 MHz',
    'AB' => 'Kingston',
    'AC' => 'Dell P2219H',
    'AD' => 'MON-001',
    'AE' => 'SN-MON-001',
    'AF' => '22"',
    'AG' => '1920x1080',
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
$sheet->getColumnDimension('A')->setWidth(34);
$sheet->getColumnDimension('B')->setWidth(36);
$sheet->getColumnDimension('C')->setWidth(42);
$sheet->getColumnDimension('D')->setWidth(28);
$sheet->getColumnDimension('E')->setWidth(18);
$sheet->getColumnDimension('F')->setWidth(22);

// Anclar filas 1-6 (encabezado institucional + nombres de columna)
$sheet->freezePane('A7');

// ── Hoja oculta de catalogos (origen de desplegables) ────────────────────────
$sheetCatalogos = $objPHPExcel->createSheet();
$sheetCatalogos->setTitle('_catalogos');
$sheetCatalogos->setSheetState(PHPExcel_Worksheet::SHEETSTATE_HIDDEN);

$sheetCatalogos->setCellValue('A1', 'id_ubicacion');
$sheetCatalogos->setCellValue('B1', 'nombre_ubicacion');
$sheetCatalogos->setCellValue('C1', 'seleccion_ubicacion');
$sheetCatalogos->setCellValue('E1', 'id_usuario');
$sheetCatalogos->setCellValue('F1', 'nombre_usuario');
$sheetCatalogos->setCellValue('G1', 'seleccion_usuario');
$sheetCatalogos->setCellValue('I1', 'tipo_pc');
$sheetCatalogos->setCellValue('K1', 'id_estado');
$sheetCatalogos->setCellValue('L1', 'nombre_estado');
$sheetCatalogos->setCellValue('M1', 'seleccion_estado');
$sheetCatalogos->getStyle('A1:M1')->applyFromArray($estiloEncabezado);

$filaUbicacion = 2;
foreach ($ubicacionesPlantilla as $ubicacion) {
    $idUbicacion = (int)$ubicacion['id_ubicacion'];
    $nombreUbicacion = trim((string)($ubicacion['nombre_ubicacion'] ?? ''));
    $tipoUbicacion = trim((string)($ubicacion['tipo_ubicacion'] ?? ''));
    $labelUbicacion = $tipoUbicacion !== '' ? $tipoUbicacion . ' - ' . $nombreUbicacion : $nombreUbicacion;
    $sheetCatalogos->setCellValue('A' . $filaUbicacion, $idUbicacion);
    $sheetCatalogos->setCellValue('B' . $filaUbicacion, $labelUbicacion);
    $sheetCatalogos->setCellValue('C' . $filaUbicacion, $idUbicacion . ' - ' . $labelUbicacion);
    $filaUbicacion++;
}

$sheetCatalogos->setCellValue('E2', 0);
$sheetCatalogos->setCellValue('F2', 'Sin asignar');
$sheetCatalogos->setCellValue('G2', '0 - Sin asignar');
$usuarios = $inventario->obtenerUsuariosAsignablesPorColegio($idColegioLogo);
$filaUsuario = 3;
foreach ($usuarios as $usuario) {
    $idUsuario = (int)$usuario['id'];
    $nombreUsuario = trim((string)$usuario['nombre_completo']);
    $sheetCatalogos->setCellValue('E' . $filaUsuario, $idUsuario);
    $sheetCatalogos->setCellValue('F' . $filaUsuario, $nombreUsuario);
    $sheetCatalogos->setCellValue('G' . $filaUsuario, $idUsuario . ' - ' . $nombreUsuario);
    $filaUsuario++;
}

$tiposPc = $inventario->obtenerTiposPc();
$filaTipo = 2;
foreach ($tiposPc as $tipoPc) {
    $sheetCatalogos->setCellValue('I' . $filaTipo, $tipoPc);
    $filaTipo++;
}

$estados = $inventario->obtenerEstados();
$filaEstado = 2;
foreach ($estados as $estado) {
    $sheetCatalogos->setCellValue('K' . $filaEstado, (int)$estado['id_estado']);
    $sheetCatalogos->setCellValue('L' . $filaEstado, $estado['nombre_estado']);
    $sheetCatalogos->setCellValue('M' . $filaEstado, (int)$estado['id_estado'] . ' - ' . $estado['nombre_estado']);
    $filaEstado++;
}

// ── Desplegables en hoja Inventario ──────────────────────────────────────────
$ultimaFilaValidacion = 1000;
$ultimaFilaUsuarios = max(2, $filaUsuario - 1);
$ultimaFilaTipos = max(2, $filaTipo - 1);
$ultimaFilaEstados = max(2, $filaEstado - 1);
$ultimaFilaUbicaciones = max(2, $filaUbicacion - 1);

$validaciones = [
    'A' => "'_catalogos'!\$C\$2:\$C\$" . $ultimaFilaUbicaciones,
    'B' => "'_catalogos'!\$G\$2:\$G\$" . $ultimaFilaUsuarios,
    'E' => "'_catalogos'!\$I\$2:\$I\$" . $ultimaFilaTipos,
    'F' => "'_catalogos'!\$M\$2:\$M\$" . $ultimaFilaEstados,
];

foreach ($validaciones as $col => $formula) {
    for ($row = 7; $row <= $ultimaFilaValidacion; $row++) {
        $validation = $sheet->getCell($col . $row)->getDataValidation();
        $validation->setType(PHPExcel_Cell_DataValidation::TYPE_LIST);
        $validation->setErrorStyle($col === 'A' ? PHPExcel_Cell_DataValidation::STYLE_INFORMATION : PHPExcel_Cell_DataValidation::STYLE_STOP);
        $validation->setAllowBlank($col === 'B');
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage($col !== 'A');
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Valor no valido');
        $validation->setError('Seleccione un valor de la lista.');
        $validation->setPromptTitle($col === 'A' ? 'Seleccione o escriba' : 'Seleccione de la lista');
        $validation->setPrompt($col === 'A' ? 'Use el desplegable o escriba una nueva ubicacion. Se creara para este colegio al insertar.' : 'Use el desplegable para elegir un valor disponible.');
        $validation->setFormula1('=' . $formula);
    }
}

// Activar hoja Inventario
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
