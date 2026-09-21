<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");

$db = new MySQL("", "", "");

$id         = $_POST['id'];
$mensaje     = $_POST['mensaje'];


$sql = "UPDATE mensajes SET mensaje = '$mensaje'  WHERE id = $id";
$resultado = $db->consulta($sql);

if ($resultado) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>
