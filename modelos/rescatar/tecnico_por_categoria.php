<?php
include '../../class/conexion.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_POST['id_categoria'])) {
    echo json_encode(['success' => false, 'message' => 'Categoría no enviada']);
    exit;
}

$id_categoria = intval($_POST['id_categoria']);
$db = new MySQL('', '', '');
$st = "SELECT id_tecnico FROM categoria_tecnico WHERE id_categoria = $id_categoria LIMIT 1";
$resultado = $db->consulta($st);
$row = $db->fetch_array($resultado);

if ($row) {
    echo json_encode(['success' => true, 'id_tecnico' => $row['id_tecnico']]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se encontró técnico']);
}
?>
