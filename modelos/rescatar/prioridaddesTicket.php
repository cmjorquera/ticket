<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Crear una instancia de la conexión a la base de datos con UTF-8
$db = new MySQL("", "", "");
$db->consulta("SET NAMES 'utf8'");

$st = "SELECT * FROM prioridad";
$consulta = $db->consulta($st);

$listaPrioridades = []; // Inicializar el arreglo para las prioridades

// Verificar si hay resultados y agregarlos al arreglo
if ($db->num_rows($consulta) > 0) {
    while ($row = $db->fetch_array($consulta)) {
        $razon = [
            'id'     => $row['id'],
            'nombre' => $row['nombre'],
            'orden'  => $row['orden']  // sin espacio extra
        ];
        $listaPrioridades[] = $razon; // Agregar cada prioridad al arreglo
    }
}
echo json_encode($listaPrioridades);
?>