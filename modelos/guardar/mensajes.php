<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// ✅ Recolectar los datos
$de     = $_POST['emisor']    ?? null;
$para   = $_POST['receptor']  ?? null;
$mensaje = trim($_POST['mensaje'] ?? '');

if (!$de || !$para || $mensaje === '') {
    echo json_encode([
        "success" => false,
        "message" => "Faltan datos requeridos"
    ]);
    exit;
}

// ✅ Preparar valores
$fecha = date('Y-m-d');
$hora = date('H:i:s');
$tiempo = date('Y-m-d H:i:s');

$leido = 0;
$urgente = 0;
$eliminado = 0;
$prioridad = 1;         // por defecto
$id_conversacion = 0;   // o puede ser generado/relacionado

// ✅ Conectar
$db = new MySQL("", "", "");
// $mensaje = $db->escape($mensaje);

// ✅ Insertar
$sql = "INSERT INTO mensajes (mensaje, para, de, tiempo, leido, urgente, fecha, hora, eliminado, prioridad, id_conversacion)
        VALUES ('$mensaje', '$para', '$de', '$tiempo', '$leido', '$urgente', '$fecha', '$hora', '$eliminado', '$prioridad', '$id_conversacion')";

$result = $db->consulta($sql);

if ($result) {
    echo json_encode([
        "success" => true,
        "message" => "Mensaje insertado correctamente"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error al guardar el mensaje",
        "sql_error" => $db->error()
    ]);
}
?>
