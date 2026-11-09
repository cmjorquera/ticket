<?php
declare(strict_types=1);

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/ConfigPrivada.php';

/**
 * Activación de cuentas nuevas por ENLACE (como en el sistema antiguo:
 * acceso.seduc.cl/reiniciar_clave.php?token=…).
 *
 *  1. Al crear el usuario: estado 'Pendiente', clave NULL y un token.
 *  2. Se le envía un correo con el enlace  {BASE_URL}activar_cuenta.php?token=…
 *  3. En esa página elige su contraseña → estado 'Activo', token borrado.
 *  - El enlace vence a los DIAS_VIGENCIA días; "Reenviar activación" crea otro.
 *  - En la base NO se guarda el token, solo su hash:
 *      usuarios.token_reinicio = 'ACT:<vence_unix>:<sha256 del token>'
 *  - Si el correo no se puede enviar (SMTP no configurado), quien creó la
 *    cuenta ve el enlace para copiarlo y enviarlo por otro medio.
 */
final class ActivacionCuenta
{
    public const DIAS_VIGENCIA = 7;
    private const PREFIJO = 'ACT:';

    private Conexion $db;

    public function __construct(Conexion $db)
    {
        $this->db = $db;
    }

    /** Crea un token nuevo para el usuario y devuelve el enlace. */
    public function generar(int $idUsuario): string
    {
        $token = bin2hex(random_bytes(32));
        $vence = time() + self::DIAS_VIGENCIA * 86400;
        $this->db->execute(
            'UPDATE usuarios SET token_reinicio = ? WHERE id = ?',
            [self::PREFIJO . $vence . ':' . hash('sha256', $token), $idUsuario]
        );
        return self::enlace($token);
    }

    public static function enlace(string $token): string
    {
        if (!defined('BASE_URL') && is_file(dirname(__DIR__) . '/config.php')) {
            require_once dirname(__DIR__) . '/config.php';
        }
        if (defined('BASE_URL')) {
            $base = rtrim((string) BASE_URL, '/') . '/';
        } else {
            $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $host = preg_replace('/[^\w.\-:]/', '', (string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));
            $base = ($https ? 'https' : 'http') . '://' . $host . '/';
        }
        return $base . 'activar_cuenta.php?token=' . rawurlencode($token);
    }

