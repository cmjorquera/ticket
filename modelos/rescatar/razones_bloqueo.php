<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Crear una instancia de la conexión a la base de datos con UTF-8
$db = new MySQL("", "", "");
$db->consulta("SET NAMES 'utf8'");

$st = "SELECT * FROM razones_bloqueo_usuario";
$consulta = $db->consulta($st);

$listaRazones = []; // Inicializar el arreglo para las razones

// Verificar si hay resultados y agregarlos al arreglo
if($db->num_rows($consulta) > 0) {
    while ($row = $db->fetch_array($consulta)) {
        $razon = [
            'id' => $row['id'],
            'razon' => $row['razon'],
            'comentario' => $row['comentario']
        ];
        $listaRazones[] = $razon; // Agregar cada razón al arreglo
    }
}

// Devolver el arreglo en formato JSON
echo json_encode($listaRazones);
?>
