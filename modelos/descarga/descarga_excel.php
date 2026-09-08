<?php
require_once '../../class/conexion.php'; // Conexión a la base de datos
require '../../vendor/autoload.php'; // Cargar las dependencias de Composer
require_once '../../class/funciones.php'; // Incluir las funciones donde está obtenerEquipoPorId

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

$funciones              = new Funciones(); // Instancia de la clase Funciones
$equiposSeleccionados   = $_POST['equiposSeleccionados'] ?? []; // Recibir los IDs seleccionados

// Crear el objeto Spreadsheet
$spreadsheet        = new Spreadsheet();
$sheet              = $spreadsheet->getActiveSheet();

// Título del documento
$sheet->setCellValue('A1', 'Reporte de Equipos Seleccionados');
$sheet->mergeCells('A1:K1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

// Agregar logo
$drawing = new Drawing();
$drawing->setName('Logo');
// $drawing->setPath('../../img/logo_seduc.png'); // Ajusta la ruta si tienes un logo
$drawing->setCoordinates('J2');
$drawing->setWorksheet($sheet);

// Cabeceras de las columnas
$cabeceras = [
    'N', 'Nombre del Equipo', 'Modelo del Procesador', 'Tamaño de Memoria', 
    'Tipo de Almacenamiento', 'Capacidad de Almacenamiento', 
    'Sistema', 'Producto', 'Fecha de Registro', 
    'Usuario', 'Área de Trabajo'
];

// Aplicar estilo de cabecera
$colIndex = 'A';
foreach ($cabeceras as $cabecera) {
    $sheet->setCellValue($colIndex . '3', $cabecera);
    $sheet->getStyle($colIndex . '3')->getFont()->setBold(true);
    $sheet->getStyle($colIndex . '3')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
    $sheet->getStyle($colIndex . '3')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $colIndex++;
}

// Verificar si se seleccionaron equipos
if (!empty($equiposSeleccionados)) {
    $fila = 4; // Fila de inicio después de la cabecera

    foreach ($equiposSeleccionados as $index => $id_equipo) {
        $equipo = $funciones->obtenerEquipoPorId($id_equipo);
        
        if ($equipo) {
            $sheet->setCellValue('A' . $fila, $index + 1);
            $sheet->setCellValue('B' . $fila, $equipo['nombre_equipo'] ?? 'N/A');
            $sheet->setCellValue('C' . $fila, $equipo['modelo_procesador'] ?? 'N/A');
            $sheet->setCellValue('D' . $fila, ($equipo['tamano_memoria'] ?? 'N/A') . ' GB');
            $sheet->setCellValue('E' . $fila, $equipo['fabricante'] ?? 'N/A');
            $sheet->setCellValue('F' . $fila, ($equipo['capacidad_almacenamiento'] ?? 'N/A') . ' GB');
            $sheet->setCellValue('G' . $fila, $equipo['windows'] ?? 'N/A');
            $sheet->setCellValue('H' . $fila, $equipo['producto'] ?? 'N/A');
            $sheet->setCellValue('I' . $fila, date("d-m-Y", strtotime($equipo['fecha_creacion'])));
            $sheet->setCellValue('J' . $fila, $equipo['nombre_completo_usuario'] ?? 'Sin usuario asociado');
            $sheet->setCellValue('K' . $fila, $equipo['nombre_area'] ?? 'Sin área');
            
            // Aplicar borde a las celdas
            $sheet->getStyle('A' . $fila . ':K' . $fila)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $fila++;
        }
    }
} else {
    $sheet->setCellValue('A4', 'No se seleccionaron equipos para el reporte.');
}

// Ajustar el ancho de las columnas
foreach (range('A', 'K') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Guardar el archivo como Excel
$writer = new Xlsx($spreadsheet);
$fecha = date('d-m-Y');
$filename = "reporte_equipos_seleccionados_$fecha.xlsx";

// Enviar el archivo al navegador para su descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');
$writer->save('php://output');
exit;
