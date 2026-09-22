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
                    jd.tipo_jefatura
               FROM usuarios u
          LEFT JOIN jefatura_departamento jd
                 ON jd.id_usuario = u.id
                AND jd.estado = 1
                AND jd.tipo_jefatura = 'Admin_Departamento'
              WHERE u.id = ?
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
     * Los administradores de departamento ven únicamente su departamento.
     * Los demás usuarios quedan limitados a su área de trabajo.
     */
    public function obtenerUsuariosAsociados($idUsuarioActual)
    {
        $datosAcceso = $this->obtenerDatosAccesoUsuario($idUsuarioActual);
        if (!$datosAcceso) {
            return [];
        }

        $idDepartamento = (int) ($datosAcceso['id_departamento_colegio'] ?? 0);
        if ($idDepartamento > 0) {
            $idColegio = (int) ($datosAcceso['id_colegio'] ?? 0);
            if ($idColegio <= 0) {
                return [];
            }
            return Usuario::listar($this->bdato, [
                'id_departamento_colegio' => $idDepartamento,
                'id_colegio' => $idColegio,
            ]);
        }

        $idArea = (int) ($datosAcceso['id_area_trabajo'] ?? 0);
        if ($idArea <= 0) {
            return [];
        }

        return Usuario::listar($this->bdato, [
            'id_area' => $idArea,
        ]);
    }

    /**
     * Valida si el usuario actual puede modificar al usuario objetivo.
     */
    public function validarAccesoAUsuario($idUsuarioActual, $idUsuarioObjetivo)
    {
        $idUsuarioActual = (int) $idUsuarioActual;
        $idUsuarioObjetivo = (int) $idUsuarioObjetivo;
        if ($idUsuarioActual <= 0 || $idUsuarioObjetivo <= 0 || $idUsuarioActual === $idUsuarioObjetivo) {
            return false;
        }

        $datosActual = $this->obtenerDatosAccesoUsuario($idUsuarioActual);
        $datosObjetivo = $this->obtenerDatosAccesoUsuario($idUsuarioObjetivo);
        if (!$datosActual || !$datosObjetivo) {
            return false;
        }

        $idDepartamento = (int) ($datosActual['id_departamento_colegio'] ?? 0);
        if ($idDepartamento > 0) {
            return $idDepartamento === (int) ($datosObjetivo['id_departamento_colegio'] ?? 0)
                && (int) ($datosActual['id_colegio'] ?? 0) === (int) ($datosObjetivo['id_colegio'] ?? 0);
        }

        $idArea = (int) ($datosActual['id_area_trabajo'] ?? 0);
        return $idArea > 0 && $idArea === (int) ($datosObjetivo['id_area_trabajo'] ?? 0);
    }

    /**
     * Retorna una condición SQL para consultas que ya incorporan los alias
     * de usuario y jefatura de departamento.
     */
    public function obtenerFiltroAccesoSQL($idUsuario, $aliasUsuario = 'u')
    {
        $datosAcceso = $this->obtenerDatosAccesoUsuario($idUsuario);
        if (!$datosAcceso) {
            return '1=0';
        }

        $idDepartamento = (int) ($datosAcceso['id_departamento_colegio'] ?? 0);
        if ($idDepartamento > 0) {
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
