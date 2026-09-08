<?php

include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');
require_once("../../class/PHPMailer/src/PHPMailer.php");
require_once("../../class/PHPMailer/src/Exception.php");
require_once("../../class/PHPMailer/src/SMTP.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Conectar a la base de datos
$db = new MySQL("", "", "");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ticket              = $_POST['id_ticket'] ?? '';
    $comentario             = $_POST['comentario'] ?? '';
    $validacionSeleccionada = $_POST['validacion'] ?? '';
    $idUsuarioSession       = $_POST['usuario'] ?? ''; // Asegúrate de que este campo se envíe correctamente desde el frontend

    // Verificar que el ID del ticket sea válido
    if (empty($id_ticket)) {
        echo "ID de ticket no válido.";
        exit;
    }

    $fecha = date("Y-m-d");
    $hora = date("H:i:s");

    // Si la validación seleccionada es "El ticket está bien resuelto", se considera que el ticket se cierra.
    if ($validacionSeleccionada === 'El ticket está bien resuelto') {
        // Actualización en la tabla `proceso_tickets`
        $sql1 = "UPDATE `proceso_tickets` SET 
                 `fecha_cerrado_ticket` = '$fecha', 
                 `hora_cerrado_ticket` = '$hora'
                 WHERE `id_ticket` = '$id_ticket'";
        $result1 = $db->guardar($sql1);

        // Actualización de la tabla `tickets` poniendo el ticket en estado "cerrado"
        $sql2 = "UPDATE `tickets` SET 
                 `id_estado` = '7'
                 WHERE `id_ticket` = '$id_ticket'";
        $result2 = $db->guardar($sql2);
//  echo $result2;

        // Inserción en la tabla `avance_tecnicos`
        $sql3 = "INSERT INTO `avance_tecnicos`(`id_ticket`, `accion`, `fecha_avance`, `hora_avance`)
                 VALUES ('$id_ticket', 'Ticket Cerrado', '$fecha', '$hora')";
        $result3 = $db->guardar($sql3);

        if (!$result3) {
            echo "Error al actualizar la tabla proceso_tickets.";
            error_log($db->error());
            exit;
        }
    } else {
        // Inserción en la tabla `reactivacion_ticket` si el ticket se está reactivando
        $sql1 = "INSERT INTO `reactivacion_ticket`(`id_ticket`, `comentario`, `fecha_reactivacion`, `hora_reactivacion`, `usuario_reactivacion`)
                 VALUES ('$id_ticket', '$comentario', '$fecha', '$hora', '$idUsuarioSession')";
        $result1 = $db->guardar($sql1);

        // Actualización de la tabla `tickets` poniendo el ticket en estado "en validación"
        $sql2 = "UPDATE `tickets` SET 
                 `id_estado` = '3'                
                 WHERE `id_ticket` = '$id_ticket'";
                //  echo $sql2."*********";
                //  die();
        $result2 = $db->guardar($sql2);

        $sql3 = "INSERT INTO `avance_tecnicos`(`id_ticket`, `accion`, `fecha_avance`, `hora_avance`)
        VALUES ('$id_ticket', 'Ticket reactivado', '$fecha', '$hora')";
        $result3 = $db->guardar($sql3);
    }

    // Obtener el correo electrónico del usuario y los detalles del ticket
    $sql4 = "SELECT t.*, u.email, u.nombre, u.apellido_paterno, uss.nombre AS nombre_tecnico, uss.apellido_paterno AS apePaternoTecnico
             FROM tickets t
             JOIN usuarios u ON t.id_usuario = u.id
             JOIN usuarios uss ON t.id_tecnico = uss.id
             WHERE t.id_ticket = '$id_ticket'";
    $result4 = $db->consulta($sql4);

    if ($row = $db->fetch_array($result4)) {
        $emailUsuario = $row['email'];
        $nombreUsarioCompleto = $row['nombre'] . ' ' . $row['apellido_paterno'];
        $asunto_ticket = $row['asunto'];
        $descripcion_ticket = $row['descripcion_ticket'];
        $identificador = $row['identificador'];
        $nombreTecnicoCompleto = $row['nombre_tecnico'] . ' ' . $row['apePaternoTecnico'];

        // Enviar correo electrónico al usuario usando PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->IsSMTP();
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
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
            $mail->Subject = 'Ticket Terminado';

            // Cargar plantilla de correo y reemplazar los detalles del ticket
            $mailBody = file_get_contents('../../class/correos/cerrar_ticket.php');
            $mailBody = str_replace('{identificador}', htmlspecialchars($identificador), $mailBody);
            $mailBody = str_replace('{codigo}', htmlspecialchars($id_ticket), $mailBody);
            $mailBody = str_replace('{fecha}', htmlspecialchars($fecha), $mailBody);
            $mailBody = str_replace('{hora}', htmlspecialchars($hora), $mailBody);
            $mailBody = str_replace('{nombreUsarioCompleto}', htmlspecialchars($nombreUsarioCompleto), $mailBody);
            $mailBody = str_replace('{asunto}', htmlspecialchars($asunto_ticket), $mailBody);
            $mailBody = str_replace('{descripcion}', nl2br(htmlspecialchars(trim(html_entity_decode(strip_tags((string) $descripcion_ticket), ENT_QUOTES | ENT_HTML5, 'UTF-8')), ENT_QUOTES, 'UTF-8')), $mailBody);
            $mailBody = str_replace('{nombre_tecnico}', htmlspecialchars($nombreTecnicoCompleto), $mailBody);
            $mail->Body = $mailBody;

            $mail->send();
            echo json_encode(['status' => 'success', 'message' => 'Ticket finalizado y correo enviado']);
        } catch (Exception $e) {
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            echo json_encode(['status' => 'error', 'message' => "El correo no se pudo enviar al usuario. Mailer Error: {$mail->ErrorInfo}"]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al obtener los detalles del ticket']);
    }

    // Verificación de resultados
    if ($result1 && $result2 && $result3) {
        echo "Ticket $id_ticket procesado correctamente.";
    } else {
        echo "Error al procesar el ticket.";
    }
}

?>