    /**
     * Usuario dueño del token si es válido y no venció.
     * @return array{usuario:?array, motivo:string}  motivo: ok | invalido | vencido | activo
     */
    public function buscar(string $token): array
    {
        if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
            return ['usuario' => null, 'motivo' => 'invalido'];
        }
        $u = $this->db->fetchOne(
            "SELECT id, nombre, apellido_paterno, email, estado, token_reinicio
               FROM usuarios WHERE token_reinicio LIKE ? LIMIT 1",
            [self::PREFIJO . '%:' . hash('sha256', $token)]
        );
        if (!$u) {
            return ['usuario' => null, 'motivo' => 'invalido'];
        }
        $vence = (int) explode(':', (string) $u['token_reinicio'])[1];
        if ($vence < time()) {
            return ['usuario' => $u, 'motivo' => 'vencido'];
        }
        return ['usuario' => $u, 'motivo' => 'ok'];
    }

    /** Guarda la contraseña elegida y activa la cuenta. */
    public function activar(string $token, string $clave, string $repetir): array
    {
        $r = $this->buscar($token);
        if ($r['motivo'] !== 'ok') {
            throw new RuntimeException($r['motivo'] === 'vencido'
                ? 'El enlace venció. Pide que te reenvíen la activación.'
                : 'El enlace no es válido o ya fue usado.');
        }
        if ($clave !== $repetir) {
            throw new RuntimeException('Las contraseñas no coinciden.');
        }
        $errores = self::erroresClave($clave);
        if ($errores) {
            throw new RuntimeException('La contraseña debe tener ' . implode(', ', $errores) . '.');
        }
        $this->db->execute(
            "UPDATE usuarios SET clave = ?, estado = 'Activo', intentos_fallidos = 0, token_reinicio = NULL WHERE id = ?",
            [password_hash($clave, PASSWORD_DEFAULT), (int) $r['usuario']['id']]
        );
        return $r['usuario'];
    }

    /** Mismas reglas que Mi cuenta → Contraseña. */
    public static function erroresClave(string $c): array
    {
        $e = [];
        if (mb_strlen($c) < 8) { $e[] = 'al menos 8 caracteres'; }
        if (!preg_match('/[A-Za-zÁÉÍÓÚÑáéíóúñ]/u', $c)) { $e[] = 'una letra'; }
        if (!preg_match('/\d/', $c)) { $e[] = 'un número'; }
        if (mb_strlen($c) > 72) { $e[] = 'máximo 72 caracteres'; }
        return $e;
    }

    /**
     * Envía el correo con el enlace. Usa la sección 'smtp' de config_privada.
     * @return array{enviado:bool, motivo:string}
     */
    public static function enviarCorreo(string $email, string $nombre, string $donde, string $enlace): array
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
            $mail->Timeout = 15;
            $mail->setFrom((string) ($smtp['remitente'] ?? 'seduc.informa@seduc.cl'), (string) ($smtp['nombre_remitente'] ?? 'SEDUC'));
            $mail->addAddress($email, $nombre);
            $mail->isHTML(true);
            $mail->Subject = 'Activa tu cuenta del Panel SEDUC';
            $mail->Body = self::cuerpoCorreo($nombre, $email, $donde, $enlace);
            $mail->AltBody = "Hola {$nombre}:\n\nSe creó tu cuenta del Panel SEDUC ({$donde}).\n"
                . "Para elegir tu contraseña entra a:\n{$enlace}\n\nEl enlace vence en " . self::DIAS_VIGENCIA . " días.";
            $mail->send();
            return ['enviado' => true, 'motivo' => ''];
        } catch (Throwable $e) {
            error_log('[activacion] correo a ' . $email . ': ' . $e->getMessage());
            return ['enviado' => false, 'motivo' => 'No fue posible enviar el correo.'];
        }
    }

    private static function cuerpoCorreo(string $nombre, string $email, string $donde, string $enlace): string
    {
        $h = static fn (string $t) => htmlspecialchars($t, ENT_QUOTES, 'UTF-8');
        $dias = self::DIAS_VIGENCIA;
        return <<<HTML
<div style="background:#f3f4f6;padding:24px 12px;font-family:Arial,Helvetica,sans-serif">
  <table role="presentation" width="100%" style="max-width:560px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden">
    <tr><td style="background:#005B96;border-bottom:4px solid #F4B000;padding:18px 24px;color:#ffffff;font-size:18px;font-weight:bold">Panel SEDUC</td></tr>
    <tr><td style="padding:24px;color:#1f2937;font-size:15px;line-height:1.55">
      <p style="margin:0 0 12px">Hola <strong>{$h($nombre)}</strong>:</p>
      <p style="margin:0 0 12px">Se creó tu cuenta en el Panel SEDUC ({$h($donde)}). Tu usuario es <strong>{$h($email)}</strong>.</p>
      <p style="margin:0 0 20px">Para empezar, elige tu contraseña:</p>
      <p style="margin:0 0 20px;text-align:center"><a href="{$h($enlace)}" style="display:inline-block;background:#005B96;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:8px;font-weight:bold">Activar mi cuenta</a></p>
      <p style="margin:0 0 6px;font-size:13px;color:#6b7280">Si el botón no funciona, copia este enlace en tu navegador:</p>
      <p style="margin:0 0 16px;font-size:12px;word-break:break-all"><a href="{$h($enlace)}" style="color:#005B96">{$h($enlace)}</a></p>
      <p style="margin:0;font-size:13px;color:#6b7280">El enlace vence en {$dias} días. Si no esperabas este correo, ignóralo.</p>
    </td></tr>
  </table>
</div>
HTML;
    }
}
