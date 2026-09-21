<?php
include("../../class/conexion.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit;
}

$bdato = new MySQL("", "", "");
$idCategoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;

if ($idCategoria <= 0) {
    echo json_encode(['success' => false, 'message' => 'Categoria invalida']);
    exit;
}

$sqlActual = "SELECT estado FROM categoria_de_ticket WHERE id_categoria = $idCategoria LIMIT 1";
$resultadoActual = $bdato->consulta($sqlActual);

if (!$resultadoActual || mysqli_num_rows($resultadoActual) === 0) {
    echo json_encode(['success' => false, 'message' => 'Categoria no encontrada']);
    exit;
}

$fila = mysqli_fetch_assoc($resultadoActual);
$estadoActual = (int)($fila['estado'] ?? 0);
$nuevoEstado = $estadoActual === 1 ? 0 : 1;

$sqlUpdate = "UPDATE categoria_de_ticket
              SET estado = $nuevoEstado
              WHERE id_categoria = $idCategoria";

$ok = $bdato->consulta($sqlUpdate);

if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'No se pudo cambiar el estado']);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => $nuevoEstado === 1 ? 'Categoria activada' : 'Categoria desactivada',
    'estado' => $nuevoEstado
]);
