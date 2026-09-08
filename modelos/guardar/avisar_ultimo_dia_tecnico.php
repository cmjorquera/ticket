<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../class/PHPMailer/src/Exception.php';
require '../../class/PHPMailer/src/PHPMailer.php';
require '../../class/PHPMailer/src/SMTP.php';

include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit;
}

$idTicket = isset($_POST['id_ticket']) ? (int) $_POST['id_ticket'] : 0;
if ($idTicket <= 0) {
    echo json_encode(['success' => false, 'message' => 'Ticket invalido']);
    exit;
}

$db = new MySQL("", "", "");
$sql = "SELECT 
            t.id_ticket,
            t.asunto,
            t.descripcion_ticket,
            t.id_tecnico,
            u.nombre AS nombre_tecnico,
            u.apellido_paterno AS apellido_tecnico,
            u.email AS email_tecnico,
            pt.fecha_creacion_inicio,
            pt.fecha_estimada_admin,
            pt.dias_estimada_admin
        FROM tickets t
        INNER JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
        INNER JOIN usuarios u ON u.id = t.id_tecnico
        WHERE t.id_ticket = '$idTicket'
        LIMIT 1";

$rs = $db->consulta($sql);
$row = $db->fetch_array($rs);

if (!$row) {
    echo json_encode(['success' => false, 'message' => 'No se encontraron datos del ticket']);
    exit;
}

$emailTecnico = trim((string) ($row['email_tecnico'] ?? ''));
if (!filter_var($emailTecnico, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El tecnico no tiene un correo valido']);
    exit;
}

$nombreTecnico = trim(($row['nombre_tecnico'] ?? '') . ' ' . ($row['apellido_tecnico'] ?? ''));
$asuntoTicket = trim((string) ($row['asunto'] ?? 'Sin asunto'));
$descripcionTicket = nl2br(htmlspecialchars(trim((string) ($row['descripcion_ticket'] ?? '')), ENT_QUOTES, 'UTF-8'));
$fechaCreacion = !empty($row['fecha_creacion_inicio']) && $row['fecha_creacion_inicio'] !== '0000-00-00'
    ? date('d-m-Y', strtotime($row['fecha_creacion_inicio']))
    : '--';
$fechaEstimada = !empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00'
    ? date('d-m-Y', strtotime($row['fecha_estimada_admin']))
    : '--';
$diasEstimados = (int) ($row['dias_estimada_admin'] ?? 0);

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPDebug  = SMTP::DEBUG_OFF;
    $mail->Host       = 'smtp.itdchile.cl';
    $mail->Port       = 46500;
    $mail->SMTPAuth   = true;
    $mail->Username   = 'm.gutierrez';
    $mail->Password   = 'Seduc2024.,';
    $mail->SMTPSecure = 'tcp';
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('seduc.informa@seduc.cl', 'SeducSPA');
    $mail->addAddress($emailTecnico, $nombreTecnico ?: 'Tecnico');
    $mail->isHTML(true);
    $mail->Subject = 'Aviso: queda 1 dia para cerrar el ticket A-0' . $idTicket;
    $mail->Body = '
        <div style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#1f2937;line-height:1.5;">
            <h2 style="color:#b45309;">Aviso de plazo</h2>
            <p>Hola ' . htmlspecialchars($nombreTecnico ?: 'Tecnico', ENT_QUOTES, 'UTF-8') . '.</p>
            <p>El administrador te informa que queda <strong>1 dia</strong> para el ticket <strong>A-0' . $idTicket . '</strong>.</p>
            <p><strong>Asunto:</strong> ' . htmlspecialchars($asuntoTicket, ENT_QUOTES, 'UTF-8') . '</p>
            <p><strong>Fecha de inicio:</strong> ' . htmlspecialchars($fechaCreacion, ENT_QUOTES, 'UTF-8') . '</p>
            <p><strong>Fecha estimada:</strong> ' . htmlspecialchars($fechaEstimada, ENT_QUOTES, 'UTF-8') . '</p>
            <p><strong>Dias estimados:</strong> ' . htmlspecialchars((string) $diasEstimados, ENT_QUOTES, 'UTF-8') . '</p>
            <p><strong>Descripcion:</strong><br>' . $descripcionTicket . '</p>
            <p>Por favor revisa el ticket en el sistema y gestiona su cierre dentro del plazo.</p>
        </div>';

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Se envio el aviso al tecnico']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'No se pudo enviar el correo']);
}
