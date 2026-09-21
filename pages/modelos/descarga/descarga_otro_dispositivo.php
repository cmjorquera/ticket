<?php
require_once '../../class/conexion.php';
require '../../vendor/autoload.php';
require_once '../../class/funciones.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

$funciones = new Funciones();
$dispositivosSeleccionados = $_POST['dispositivosSeleccionados'] ?? [];

// Verificar si hay dispositivos seleccionados
if (empty($dispositivosSeleccionados)) {
    die(json_encode(['error' => 'No se recibieron dispositivos seleccionados.']));
}

try {
    // Crear hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Configurar título
    $sheet->setCellValue('A1', 'Reporte de Dispositivos Seleccionados');
    $sheet->mergeCells('A1:I1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

    // Cabeceras de las columnas
    $cabeceras = ['N', 'Tipo', 'Marca', 'Modelo', 'Número de Serie', 'Asignado a', 'Precio', 'Fecha de Compra', 'Observaciones'];
    foreach ($cabeceras as $i => $titulo) {
        $columna = chr(65 + $i) . '3'; // 'A3', 'B3', etc.
        $sheet->setCellValue($columna, $titulo);
        $sheet->getStyle($columna)->getFont()->setBold(true);
        $sheet->getStyle($columna)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
        $sheet->getStyle($columna)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    // Rellenar datos
    $fila = 4;
    foreach ($dispositivosSeleccionados as $index => $id_dispositivo) {
        $dispositivo = $funciones->obtenerDispositivoPorId($id_dispositivo);

        if ($dispositivo) {
            $sheet->setCellValue("A$fila", $index + 1);
            $sheet->setCellValue("B$fila", $dispositivo['tipo'] ?? 'N/A');
            $sheet->setCellValue("C$fila", $dispositivo['marca'] ?? 'N/A');
            $sheet->setCellValue("D$fila", $dispositivo['modelo'] ?? 'N/A');
            $sheet->setCellValue("E$fila", $dispositivo['n_serie'] ?? 'N/A');
            $sheet->setCellValue("F$fila", $dispositivo['asignado'] ?? 'N/A');
            $sheet->setCellValue("G$fila", $dispositivo['precio'] ?? 'N/A');
            $sheet->setCellValue("H$fila", $dispositivo['fecha_compra'] ? date("d-m-Y", strtotime($dispositivo['fecha_compra'])) : 'N/A');
            $sheet->setCellValue("I$fila", $dispositivo['observaciones'] ?? 'N/A');
            $sheet->getStyle("A$fila:I$fila")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $fila++;
        }
    }

    // Guardar y descargar el archivo Excel
    $writer = new Xlsx($spreadsheet);
    $fecha = date('d-m-Y');
    $filename = "reporte_dispositivos_seleccionados_$fecha.xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    error_log($e->getMessage());
    die(json_encode(['error' => 'Hubo un problema al generar el reporte.']));
}
?>
