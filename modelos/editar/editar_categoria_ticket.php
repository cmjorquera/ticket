<?php
include("../../class/conexion.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metodo no permitido']);
    exit;
}

$bdato = new MySQL("", "", "");

$idCategoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;
$nombre = trim($_POST['nombre_categoria'] ?? '');
$abreviacion = trim($_POST['abreviacion'] ?? '');
$icono = trim($_POST['icono'] ?? '');
$orden = isset($_POST['orden']) ? intval($_POST['orden']) : 0;

if ($idCategoria <= 0) {
    echo json_encode(['success' => false, 'message' => 'Categoria invalida']);
    exit;
}

if ($nombre === '') {
    echo json_encode(['success' => false, 'message' => 'El nombre es obligatorio']);
    exit;
}

$nombreEsc = $bdato->escape_string($nombre);
$abreviacionEsc = $bdato->escape_string($abreviacion);
$iconoEsc = $bdato->escape_string($icono);

$sql = "UPDATE categoria_de_ticket
        SET nombre_categoria = '$nombreEsc',
            abreviacion = '$abreviacionEsc',
            icono = '$iconoEsc',
            orden = $orden
        WHERE id_categoria = $idCategoria";

$ok = $bdato->consulta($sql);

if (!$ok) {
    echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la categoria']);
    exit;
}

echo json_encode(['success' => true, 'message' => 'Categoria actualizada correctamente']);
