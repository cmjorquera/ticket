<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../clases/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../clases/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../clases/PHPMailer/src/SMTP.php';

Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['ok' => false, 'mensaje' => 'Método no permitido.'], 405);
}
if (strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) !== 'xmlhttprequest') {
    responder_json(['ok' => false, 'mensaje' => 'Solicitud no válida.'], 400);
}

/** @return int[] */
function destinatarios_ids(mixed $valor): array
{
    if (!is_array($valor)) {
        return [];
    }
    return array_slice(array_values(array_unique(array_filter(
        array_map('intval', $valor),
        static fn (int $id): bool => $id > 0
    ))), 0, 500);
}

function crear_mailer_colegio(): PHPMailer
{
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';

    $host = trim((string) getenv('SMTP_HOST'));
    if ($host !== '') {
        $mail->isSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->Host = $host;
        $mail->Port = max(1, (int) (getenv('SMTP_PORT') ?: 587));
        $usuario = (string) getenv('SMTP_USER');
        $mail->SMTPAuth = $usuario !== '';
        if ($mail->SMTPAuth) {
            $mail->Username = $usuario;
            $mail->Password = (string) getenv('SMTP_PASSWORD');
        }
        $seguridad = trim((string) getenv('SMTP_SECURE'));
        if ($seguridad !== '') {
            $mail->SMTPSecure = $seguridad;
        }
    } else {
        $mail->isMail();
    }

    $desde = trim((string) getenv('MAIL_FROM_ADDRESS')) ?: 'seduc.informa@seduc.cl';
    $nombreDesde = trim((string) getenv('MAIL_FROM_NAME')) ?: 'SeducSPA';
    $mail->setFrom($desde, $nombreDesde);
    $mail->isHTML(true);
    return $mail;
}

