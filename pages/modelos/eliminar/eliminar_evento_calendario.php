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

$db = new MySQL("", "", "");  // Asegúrate de que los parámetros están correctamente proporcionados

$sql = "UPDATE eventos SET eliminado = 'si' WHERE id = " . $db->escape_string($id);
$result = $db->guardar($sql);

if ($result === 0) {
    echo json_encode(['success' => true, 'message' => "Evento $id actualizado correctamente"]);
} else {
    echo json_encode(['success' => false, 'error' => "Error al actualizar el evento, código de error: $result"]);
}

$db->CerrarConexion();
?>