<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");

$db = new MySQL("", "", "");

$id = $_POST['id'];

$sql = "SELECT id, id_usuario, nombre, url_, imagen, fecha, hora FROM contenedor WHERE id = $id";
$consulta = $db->consulta($sql);

if ($db->num_rows($consulta) > 0) {
    $contenedor = $db->fetch_array($consulta);
    echo json_encode(['success' => true, 'contenedor' => $contenedor]);
} else {
    echo json_encode(['success' => false]);
}
?>
