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

    public static function listar(Conexion $db, array $filtros = [], ?Conexion $dbPermisos = null): array
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
                $where[] = 'uc.id_colegio = ?';
                $parametros[] = $idColegio;
            }

            $buscar = trim((string) ($filtros['buscar'] ?? ''));
            if ($buscar !== '') {
                $where[] = '(u.nombre LIKE ?
                    OR u.apellido_paterno LIKE ?
                    OR u.apellido_materno LIKE ?
                    OR u.email LIKE ?
                    OR at.nombre_area LIKE ?
                    OR col.nom_colegio LIKE ?)';
                $termino = '%' . $buscar . '%';
                array_push($parametros, $termino, $termino, $termino, $termino, $termino, $termino);
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
                        uc.id_colegio,
                        col.nom_colegio,
                        u.fecha_creacion,
                        u.sexo
                    FROM usuarios u
                    LEFT JOIN area_trabajo at ON at.id_area = u.id_area_trabajo
                    LEFT JOIN (
                        SELECT id_usuario, MIN(id_colegio) AS id_colegio
                          FROM usuario_colegio
                         WHERE estado = 1
                      GROUP BY id_usuario
                    ) uc ON uc.id_usuario = u.id
                    LEFT JOIN colegio col ON col.id_colegio = uc.id_colegio";

            if ($where !== []) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }

            $sql .= ' ORDER BY u.nombre ASC, u.apellido_paterno ASC';

            $permisosPorUsuario = [];
            if ($dbPermisos !== null) {
                try {
                    $filasPermisos = $dbPermisos->fetchAll(
                        "SELECT u.id AS id_usuario,
                                GROUP_CONCAT(DISTINCT p.nombre ORDER BY p.id_perfil SEPARATOR ',') AS perfiles,
                                MAX(j.id_departamento) AS id_departamento,
                                MAX(j.id_colegio) AS id_colegio_jefatura,
                                MAX(d.nombre_departamento) AS nombre_departamento,
                                MAX(c.nom_colegio) AS nom_colegio_jefatura
                           FROM usuarios u
                      LEFT JOIN usuario_perfil up
                             ON up.id_usuario = u.id AND up.estado = 1
                      LEFT JOIN perfiles p
                             ON p.id_perfil = up.id_perfil AND p.estado = 1
                      LEFT JOIN jefatura_departamento j
                             ON j.id_usuario = u.id AND j.estado = 1
                      LEFT JOIN departamentos d
                             ON d.id_departamento = j.id_departamento
                      LEFT JOIN colegio c
                             ON c.id_colegio = j.id_colegio
                       GROUP BY u.id"
                    );
                    foreach ($filasPermisos as $filaPermisos) {
                        $id = (int) ($filaPermisos['id_usuario'] ?? 0);
                        if ($id > 0) {
                            $permisosPorUsuario[$id] = $filaPermisos;
                        }
                    }
                } catch (Throwable $e) {
                    error_log('No fue posible anexar perfiles al listado: ' . $e->getMessage());
                }
            }

            $resultado = [];
            foreach ($db->fetchAll($sql, $parametros) as $fila) {
                $datosPermisos = $permisosPorUsuario[(int) ($fila['id'] ?? 0)] ?? [];
                $nombreArea = trim((string) ($fila['nombre_area'] ?? ''));
                $estadoUsuario = trim((string) ($fila['estado'] ?? ''));
                $perfiles = array_values(array_filter(array_map(
                    'trim',
                    explode(',', (string) ($datosPermisos['perfiles'] ?? ''))
                )));
                $colegioJefatura = trim((string) ($datosPermisos['nom_colegio_jefatura'] ?? ''));
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
                    'id_departamento'  => (int) ($datosPermisos['id_departamento'] ?? 0),
                    'nombre_departamento' => trim((string) ($datosPermisos['nombre_departamento'] ?? '')) ?: '—',
                    'id_colegio'       => (int) ($datosPermisos['id_colegio_jefatura'] ?? $fila['id_colegio'] ?? 0),
                    'nom_colegio'      => $colegioJefatura ?: ($colegioUsuario ?: 'Sin colegio'),
                ];
            }

            return $resultado;
        } catch (Throwable $e) {
            error_log('No fue posible listar los usuarios: ' . $e->getMessage());
            return [];
        }
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
