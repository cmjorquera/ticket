<?php
session_start();
include_once("class/conexion.php");

// Crear una instancia de la clase MySQL
$bdato = new MySQL("", "", ""); // Asegúrate de que los parámetros coincidan con tus requerimientos

// Consulta para obtener los mensajes
$sql = "SELECT mensajes.*, usuarios.nombre, usuarios.apellido_paterno
        FROM mensajes 
        JOIN usuarios ON usuarios.id = mensajes.de 
        WHERE mensajes.para = '" . $_SESSION['id'] . "'";

$usuario_id = $_SESSION['id']; // Asegúrate de que este índice está correctamente definido en $_SESSION

// Preparar y ejecutar la consulta
$resultado = $bdato->consulta($sql, [$usuario_id]);

$mensajes = [];
while ($row = $bdato->fetch_array($resultado)) {
    $mensajes[] = [
        'mensaje' => $row['mensaje'],
        'nombre' => $row['nombre'],
        'apellido_paterno' => $row['apellido_paterno'],
        'tiempo' => $row['tiempo']  // Asegúrate de que 'tiempo' es una columna en tu base de datos
    ];
}

$bdato->LimpiarConsulta(); // Limpiar resultados si es necesario
$bdato->CerrarConexion(); // Cerrar la conexión si no es persistente

// Devolver los resultados como JSON
header('Content-Type: application/json');
echo json_encode($mensajes);
?>
