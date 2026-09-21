<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$id1 = $_POST['usuario1'] ?? null;
$id2 = $_POST['usuario2'] ?? null;

if (!$id1 || !$id2) {
    echo json_encode(["success" => false, "message" => "Faltan IDs"]);
    exit;
}

$db = new MySQL("", "", "");
$sql = "SELECT mensaje, de, para, tiempo, hora,fecha
        FROM mensajes 
        WHERE ((de = '$id1' AND para = '$id2') OR (de = '$id2' AND para = '$id1'))
        ORDER BY hora ASC";
        
        
        

$resultado = $db->consulta($sql);

$mensajes = [];
while ($row = $db->fetch_array($resultado)) {
    $mensajes[] = $row;
}

echo json_encode(["success" => true, "mensajes" => $mensajes]);
