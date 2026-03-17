<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualieqweqweqwezar Contraseñas</title>
</head>
<body>
<?php

require_once '../class/conexion.php'; 
$bdato = new MySQL("", "", ""); // Asegúrate de utilizar las credenciales correctas

// La contraseña que deseas asignar a todos los usuarios (en este caso '123456')
$nuevaClave = '123456';

// Consulta para seleccionar todos los usuarios
$sql = "SELECT id FROM usuarios";
$resultado = $bdato->consulta($sql);

// Recorremos todos los usuarios y actualizamos sus contraseñas a la versión encriptada de '123456'
while ($row = $bdato->fetch_array($resultado)) {
    $idUsuario = $row['id'];

    // Encriptamos la nueva contraseña con password_hash() individualmente para cada usuario
    $hashClaveSegura = password_hash($nuevaClave, PASSWORD_DEFAULT);

    // Actualizamos la contraseña encriptada en la base de datos
    $sqlUpdate = "UPDATE usuarios SET clave = '$hashClaveSegura' WHERE id = $idUsuario";
    $bdato->consulta($sqlUpdate);
    echo "Clave del usuario con ID $idUsuario actualizada a su versión encriptada.<br>";
}

echo "Proceso completado.";
?>

</body>
</html>
