<?php
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Obtener el ID del mensaje y la acción de la solicitud POST
$id = isset($_POST["id"]) ? $_POST["id"] : null;
$accion = isset($_POST["accion"]) ? $_POST["accion"] : null;

// Verificar que se hayan proporcionado un ID y una acción válidos
if ($id === null || $accion === null) {
    echo "Error: ID de mensaje o acción no proporcionados.";
    exit;
}

// Crear una nueva instancia de la conexión a la base de datos
$db = new MySQL("", "", "");

switch ($accion) {
    case "crear_borrador":
        $sql = "UPDATE `mensajes` SET `leido` = 'SI' WHERE `id` = $id";
        break;
    
    case "borrador_mensaje":
        $sql = "UPDATE `mensajes` SET `leido` = 'NO' WHERE `id` = $id";
        break;
        echo $sql."*********";
    default:
        echo "Error: Acción no válida.";
        $db->CerrarConexion();
        exit;
}

// Ejecutar la consulta
$result = $db->guardar($sql);

// Verificar el resultado de la operación
if ($result === 0) { // Asumiendo que el método guardar devuelve 0 en caso de éxito
    echo "El mensaje con ID $id ha sido actualizado correctamente.";
} else {
    echo "Error al actualizar el mensaje.";
}

// Cerrar la conexión a la base de datos
$db->CerrarConexion();
?>
