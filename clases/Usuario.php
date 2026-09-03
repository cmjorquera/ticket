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
