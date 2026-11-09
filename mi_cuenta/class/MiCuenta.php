<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/clases/Conexion.php';

/**
 * Datos de la persona que inició sesión: perfil, actividad, cambio de clave y foto.
 * Todo trabaja SIEMPRE con el id de la sesión (nunca con un id enviado por el navegador).
 */
final class MiCuenta
{
    public const FOTO_MAX_BYTES = 3 * 1024 * 1024;   // archivo subido
    public const FOTO_LADO = 320;                    // se guarda cuadrada, 320 px, JPEG

    private Conexion $db;

    public function __construct(Conexion $db)
    {
        $this->db = $db;
    }

    /** Ficha de la persona (sin la clave ni la foto). */
    public function usuario(int $id): ?array
    {
        $u = $this->db->fetchOne(
            "SELECT u.id, u.nombre, u.apellido_paterno, u.apellido_materno, u.email, u.telefono, u.anexo,
                    u.fecha_nacimiento, u.cargo, u.estado, u.fecha_creacion, u.es_admin_global,
                    (u.foto IS NOT NULL AND LENGTH(u.foto) > 0) AS tiene_foto,
                    a.nombre_area
               FROM usuarios u
          LEFT JOIN area_trabajo a ON a.id_area = u.id_area_trabajo
              WHERE u.id = ?",
            [$id]
        );
        if (!$u) {
            return null;
        }
        $u['nombre_completo'] = trim(preg_replace('/\s+/u', ' ', $u['nombre'] . ' ' . $u['apellido_paterno'] . ' ' . $u['apellido_materno']));
        $u['fecha_nacimiento'] = self::fechaValida($u['fecha_nacimiento']);
        return $u;
    }

    /** Lo que necesitan las fuentes de RRHH (ver FuenteRrhh). */
    public function persona(int $id): array
    {
        $u = $this->usuario($id) ?? ['nombre_completo' => 'Usuario', 'email' => '', 'cargo' => '', 'nombre_area' => ''];
        $rrhh = null;
        try {
            $rrhh = $this->db->fetchOne('SELECT rut, id_empleado_buk FROM usuario_rrhh WHERE id_usuario = ?', [$id]);
        } catch (Throwable $e) {
            // la tabla es opcional (mi_cuenta/sql/01_usuario_rrhh.sql)
        }
        return [
            'id' => $id,
            'nombre' => $u['nombre_completo'],
            'email' => (string) ($u['email'] ?? ''),
            'cargo' => (string) ($u['cargo'] ?? ''),
            'area' => (string) ($u['nombre_area'] ?? ''),
            'rut' => $rrhh['rut'] ?? null,
            'id_buk' => $rrhh['id_empleado_buk'] ?? null,
        ];
    }

    /** Colegios y perfil en cada uno. */
    public function colegios(int $id): array
    {
        return $this->db->fetchAll(
            "SELECT c.id_colegio, c.nom_colegio, p.nombre_perfil, uc.fecha_asignacion
               FROM usuario_colegio uc
               JOIN colegio c       ON c.id_colegio = uc.id_colegio
          LEFT JOIN perfiles p      ON p.id_perfil = uc.id_perfil
              WHERE uc.id_usuario = ? AND uc.estado = 1
           ORDER BY c.nom_colegio",
            [$id]
        );
    }

    /** Números para "Mi actividad". Cada bloque es opcional (si una tabla falta, no se muestra). */
    public function actividad(int $id): array
    {
        $r = [];
        $uno = function (string $sql, array $p) {
            try {
                return (int) ($this->db->fetchOne($sql, $p)['n'] ?? 0);
            } catch (Throwable $e) {
                return null;
            }
        };
        $r['tickets_creados'] = $uno('SELECT COUNT(*) AS n FROM tickets WHERE id_usuario = ? AND estado = 1', [$id]);
        $r['tickets_atendidos'] = $uno('SELECT COUNT(*) AS n FROM tickets WHERE id_tecnico = ? AND estado = 1', [$id]);
        $r['almuerzos_mes'] = $uno(
            "SELECT COUNT(*) AS n FROM almuerzo_eleccion e JOIN almuerzo_dia d ON d.id_dia_menu = e.id_dia_menu
              WHERE e.id_usuario = ? AND e.id_menu IS NOT NULL AND DATE_FORMAT(d.fecha, '%Y-%m') = ?",
            [$id, (new DateTimeImmutable('now', new DateTimeZone('America/Santiago')))->format('Y-m')]
        );
        return array_filter($r, static fn ($v) => $v !== null);
    }

