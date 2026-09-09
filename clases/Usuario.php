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

            $buscar = trim((string) ($filtros['buscar'] ?? ''));
            if ($buscar !== '') {
                $where[] = '(u.nombre LIKE ?
                    OR u.apellido_paterno LIKE ?
                    OR u.apellido_materno LIKE ?
                    OR u.email LIKE ?
                    OR at.nombre_area LIKE ?)';
                $termino = '%' . $buscar . '%';
                array_push($parametros, $termino, $termino, $termino, $termino, $termino);
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
                        u.fecha_creacion,
                        u.sexo
                    FROM usuarios u
                    LEFT JOIN area_trabajo at ON at.id_area = u.id_area_trabajo";

            if ($where !== []) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }

            $sql .= ' ORDER BY u.nombre ASC, u.apellido_paterno ASC';

            $resultado = [];
            foreach ($db->fetchAll($sql, $parametros) as $fila) {
                $nombreArea = trim((string) ($fila['nombre_area'] ?? ''));
                $estadoUsuario = trim((string) ($fila['estado'] ?? ''));

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
                    'nom_colegio'      => 'Sin colegio',
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
