<?php

/**
 * GestorAccesoModular
 * Ubicación definitiva: clases/GestorAccesoModular.php
 *
 * Controla qué usuarios puede ver/modificar cada perfil.
 * Lógica según logica/PERFILES_Y_ACCESO.txt y logica/ARQUITECTURA_MODULAR.txt
 *
 * REGLAS DE ACCESO:
 *   CASO 1 — id_perfil >= 3 (Super Admin)      → ve TODOS los usuarios
 *   CASO 2 — jefatura activa, depto = NULL      → ve usuarios de su COLEGIO   (Admin_Colegio)
 *   CASO 3 — jefatura activa, depto > 0         → ve usuarios de su DEPTO+COLEGIO (Admin_Departamento)
 *   CASO 4 — sin jefatura                       → ve usuarios de su ÁREA
 */
class GestorAccesoModular
{
    private $bdato;

    public function __construct($bdato)
    {
        $this->bdato = $bdato;
    }

    // -------------------------------------------------------------------------
    // Perfil máximo del usuario (tabla usuario_perfil)
    // -------------------------------------------------------------------------
    public function obtenerPerfil(int $idUsuario): int
    {
        if ($idUsuario <= 0) return 0;

        $r = $this->bdato->fetchOne(
            "SELECT MAX(up.id_perfil) AS id_perfil
               FROM usuario_perfil up
              WHERE up.id_usuario = ?",
            [$idUsuario]
        );
        return $r ? (int) $r['id_perfil'] : 0;
    }

    // -------------------------------------------------------------------------
    // Datos de acceso del usuario:
    //   id_perfil              → perfil máximo
    //   id_area_trabajo        → área del usuario
    //   id_colegio             → colegio de su jefatura activa (0 si no tiene)
    //   id_departamento_colegio → NULL = Admin_Colegio | int = Admin_Departamento | null = sin jefatura
    //   tipo_jefatura          → 'Admin_Colegio' | 'Admin_Departamento' | null
    // -------------------------------------------------------------------------
    public function obtenerDatosAccesoUsuario(int $idUsuario)
    {
        if ($idUsuario <= 0) return false;

        $idPerfil = $this->obtenerPerfil($idUsuario);

        $usuario = $this->bdato->fetchOne(
            "SELECT id, id_area_trabajo FROM usuarios WHERE id = ?",
            [$idUsuario]
        );
        if (!$usuario) return false;

        // Jefatura activa (cualquier tipo — Admin_Colegio o Admin_Departamento)
        $jefatura = $this->bdato->fetchOne(
            "SELECT id_departamento_colegio, id_colegio, tipo_jefatura
               FROM jefatura_departamento
              WHERE id_usuario = ? AND estado = 1
              LIMIT 1",
            [$idUsuario]
        );

        return [
            'id'                      => (int) $usuario['id'],
            'id_area_trabajo'         => (int) ($usuario['id_area_trabajo'] ?? 0),
            'id_perfil'               => $idPerfil,
            'id_colegio'              => $jefatura ? (int) ($jefatura['id_colegio'] ?? 0) : 0,
            // NULL en BD = Admin_Colegio  |  int > 0 = Admin_Departamento  |  null (sin jefatura)
            'id_departamento_colegio' => $jefatura
                ? ($jefatura['id_departamento_colegio'] !== null
                    ? (int) $jefatura['id_departamento_colegio']
                    : null)
                : null,
            'tipo_jefatura'           => $jefatura ? $jefatura['tipo_jefatura'] : null,
        ];
    }

    // -------------------------------------------------------------------------
    // SELECT base reutilizable — compatible con listado_usuarios.js
    // Alias estándar: u at up p jd dc col
    // -------------------------------------------------------------------------
    private function _selectBase(): string
    {
        return "SELECT
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
                    GROUP_CONCAT(DISTINCT p.id_perfil    ORDER BY p.id_perfil SEPARATOR ',') AS perfil_ids,
                    MAX(jd.id_departamento_colegio) AS id_departamento_colegio,
                    MAX(dc.nombre_departamento)     AS nombre_departamento,
                    MAX(jd.id_colegio)              AS id_colegio,
                    MAX(col.nom_colegio)            AS nom_colegio
                FROM usuarios u
                LEFT JOIN area_trabajo at          ON at.id_area    = u.id_area_trabajo
                LEFT JOIN usuario_perfil up         ON up.id_usuario = u.id
                LEFT JOIN perfiles p               ON p.id_perfil   = up.id_perfil
                LEFT JOIN jefatura_departamento jd ON jd.id_usuario = u.id AND jd.estado = 1
                LEFT JOIN departamentos_colegio dc ON dc.id         = jd.id_departamento_colegio
                LEFT JOIN colegio col              ON col.id_colegio = jd.id_colegio";
    }

    private function _groupBy(): string
    {
        return " GROUP BY u.id, u.nombre, u.apellido_paterno, u.apellido_materno,
                          u.email, u.telefono, u.cargo, u.estado, u.id_area_trabajo,
                          at.nombre_area, at.sigla_area, u.fecha_creacion, u.sexo
                 ORDER BY u.nombre ASC, u.apellido_paterno ASC";
    }

