<?php
header('Content-Type: application/json; charset=utf-8');

include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$response = ["success" => false];

// Validación básica
if (!isset($_POST['id_ticket'], $_POST['emisor'], $_POST['receptor']) || !isset($_FILES['archivo'])) {
    echo json_encode($response);
    exit;
}

$id_ticket = $_POST['id_ticket'];
$emisor = $_POST['emisor'];
$receptor = $_POST['receptor'];
$fecha = date('Y-m-d');
$hora        = date('H:i:s');

// Manejo del archivo
$archivo = $_FILES['archivo'];
$nombre_archivo = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($archivo['name']));

// Ruta física (en el servidor)
$ruta_destino = '../../archivos/adjuntosConversacionTicket/' . $nombre_archivo;

// Ruta relativa (para mostrar en el navegador)
$ruta_relativa = 'archivos/adjuntosConversacionTicket/' . $nombre_archivo;

if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
    echo json_encode($response);
    exit;
}

// Insertar en la base de datos
// Conexión BD
$db = new MySQL("", "", "");

// 1. Insertar en ticket_conversaciones
$sql1 = "INSERT INTO ticket_conversaciones 
        (id_ticket, emisor, receptor, mensaje, adjunto, fecha, hora, leido, eliminado, creado_en) 
        VALUES 
        ('$id_ticket', '$emisor', '$receptor', '', '$ruta_relativa', '$fecha', '$hora', 0, 0, NOW())";

$ok1 = $db->consulta($sql1);

// 2. Insertar en archivos_adjuntos_ticket
$sql2 = "INSERT INTO archivos_adjuntos_ticket (id_ticket, adjunto) 
         VALUES ('$id_ticket', '$ruta_relativa')";
$ok2 = $db->consulta($sql2);

if ($ok1 && $ok2) {
    $response['success'] = true;
    $response['archivo'] = $ruta_relativa;
    $response['fecha'] = $fecha;
    $response['hora'] = $hora;
} else {
    $response['error'] = 'No se pudo guardar en ambas tablas';
}

echo json_encode($response);