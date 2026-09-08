<?php
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');
require_once("../../class/PHPMailer/src/PHPMailer.php");
require_once("../../class/PHPMailer/src/Exception.php");
require_once("../../class/PHPMailer/src/SMTP.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

header('Content-Type: application/json');

$id_ticket    = isset($_POST["id_ticket"]) ? intval($_POST["id_ticket"]) : 0;
$calificacion = isset($_POST["calificacion"]) ? intval($_POST["calificacion"]) : 0;
$comentario   = isset($_POST["comentario"]) ? trim($_POST["comentario"]) : "";
$idUsuario    = isset($_POST["id_usuario"]) ? intval($_POST["id_usuario"]) : 0;
$idTecnico    = isset($_POST["id_tecnico"]) ? intval($_POST["id_tecnico"]) : 0;

$fechaCierre  = date("Y-m-d");
$horaCierre   = date("H:i:s");
$ip_usuario   = $_SERVER['REMOTE_ADDR'];

if ($id_ticket <= 0 || $calificacion <= 0) {
    echo json_encode(["success" => false, "message" => "Error: Datos inválidos. Debes seleccionar una calificación."]);
    exit;
}


echo $calificacion."</br>";
echo $comentario;

$db = new MySQL("", "", "");

$nuevoEstado = 5;

// Actualizar estado del ticket
$result1 = $db->guardar("UPDATE tickets SET id_estado = $nuevoEstado WHERE id_ticket = $id_ticket");

// Actualizar tabla proceso_tickets con fecha/hora de cierre
$result2 = $db->guardar("UPDATE proceso_tickets SET fecha_cierre_ticket = '$fechaCierre', hora_cierre_ticket = '$horaCierre' WHERE id_ticket = $id_ticket");

// Insertar en calificación
$insert = "INSERT INTO calificacion_tickett (
                id_ticket, id_usuario, id_tecnico, id_calificacion, comentario, fecha, hora, ip_usuario
            ) VALUES (
                $id_ticket, $idUsuario, $idTecnico, $calificacion, '$comentario', '$fechaCierre', '$horaCierre', '$ip_usuario'
            )";
$result3 = $db->guardar($insert);



if ($nuevoEstado == 5) {
    $sql4 = "SELECT t.*, u.email, u.nombre, u.apellido_paterno, uss.nombre AS nombre_tecnico, uss.apellido_paterno AS apePaternoTecnico, pt.* FROM tickets t JOIN usuarios u ON t.id_usuario = u.id JOIN usuarios uss ON t.id_tecnico = uss.id JOIN proceso_tickets pt ON t.id_ticket = pt.id_ticket WHERE t.id_ticket = '$id_ticket'";
    $result = $db->consulta($sql4);
    if ($row = $db->fetch_array($result)) {
        $emailUsuario = $row['email'];
        $nombreUsarioCompleto = $row['nombre'] . ' ' . $row['apellido_paterno'];
        $asunto_ticket = $row['asunto'];
        $descripcion_ticket = $row['descripcion_ticket'];
        $identificador = $row['identificador'];
        $nombreTecnicoCompleto = $row['nombre_tecnico'] . ' ' . $row['apePaternoTecnico'];
        $fecha_creacion_inicio = $row['fecha_creacion_inicio'];
        $hora_creacion_inicio = $row['hora_creacion_inicio'];
        $fecha_asignacion_tecnico = $row['fecha_asignacion_tecnico'];
        $hora_asignacion_tecnico = $row['hora_asignacion_tecnico'];
        $fecha_comienzo_ticket = $row['fecha_comienzo_ticket'];
        $hora_comienzo_ticket = $row['hora_comienzo_ticket'];
        $fecha_cierre_ticket = $row['fecha_cierre_ticket'];
        $hora_cierre_ticket = $row['hora_cierre_ticket'];
        $fecha_termino_ticket = $row['fecha_termino_ticket'];
        $hora_termino_ticket = $row['hora_termino_ticket'];

        $mail = new PHPMailer(true);
        try {
            $mail->IsSMTP();
            $mail->Host = 'smtp.itdchile.cl';
            $mail->Port = 46500;
            $mail->SMTPAuth = true;
            $mail->Username = 'm.gutierrez';
            $mail->Password = 'Seduc2024.,';
            $mail->SMTPSecure = 'tcp';
            $mail->CharSet = 'UTF-8';

            $mail->setFrom('seduc.informa@seduc.cl', 'SeducSPA');
            $mail->addAddress($emailUsuario);
            $mail->isHTML(true);
            $mail->Subject = 'Ticket Calificado';

            $mailBody = file_get_contents('../../class/correos/gracias.php');
            $mailBody = str_replace('{identificador}', $identificador, $mailBody);
            $mailBody = str_replace('{codigo}', $id_ticket, $mailBody);
            $mailBody = str_replace('{fecha}', $fechaCierre, $mailBody);
            $mailBody = str_replace('{hora}', $horaCierre, $mailBody);
            $mailBody = str_replace('{nombreUsarioCompleto}', $nombreUsarioCompleto, $mailBody);
            $mailBody = str_replace('{asunto}', $asunto_ticket, $mailBody);
            $mailBody = str_replace('{descripcion}', nl2br(htmlspecialchars(trim(html_entity_decode(strip_tags((string) $descripcion_ticket), ENT_QUOTES | ENT_HTML5, 'UTF-8')), ENT_QUOTES, 'UTF-8')), $mailBody);
            $mailBody = str_replace('{nombre_tecnico}', $nombreTecnicoCompleto, $mailBody);
            $mailBody = str_replace('{fecha_creacion_inicio}', $fecha_creacion_inicio, $mailBody);
            $mailBody = str_replace('{hora_creacion_inicio}', $hora_creacion_inicio, $mailBody);
            $mailBody = str_replace('{fecha_asignacion_tecnico}', $fecha_asignacion_tecnico, $mailBody);
            $mailBody = str_replace('{hora_asignacion_tecnico}', $hora_asignacion_tecnico, $mailBody);
            $mailBody = str_replace('{fecha_comienzo_ticket}', $fecha_comienzo_ticket, $mailBody);
            $mailBody = str_replace('{hora_comienzo_ticket}', $hora_comienzo_ticket, $mailBody);
            $mailBody = str_replace('{fecha_cierre_ticket}', $fecha_cierre_ticket, $mailBody);
            $mailBody = str_replace('{hora_cierre_ticket}', $hora_cierre_ticket, $mailBody);
            $mailBody = str_replace('{fecha_termino_ticket}', $fecha_termino_ticket, $mailBody);
            $mailBody = str_replace('{hora_termino_ticket}', $hora_termino_ticket, $mailBody);

            $mail->Body = $mailBody;
            $mail->send();
        } catch (Exception $e) {
            // error de envío ignorado
        }
    }
}

if ($result1 && $result2 && $result3 && $result4) {
    echo json_encode(["success" => true, "message" => "Ticket actualizado correctamente."]);
} else {
    echo json_encode(["success" => false, "message" => "Hubo un problema al actualizar el ticket."]);
}
