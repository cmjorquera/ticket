<?php
require_once '../../class/conexion.php';
require '../../vendor/autoload.php';

use Mpdf\Mpdf;

// Validación de ID
if (!isset($_GET['id'])) {
    die("Falta el parámetro 'id'.");
}
$id_ticket = intval($_GET['id']);

// Conexión
$bd = new MySQL('', '', '');

// Obtener datos del ticket y proceso
$sql = "SELECT 
            t.id_ticket, t.asunto, t.descripcion_ticket,
            p.fecha_creacion_inicio, p.hora_creacion_inicio,
            p.fecha_asignacion_tecnico, p.hora_asignacion_tecnico,
            p.fecha_comienzo_ticket, p.hora_comienzo_ticket,
            p.fecha_termino_ticket, p.hora_termino_ticket,
            p.fecha_cierre_ticket, p.hora_cierre_ticket,
            u.nombre AS nombre_usuario, u.apellido_paterno AS ape_usuario,
            tec.nombre AS nombre_tecnico, tec.apellido_paterno AS ape_tecnico,
            c.nombre_categoria, e.nombre AS nombre_estado
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

$descripcionLimpia = nl2br(htmlspecialchars(trim(html_entity_decode(strip_tags((string) ($ticket['descripcion_ticket'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8')), ENT_QUOTES, 'UTF-8'));

// Avances técnicos
$sql2 = "SELECT fecha_avance, hora_avance, accion 
         FROM avance_tecnicos 
         WHERE id_ticket = $id_ticket 
         ORDER BY fecha_avance ASC, hora_avance ASC";
$res2 = $bd->consulta($sql2);

// Preparar HTML para el PDF
$html = '<h2 style="text-align:center;">Reporte de Ticket Técnico</h2>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';

$html .= '<tr><td><strong>ID Ticket</strong></td><td>' . $ticket['id_ticket'] . '</td></tr>';
$html .= '<tr><td><strong>Asunto</strong></td><td>' . $ticket['asunto'] . '</td></tr>';
$html .= '<tr><td><strong>Descripción</strong></td><td>' . $descripcionLimpia . '</td></tr>';
$html .= '<tr><td><strong>Usuario</strong></td><td>' . $ticket['nombre_usuario'] . ' ' . $ticket['ape_usuario'] . '</td></tr>';
$html .= '<tr><td><strong>Técnico</strong></td><td>' . $ticket['nombre_tecnico'] . ' ' . $ticket['ape_tecnico'] . '</td></tr>';
$html .= '<tr><td><strong>Categoría</strong></td><td>' . $ticket['nombre_categoria'] . '</td></tr>';
$html .= '<tr><td><strong>Estado</strong></td><td>' . $ticket['nombre_estado'] . '</td></tr>';
$html .= '<tr><td><strong>Fecha de Creación</strong></td><td>' . $ticket['fecha_creacion_inicio'] . ' ' . $ticket['hora_creacion_inicio'] . '</td></tr>';
$html .= '<tr><td><strong>Fecha Asignación</strong></td><td>' . $ticket['fecha_asignacion_tecnico'] . ' ' . $ticket['hora_asignacion_tecnico'] . '</td></tr>';
$html .= '<tr><td><strong>Inicio Proceso</strong></td><td>' . $ticket['fecha_comienzo_ticket'] . ' ' . $ticket['hora_comienzo_ticket'] . '</td></tr>';
$html .= '<tr><td><strong>Término</strong></td><td>' . $ticket['fecha_termino_ticket'] . ' ' . $ticket['hora_termino_ticket'] . '</td></tr>';
$html .= '<tr><td><strong>Cierre</strong></td><td>' . $ticket['fecha_cierre_ticket'] . ' ' . $ticket['hora_cierre_ticket'] . '</td></tr>';
$html .= '</table>';

$html .= '<br><h3>Avances Técnicos</h3>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
$html .= '<thead><tr><th>Fecha y Hora</th><th>Detalle</th></tr></thead><tbody>';

while ($avance = $bd->fetch_assoc($res2)) {
    $fecha = $avance['fecha_avance'] . ' ' . $avance['hora_avance'];
    $accion = nl2br(htmlspecialchars(trim(html_entity_decode(strip_tags((string) ($avance['accion'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8')), ENT_QUOTES, 'UTF-8'));
    $html .= "<tr><td>$fecha</td><td>$accion</td></tr>";
}
$html .= '</tbody></table>';

// Crear PDF
$mpdf = new Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output('ticket_' . $ticket['id_ticket'] . '.pdf', 'D'); // 'D' fuerza la descarga
exit;
