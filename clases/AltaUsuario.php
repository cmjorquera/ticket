<?php
declare(strict_types=1);

require_once __DIR__ . '/Conexion.php';
require_once __DIR__ . '/RolesUsuario.php';
require_once __DIR__ . '/ActivacionCuenta.php';
require_once dirname(__DIR__) . '/helpers/permisos.php';

/**
 * Crea una cuenta con el modelo de roles (configuracion/logica/LOGICA_ROLES.txt):
 *   usuarios + usuario_perfil (usuario [+ técnico] [+ super_admin])
 *   + usuario_colegio (colegio, departamento, rol) + permisos de menú iniciales.
 *
 * La cuenta queda 'Pendiente' y sin clave: se envía un correo con un ENLACE
 * para que la persona elija su contraseña (clases/ActivacionCuenta.php,
 * activar_cuenta.php). Si el correo no sale, quien la creó ve el enlace.
 */
final class AltaUsuario
{
    private Conexion $db;
    private RolesUsuario $roles;

    public function __construct(Conexion $db)
    {
        $this->db = $db;
        $this->roles = new RolesUsuario($db);
    }

    /** 1 masculino, 2 femenino, 0 sin dato (también acepta M/F). */
    public static function sexo(mixed $v): int
    {
        $v = strtolower(trim((string) $v));
        return match ($v) {
            '1', 'm', 'masculino' => 1,
            '2', 'f', 'femenino' => 2,
            default => 0,
        };
    }

