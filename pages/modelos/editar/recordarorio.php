<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");

$db = new MySQL("", "", "");

$id = $_POST['id'];
$titulo = $_POST['titulo'];
$detalle = $_POST['detalle'];
$fecha = $_POST['fecha'];

// Verificar si los campos necesarios están presentes
if (!isset($id) || !isset($titulo) || !isset($detalle) || !isset($fecha)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

$sql = "UPDATE `recordatorio` SET `titulo` = ?, `detalle` = ?, `fecha` = ? WHERE `id` = ?";
$stmt = $db->prepare($sql);

if ($stmt) {
    $stmt->bind_param('sssi', $titulo, $detalle, $fecha, $id);
    $resultado = $stmt->execute();

    if ($resultado) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta']);
}

$db->close();
?>
