<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'mensaje' => 'ID no proporcionado']);
    exit;
}

require_once '../../class/conexion.php';
$bdato = new MySQL('', '', '');

$id = intval($_POST['id']);
$sql = "UPDATE eventos SET eliminado = 'sí' WHERE id = $id";

if ($bdato->consulta($sql)) {
    echo json_encode(['success' => true, 'mensaje' => 'Evento marcado como eliminado']);
} else {
    echo json_encode(['success' => false, 'mensaje' => 'Error al eliminar el evento']);
}
?>
