<?php
declare(strict_types=1);

require_once __DIR__ . '/ConfigPrivada.php';

/**
 * Envío de correos con PHPMailer usando la sección 'smtp' de config_privada
 * (nunca credenciales en el código). Mismo esquema que ActivacionCuenta.
 */
final class Correo
{
    /**
     * @param array<int,array{0:string,1:string}> $para      [[email, nombre], …]
     * @param array<int,array{0:string,1:string,2:string}> $adjuntos [[contenido, nombre, mime], …]
     * @param string $ical  invitación de calendario (.ics, METHOD:REQUEST/CANCEL): Gmail y Outlook
     *                      muestran "Agregar al calendario" y los botones Sí / No / Quizás
     * @return array{enviado:bool,motivo:string}
     */
    public static function enviar(array $para, string $asunto, string $html, string $texto = '', array $adjuntos = [], string $ical = ''): array
    {
        $smtp = ConfigPrivada::opcional('smtp');
        if (!$smtp || empty($smtp['host']) || str_starts_with((string) ($smtp['password'] ?? ''), 'CAMBIAR')) {
            return ['enviado' => false, 'motivo' => 'El envío de correos no está configurado en este servidor.'];
        }
        require_once __DIR__ . '/PHPMailer/src/Exception.php';
        require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
        require_once __DIR__ . '/PHPMailer/src/SMTP.php';
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->SMTPDebug = 0;
            $mail->Host = (string) $smtp['host'];
            $mail->Port = (int) ($smtp['puerto'] ?? 465);
            $mail->SMTPAuth = true;
            $mail->Username = (string) ($smtp['usuario'] ?? '');
            $mail->Password = (string) ($smtp['password'] ?? '');
            $mail->SMTPSecure = (string) ($smtp['seguridad'] ?? 'ssl');
            $mail->CharSet = 'UTF-8';
            $mail->Timeout = 20;
            $mail->setFrom((string) ($smtp['remitente'] ?? 'seduc.informa@seduc.cl'), (string) ($smtp['nombre_remitente'] ?? 'SEDUC'));
            foreach ($para as [$email, $nombre]) {
                $mail->addAddress($email, $nombre);
            }
            foreach ($adjuntos as [$contenido, $nombre, $mime]) {
                $mail->addStringAttachment($contenido, $nombre, 'base64', $mime);
            }
            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body = $html;
            $mail->AltBody = $texto !== '' ? $texto : trim(html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8'));
            if ($ical !== '') {
                $mail->Ical = $ical;   // parte text/calendar (la que leen los clientes de correo)
                $metodo = preg_match('/METHOD:(\w+)/', $ical, $m) ? $m[1] : 'REQUEST';
                $mail->addStringAttachment($ical, $metodo === 'CANCEL' ? 'cancelacion.ics' : 'invitacion.ics', 'base64', 'text/calendar; charset=utf-8; method=' . $metodo);
            }
            $mail->send();
            return ['enviado' => true, 'motivo' => ''];
        } catch (Throwable $e) {
            error_log('[correo] ' . $asunto . ': ' . $e->getMessage());
            return ['enviado' => false, 'motivo' => 'No fue posible enviar el correo.'];
        }
    }
}
