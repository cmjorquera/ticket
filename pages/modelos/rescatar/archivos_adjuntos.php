<?php
header('Content-Type: application/json');
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');
$bdato = new MySQL("", "", ""); 

$id_ticket = $_POST['id_ticket'];

$sql = "SELECT adjunto FROM archivos_adjuntos_ticket WHERE id_ticket = '$id_ticket'";
$resultado = $bdato->consulta($sql);

$archivos = [];
while ($row = $bdato->fetch_array($resultado)) {
    $archivos[] = [
        'nombreArchivo' => basename($row['adjunto']), // Nombre del archivo
        'urlArchivo'    => $row['adjunto'] // URL del archivo
    ];
}

echo json_encode($archivos);
?>