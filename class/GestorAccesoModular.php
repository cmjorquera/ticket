<?php

require_once dirname(__DIR__) . '/clases/Usuario.php';

class GestorAccesoModular
{
    private $bdato;

    public function __construct($bdato)
    {
        $this->bdato = $bdato;
    }

    /**
     * Obtiene el perfil más alto del usuario desde tabla usuario_perfil
     */
    public function obtenerPerfil($idUsuario)
    {
        $idUsuario = (int) $idUsuario;
        if ($idUsuario <= 0) {
            return 0;
        }

        $resultado = $this->bdato->fetchOne(
            "SELECT MAX(up.id_perfil) as id_perfil
               FROM usuario_perfil up
              WHERE up.id_usuario = ?",
            [$idUsuario]
        );

        return $resultado ? (int) $resultado['id_perfil'] : 0;
    }

    /**
     * Obtiene el área y la jefatura activa del usuario.
     */
    public function obtenerDatosAccesoUsuario($idUsuario)
    {
        $idUsuario = (int) $idUsuario;
        if ($idUsuario <= 0) {
            return false;
        }

        return $this->bdato->fetchOne(
            "SELECT u.id,
                    u.id_area_trabajo,
                    jd.id_colegio,
                    jd.id_departamento_colegio,
                    jd.tipo_jefatura,
                    MAX(up.id_perfil) as id_perfil
               FROM usuarios u
          LEFT JOIN usuario_perfil up ON up.id_usuario = u.id
          LEFT JOIN jefatura_departamento jd
                 ON jd.id_usuario = u.id
                AND jd.estado = 1
                AND jd.tipo_jefatura = 'Admin_Departamento'
              WHERE u.id = ?
           GROUP BY u.id
           ORDER BY CASE
                        WHEN jd.id_departamento_colegio IS NOT NULL THEN 0
                        ELSE 1
                    END,
                    jd.id_departamento_colegio ASC
              LIMIT 1",
            [$idUsuario]
        );
    }

    /**
     * Obtiene los usuarios asociados según el perfil.
     * Devuelve estructura compatible con listado_usuarios.js:
     * perfiles (array), perfil_ids (array), nombre_area, nom_colegio, etc.
     */
    public function obtenerUsuariosAsociados($idUsuarioActual)
    {
        $datosAcceso = $this->obtenerDatosAccesoUsuario($idUsuarioActual);
        if (!$datosAcceso) {
            return [];
        }

        $idPerfil = (int) ($datosAcceso['id_perfil'] ?? 0);

        // Base SELECT con GROUP_CONCAT para perfiles (compatible con listado_usuarios.js)
        $selectBase = "SELECT
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
                    MAX(jd.id_colegio)   AS id_colegio,
                    MAX(col.nom_colegio) AS nom_colegio
                FROM usuarios u
                LEFT JOIN area_trabajo at      ON at.id_area    = u.id_area_trabajo
                LEFT JOIN usuario_perfil up     ON up.id_usuario = u.id
                LEFT JOIN perfiles p           ON p.id_perfil   = up.id_perfil
                LEFT JOIN jefatura_departamento jd
                       ON jd.id_usuario = u.id AND jd.estado = 1
                LEFT JOIN colegio col          ON col.id_colegio = jd.id_colegio";

        $groupBy = " GROUP BY u.id, u.nombre, u.apellido_paterno, u.apellido_materno,
                         u.email, u.telefono, u.cargo, u.estado, u.id_area_trabajo,
                         at.nombre_area, at.sigla_area, u.fecha_creacion, u.sexo
                  ORDER BY u.nombre ASC, u.apellido_paterno ASC";

        // CASO 1: SUPER ADMIN (id_perfil >= 3) → VER TODOS
        if ($idPerfil >= 3) {
            $filas = $this->bdato->fetchAll($selectBase . $groupBy, []);
            return $this->_mapearFilas($filas);
        }

        // CASO 2: TÉCNICO/JEFE (id_perfil = 2) con departamento → VER SU COLEGIO
        $idDepartamento = (int) ($datosAcceso['id_departamento_colegio'] ?? 0);
        if ($idPerfil === 2 && $idDepartamento > 0) {
            $idColegio = (int) ($datosAcceso['id_colegio'] ?? 0);
            if ($idColegio <= 0) {
                return [];
            }
            $filas = $this->bdato->fetchAll(
                $selectBase .
                " WHERE (jd.id_departamento_colegio = ? AND jd.id_colegio = ?) OR u.id = ?" .
                $groupBy,
                [$idDepartamento, $idColegio, $idUsuarioActual]
            );
            return $this->_mapearFilas($filas);
        }

        // CASO 3: USUARIO NORMAL → VER SU ÁREA
        $idArea = (int) ($datosAcceso['id_area_trabajo'] ?? 0);
        if ($idArea <= 0) {
            return [];
        }
        $filas = $this->bdato->fetchAll(
            $selectBase . " WHERE u.id_area_trabajo = ?" . $groupBy,
            [$idArea]
        );
        return $this->_mapearFilas($filas);
    }

