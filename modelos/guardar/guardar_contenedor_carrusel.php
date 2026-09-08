<?php
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = isset($_POST['titulo']) ? $_POST['titulo'] : '';
    $imagen = isset($_FILES['urlImagen']) ? $_FILES['urlImagen'] : null;
    $url = isset($_POST['urlBeneficio']) ? $_POST['urlBeneficio'] : '';
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';

    // Verificar que todos los campos estén completos
    if (empty($titulo) || empty($imagen) || empty($url) || empty($descripcion)) {
        echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
        exit;
    }

    // Procesar la imagen
    $targetDir = "../../imagenes/"; // Directorio donde se guardarán las imágenes
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $targetFile = $targetDir . basename($imagen["name"]);
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Validar el formato de la imagen
    $check = getimagesize($imagen["tmp_name"]);
    if ($check === false) {
        echo json_encode(['status' => 'error', 'message' => 'El archivo no es una imagen válida.']);
        exit;
    }

    // Mover la imagen al directorio de destino
    if (!move_uploaded_file($imagen["tmp_name"], $targetFile)) {
        echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen.']);
        exit;
    }

    // Ajustar la ruta de la imagen para la base de datos
    $relativeFilePath = str_replace("../../", "", $targetFile);

    // Conectar a la base de datos e insertar los datos
    $db = new MySQL("", "", ""); // Ajusta los valores de conexión
    $sql = "INSERT INTO `beneficios_destacados`(`titulo`, `img`, `descripcion`, `url`) 
            VALUES ('$titulo', '$relativeFilePath', '$descripcion', '$url')";
    
    if ($db->guardar($sql) === 0) { // Asumiendo que el método guardar devuelve 0 en caso de éxito
        echo json_encode(['status' => 'success', 'message' => 'Beneficio insertado correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al insertar el beneficio.']);
    }

    $db->CerrarConexion();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método de solicitud no permitido.']);
}
?>
