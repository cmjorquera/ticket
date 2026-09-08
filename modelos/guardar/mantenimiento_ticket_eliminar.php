<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/../../class/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../class/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../class/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../../class/conexion.php';

date_default_timezone_set('America/Santiago');

function responderEliminarTicket($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function enviarCorreoCodigoEliminar($correoDestino, $nombreDestino, $ticketId, $codigo)
{
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->SMTPDebug = SMTP::DEBUG_OFF;
    $mail->Host = 'smtp.itdchile.cl';
    $mail->Port = 46500;
    $mail->SMTPAuth = true;
    $mail->Username = 'm.gutierrez';
    $mail->Password = 'Seduc2024.,';
    $mail->SMTPSecure = 'tcp';
    $mail->CharSet = 'UTF-8';

    $mail->setFrom('seduc.informa@seduc.cl', 'SeducSPA');
    $mail->addAddress($correoDestino, $nombreDestino);
    $mail->isHTML(true);
    $mail->Subject = 'Codigo de verificacion para eliminar ticket';

    $ticketSeguro = htmlspecialchars((string) $ticketId, ENT_QUOTES, 'UTF-8');
    $codigoSeguro = htmlspecialchars((string) $codigo, ENT_QUOTES, 'UTF-8');
    $nombreSeguro = htmlspecialchars((string) $nombreDestino, ENT_QUOTES, 'UTF-8');

    $mail->Body = "
        <div style='font-family:Arial,sans-serif;font-size:14px;color:#1f2937;line-height:1.5'>
            <p>Hola {$nombreSeguro},</p>
            <p>Solicitaste autorizar la eliminacion administrativa del ticket <strong>#{$ticketSeguro}</strong>.</p>
            <p>Tu codigo de verificacion es:</p>
            <div style='display:inline-block;padding:12px 18px;background:#eef2ff;border:1px solid #c7d2fe;border-radius:10px;font-size:24px;font-weight:700;letter-spacing:4px'>
                {$codigoSeguro}
            </div>
            <p style='margin-top:16px'>Este codigo vence en 10 minutos.</p>
        </div>
    ";

    $mail->send();
}

$accion = trim((string) ($_POST['accion'] ?? ''));
$ticketId = (int) ($_POST['ticket_id'] ?? 0);
$codigoIngresado = trim((string) ($_POST['codigo'] ?? ''));
$tipoEliminacion = trim((string) ($_POST['tipo_eliminacion'] ?? 'completa'));
$motivo = trim((string) ($_POST['motivo'] ?? ''));
$idUsuarioSession = (int) ($_SESSION['id'] ?? 0);

if ($idUsuarioSession <= 0) {
    responderEliminarTicket([
        'success' => false,
        'message' => 'Sesion no valida.'
    ], 401);
}

if ($ticketId <= 0) {
    responderEliminarTicket([
        'success' => false,
        'message' => 'Debes indicar un ticket valido.'
    ], 422);
}

$db = new MySQL("", "", "");
$db->set_charset('utf8mb4');

$ticketIdSeguro = (int) $ticketId;
$resultadoTicket = $db->consulta("SELECT id_ticket FROM tickets WHERE id_ticket = {$ticketIdSeguro} LIMIT 1");
if ($db->num_rows($resultadoTicket) === 0) {
    responderEliminarTicket([
        'success' => false,
        'message' => 'El ticket indicado no existe.'
    ], 404);
}

if ($accion === 'solicitar_codigo') {
    $resultadoUsuario = $db->consulta("
        SELECT nombre, apellido_paterno, email
        FROM usuarios
        WHERE id = {$idUsuarioSession}
        LIMIT 1
    ");

    if ($db->num_rows($resultadoUsuario) === 0) {
        responderEliminarTicket([
            'success' => false,
            'message' => 'No se pudo identificar al usuario actual.'
        ], 404);
    }

    $usuario = $db->fetch_assoc($resultadoUsuario);
    $correoDestino = trim((string) ($usuario['email'] ?? ''));
    $nombreDestino = trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido_paterno'] ?? ''));

    if (!filter_var($correoDestino, FILTER_VALIDATE_EMAIL)) {
        responderEliminarTicket([
            'success' => false,
            'message' => 'Tu usuario no tiene un correo valido para enviar el codigo.'
        ], 422);
    }

    $codigo = (string) random_int(100000, 999999);

    $_SESSION['mantenimiento_ticket_eliminar'] = [
        'ticket_id' => $ticketIdSeguro,
        'codigo' => password_hash($codigo, PASSWORD_DEFAULT),
        'expires_at' => time() + 600,
        'tipo_eliminacion' => $tipoEliminacion,
        'motivo' => $motivo,
        'id_usuario' => $idUsuarioSession
    ];

    try {
        enviarCorreoCodigoEliminar($correoDestino, $nombreDestino ?: 'Administrador', $ticketIdSeguro, $codigo);
    } catch (Exception $e) {
        responderEliminarTicket([
            'success' => false,
            'message' => 'No se pudo enviar el codigo al correo del administrador.'
        ], 500);
    }

    responderEliminarTicket([
        'success' => true,
        'message' => 'Codigo enviado al correo del administrador.'
    ]);
}

if ($accion === 'confirmar_eliminacion') {
    $tokenSesion = $_SESSION['mantenimiento_ticket_eliminar'] ?? null;

    if (!is_array($tokenSesion)) {
        responderEliminarTicket([
            'success' => false,
            'message' => 'Primero debes solicitar el codigo de verificacion.'
        ], 422);
    }

    if ((int) ($tokenSesion['ticket_id'] ?? 0) !== $ticketIdSeguro) {
        responderEliminarTicket([
            'success' => false,
            'message' => 'El codigo solicitado no corresponde a este ticket.'
        ], 422);
    }

    if ((int) ($tokenSesion['id_usuario'] ?? 0) !== $idUsuarioSession) {
        responderEliminarTicket([
            'success' => false,
            'message' => 'El codigo no pertenece a la sesion actual.'
        ], 403);
    }

    if (time() > (int) ($tokenSesion['expires_at'] ?? 0)) {
        unset($_SESSION['mantenimiento_ticket_eliminar']);
        responderEliminarTicket([
            'success' => false,
            'message' => 'El codigo ya vencio. Solicita uno nuevo.'
        ], 422);
    }

    if ($codigoIngresado === '' || !password_verify($codigoIngresado, (string) ($tokenSesion['codigo'] ?? ''))) {
        responderEliminarTicket([
            'success' => false,
            'message' => 'El codigo ingresado no es correcto.'
        ], 422);
    }

    $resultadoUpdate = $db->guardar("UPDATE tickets SET id_estado = 2 WHERE id_ticket = {$ticketIdSeguro} LIMIT 1");
    if ($resultadoUpdate !== 0) {
        responderEliminarTicket([
            'success' => false,
            'message' => 'No se pudo actualizar el estado del ticket.'
        ], 500);
    }

    unset($_SESSION['mantenimiento_ticket_eliminar']);

    responderEliminarTicket([
        'success' => true,
        'message' => 'El ticket fue actualizado correctamente.',
        'ticket_id' => $ticketIdSeguro,
        'id_estado' => 2
    ]);
}

responderEliminarTicket([
    'success' => false,
    'message' => 'Accion no valida.'
], 400);
