<?php
session_start();
include_once("class/conexion.php");

$bdato = new MySQL("", "", "");
$sql = "SELECT mensajes.*, usuarios.nombre, usuarios.apellido_paterno, usuarios.apellido_materno
        FROM mensajes 
        JOIN usuarios ON usuarios.id = mensajes.de 
        WHERE mensajes.para = '" . $_SESSION['id'] . "'";


$resultado = $bdato->consulta($sql);
$mensajes = [];
while ($row = $bdato->fetch_array($resultado)) {
    $mensajes[] = [
        'mensaje' => $row['mensaje'],
        'nombre' => $row['nombre'],
        'apellido_paterno' => $row['apellido_paterno'],
        'tiempo' => $row['tiempo']
    ];
}

header('Content-Type: application/json');
echo json_encode($mensajes);
?>
