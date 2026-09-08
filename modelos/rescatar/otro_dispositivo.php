<?php
header('Content-Type: application/json');
require_once '../../class/conexion.php';

$db = new MySQL('', '', '');

// Decodifica el JSON enviado en la solicitud
$input = json_decode(file_get_contents('php://input'), true);
$id_dispositivo = $input['id_dispositivo'] ?? 0;

$result = $db->consulta("SELECT * FROM otros_dispositivos WHERE id_dispositivo = $id_dispositivo");

if ($db->num_rows($result) > 0) {
    $dispositivo = $db->fetch_assoc($result);
    echo json_encode(['success' => true, 'data' => $dispositivo]);
} else {
    echo json_encode(['success' => false, 'error' => 'No se encontró el dispositivo']);
}
?>