    /** Solo teléfono, anexo y fecha de nacimiento: nombre, correo y cargo los cambia un administrador. */
    public function actualizarDatos(int $id, string $telefono, string $anexo, string $nacimiento): void
    {
        $telefono = preg_replace('/[^\d+ ]/', '', trim($telefono)) ?? '';
        $anexo = preg_replace('/\D/', '', trim($anexo)) ?? '';
        if (strlen(str_replace([' ', '+'], '', $telefono)) > 0 && !preg_match('/^\+?[\d ]{8,15}$/', $telefono)) {
            throw new RuntimeException('El teléfono debe tener entre 8 y 15 dígitos.');
        }
        if (strlen($telefono) > 15) {
            throw new RuntimeException('El teléfono es demasiado largo.');
        }
        if (strlen($anexo) > 10) {
            throw new RuntimeException('El anexo es demasiado largo.');
        }
        $fecha = null;
        if ($nacimiento !== '') {
            $d = DateTimeImmutable::createFromFormat('!Y-m-d', $nacimiento);
            $hoy = new DateTimeImmutable('today');
            if (!$d || $d->format('Y-m-d') !== $nacimiento || $d > $hoy->modify('-15 years') || $d < $hoy->modify('-100 years')) {
                throw new RuntimeException('La fecha de nacimiento no es válida.');
            }
            $fecha = $nacimiento;
        }
        $this->db->execute('UPDATE usuarios SET telefono = ?, anexo = ?, fecha_nacimiento = ? WHERE id = ?',
            [$telefono, $anexo, $fecha, $id]);
    }

    /** Requisitos de la clave (los mismos que muestra la pantalla). */
    public static function erroresClave(string $nueva): array
    {
        $e = [];
        if (mb_strlen($nueva) < 8) { $e[] = 'al menos 8 caracteres'; }
        if (!preg_match('/[A-Za-zÁÉÍÓÚÑáéíóúñ]/u', $nueva)) { $e[] = 'una letra'; }
        if (!preg_match('/\d/', $nueva)) { $e[] = 'un número'; }
        if (mb_strlen($nueva) > 72) { $e[] = 'máximo 72 caracteres'; }
        return $e;
    }

    public function cambiarClave(int $id, string $actual, string $nueva, string $repetir): void
    {
        $u = $this->db->fetchOne('SELECT clave FROM usuarios WHERE id = ?', [$id]);
        if (!$u || !password_verify($actual, (string) $u['clave'])) {
            throw new RuntimeException('La contraseña actual no es correcta.');
        }
        if ($nueva !== $repetir) {
            throw new RuntimeException('La nueva contraseña y su confirmación no coinciden.');
        }
        if ($e = self::erroresClave($nueva)) {
            throw new RuntimeException('La nueva contraseña debe tener ' . implode(', ', $e) . '.');
        }
        if (password_verify($nueva, (string) $u['clave'])) {
            throw new RuntimeException('La nueva contraseña debe ser distinta de la actual.');
        }
        $this->db->execute('UPDATE usuarios SET clave = ?, intentos_fallidos = 0, token_reinicio = NULL WHERE id = ?',
            [password_hash($nueva, PASSWORD_DEFAULT), $id]);
    }

    /** Guarda la foto: valida que sea imagen, la recorta cuadrada y la guarda como JPEG. */
    public function guardarFoto(int $id, array $archivo): void
    {
        if (($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file((string) $archivo['tmp_name'])) {
            throw new RuntimeException('No se recibió la imagen.');
        }
        if ((int) $archivo['size'] > self::FOTO_MAX_BYTES) {
            throw new RuntimeException('La imagen supera los 3 MB.');
        }
        $info = @getimagesize((string) $archivo['tmp_name']);
        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw new RuntimeException('La foto debe ser JPG, PNG o WEBP.');
        }
        if (!function_exists('imagecreatefromstring')) {
            throw new RuntimeException('El servidor no puede procesar imágenes (falta GD).');
        }
        $img = @imagecreatefromstring((string) file_get_contents((string) $archivo['tmp_name']));
        if (!$img) {
            throw new RuntimeException('No fue posible leer la imagen.');
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $lado = min($w, $h);
        $dest = imagecreatetruecolor(self::FOTO_LADO, self::FOTO_LADO);
        imagefill($dest, 0, 0, imagecolorallocate($dest, 255, 255, 255));
        imagecopyresampled($dest, $img, 0, 0, (int) (($w - $lado) / 2), (int) (($h - $lado) / 2), self::FOTO_LADO, self::FOTO_LADO, $lado, $lado);
        // La columna usuarios.foto es BLOB (máx. 64 KB): se baja la calidad hasta que quepa
        foreach ([85, 75, 65, 55, 45] as $calidad) {
            ob_start();
            imagejpeg($dest, null, $calidad);
            $jpg = (string) ob_get_clean();
            if (strlen($jpg) < 60000) {
                break;
            }
        }
        imagedestroy($img);
        imagedestroy($dest);
        $st = $this->db->getPDO()->prepare('UPDATE usuarios SET foto = ? WHERE id = ?');
        $st->bindValue(1, $jpg, PDO::PARAM_LOB);
        $st->bindValue(2, $id, PDO::PARAM_INT);
        $st->execute();
    }

    public function quitarFoto(int $id): void
    {
        $this->db->execute('UPDATE usuarios SET foto = NULL WHERE id = ?', [$id]);
    }

    public function foto(int $id): ?string
    {
        $f = $this->db->fetchOne('SELECT foto FROM usuarios WHERE id = ?', [$id]);
        $b = $f['foto'] ?? null;
        return is_string($b) && $b !== '' ? $b : null;
    }

    private static function fechaValida(?string $f): ?string
    {
        if (!$f || $f === '0000-00-00' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)) {
            return null;
        }
        return $f;
    }
}