try {
    $datos = entrada_ajax();
    $idColegio = (int) ($datos['id_colegio'] ?? 0);
    $idAdministradorEnviado = (int) ($datos['id_usuario_administrador'] ?? 0);
    $idAdministrador = (int) ($_SESSION['id'] ?? 0);
    $destinatariosSolicitados = destinatarios_ids($datos['destinatarios'] ?? []);

    if ($idColegio <= 0 || $idAdministrador <= 0 || $idAdministradorEnviado !== $idAdministrador) {
        responder_json(['ok' => false, 'mensaje' => 'Los datos del envío no son válidos.'], 422);
    }
    if ($destinatariosSolicitados === []) {
        responder_json(['ok' => false, 'mensaje' => 'El colegio no tiene destinatarios seleccionados.'], 422);
    }

    $db = Conexion::getInstance('sistema_panel_central');
    $perfiles = $db->fetchAll(
        'SELECT p.id_perfil, p.nombre
           FROM usuario_perfil up
           JOIN perfiles p ON p.id_perfil = up.id_perfil
          WHERE up.id_usuario = ? AND p.estado = 1',
        [$idAdministrador]
    );
    $esSuperAdmin = false;
    $esAdminColegio = false;
    foreach ($perfiles as $perfil) {
        $idPerfil = (int) ($perfil['id_perfil'] ?? 0);
        $nombrePerfil = strtolower(trim((string) ($perfil['nombre'] ?? '')));
        $esSuperAdmin = $esSuperAdmin || $idPerfil === 3 || in_array($nombrePerfil, ['super_admin', 'super admin', 'superadmin'], true);
        $esAdminColegio = $esAdminColegio || $idPerfil === 4
            || (str_contains($nombrePerfil, 'admin') && str_contains($nombrePerfil, 'colegio'));
    }
    if (!$esSuperAdmin && !$esAdminColegio) {
        responder_json(['ok' => false, 'mensaje' => 'No tienes autorización para enviar correos por colegio.'], 403);
    }
    if (!$esSuperAdmin && !$db->fetchOne(
        "SELECT 1
           FROM jefatura_departamento jd
          WHERE jd.id_usuario = ? AND jd.id_colegio = ?
            AND jd.tipo_jefatura = 'Admin_Colegio'
            AND jd.id_departamento_colegio IS NULL
            AND jd.estado = 1
          LIMIT 1",
        [$idAdministrador, $idColegio]
    )) {
        responder_json(['ok' => false, 'mensaje' => 'No administras el colegio seleccionado.'], 403);
    }

    $colegio = $db->fetchOne(
        'SELECT nom_colegio FROM colegio WHERE id_colegio = ? AND estado = 1 LIMIT 1',
        [$idColegio]
    );
    if (!$colegio) {
        responder_json(['ok' => false, 'mensaje' => 'El colegio seleccionado no está disponible.'], 404);
    }

    $marcadores = implode(',', array_fill(0, count($destinatariosSolicitados), '?'));
    $parametros = [$idColegio, ...$destinatariosSolicitados];
    $destinatarios = $db->fetchAll(
        "SELECT DISTINCT u.id,
                CONCAT_WS(' ', u.nombre, u.apellido_paterno, u.apellido_materno) AS nombre,
                u.email
           FROM usuarios u
           JOIN jefatura_departamento jd
             ON jd.id_usuario = u.id
            AND jd.id_colegio = ?
            AND jd.estado = 1
          WHERE u.id IN ({$marcadores})",
        $parametros
    );
    $destinatarios = array_values(array_filter(
        $destinatarios,
        static fn (array $usuario): bool => filter_var((string) ($usuario['email'] ?? ''), FILTER_VALIDATE_EMAIL) !== false
    ));
    if ($destinatarios === []) {
        responder_json(['ok' => false, 'mensaje' => 'No se encontraron correos válidos para este colegio.'], 422);
    }

    $administrador = $db->fetchOne(
        "SELECT CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) AS nombre
           FROM usuarios WHERE id = ? LIMIT 1",
        [$idAdministrador]
    );
    $nombreColegio = trim((string) $colegio['nom_colegio']);
    $nombreAdministrador = trim((string) ($administrador['nombre'] ?? 'Administrador')) ?: 'Administrador';
    $enviados = 0;
    $fallidos = 0;

    foreach ($destinatarios as $destinatario) {
        try {
            $mail = crear_mailer_colegio();
            $nombreDestinatario = trim((string) ($destinatario['nombre'] ?? 'Usuario')) ?: 'Usuario';
            $mail->addAddress((string) $destinatario['email'], $nombreDestinatario);
            $mail->Subject = 'Comunicado de ' . $nombreColegio;
            $mail->Body = '<div style="font-family:Arial,Helvetica,sans-serif;color:#24384a;line-height:1.55">'
                . '<h2 style="color:#075f93">Comunicado del colegio</h2>'
                . '<p>Hola ' . htmlspecialchars($nombreDestinatario, ENT_QUOTES, 'UTF-8') . '.</p>'
                . '<p><strong>' . htmlspecialchars($nombreAdministrador, ENT_QUOTES, 'UTF-8') . '</strong> ha enviado una notificación general a los usuarios de <strong>'
                . htmlspecialchars($nombreColegio, ENT_QUOTES, 'UTF-8') . '</strong>.</p>'
                . '<p>Revisa el sistema para mantenerte al día con la información de tu colegio.</p></div>';
            $mail->AltBody = $nombreAdministrador . ' ha enviado una notificación general a los usuarios de ' . $nombreColegio . '.';
            $mail->send();
            $enviados++;
        } catch (MailException $ex) {
            $fallidos++;
            error_log('Correo de colegio no enviado a usuario ' . (int) $destinatario['id'] . ': ' . $ex->getMessage());
        }
    }

    if ($enviados === 0) {
        responder_json(['ok' => false, 'mensaje' => 'No fue posible enviar los correos. Revisa la configuración de correo del servidor.'], 500);
    }
    $mensaje = "Se enviaron {$enviados} correo" . ($enviados === 1 ? '' : 's') . '.';
    if ($fallidos > 0) {
        $mensaje .= " {$fallidos} envío" . ($fallidos === 1 ? ' falló.' : 's fallaron.');
    }
    responder_json(['ok' => true, 'data' => ['mensaje' => $mensaje, 'enviados' => $enviados, 'fallidos' => $fallidos]]);
} catch (Throwable $ex) {
    error_log('Error en enviar_correo.php: ' . $ex->getMessage());
    responder_json(['ok' => false, 'mensaje' => 'No fue posible procesar el envío de correos.'], 500);
}
