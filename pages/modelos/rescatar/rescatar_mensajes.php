<?php
header('Content-Type: application/json');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$id_ticket = $_POST["id"];

$mensajeData = [];

$db = new MySQL("","","");
$consulta = $db->consulta("SELECT m.id, m.mensaje, m.para, m.de, m.tiempo, m.urgente, m.fecha, m.hora,
                        remitente.nombre as 'nombreRemitente', remitente.apellido_paterno as 'apellidoRemitente', 
                        destinatario.nombre as 'nombreDestinatario', destinatario.apellido_paterno as 'apellidoDestinatario'
                        FROM mensajes m
                        JOIN usuarios remitente ON m.de = remitente.id
                        JOIN usuarios destinatario ON m.para = destinatario.id
                        WHERE m.id = '$id_ticket'");

if($db->num_rows($consulta) > 0) {
    $row = $db->fetch_array($consulta);

    $mensajeData['id'] = $row['id'];
    $mensajeData['mensaje'] = $row['mensaje'];
    $mensajeData['fecha'] = $row['fecha'];
    $hora = date('H:i', strtotime($row['hora'])); // Formatea la hora si es necesario
    $mensajeData['hora'] = $hora;
    $mensajeData['nombreRemitente'] = $row['nombreRemitente'];
    $mensajeData['apellidoRemitente'] = $row['apellidoRemitente'];
    $mensajeData['nombreDestinatario'] = $row['nombreDestinatario'];
    $mensajeData['apellidoDestinatario'] = $row['apellidoDestinatario'];
}

echo json_encode($mensajeData);
?>