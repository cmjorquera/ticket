<?php
declare(strict_types=1);

class Usuarios
{
    public static function listar(Conexion $db, array $filtros = []): array
    {
        $where      = [];
        $parametros = [];

        // Filtro estado
        $estado = trim((string) ($filtros['estado'] ?? ''));
        if ($estado !== '') {
            $where[]      = 'u.estado = ?';
            $parametros[] = $estado;
        }

        // Filtro área
        $idArea = (int) ($filtros['id_area'] ?? 0);
        if ($idArea > 0) {
            $where[]      = 'u.id_area_trabajo = ?';
            $parametros[] = $idArea;
        }

        // Búsqueda libre
        $buscar = trim((string) ($filtros['buscar'] ?? ''));
        if ($buscar !== '') {
            $where[] = '(u.nombre LIKE ?
                OR u.apellido_paterno LIKE ?
                OR u.apellido_materno LIKE ?
                OR u.email LIKE ?
                OR at.nombre_area LIKE ?
                OR p.nombre_perfil LIKE ?)';
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
                    u.fecha_creacion,
                    u.sexo,
                    GROUP_CONCAT(DISTINCT p.nombre_perfil ORDER BY p.id_perfil SEPARATOR ',') AS perfiles,
                    GROUP_CONCAT(DISTINCT p.id_perfil    ORDER BY p.id_perfil SEPARATOR ',') AS perfil_ids
                FROM usuarios u
                LEFT JOIN area_trabajo at
                       ON at.id_area = u.id_area_trabajo
                LEFT JOIN usuario_perfil up
                       ON up.id_usuario = u.id
                LEFT JOIN perfiles p
                       ON p.id_perfil = up.id_perfil";

        if ($where !== []) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY u.id, u.nombre, u.apellido_paterno, u.apellido_materno,
                           u.email, u.telefono, u.cargo, u.estado, u.id_area_trabajo,
                           at.nombre_area, at.sigla_area, u.fecha_creacion, u.sexo
                  ORDER BY u.nombre ASC, u.apellido_paterno ASC';

        $filas = $db->fetchAll($sql, $parametros);

        $resultado = [];
        foreach ($filas as $fila) {
            $perfiles = array_values(array_filter(
                array_map('trim', explode(',', (string) ($fila['perfiles'] ?? '')))
            ));
            $perfilIds = array_values(array_unique(array_filter(
                array_map('intval', explode(',', (string) ($fila['perfil_ids'] ?? ''))),
                static fn (int $id): bool => $id > 0
            )));

            $resultado[] = [
                'id'               => (int)    ($fila['id']               ?? 0),
                'nombre'           => (string) ($fila['nombre']           ?? ''),
                'apellido_paterno' => (string) ($fila['apellido_paterno'] ?? ''),
                'apellido_materno' => (string) ($fila['apellido_materno'] ?? ''),
                'email'            => (string) ($fila['email']            ?? ''),
                'telefono'         => (string) ($fila['telefono']         ?? ''),
                'cargo'            => (string) ($fila['cargo']            ?? ''),
                'estado'           => (string) ($fila['estado']           ?? 'activo'),
                'id_area_trabajo'  => (int)    ($fila['id_area_trabajo']  ?? 0),
                'nombre_area'      => trim((string) ($fila['nombre_area'] ?? '')) ?: 'Sin área',
                'sigla_area'       => (string) ($fila['sigla_area']       ?? ''),
                'fecha_creacion'   => (string) ($fila['fecha_creacion']   ?? ''),
                'sexo'             => (string) ($fila['sexo']             ?? ''),
                'perfiles'         => $perfiles,
                'perfil_ids'       => $perfilIds,
                'id_colegio'       => 0,
                'nom_colegio'      => 'Sin colegio',
            ];
        }

        return $resultado;
    }
}