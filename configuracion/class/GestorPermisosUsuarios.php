<?php

require_once dirname(__DIR__, 2) . '/class/GestorAccesoModular.php';

class GestorPermisosUsuarios
{
    private $bdato;
    private $gestorAcceso;

    public function __construct($bdato)
    {
        $this->bdato = $bdato;
        $this->gestorAcceso = new GestorAccesoModular($bdato);
    }

    public function obtenerUsuariosAsociados($idUsuarioActual)
    {
        return $this->gestorAcceso->obtenerUsuariosAsociados($idUsuarioActual);
    }

    public function obtenerPermisosUsuario($idUsuario)
    {
        $sql = "SELECT pm.id, pm.id_usuario, pm.id_menu, pm.estado, m.nombre 
                FROM permiso_menu_1 pm
                LEFT JOIN menu_1 m ON pm.id_menu = m.id
                WHERE pm.id_usuario = ?
                ORDER BY m.nombre ASC";
        
        $stmt = $this->bdato->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $permisos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $permisos[] = $fila;
        }
        
        return $permisos;
    }

    public function guardarPermisosUsuario($idUsuarioActual, $idUsuario, $permisos)
    {
        if (!$this->gestorAcceso->validarAccesoAUsuario($idUsuarioActual, $idUsuario)) {
            throw new RuntimeException('No tiene permiso para modificar permisos de este usuario.');
        }

        if (!is_array($permisos) || empty($permisos)) {
            throw new RuntimeException('Permisos no proporcionados o formato inválido.');
        }

        $procesados = 0;
        $errores = [];

        foreach ($permisos as $permiso) {
            $idMenu = (int)($permiso['id_menu'] ?? 0);
            $estado = (int)($permiso['estado'] ?? 0);

            if ($idMenu <= 0) {
                $errores[] = "ID de menú inválido: $idMenu";
                continue;
            }

            try {
                $checkSql = "SELECT id FROM permiso_menu_1 WHERE id_usuario = ? AND id_menu = ?";
                $stmtCheck = $this->bdato->prepare($checkSql);
                $stmtCheck->bind_param("ii", $idUsuario, $idMenu);
                $stmtCheck->execute();
                $resultCheck = $stmtCheck->get_result();
                $existe = $resultCheck->num_rows > 0;

                if ($existe) {
                    $updateSql = "UPDATE permiso_menu_1 SET estado = ? WHERE id_usuario = ? AND id_menu = ?";
                    $stmtUpdate = $this->bdato->prepare($updateSql);
                    $stmtUpdate->bind_param("iii", $estado, $idUsuario, $idMenu);
                    $stmtUpdate->execute();
                } else {
                    $insertSql = "INSERT INTO permiso_menu_1 (id_usuario, id_menu, estado) VALUES (?, ?, ?)";
                    $stmtInsert = $this->bdato->prepare($insertSql);
                    $stmtInsert->bind_param("iii", $idUsuario, $idMenu, $estado);
                    $stmtInsert->execute();
                }

                $procesados++;
            } catch (Exception $e) {
                $errores[] = "Error al procesar menú $idMenu: " . $e->getMessage();
            }
        }

        return [
            'procesados' => $procesados,
            'errores' => $errores
        ];
    }
}
?>
