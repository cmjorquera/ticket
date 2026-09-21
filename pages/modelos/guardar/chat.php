<?php
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Recolectar los datos del formulario
$idTicket = isset($_POST["idTicket"]) ? $_POST["idTicket"] : "NO TIENE";
$mensaje = isset($_POST["mensaje"]) ? $_POST["mensaje"] : "NO TIENE";
$id_usuario = isset($_POST["id_usuario"]) ? $_POST["id_usuario"] : "NO TIENE";

$db = new MySQL("seduc", "", "");
// $sql = "UPDATE `tabla_conversacion` SET `texto`='$mensaje' WHERE id_ticket = '$idTicket'";


$sql = "INSERT INTO `tabla_conversacion`(`texto`, `id_usuario`, `id_ticket`) 
            VALUES ('$mensaje','$id_usuario','$idTicket')";


$bl = $db->guardar($sql);

if ($bl === 0) {
    // Asumiendo que el método guardar devuelve 0 en caso de éxito
    echo "<script>window.history.back();</script>";
} else {
    echo "<script>alert('Error al actualizar la cuenta.'); window.history.back();</script>";
}

$db->CerrarConexion();
?>


