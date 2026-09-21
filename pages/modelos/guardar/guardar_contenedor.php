<?php
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$nombreContenedor = $_POST['nombreContenedor'] ?? '';
$url = $_POST['url'] ?? '';
$idUsuario = $_POST['idUsuario'] ?? '';
$imagen = $_FILES['image'] ?? '';
$fecha = date("Y-m-d");
$hora = date("H:i:s");

// Verificar si la variable $imagen está vacía o no se ha proporcionado ningún archivo
if (empty($imagen) || (is_array($imagen) && $imagen['error'] !== UPLOAD_ERR_OK)) {
    // Si está vacía o hay un error al subir el archivo, asignar el valor predeterminado
    $filename = "logo_contenedor.png";
} else {
    // Si se proporciona un archivo, obtener el nombre del archivo
    $filename = basename($imagen['name']);

    // Mover el archivo subido a la ubicación deseada
    $tmp_name = $imagen['tmp_name'];
    $destination = "../../imagenes/" . $filename;
    if (!move_uploaded_file($tmp_name, $destination)) {
        // Manejar el error si no se puede mover el archivo
        echo "Error al mover el archivo.";
        exit; // Salir del script si hay un error
    }
}

// Crear la conexión a la base de datos
$db = new MySQL("", "", "");  // Asegúrate de cambiar estos detalles

// Preparar y ejecutar la consulta SQL para insertar los datos en la base de datos
$sql = "INSERT INTO `contenedor` (`id_usuario`, `nombre`, `url_`, `imagen`, `fecha`, `hora`)
        VALUES ('$idUsuario', '$nombreContenedor', '$url', '$filename', '$fecha', '$hora')";
$params = [$idUsuario, $nombre, $url, $filename];
$bl = $db->guardar($sql, $params);

// Verificar si la consulta se ejecutó correctamente
if ($bl === 0) {
    echo "Cuenta $nombreContenedor con ID $url insertada correctamente. $sql";
} else {
    echo "Error al insertar la cuenta: $bl";
}

// Cerrar la conexión a la base de datos
$db->CerrarConexion();
?>
