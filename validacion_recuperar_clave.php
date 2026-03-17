<?php
header('Content-type: text/html; charset=utf-8');

session_start();
require_once("class/conexion.php");
require_once("class/PHPMailer/src/PHPMailer.php");
require_once("class/PHPMailer/src/Exception.php");
require_once("class/PHPMailer/src/SMTP.php");


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$emailRecuperacion = $_POST['email'] ?? ''; // Usar operador de fusión null para manejar si el email no se envía






if (empty($emailRecuperacion)) {
    header('Location: recuperar_clave.php?error=campo_vacio');          // Redireccionar de vuelta al formulario con un mensaje de error
    exit;
} else if (!filter_var($emailRecuperacion, FILTER_VALIDATE_EMAIL)) {   
    header('Location: recuperar_clave.php?error=formato_incorrecto');   // Redireccionar de vuelta al formulario con un mensaje de error diferente
    exit;
}

$db         = new MySQL("", "", ""); 
$result = $db->consulta("SELECT u.*, a.nombre_area AS areaTrabajo 
                         FROM usuarios u 
                         JOIN area_trabajo a ON u.id_area_trabajo = a.id_area 
                         WHERE u.email = '$emailRecuperacion'");

if ($db->num_rows($result) == 0) {
    header("Location: recuperar_clave.php?error=usuario_no_existe&email=" . urlencode($emailRecuperacion));
    exit;
} else {
    // Extraer los datos del usuario
    $row = $result->fetch_assoc();
    $nombreUsuario          = $row['nombre'];               // Nombre del usuario desde la base de datos
    $apellidoUsuario        = $row['apellido_paterno'];     // Apellido Paterno  del usuario desde la base de datos
    $emailUsuario           = $row['email'];               // Email del usuario desde la base de datos
    $areaTrabajo            = $row['areaTrabajo'];
    
}
$token = bin2hex(random_bytes(16));                                         // Generar un token seguro

// Actualizar el token en la base de datos 
// aca genero el token 
$db->guardar("UPDATE usuarios SET token_reinicio = '$token' WHERE email = '$emailRecuperacion'");

// Recuperar el token actualizado desde la base de datos
$resultToken = $db->consulta("SELECT token_reinicio FROM usuarios WHERE email = '$emailRecuperacion'");
if ($db->num_rows($resultToken) > 0) {
    $rowToken = $resultToken->fetch_assoc();
    $token =         $rowToken['token_reinicio']; // Actualizar el valor del token
}

// Enviar correo electrónico usando PHPMailer
$mail = new PHPMailer(true);

try {
    $mail->IsSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
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
    $mail->Subject = 'Reinicio de Clave';

    // Cargar plantilla de correo y reemplazar el nombre del usuario y el enlace de reinicio
    $mailBody = file_get_contents('class/correos/reinicio_pass.php');
    $mailBody = str_replace('{nombreUsuario}', htmlspecialchars($nombreUsuario), $mailBody);
    $mailBody = str_replace('{apellidoUsuario}', htmlspecialchars($apellidoUsuario), $mailBody);
    $mailBody = str_replace('{emailUsuario}', htmlspecialchars($emailUsuario), $mailBody);
    $mailBody = str_replace('{areaTrabajo}', htmlspecialchars($areaTrabajo), $mailBody);
    $mailBody = str_replace('{token}', htmlspecialchars($token), $mailBody);

    $mail->Body = $mailBody;
    $mail->send();
    header("Location: index.php?mensaje=correo_enviado");
} catch (Exception $e) {
    error_log('Mailer Error: ' . $mail->ErrorInfo);
    header("Location: recuperar_clave.php?error=error_envio");
}
