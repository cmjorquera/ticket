<?php



// echo "HOLA GUADALUPE";
include("../../class/conexion.php");

$idMensaje = $_POST['idMensaje'];
$leido = $_POST['leido'] ? 'NO' : 'SI';  // Asumiendo que 'leido' se almacena como un entero (1 para true, 0 para false)


$db = new MySQL("seduc", "", "");
$sql = "UPDATE mensajes
         SET leido = '$leido'
          WHERE id = '$idMensaje'"  ;


        echo $sql."*******";
        $bl = $db->guardar($sql);

if ($bl === 0) { // Asumiendo que el método guardar devuelve 0 en caso de éxito
    echo "Cuenta $mensaje con ID $para insertada correctamente.";
} else {
    echo "Error al insertar la cuenta.";
}

$db->CerrarConexion();