    /**
     * Mapea filas DB al formato que espera listado_usuarios.js
     */
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
                'id_colegio'       => (int)    ($fila['id_colegio']       ?? 0),
                'nom_colegio'      => trim((string) ($fila['nom_colegio'] ?? '')) ?: 'Sin colegio',
            ];
        }
        return $resultado;
    }

    /**
     * Valida si el usuario actual puede modificar al usuario objetivo.
     */
    public function validarAccesoAUsuario($idUsuarioActual, $idUsuarioObjetivo)
    {
        $idUsuarioActual   = (int) $idUsuarioActual;
        $idUsuarioObjetivo = (int) $idUsuarioObjetivo;
        if ($idUsuarioActual <= 0 || $idUsuarioObjetivo <= 0 || $idUsuarioActual === $idUsuarioObjetivo) {
            return false;
        }

        $datosActual   = $this->obtenerDatosAccesoUsuario($idUsuarioActual);
        $datosObjetivo = $this->obtenerDatosAccesoUsuario($idUsuarioObjetivo);
        if (!$datosActual || !$datosObjetivo) {
            return false;
        }

        $idPerfil = (int) ($datosActual['id_perfil'] ?? 0);
        if ($idPerfil >= 3) {
            return true;
        }

        $idDepartamento = (int) ($datosActual['id_departamento_colegio'] ?? 0);
        if ($idPerfil === 2 && $idDepartamento > 0) {
            return $idDepartamento === (int) ($datosObjetivo['id_departamento_colegio'] ?? 0)
                && (int) ($datosActual['id_colegio'] ?? 0) === (int) ($datosObjetivo['id_colegio'] ?? 0);
        }

        $idArea = (int) ($datosActual['id_area_trabajo'] ?? 0);
        return $idArea > 0 && $idArea === (int) ($datosObjetivo['id_area_trabajo'] ?? 0);
    }

    /**
     * Retorna una condición SQL para consultas que ya incorporan los alias de usuario.
     */
    public function obtenerFiltroAccesoSQL($idUsuario, $aliasUsuario = 'u')
    {
        $datosAcceso = $this->obtenerDatosAccesoUsuario($idUsuario);
        if (!$datosAcceso) {
            return '1=0';
        }

        $idPerfil = (int) ($datosAcceso['id_perfil'] ?? 0);

        if ($idPerfil >= 3) {
            return '1=1';
        }

        $idDepartamento = (int) ($datosAcceso['id_departamento_colegio'] ?? 0);
        if ($idPerfil === 2 && $idDepartamento > 0) {
            $idColegio = (int) ($datosAcceso['id_colegio'] ?? 0);
            return $idColegio > 0
                ? "jd.id_departamento_colegio = $idDepartamento AND jd.id_colegio = $idColegio"
                : '1=0';
        }

        $idArea = (int) ($datosAcceso['id_area_trabajo'] ?? 0);
        if ($idArea <= 0) {
            return '1=0';
        }

        $aliasUsuario = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $aliasUsuario) ?: 'u';
        return "{$aliasUsuario}.id_area_trabajo = $idArea";
    }
}
?>