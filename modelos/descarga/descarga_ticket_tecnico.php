<?php
require_once '../../class/conexion.php';
require '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (!isset($_GET['id'])) {
    die("Falta el parámetro 'id'.");
}
$id_ticket = intval($_GET['id']);

$bd = new MySQL('', '', '');
// Consulta completa
$sql = "SELECT 
            t.id_ticket, 
            t.asunto, 
            t.descripcion_ticket, 
            p.fecha_creacion_inicio, 
            p.hora_creacion_inicio, 
            p.fecha_asignacion_tecnico, 
            p.hora_asignacion_tecnico, 
            p.fecha_comienzo_ticket, 
            p.hora_comienzo_ticket, 
            p.fecha_termino_ticket, 
            p.hora_termino_ticket, 
            p.fecha_cierre_ticket, 
            p.hora_cierre_ticket,
            u.nombre AS nombre_usuario, 
            u.apellido_paterno AS ape_usuario, 
            tec.nombre AS nombre_tecnico, 
            tec.apellido_paterno AS ape_tecnico, 
            c.nombre_categoria, 
            e.nombre AS nombre_estado
        FROM tickets t
        LEFT JOIN proceso_tickets p ON p.id_ticket = t.id_ticket
        LEFT JOIN usuarios u ON t.id_usuario = u.id
        LEFT JOIN usuarios tec ON t.id_tecnico = tec.id 
        LEFT JOIN categoria_de_ticket c ON t.id_categoria_ticket = c.id_categoria
        LEFT JOIN estados_ticket e ON t.id_estado = e.id
        WHERE t.id_ticket = $id_ticket";

$res = $bd->consulta($sql);
$ticket = $bd->fetch_assoc($res);

if (!$ticket) {
    die("Ticket no encontrado.");
}

// Crear Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Ticket");

$sheet->setCellValue('A1', 'Reporte de Ticket Técnico');
$sheet->mergeCells('A1:D1');

// Datos generales
$sheet->setCellValue('A3', 'ID Ticket');
$sheet->setCellValue('B3', $ticket['id_ticket']);

$sheet->setCellValue('A4', 'Asunto');
$sheet->setCellValue('B4', $ticket['asunto']);

$sheet->setCellValue('A5', 'Descripción');
$sheet->setCellValue('B5', $ticket['descripcion_ticket']);

$sheet->setCellValue('A6', 'Creado');
$sheet->setCellValue('B6', $ticket['fecha_creacion_inicio'] . ' ' . $ticket['hora_creacion_inicio']);

$sheet->setCellValue('A7', 'Asignado');
$sheet->setCellValue('B7', $ticket['fecha_asignacion_tecnico'] . ' ' . $ticket['hora_asignacion_tecnico']);

$sheet->setCellValue('A8', 'Comienzo');
$sheet->setCellValue('B8', $ticket['fecha_comienzo_ticket'] . ' ' . $ticket['hora_comienzo_ticket']);

$sheet->setCellValue('A9', 'Finalizado');
$sheet->setCellValue('B9', $ticket['fecha_termino_ticket'] . ' ' . $ticket['hora_termino_ticket']);

$sheet->setCellValue('A10', 'Cerrado');
$sheet->setCellValue('B10', $ticket['fecha_cierre_ticket'] . ' ' . $ticket['hora_cierre_ticket']);

$sheet->setCellValue('A11', 'Usuario');
$sheet->setCellValue('B11', $ticket['nombre_usuario'] . ' ' . $ticket['ape_usuario']);

$sheet->setCellValue('A12', 'Técnico');
$sheet->setCellValue('B12', $ticket['nombre_tecnico'] . ' ' . $ticket['ape_tecnico']);

$sheet->setCellValue('A13', 'Categoría');
$sheet->setCellValue('B13', $ticket['nombre_categoria']);

$sheet->setCellValue('A14', 'Estado');
$sheet->setCellValue('B14', $ticket['nombre_estado']);

// Avances técnicos
$sheet->setCellValue('A16', 'Avances del Técnico:');
$sql2 = "SELECT fecha_avance, hora_avance, accion 
         FROM avance_tecnicos  
         WHERE id_ticket = $id_ticket 
         ORDER BY fecha_avance ASC, hora_avance ASC";
$res2 = $bd->consulta($sql2);

$fila = 17;
while ($avance = $bd->fetch_assoc($res2)) {
    $sheet->setCellValue("A$fila", $avance['fecha_avance'] . ' ' . $avance['hora_avance']);
    $sheet->setCellValue("B$fila", $avance['accion']);
    $fila++;
}

// Descargar
$filename = "ticket_" . $ticket['id_ticket'] . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
