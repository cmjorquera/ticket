<?php
header('Content-Type: application/json');  // Esto indicará explícitamente al navegador (y a jQuery) que la respuesta debe ser tratada como JSON.

include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Recibir el ID con validación básica
$id = $_POST['id'] ?? null;

if ($id === null) {
    echo json_encode(['success' => false, 'error' => 'ID no proporcionado']);
    exit;
}

// Crear la conexión utilizando la clase MySQL
$db = new MySQL("", "", "");  // Asegúrate de que los parámetros están correctamente proporcionados

// Preparar la consulta SQL para la eliminación
$sql = "DELETE FROM contenedor WHERE id = " . $db->escape_string($id);

// Llamar a la función guardar para ejecutar la consulta
$result = $db->guardar($sql);

if ($result === 0) {
    echo json_encode(['success' => true, 'message' => "Contenedor $id eliminado correctamente"]);
} else {
    echo json_encode(['success' => false, 'error' => "Error al eliminar el contenedor, código de error: $result"]);
}

$db->CerrarConexion();
?>
