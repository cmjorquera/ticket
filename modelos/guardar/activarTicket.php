<?php
include("../../class/conexion.php");
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['id_ticket'])) {
    $id_ticket = intval($_POST['id_ticket']);
    $bd = new MySQL("", "", "");

    // Obtener categoría del ticket
    $sqlCategoria = "SELECT id_categoria_ticket FROM tickets WHERE id_ticket = $id_ticket";
    $resCategoria = $bd->consulta($sqlCategoria);
    if ($fila = $bd->fetch_array($resCategoria)) {
        $id_categoria = intval($fila['id_categoria_ticket']);

        // Buscar técnicos asociados a esa categoría
        $sqlTecnicos = "SELECT id_tecnico FROM categoria_tecnico WHERE id_categoria = $id_categoria";
        $resTecnicos = $bd->consulta($sqlTecnicos);

        $tecnicos = [];
        while ($rowTec = $bd->fetch_array($resTecnicos)) {
            $tecnicos[] = $rowTec['id_tecnico'];
        }

        // Determinar técnico y estado
        if (!empty($tecnicos)) {
            $id_tecnico = intval($tecnicos[0]); // puedes implementar lógica más compleja si quieres
            $id_estado = 2; // Asignado
        } else {
            $id_tecnico = 'NULL';
            $id_estado = 1; // Recibido
        }

        // Actualizar ticket con técnico (si hay) y estado
        $sqlUpdate = "UPDATE tickets 
                      SET id_estado = $id_estado, 
                          id_tecnico = " . ($id_tecnico !== 'NULL' ? $id_tecnico : "NULL") . "
                      WHERE id_ticket = $id_ticket";

        if ($bd->consulta($sqlUpdate)) {
            echo json_encode(["success" => true, "estado" => $id_estado, "tecnico" => $id_tecnico]);
        } else {
            echo json_encode(["success" => false, "error" => "No se pudo actualizar el ticket"]);
        }
    } else {
        echo json_encode(["success" => false, "error" => "Ticket no encontrado"]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Falta ID del ticket"]);
}