    // -------------------------------------------------------------------------
    // Retorna los usuarios que puede ver el usuario actual
    // -------------------------------------------------------------------------
    public function obtenerUsuariosAsociados(int $idUsuarioActual): array
    {
        $d = $this->obtenerDatosAccesoUsuario($idUsuarioActual);
        if (!$d) return [];

        $select  = $this->_selectBase();
        $groupBy = $this->_groupBy();

        // CASO 1: Super Admin (id_perfil = 3 exactamente) → todos
        if ($d['id_perfil'] === 3) {
            return $this->_mapearFilas(
                $this->bdato->fetchAll($select . $groupBy, [])
            );
        }

        // CASO 2: Admin_Colegio (jefatura activa, id_departamento_colegio es NULL en BD)
        if ($d['id_colegio'] > 0 && $d['id_departamento_colegio'] === null) {
            return $this->_mapearFilas(
                $this->bdato->fetchAll(
                    $select . " WHERE jd.id_colegio = ?" . $groupBy,
                    [$d['id_colegio']]
                )
            );
        }

        // CASO 3: Admin_Departamento (jefatura activa, id_departamento_colegio > 0)
        if ($d['id_colegio'] > 0 && $d['id_departamento_colegio'] > 0) {
            return $this->_mapearFilas(
                $this->bdato->fetchAll(
                    $select . " WHERE jd.id_departamento_colegio = ? AND jd.id_colegio = ?" . $groupBy,
                    [$d['id_departamento_colegio'], $d['id_colegio']]
                )
            );
        }

        // CASO 4: Usuario normal → solo su área
        if ($d['id_area_trabajo'] <= 0) return [];

        return $this->_mapearFilas(
            $this->bdato->fetchAll(
                $select . " WHERE u.id_area_trabajo = ?" . $groupBy,
                [$d['id_area_trabajo']]
            )
        );
    }

    // -------------------------------------------------------------------------
    // Mapea filas DB al formato que espera listado_usuarios.js
    // -------------------------------------------------------------------------
    private function _mapearFilas(array $filas): array
    {
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
                'id'                      => (int)    ($fila['id']               ?? 0),
                'nombre'                  => (string) ($fila['nombre']           ?? ''),
                'apellido_paterno'        => (string) ($fila['apellido_paterno'] ?? ''),
                'apellido_materno'        => (string) ($fila['apellido_materno'] ?? ''),
                'email'                   => (string) ($fila['email']            ?? ''),
                'telefono'                => (string) ($fila['telefono']         ?? ''),
                'cargo'                   => (string) ($fila['cargo']            ?? ''),
                'estado'                  => (string) ($fila['estado']           ?? 'activo'),
                'id_area_trabajo'         => (int)    ($fila['id_area_trabajo']  ?? 0),
                'nombre_area'             => trim((string) ($fila['nombre_area'] ?? '')) ?: 'Sin área',
                'sigla_area'              => (string) ($fila['sigla_area']       ?? ''),
                'fecha_creacion'          => (string) ($fila['fecha_creacion']   ?? ''),
                'sexo'                    => (string) ($fila['sexo']             ?? ''),
                'perfiles'                => $perfiles,
                'perfil_ids'              => $perfilIds,
                'id_departamento_colegio' => (int)    ($fila['id_departamento_colegio'] ?? 0),
                'nombre_departamento'     => trim((string) ($fila['nombre_departamento'] ?? '')),
                'id_colegio'              => (int)    ($fila['id_colegio']       ?? 0),
                'nom_colegio'             => trim((string) ($fila['nom_colegio'] ?? '')) ?: 'Sin colegio',
            ];
        }
        return $resultado;
    }

    // -------------------------------------------------------------------------
    // Valida si el usuario actual puede modificar al usuario objetivo
    // -------------------------------------------------------------------------
    public function validarAccesoAUsuario(int $idUsuarioActual, int $idUsuarioObjetivo): bool
    {
        if ($idUsuarioActual <= 0 || $idUsuarioObjetivo <= 0
                || $idUsuarioActual === $idUsuarioObjetivo) {
            return false;
        }

        $da = $this->obtenerDatosAccesoUsuario($idUsuarioActual);
        $do = $this->obtenerDatosAccesoUsuario($idUsuarioObjetivo);
        if (!$da || !$do) return false;

        // Super Admin (id_perfil = 3 exactamente) → puede modificar a todos
        if ($da['id_perfil'] === 3) return true;

        // Admin_Colegio → usuarios de su colegio
        if ($da['id_colegio'] > 0 && $da['id_departamento_colegio'] === null) {
            return $da['id_colegio'] === $do['id_colegio'];
        }

        // Admin_Departamento → mismo departamento y colegio
        if ($da['id_colegio'] > 0 && $da['id_departamento_colegio'] > 0) {
            return $da['id_departamento_colegio'] === $do['id_departamento_colegio']
                && $da['id_colegio'] === $do['id_colegio'];
        }

        // Usuario normal → misma área
        $idArea = $da['id_area_trabajo'];
        return $idArea > 0 && $idArea === $do['id_area_trabajo'];
    }

    // -------------------------------------------------------------------------
    // Condición SQL para filtrar por acceso (usado en otros módulos)
    // -------------------------------------------------------------------------
    public function obtenerFiltroAccesoSQL(int $idUsuario, string $aliasUsuario = 'u'): string
    {
        $d = $this->obtenerDatosAccesoUsuario($idUsuario);
        if (!$d) return '1=0';

        if ($d['id_perfil'] === 3) return '1=1';

        if ($d['id_colegio'] > 0 && $d['id_departamento_colegio'] === null) {
            return "jd.id_colegio = {$d['id_colegio']}";
        }

        if ($d['id_colegio'] > 0 && $d['id_departamento_colegio'] > 0) {
            return "jd.id_departamento_colegio = {$d['id_departamento_colegio']} AND jd.id_colegio = {$d['id_colegio']}";
        }

        $idArea = $d['id_area_trabajo'];
        if ($idArea <= 0) return '1=0';

        $alias = preg_replace('/[^a-zA-Z0-9_]/', '', $aliasUsuario) ?: 'u';
        return "{$alias}.id_area_trabajo = {$idArea}";
    }
}