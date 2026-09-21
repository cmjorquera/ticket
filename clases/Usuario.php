<?php

require_once __DIR__ . '/Conexion.php';

class Usuario
{
    private Conexion $db;

    public function __construct(string $bd = 'sistema_panel_central')
    {
        $this->db = new Conexion($bd);
    }

    public function login(string $correo, string $password): array|false
    {
        $usuario = $this->db->fetchOne(
            "SELECT * FROM usuarios WHERE correo = ? AND activo = 1 LIMIT 1",
            [$correo]
        );

        if (!$usuario) {
            return false;
        }

        if (!password_verify($password, $usuario['password'])) {
            return false;
        }

        unset($usuario['password']);
        return $usuario;
    }

    public function existe(string $correo): bool
    {
        $resultado = $this->db->fetchOne(
            "SELECT id FROM usuarios WHERE correo = ? LIMIT 1",
            [$correo]
        );
        return $resultado !== false;
    }

    public function getById(int $id): array|false
    {
        $usuario = $this->db->fetchOne(
            "SELECT id, nombre, apellido, correo, rol, activo, created_at FROM usuarios WHERE id = ? LIMIT 1",
            [$id]
        );
        return $usuario;
    }

    public function getAll(): array
    {
        return $this->db->fetchAll(
            "SELECT id, nombre, apellido, correo, rol, activo, created_at FROM usuarios ORDER BY nombre ASC"
        );
    }

    public static function listar(Conexion $db, array $filtros = []): array
    {
        try {
            $where = [];
            $parametros = [];

            $estado = trim((string) ($filtros['estado'] ?? ''));
            if ($estado !== '') {
                $where[] = 'u.estado = ?';
                $parametros[] = $estado;
            }

            $idArea = (int) ($filtros['id_area'] ?? 0);
            if ($idArea > 0) {
                $where[] = 'u.id_area_trabajo = ?';
                $parametros[] = $idArea;
            }

            $idColegio = (int) ($filtros['id_colegio'] ?? 0);
            if ($idColegio > 0) {
                $where[] = 'jd.id_colegio = ?';
                $parametros[] = $idColegio;
            }

            if (array_key_exists('id_colegios_permitidos', $filtros)) {
                $colegiosPermitidos = array_values(array_unique(array_filter(
                    array_map('intval', (array) $filtros['id_colegios_permitidos']),
                    static fn (int $id): bool => $id > 0
                )));
                if ($colegiosPermitidos === []) {
                    $where[] = '1 = 0';
                } else {
                    $where[] = 'jd.id_colegio IN (' . implode(',', array_fill(0, count($colegiosPermitidos), '?')) . ')';
                    array_push($parametros, ...$colegiosPermitidos);
                }
            }

            $buscar = trim((string) ($filtros['buscar'] ?? ''));
            if ($buscar !== '') {
                $where[] = '(u.nombre LIKE ?
                    OR u.apellido_paterno LIKE ?
                    OR u.apellido_materno LIKE ?
                    OR u.email LIKE ?
                    OR at.nombre_area LIKE ?
                    OR p.nombre LIKE ?
                    OR dc.nombre_departamento LIKE ?
                    OR col.nom_colegio LIKE ?)';
                $termino = '%' . $buscar . '%';
                array_push($parametros, $termino, $termino, $termino, $termino, $termino, $termino, $termino, $termino);
            }

            $sql = "SELECT
                        u.id,
                        u.nombre,
                        u.apellido_paterno,
                        u.apellido_materno,
                        u.email,
                        u.telefono,
                        u.cargo,
                        u.estado,
                        u.id_area_trabajo,
                        at.nombre_area,
                        at.sigla_area,
                        GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.id_perfil SEPARATOR ',') AS perfiles,
                        MAX(jd.id_departamento_colegio) AS id_departamento_colegio,
                        MAX(dc.nombre_departamento) AS nombre_departamento,
                        MAX(jd.tipo_jefatura) AS tipo_jefatura,
                        jd.id_colegio,
                        col.nom_colegio,
                        u.fecha_creacion,
                        u.sexo
                    FROM usuarios u
                    LEFT JOIN area_trabajo at ON at.id_area = u.id_area_trabajo
                    LEFT JOIN usuario_perfil up ON up.id_usuario = u.id
                    LEFT JOIN perfiles p ON p.id_perfil = up.id_perfil AND p.estado = 1
                    LEFT JOIN jefatura_departamento jd
                           ON jd.id_usuario = u.id
                          AND jd.estado = 1
                    LEFT JOIN departamentos_colegio dc
                           ON dc.id = jd.id_departamento_colegio
                    LEFT JOIN colegio col ON col.id_colegio = jd.id_colegio";

