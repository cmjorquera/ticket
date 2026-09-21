<?php
header('Content-Type: application/json');  // Indica que la respuesta será JSON
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$id = isset($_POST['idMensaje']) ? (int) $_POST['idMensaje'] : null;  // Conversión a entero para mayor seguridad

if (null === $id) {
    echo json_encode(['success' => false, 'error' => "ID no proporcionado."]);
    exit;
}

$db = new MySQL("", "", "");
$sql = "UPDATE `mensajes_chat` SET `eliminado`='1' WHERE id ='$id'";

$result = $db->guardar($sql);

if ($result === 0) {
    echo json_encode(['success' => true, 'message' => "Mensaje $id eliminado correctamente"]);
} else {
    echo json_encode(['success' => false, 'error' => "Error al eliminar el mensaje, código de error: $result"]);
}

$db->CerrarConexion();
?>
