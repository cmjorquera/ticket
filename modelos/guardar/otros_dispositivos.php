<?php
header('Content-Type: application/json');

// Incluir la conexión a la base de datos
require_once '../../class/conexion.php';

if (!isset($_POST['nombre_dispositivo']) || empty(trim($_POST['nombre_dispositivo']))) {
    echo json_encode(['success' => false, 'error' => 'El nombre del dispositivo no puede estar vacío']);
    exit;
}

// Obtener el nombre del dispositivo desde la solicitud
$nombreDispositivo = trim($_POST['nombre_dispositivo']);

// Instanciar la conexión a la base de datos
$conexion = new MySQL('', '', ''); // Agrega los parámetros necesarios si aplica

// Escapar el valor para evitar inyección SQL
$nombreDispositivo = $conexion->escape_string($nombreDispositivo);

// Verificar si el dispositivo ya existe
$sqlVerificar = "SELECT COUNT(*) AS total FROM tipos_dispositivos WHERE nombre_dispositivo = '$nombreDispositivo'";
$resultadoVerificar = $conexion->consulta($sqlVerificar);
$row = $conexion->fetch_assoc($resultadoVerificar);

if ($row['total'] > 0) {
    echo json_encode(['success' => false, 'error' => 'El dispositivo ya existe en la base de datos']);
    exit;
}

// Insertar el nuevo dispositivo
$sqlInsertar = "INSERT INTO tipos_dispositivos (nombre_dispositivo) VALUES ('$nombreDispositivo')";
if ($conexion->guardar($sqlInsertar) === 0) { // Verifica si la consulta fue exitosa
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar en la base de datos: ' . $conexion->getLastError()]);
}

exit;