            if ($where !== []) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }

            $sql .= ' GROUP BY u.id, u.nombre, u.apellido_paterno, u.apellido_materno,
                               u.email, u.telefono, u.cargo, u.estado, u.id_area_trabajo,
                               at.nombre_area, at.sigla_area, jd.id_colegio, col.nom_colegio,
                               u.fecha_creacion, u.sexo
                      ORDER BY col.nom_colegio ASC, u.nombre ASC, u.apellido_paterno ASC';

            $filasUsuarios = $db->fetchAll($sql, $parametros);

            $resultado = [];
            foreach ($filasUsuarios as $fila) {
                $nombreArea = trim((string) ($fila['nombre_area'] ?? ''));
                $estadoUsuario = trim((string) ($fila['estado'] ?? ''));
                $perfiles = array_values(array_filter(array_map(
                    'trim',
                    explode(',', (string) ($fila['perfiles'] ?? ''))
                )));
                $colegioUsuario = trim((string) ($fila['nom_colegio'] ?? ''));

                $resultado[] = [
                    'id'               => (int) ($fila['id'] ?? 0),
                    'nombre'           => (string) ($fila['nombre'] ?? ''),
                    'apellido_paterno' => (string) ($fila['apellido_paterno'] ?? ''),
                    'apellido_materno' => (string) ($fila['apellido_materno'] ?? ''),
                    'email'            => (string) ($fila['email'] ?? ''),
                    'telefono'         => (string) ($fila['telefono'] ?? ''),
                    'cargo'            => (string) ($fila['cargo'] ?? ''),
                    'estado'           => $estadoUsuario !== '' ? $estadoUsuario : 'Activo',
                    'id_area_trabajo'  => (int) ($fila['id_area_trabajo'] ?? 0),
                    'nombre_area'      => $nombreArea !== '' ? $nombreArea : 'Sin área',
                    'sigla_area'       => (string) ($fila['sigla_area'] ?? ''),
                    'fecha_creacion'   => (string) ($fila['fecha_creacion'] ?? ''),
                    'sexo'             => (int) ($fila['sexo'] ?? 0),
                    'perfiles'         => $perfiles,
                    'id_departamento_colegio' => (int) ($fila['id_departamento_colegio'] ?? 0),
                    'nombre_departamento' => trim((string) ($fila['nombre_departamento'] ?? '')) ?: '—',
                    'tipo_jefatura'  => trim((string) ($fila['tipo_jefatura'] ?? '')),
                    'id_colegio'       => (int) ($fila['id_colegio'] ?? 0),
                    'nom_colegio'      => $colegioUsuario ?: 'Sin colegio',
                ];
            }

            return $resultado;
        } catch (Throwable $e) {
            error_log('No fue posible listar los usuarios: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Devuelve null cuando el usuario tiene alcance global. Para un
     * admin_colegio devuelve exclusivamente los colegios que administra.
     *
     * @return int[]|null
     */
    public static function colegiosPermitidosPara(Conexion $db, int $idUsuario): ?array
    {
        $perfiles = $db->fetchAll(
            'SELECT p.id_perfil, p.nombre
               FROM usuario_perfil up
               JOIN perfiles p ON p.id_perfil = up.id_perfil
              WHERE up.id_usuario = ? AND p.estado = 1',
            [$idUsuario]
        );

        $esSuperAdmin = false;
        $esAdminColegio = false;
        foreach ($perfiles as $perfil) {
            $idPerfil = (int) ($perfil['id_perfil'] ?? 0);
            $nombre = strtolower(trim((string) ($perfil['nombre'] ?? '')));
            $esSuperAdmin = $esSuperAdmin || $idPerfil === 3 || in_array($nombre, ['super_admin', 'super admin', 'superadmin'], true);
            $esAdminColegio = $esAdminColegio || $idPerfil === 4
                || (str_contains($nombre, 'admin') && str_contains($nombre, 'colegio'));
        }

        if ($esSuperAdmin || !$esAdminColegio) {
            return null;
        }

        $filas = $db->fetchAll(
            "SELECT DISTINCT jd.id_colegio
               FROM jefatura_departamento jd
              WHERE jd.id_usuario = ?
                AND jd.tipo_jefatura = 'Admin_Colegio'
                AND jd.id_departamento_colegio IS NULL
                AND jd.estado = 1
                AND jd.id_colegio > 0",
            [$idUsuario]
        );

        return array_values(array_unique(array_filter(
            array_map('intval', array_column($filas, 'id_colegio')),
            static fn (int $id): bool => $id > 0
        )));
    }

    public function crear(array $datos): int|false
    {
        if (empty($datos['correo']) || empty($datos['password'])) {
            return false;
        }

        if ($this->existe($datos['correo'])) {
            return false;
        }

        $this->db->execute(
            "INSERT INTO usuarios (nombre, apellido, correo, password, rol, activo, created_at)
             VALUES (?, ?, ?, ?, ?, 1, NOW())",
            [
                $datos['nombre']   ?? '',
                $datos['apellido'] ?? '',
                $datos['correo'],
                password_hash($datos['password'], PASSWORD_BCRYPT),
                $datos['rol']      ?? 'usuario',
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    public function actualizar(int $id, array $datos): bool
    {
        $campos = [];
        $valores = [];

        $permitidos = ['nombre', 'apellido', 'correo', 'rol', 'activo'];
        foreach ($permitidos as $campo) {
            if (isset($datos[$campo])) {
                $campos[]  = "$campo = ?";
                $valores[] = $datos[$campo];
            }
        }

        if (!empty($datos['password'])) {
            $campos[]  = "password = ?";
            $valores[] = password_hash($datos['password'], PASSWORD_BCRYPT);
        }

        if (empty($campos)) {
            return false;
        }

        $valores[] = $id;
        $filas = $this->db->execute(
            "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?",
            $valores
        );

        return $filas > 0;
    }

    public function eliminar(int $id): bool
    {
        $filas = $this->db->execute(
            "UPDATE usuarios SET activo = 0 WHERE id = ?",
            [$id]
        );
        return $filas > 0;
    }
}
