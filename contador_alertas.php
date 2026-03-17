<?php
session_start();
include_once("class/conexion.php");

// Asumiendo que tienes una instancia de la clase MySQL como en tu ejemplo
$bdato = new MySQL("", "", "");

// Aquí deberías modificar la consulta SQL según el criterio para contar alertas
// Por ejemplo, supongamos que quieres contar alertas que tienen un estado particular
$sql = 'SELECT COUNT(*) AS num_alertas FROM alertas WHERE id_usuario_asignado = "' . $_SESSION['id'] . '"';
// echo $sql."****";
$resultado = $bdato->consulta($sql);
$fila = $bdato->fetch_array($resultado);
$numeroAlertas = $fila['num_alertas'];

echo $numeroAlertas > 0 ? $numeroAlertas : "Sin alertas";
?>
