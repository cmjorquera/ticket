<?php
include("../../class/conexion.php");

session_start();

$id_ticket              = $_POST['id_ticket'] ?? null;
$calificacion           = $_POST['calificacion'] ?? null;
$comentario             = $_POST['comentario'] ?? '';
$fecha_calificacion     = date("Y-m-d"); // Solo la fecha (YYYY-MM-DD)
$hora_calificacion      = date("H:i:s"); // Solo la hora (HH:MM:SS)

// Validar que el id_ticket existe en la tabla tickets
$db = new MySQL("", "", "");

// echo $id_ticket."<br>";
// echo $calificacion."<br>";
// echo $comentario."<br>";           
// echo $fecha_calificacion."<br>";
// die();

$verificar = $db->consulta("SELECT id_ticket FROM tickets WHERE id_ticket = '$id_ticket'");
if ($db->num_rows($verificar) == 0) {
    die("Error: El id_ticket no existe en la tabla tickets.'$id_ticket'");
}


// Inserción segura con protección contra SQL Injection
$id_ticket = $db->escape_string($id_ticket);
$calificacion = $db->escape_string($calificacion);
$comentario = $db->escape_string($comentario);

$sql = "INSERT INTO `calificacion` (`id_ticket`, `calificacion`, `comentario`, `fecha_calificacion`, `hora_calificacion`) 
        VALUES ('$id_ticket', '$calificacion', '$comentario', '$fecha_calificacion','$hora_calificacion')";
echo $sql;
$result = $db->guardar($sql);

if ($result) {
    header("Location: ../../gracias.php");
    exit;
} else {
    echo "Error al guardar la calificación.";
}
?>
