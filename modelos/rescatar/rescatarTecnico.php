<?php
header('Content-Type: application/json');
include("../../class/conexion.php");

$id_tecnico = $_POST['id_tecnico'];

$db = new MySQL("", "", "");
$consulta = $db->consulta("SELECT nombre, apellido_paterno FROM usuarios WHERE id = '$id_tecnico' LIMIT 1");

if ($row = $db->fetch_array($consulta)) {
    echo json_encode([
        'nombre' => $row['nombre'],
        'apellido_paterno' => $row['apellido_paterno']
    ]);
} else {
    echo json_encode(['error' => 'No encontrado']);
}
?>
