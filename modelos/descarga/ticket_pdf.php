<?php
require_once '../../class/conexion.php';
require '../../vendor/autoload.php';

use Mpdf\Mpdf;

// Activar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Validar ID
if (!isset($_GET['id'])) {
    die("Falta el parámetro 'id'.");
}
$id_ticket = intval($_GET['id']);

$bd = new MySQL('', '', '');

// --- CONSULTAS ---
// Datos principales del ticket
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
$sqlAv = "SELECT fecha_avance, hora_avance, accion 
          FROM avance_tecnicos 
          WHERE id_ticket = $id_ticket 
          ORDER BY fecha_avance ASC, hora_avance ASC";
$resAv = $bd->consulta($sqlAv);

// Calificación del usuario
$sqlCal = "SELECT ct.calificacion, cal.comentario, cal.fecha 
           FROM calificacion_tickett cal
           LEFT JOIN calificacion_ticket ct ON ct.id = cal.id_calificacion
           WHERE cal.id_ticket = $id_ticket 
           LIMIT 1";

$resCal = $bd->consulta($sqlCal);
$calificacion = $bd->fetch_assoc($resCal);

// --- COMIENZO DEL HTML ---
$logo = '../../imagenes/logo_seduc.png';

$html = '
<div style="text-align:center; margin-bottom: 20px;">
    <img src="' . $logo . '" width="120" style="margin-bottom:10px;"><br>
    <h2 style="margin:0;">📄 Reporte de Ticket Técnico</h2>
</div>

<table border="1" cellpadding="5" cellspacing="0" width="100%" style="border-collapse:collapse;">';

$datos = [
    'ID Ticket'    => 'A00' . $ticket['id_ticket'],
    'Asunto'       => $ticket['asunto'],
    'Descripción'  => $descripcionLimpia,
    'Usuario'      => $ticket['nombre_usuario'] . ' ' . $ticket['ape_usuario'],
    'Técnico'      => $ticket['nombre_tecnico'] . ' ' . $ticket['ape_tecnico'],
    'Categoría'    => $ticket['nombre_categoria'],
    'Estado'       => $ticket['nombre_estado']
];

foreach ($datos as $label => $value) {
    $html .= "<tr><td><strong>$label</strong></td><td>$value</td></tr>";
}
$html .= '</table>';

// --- AVANCES TÉCNICOS ---
$html .= '<br><h3>🔧 Comentarios Técnicos</h3>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%" style="border-collapse:collapse;">';
$html .= '<thead><tr><th style="width:30%;">Fecha y Hora</th><th>Detalle</th></tr></thead><tbody>';

while ($av = $bd->fetch_assoc($resAv)) {
    $fecha = $av['fecha_avance'] . ' ' . $av['hora_avance'];
    $accion = nl2br(htmlspecialchars(trim(html_entity_decode(strip_tags((string) ($av['accion'] ?? '')), ENT_QUOTES | ENT_HTML5, 'UTF-8')), ENT_QUOTES, 'UTF-8'));
    $html .= "<tr><td>$fecha</td><td>$accion</td></tr>";
}
$html .= '</tbody></table>';

// --- LÍNEA DE TIEMPO ---
$html .= '<br><h3>🕓 Línea de Tiempo</h3>';
$html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%" style="border-collapse:collapse;">';
$html .= '<thead><tr><th>Etapa</th><th>Fecha y Hora</th></tr></thead><tbody>';

$etapas = [
    'Creación del Ticket'     => $ticket['fecha_creacion_inicio'] . ' ' . $ticket['hora_creacion_inicio'],
    'Asignación del Técnico'  => $ticket['fecha_asignacion_tecnico'] . ' ' . $ticket['hora_asignacion_tecnico'],
    'Inicio del Trabajo'      => $ticket['fecha_comienzo_ticket'] . ' ' . $ticket['hora_comienzo_ticket'],
    'Término del Trabajo'     => $ticket['fecha_termino_ticket'] . ' ' . $ticket['hora_termino_ticket'],
    'Cierre del Ticket'       => $ticket['fecha_cierre_ticket'] . ' ' . $ticket['hora_cierre_ticket']
];

foreach ($etapas as $nombre => $valor) {
    if (trim($valor) && $valor !== '0000-00-00 00:00:00') {
        $html .= "<tr><td>$nombre</td><td>$valor</td></tr>";
    } else {
        $html .= "<tr><td>$nombre</td><td><span style='color:gray;'>Pendiente</span></td></tr>";
    }
}
$html .= '</tbody></table>';

// --- CALIFICACIÓN DEL USUARIO ---
$html .= '<br><h3>⭐ Calificación del Usuario</h3>';
if ($calificacion) {
    $html .= '<table border="1" cellpadding="5" cellspacing="0" width="100%" style="border-collapse:collapse;">';
    $html .= '<tr><td><strong>Fecha</strong></td><td>' . $calificacion['fecha'] . '</td></tr>';
    $html .= '<tr><td><strong>Calificación</strong></td><td>' . htmlspecialchars($calificacion['calificacion']) . '</td></tr>';
    $html .= '<tr><td><strong>Comentario</strong></td><td>' . htmlspecialchars($calificacion['comentario']) . '</td></tr>';
    $html .= '</table>';
} else {
    $html .= '<p style="color:gray;">Este ticket aún no ha sido calificado.</p>';
}


// --- PIE DE PÁGINA ---
$html .= '<div style="margin-top:40px; text-align:center;">
    <hr>
    <p style="font-size:11px; color:#555;">Generado automáticamente por el sistema SEDUC el ' . date("d-m-Y H:i") . '</p>
</div>';

// --- GENERAR PDF ---
$mpdf = new Mpdf();
$mpdf->WriteHTML($html);
$nombrePDF = 'ticket_A00' . $ticket['id_ticket'] . '.pdf';
$mpdf->Output($nombrePDF, 'D'); // 'D' = Descargar
exit;
