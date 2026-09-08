<?php
include("../../class/conexion.php");

$comentarioReactivacaion = $_POST['comentario'];
$id_ticket = $_POST['id_ticket'];

// Asegúrate de proporcionar los parámetros correctos para la conexión
$db = new MySQL("nombre_basedatos", "usuario", "contraseña");

$sql = "UPDATE tickets SET 
         comentario_reactivacion = '$comentarioReactivacaion'
         WHERE id_ticket = '$id_ticket'";

echo $sql . "*******";
$bl = $db->guardar($sql);

// Asegúrate de que el método guardar devuelve lo que esperas
if ($bl === 0) { // Aquí, 0 se asume como éxito, cambia si es necesario
    echo "El comentario '$comentarioReactivacaion' se ha agregado a la tabla ticket con el ID '$id_ticket' correctamente.";
} else {
    echo "Error al actualizar el ticket.";
}

$db->CerrarConexion();
?>
