<?php
declare(strict_types=1);

final class Usuarios
{
    /**
     * Lista usuarios con sus relaciones activas. Los filtros admitidos son:
     * estado, id_area, id_colegio y buscar.
     */
    public static function listar(Conexion $db, array $filtros = []): array
    {
        $where = [];
        $params = [];

        if (!empty($filtros['estado'])) {
            $where[] = 'u.estado = ?';
            $params[] = (string) $filtros['estado'];
        }
        if (!empty($filtros['id_area'])) {
            $where[] = 'u.id_area_trabajo = ?';
            $params[] = (int) $filtros['id_area'];
        }
        if (!empty($filtros['id_colegio'])) {
            $where[] = 'uc.id_colegio = ?';
            $params[] = (int) $filtros['id_colegio'];
        }
        if (!empty($filtros['buscar'])) {
            $where[] = "CONCAT_WS(' ', u.nombre, u.apellido_paterno, u.apellido_materno, u.email, c.nom_colegio) LIKE ?";
            $params[] = '%' . trim((string) $filtros['buscar']) . '%';
        }

        $sql = "SELECT u.id, u.nombre, u.apellido_paterno, u.apellido_materno,
                       u.email, u.estado, u.telefono, u.sexo, u.id_area_trabajo,
                       at.nombre_area,
                       uc.id_colegio, c.nom_colegio, c.logo,
                       COALESCE(uc.es_admin_colegio, 0) AS es_admin_colegio
                  FROM usuarios u
             LEFT JOIN area_trabajo at ON at.id_area = u.id_area_trabajo
             LEFT JOIN usuario_colegio uc ON uc.id_usuario = u.id AND uc.estado = 1
             LEFT JOIN colegio c ON c.id_colegio = uc.id_colegio";

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }
        $sql .= ' ORDER BY u.nombre ASC, u.apellido_paterno ASC, c.nom_colegio ASC';

        $filas = $db->fetchAll($sql, $params);
        $usuarios = [];

        foreach ($filas as $fila) {
            $id = (int) $fila['id'];
            if (!isset($usuarios[$id])) {
                $fila['id'] = $id;
                $fila['id_area_trabajo'] = isset($fila['id_area_trabajo']) ? (int) $fila['id_area_trabajo'] : null;
                $fila['id_colegio'] = isset($fila['id_colegio']) ? (int) $fila['id_colegio'] : null;
                $fila['es_admin_colegio'] = (int) $fila['es_admin_colegio'];
                $fila['colegios'] = [];
                $usuarios[$id] = $fila;
            }

            if (!empty($fila['id_colegio'])) {
                $usuarios[$id]['colegios'][] = [
                    'id_colegio' => (int) $fila['id_colegio'],
                    'nom_colegio' => (string) ($fila['nom_colegio'] ?? ''),
                    'logo' => (string) ($fila['logo'] ?? ''),
                    'es_admin_colegio' => (int) $fila['es_admin_colegio'],
                ];
            }
        }

        foreach ($usuarios as &$usuario) {
            $usuario['nom_colegio'] = implode(', ', array_column($usuario['colegios'], 'nom_colegio'));
        }
        unset($usuario);

        return array_values($usuarios);
    }
}

