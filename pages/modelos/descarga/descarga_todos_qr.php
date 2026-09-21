<?php
require_once("../../class/conexion.php");
require_once("../../class/mPDF/vendor/autoload.php"); // Ajusta ruta si usas composer o lo tienes en otro lado

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

$funciones = new Funciones();

try {
    // Crear hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Configurar título
    $sheet->setCellValue('A1', 'Reporte de Todos los Dispositivos');
    $sheet->mergeCells('A1:J1');
    $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

    // Cabeceras de las columnas
    $cabeceras = ['N', 'Tipo', 'Marca', 'Modelo', 'Número de Serie', 'Asignado a', 'Precio', 'Fecha de Compra', 'Observaciones', 'QR Code'];
    foreach ($cabeceras as $i => $titulo) {
        $columna = chr(65 + $i) . '3'; // 'A3', 'B3', etc.
        $sheet->setCellValue($columna, $titulo);
        $sheet->getStyle($columna)->getFont()->setBold(true);
        $sheet->getStyle($columna)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFF00');
        $sheet->getStyle($columna)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    // Obtener todos los dispositivos desde la base de datos
    $bdato = new MySQL('', '', ''); // Ajustar credenciales según corresponda
    $consulta = "
        SELECT 
            d.id_dispositivo, 
            d.tipo, 
            d.marca, 
            d.modelo, 
            d.n_serie, 
            CONCAT(u.nombre, ' ', u.apellido_paterno) AS asignado,
            d.precio, 
            d.fecha_compra, 
            d.observaciones, 
            d.qr_code
        FROM 
            otros_dispositivos AS d
        LEFT JOIN 
            usuarios AS u ON d.asignado = u.id
        ORDER BY 
            d.id_dispositivo;
    ";

    $resultado = $bdato->consulta($consulta);

    if (!$resultado) {
        die(json_encode(['error' => 'Error al consultar los dispositivos: ' . $bdato->getLastError()]));
    }

    $fila = 4; // Iniciar en la fila 4 después de las cabeceras
    $index = 1;

    while ($dispositivo = $bdato->fetch_assoc($resultado)) {
        $sheet->setCellValue("A$fila", $index++);
        $sheet->setCellValue("B$fila", $dispositivo['tipo'] ?? 'N/A');
        $sheet->setCellValue("C$fila", $dispositivo['marca'] ?? 'N/A');
        $sheet->setCellValue("D$fila", $dispositivo['modelo'] ?? 'N/A');
        $sheet->setCellValue("E$fila", $dispositivo['n_serie'] ?? 'N/A');
        $sheet->setCellValue("F$fila", $dispositivo['asignado'] ?? 'N/A');
        $sheet->setCellValue("G$fila", $dispositivo['precio'] ?? 'N/A');
        $sheet->setCellValue("H$fila", $dispositivo['fecha_compra'] ? date("d-m-Y", strtotime($dispositivo['fecha_compra'])) : 'N/A');
        $sheet->setCellValue("I$fila", $dispositivo['observaciones'] ?? 'N/A');
        $sheet->setCellValue("J$fila", $dispositivo['qr_code'] ?? 'N/A'); // Ruta del QR o valor

        // Aplicar borde a las filas
        $sheet->getStyle("A$fila:J$fila")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $fila++;
    }

    // Guardar y descargar el archivo Excel
    $writer = new Xlsx($spreadsheet);
    $fecha = date('d-m-Y');
    $filename = "reporte_todos_dispositivos_$fecha.xlsx";

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
