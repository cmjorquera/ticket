<?php



// echo "HOLA GUADALUPE";
include("../../class/conexion.php");

$clave            = isset($_POST["idUsuario"]) ? $_POST["idUsuario"] : "NO TIENE";
$clave            = isset($_POST["idUsuario"]) ? $_POST["idUsuario"] : "NO TIENE";


$db = new MySQL("", "", "");
$sql = "UPDATE `usuarios` SET `clave`='[value-1]' WHERE usuario ='$usuario',";

        echo $sql."*******";
        $bl = $db->guardar($sql);

if ($bl === 0) { // Asumiendo que el método guardar devuelve 0 en caso de éxito
    echo "Cuenta $mensaje con ID $para insertada correctamente.";
} else {
    echo "Error al insertar la cuenta.";
}

$db->CerrarConexion();

