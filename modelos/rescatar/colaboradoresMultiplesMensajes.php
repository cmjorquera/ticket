<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Verificar si se envían múltiples IDs
$ids = isset($_POST["ids"]) ? $_POST["ids"] : [];

// Validar que $ids sea un array y no esté vacío
if (!is_array($ids) || empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron IDs válidos']);
    exit;
}

$db = new MySQL("", "", ""); // Conexión a la base de datos
$listaUsuarios = [];

// Convertir IDs a enteros para evitar inyección SQL
$idsString = implode(',', array_map('intval', $ids));

// Consulta SQL para obtener usuarios con los IDs especificados
$st = "SELECT id, nombre, apellido_paterno, apellido_materno, anexo, email 
       FROM usuarios 
       WHERE id IN ($idsString) 
       ORDER BY nombre ASC";

$consulta = $db->consulta($st);

while ($row = $db->fetch_array($consulta)) {
    $listaUsuarios[] = [
        'id'                => $row['id'],
        'nombre'            => $row['nombre'],
        'apellido_paterno'  => $row['apellido_paterno'],
        'apellido_materno'  => $row['apellido_materno'],
        'anexo'             => $row['anexo'],
        'email'             => $row['email']
    ];
}

// Devolver los datos como JSON
echo json_encode(['success' => true, 'usuarios' => $listaUsuarios]);
?>