    /**
     * @return array{id:int, nombre:string, email:string, enlace:string, correo_enviado:bool, motivo_correo:string}
     * @throws RuntimeException con un mensaje para mostrar
     */
    public function crear(int $actor, array $d): array
    {
        if (!$this->roles->modeloActivo()) {
            throw new RuntimeException('Falta ejecutar configuracion/sql/01_roles_estructura.sql y 02_roles_asignaciones.sql.');
        }
        $txt = static fn (string $k, int $max) => mb_substr(trim(preg_replace('/\s+/u', ' ', (string) ($d[$k] ?? '')) ?? ''), 0, $max);
        $nombre = $txt('nombre', 100);
        $paterno = $txt('apellido_paterno', 100);
        $materno = $txt('apellido_materno', 100);
        $email = strtolower(trim((string) ($d['email'] ?? '')));
        $telefono = preg_replace('/[^\d+ ]/', '', (string) ($d['telefono'] ?? '')) ?? '';
        $cargo = $txt('cargo', 100);
        $anexo = preg_replace('/\D/', '', (string) ($d['anexo'] ?? '')) ?? '';
        $sexo = self::sexo($d['sexo'] ?? '');
        $idColegio = (int) ($d['id_colegio'] ?? 0);
        $idDep = (int) ($d['id_departamento_colegio'] ?? 0) ?: null;
        $rol = (string) ($d['rol'] ?? '');
        $tecnico = filter_var($d['tecnico'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $super = filter_var($d['super_admin'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // --- datos personales
        if ($nombre === '' || $paterno === '') {
            throw new RuntimeException('Nombre y apellido paterno son obligatorios.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            throw new RuntimeException('El email no es válido.');
        }
        if ($this->db->fetchOne('SELECT id FROM usuarios WHERE LOWER(TRIM(email)) = ? LIMIT 1', [$email])) {
            throw new RuntimeException('Ya existe un usuario con ese email.');
        }
        if (strlen($telefono) > 15 || strlen($anexo) > 10) {
            throw new RuntimeException('El teléfono o el anexo son demasiado largos.');
        }

        // --- asignación: lo que el actor puede crear
        if (!isset(RolesUsuario::ROLES[$rol])) {
            throw new RuntimeException('Elige el cargo del usuario.');
        }
        if (!$this->db->fetchOne('SELECT 1 FROM colegio WHERE id_colegio = ? AND estado = 1', [$idColegio])) {
            throw new RuntimeException('El colegio elegido no está disponible.');
        }
        if ($rol === 'admin_colegio') {
            $idDep = null;
        } elseif ($idDep === null) {
            throw new RuntimeException('Elige el departamento.');
        } elseif (!$this->db->fetchOne('SELECT 1 FROM departamentos_colegio WHERE id = ? AND id_colegio = ? AND estado = 1', [$idDep, $idColegio])) {
            throw new RuntimeException('Ese departamento no pertenece al colegio elegido.');
        }
        if (!in_array($rol, $this->roles->rolesPermitidos($actor, $idColegio, $idDep), true)) {
            throw new RuntimeException('No tienes permiso para crear un "' . RolesUsuario::ROLES[$rol] . '" ahí.');
        }
        if ($super && !$this->roles->esSuperAdmin($actor)) {
            throw new RuntimeException('Solo un super administrador puede crear otro super administrador.');
        }

        // --- crear
        $ahora = (new DateTimeImmutable('now', new DateTimeZone('America/Santiago')))->format('Y-m-d H:i:s');
        $pdo = $this->db->getPDO();
        $pdo->beginTransaction();
        try {
            $this->db->execute(
                "INSERT INTO usuarios
                    (nombre, apellido_paterno, apellido_materno, email, telefono, sexo, clave, cargo,
                     intentos_fallidos, id_area_trabajo, estado, anexo, token_reinicio, identificador, es_admin_global)
                 VALUES (?, ?, ?, ?, ?, ?, NULL, ?, 0, NULL, 'Pendiente', ?, NULL, ?, ?)",
                [$nombre, $paterno, $materno, $email, $telefono, $sexo, $cargo,
                 $anexo, substr(bin2hex(random_bytes(8)), 0, 16), $super ? 1 : 0]
            );
            $id = (int) $this->db->lastInsertId();

            $perfiles = [RolesUsuario::PERFIL_USUARIO];
            if ($tecnico) { $perfiles[] = RolesUsuario::PERFIL_TECNICO; }
            if ($super) { $perfiles[] = RolesUsuario::PERFIL_SUPER; }
            foreach ($perfiles as $p) {
                $this->db->execute('INSERT INTO usuario_perfil (id_usuario, id_perfil) VALUES (?, ?)', [$id, $p]);
            }

            // id_perfil: compatibilidad con Inventario (3 ve todos los colegios, 2 elige entre los suyos, 1 uno solo)
            $perfilCompat = $super ? 3 : ($rol === 'admin_colegio' ? 2 : 1);
            $this->db->execute(
                'INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion)
                 VALUES (?, ?, ?, ?, ?, 1, ?, ?)',
                [$id, $idColegio, $idDep, $rol, $perfilCompat, $actor, $ahora]
            );
            // Compatibilidad con pantallas que aún leen jefatura_departamento
            if ($rol !== 'trabajador') {
                $this->db->execute(
                    'INSERT INTO jefatura_departamento (id_usuario, id_colegio, id_departamento_colegio, tipo_jefatura, estado, fecha_asignacion)
                     VALUES (?, ?, ?, ?, 1, ?)',
                    [$id, $idColegio, $idDep, $rol === 'admin_colegio' ? 'Admin_Colegio' : 'Admin_Departamento', $ahora]
                );
            }

            // Menús iniciales según lo más alto que tenga
            $perfilMenus = $super ? 3 : ($rol === 'admin_colegio' ? 4 : ($rol === 'jefe_departamento' ? 5 : ($tecnico ? 2 : 1)));
            if (!asignar_permisos_por_defecto($id, $perfilMenus, $this->db)) {
                throw new RuntimeException('No fue posible asignar los permisos de menú iniciales.');
            }
            $activacion = new ActivacionCuenta($this->db);
            $enlace = $activacion->generar($id);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
        // Correo con el enlace (fuera de la transacción: si falla, la cuenta igual queda creada)
        $col = $this->db->fetchOne('SELECT nom_colegio FROM colegio WHERE id_colegio = ?', [$idColegio]);
        $dep = $idDep ? $this->db->fetchOne('SELECT nombre_departamento FROM departamentos_colegio WHERE id = ?', [$idDep]) : null;
        $donde = trim(($dep ? mb_convert_case(mb_strtolower((string) $dep['nombre_departamento']), MB_CASE_TITLE) . ' · ' : '') . (string) ($col['nom_colegio'] ?? ''));
        $correo = ActivacionCuenta::enviarCorreo($email, trim("$nombre $paterno"), $donde, $enlace);
        return ['id' => $id, 'nombre' => trim("$nombre $paterno"), 'email' => $email, 'enlace' => $enlace,
                'correo_enviado' => $correo['enviado'], 'motivo_correo' => $correo['motivo']];
    }
}
