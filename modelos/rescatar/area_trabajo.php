<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");

$db = new MySQL("", "", "");

$st = "SELECT id_area, nombre_area FROM area_trabajo";
$consulta = $db->consulta($st);

$areas = []; // Inicializar el arreglo para las áreas

if ($db->num_rows($consulta) > 0) {
    while ($row = $db->fetch_array($consulta)) {
        $area = [
            'id' => $row['id_area'],
            'nombre_area' => $row['nombre_area']
        ];
        $areas[] = $area; // Agregar cada área al arreglo
    }
}

// Devolver el arreglo en formato JSON
echo json_encode($areas);
?>