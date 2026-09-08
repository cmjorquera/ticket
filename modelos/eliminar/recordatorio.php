<?php
include("../../class/conexion.php");

$id = $_POST['id'];

$db = new MySQL("", "", "");

$query = "DELETE FROM recordatorio WHERE id = '$id'";
$bl = $db->guardar($query);

if ($bl == 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}

$db->CerrarConexion();
?>
