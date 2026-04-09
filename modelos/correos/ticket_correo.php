<?php
require_once("../../class/conexion.php");
require_once("../../class/PHPMailer/src/PHPMailer.php");
require_once("../../class/PHPMailer/src/Exception.php");
require_once("../../class/PHPMailer/src/SMTP.php");
require_once("../../vendor/autoload.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Mpdf\Mpdf;

header('Content-Type: application/json; charset=utf-8');

$id_ticket = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id_ticket <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

$db = new MySQL('', '', '');

// Obtener datos del ticket y usuario
$sql = "SELECT t.id_ticket, t.asunto, t.descripcion_ticket,
               u.email, u.nombre, u.apellido_paterno
        FROM tickets t
        LEFT JOIN usuarios u ON u.id = t.id_usuario
        WHERE t.id_ticket = $id_ticket";
$row = $db->fetch_array($db->consulta($sql));

$correoUsuario     = $row['email'];
$nombreUsuario     = $row['nombre'] . ' ' . $row['apellido_paterno'];
$asuntoTicket      = $row['asunto'];
$descripcionTicket = $row['descripcion_ticket'];
$descripcionTicketLimpia = trim(html_entity_decode(strip_tags((string) $descripcionTicket), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

// Obtener datos de proceso
$sqlProceso = "SELECT fecha_creacion_inicio, hora_creacion_inicio,
                      fecha_asignacion_tecnico, hora_asignacion_tecnico,
                      fecha_comienzo_ticket, hora_comienzo_ticket,
                      fecha_termino_ticket, hora_termino_ticket,
                      fecha_cierre_ticket, hora_cierre_ticket
               FROM proceso_tickets
               WHERE id_ticket = $id_ticket";
$proceso = $db->fetch_array($db->consulta($sqlProceso));

// Obtener datos de calificacion
$sqlCalif = "SELECT id_calificacion, comentario FROM calificacion_tickett WHERE id_ticket = $id_ticket ORDER BY id DESC LIMIT 1";
$resCalif = $db->consulta($sqlCalif);
$comentario = '';
$estrellas = '';

if ($db->num_rows($resCalif) > 0) {
    $rowCalif = $db->fetch_array($resCalif);
    $comentario = $rowCalif['comentario'];
    $cantEstrellas = intval($rowCalif['id_calificacion']);
    $estrellaIcono = '\u2605'; // estrella negra
    // $estrellas = str_repeat($estrellaIcono . ' ', $cantEstrellas);
    $estrellas = str_repeat('<span style="color: gold; font-size: 16px;">&#9733;</span>', $cantEstrellas);

}

// Generar PDF con plantilla
$pdf = new Mpdf();
$pdfContent = "
<style>
    body { font-family: Arial; font-size: 12px; }
    h2 { color: #005587; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
    th { background-color: #f2f2f2; }
</style>

<h2>Resumen del Ticket A00{$id_ticket}</h2>
<p><strong>Usuario:</strong> {$nombreUsuario}</p>
<p><strong>Asunto:</strong> {$asuntoTicket}</p>
<p><strong>Descripción:</strong><br>' . nl2br(htmlspecialchars($descripcionTicketLimpia, ENT_QUOTES, 'UTF-8')) . '</p>

<h3>Línea de Tiempo</h3>
<table>
    <tr><th>Evento</th><th>Fecha</th><th>Hora</th></tr>
    <tr><td>Creación Ticket</td><td>{$proceso['fecha_creacion_inicio']}</td><td>{$proceso['hora_creacion_inicio']}</td></tr>
    <tr><td>Asignación Técnico</td><td>{$proceso['fecha_asignacion_tecnico']}</td><td>{$proceso['hora_asignacion_tecnico']}</td></tr>
    <tr><td>Comienzo Atención</td><td>{$proceso['fecha_comienzo_ticket']}</td><td>{$proceso['hora_comienzo_ticket']}</td></tr>
    <tr><td>Término Atención</td><td>{$proceso['fecha_termino_ticket']}</td><td>{$proceso['hora_termino_ticket']}</td></tr>
    <tr><td>Cierre Ticket</td><td>{$proceso['fecha_cierre_ticket']}</td><td>{$proceso['hora_cierre_ticket']}</td></tr>
</table>

<h3>Evaluación del Usuario</h3>
<p><strong>Estrellas:</strong> {$estrellas}</p>
<p><strong>Comentario:</strong> {$comentario}</p>

<p style='margin-top:20px; font-size:11px; color:#666;'>Este documento fue generado automáticamente por el sistema Ticket.</p>
";

$pdf->WriteHTML($pdfContent);
$pdfPath = '../../archivos/tmp_ticket_' . $id_ticket . '.pdf';
$pdf->Output($pdfPath, \Mpdf\Output\Destination::FILE);





// Enviar correo
$mail = new PHPMailer(true);
try {
    $mail->IsSMTP();
    $mail->SMTPDebug = 0;
    $mail->Host = 'smtp.itdchile.cl';
    $mail->Port = 46500;
    $mail->SMTPAuth = true;
    $mail->Username = 'm.gutierrez';
    $mail->Password = 'Seduc2024.,';
    $mail->SMTPSecure = 'tcp';
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('seduc.informa@seduc.cl', 'SeducSPA');
    $mail->addAddress($correoUsuario, $nombreUsuario);
    $mail->Subject = 'Copia de tu Ticket A00' . $id_ticket;
    $mail->isHTML(true);

    // Cargar plantilla desde cerrado_ticket.php
    $mailBody = file_get_contents('formato_correo_ticket.php');

    // Reemplazar variables en la plantilla (ajusta según lo que tengas en tu HTML)
    $mailBody = str_replace('{nombreUsuario}', htmlspecialchars($nombreUsuario), $mailBody);
    $mailBody = str_replace('{asunto}', htmlspecialchars($asuntoTicket), $mailBody);
    $mailBody = str_replace('{descripcion}', nl2br(htmlspecialchars($descripcionTicketLimpia, ENT_QUOTES, 'UTF-8')), $mailBody);
    $mailBody = str_replace('{id_ticket}', htmlspecialchars($id_ticket), $mailBody);

    $mail->Body = $mailBody;

    $mail->addAttachment($pdfPath);
    $mail->send();
    unlink($pdfPath);

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    error_log('Mailer Error: ' . $mail->ErrorInfo);
    echo json_encode(['success' => false, 'message' => 'Error al enviar correo']);
}

?>
