<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");

$db = new MySQL("", "", "");

$id = $_POST['id'];
$nombre = $_POST['nombre'];
$url = $_POST['url'];

// Manejar la imagen si existe
if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
    $image = $_FILES['image'];

    // Validar el tipo de archivo
    $validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($image['type'], $validImageTypes)) {
        echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido']);
        exit;
    }

    // Ruta para guardar la imagen
    $imageDir = '../../imagenes/';
    $imageName = uniqid() . '_' . basename($image['name']); // Nombre único
    $imagePath = $imageDir . $imageName;

    // Mover la imagen a la carpeta
    if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la imagen']);
        exit;
    }

    // Actualizar con nueva imagen
    $sql = "UPDATE contenedor SET 
            nombre = '$nombre', 
            url_ = '$url', 
            imagen = '$imageName' 
            WHERE id = $id";
} else {
    // Actualizar sin cambiar la imagen
    $sql = "UPDATE contenedor SET 
            nombre = '$nombre', 
            url_ = '$url'
            WHERE id = $id";
}

// Ejecutar la consulta
$resultado = $db->consulta($sql);

if ($resultado) {
    echo json_encode([
        'success' => true,
        'newImage' => isset($imageName) ? $imageName : null // Enviar nueva imagen si se actualizó
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el contenedor']);
}
?>
