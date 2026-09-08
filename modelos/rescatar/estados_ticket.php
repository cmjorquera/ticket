<?php
include("../../class/conexion.php");
header('Content-Type: application/json; charset=utf-8');

$bd = new MySQL("", "", "");

$sql = "SELECT id, nombre, color FROM estados_ticket ORDER BY orden ASC";
$resultado = $bd->consulta($sql);

$estados = [];

while ($fila = $bd->fetch_array($resultado)) {
    $estados[] = [
        'id' => intval($fila['id']),
        'nombre_estado' => $fila['nombre'],
        'color_estado' => $fila['color']
    ];
}

echo json_encode($estados);
