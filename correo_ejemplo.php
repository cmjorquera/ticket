<?php
require_once("class/PHPMailer/src/PHPMailer.php");
require_once("class/PHPMailer/src/Exception.php");
require_once("class/PHPMailer/src/SMTP.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
$emailUsuario ="cjorquera@seduc.cl";

$mail = new PHPMailer(true);
try {
    // Configuración del servidor SMTP
    $mail->IsSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->Host = 'smtp.itdchile.cl';
    $mail->Port = 46500; 
    $mail->SMTPAuth = true;
    $mail->Username = 'm.gutierrez';
    $mail->Password = 'Seduc2024.,';
    $mail->SMTPSecure = 'tcp';
    $mail->CharSet = 'UTF-8';

    // Configuración del remitente y destinatario
    $mail->setFrom('seduc.informa@seduc.cl', 'SeducSPA');
    $mail->addAddress($emailUsuario); // Asegúrate de que $emailUsuario tenga el correo del destinatario

    // Configuración del correo
    $mail->isHTML(true);
    $mail->Subject = 'Nuevo Ticket Creado';
    $mail->Body = 'Hola Cristian Jorquera'; // Mensaje del correo

    // Enviar el correo
    $mail->send();
    echo 'Correo enviado correctamente';
} catch (Exception $e) {
    echo "El correo no se pudo enviar. Mailer Error: {$mail->ErrorInfo}";
}
?>
