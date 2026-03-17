<?php
header('Content-Type: application/json');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$id_ticket = $_POST["id"];
$mensajeData = [];

$db = new MySQL("", "", "");
$consulta = $db->consulta("SELECT `id_ticket`, `accion`, `fecha_avance`, `hora_avance` 
                            FROM `avance_tecnicos` WHERE `id_ticket` = '$id_ticket' ORDER BY `fecha_avance` ASC, `hora_avance` ASC");

if ($db->num_rows($consulta) > 0) {
    $mensajeData['acciones'] = [];
    while ($row = $db->fetch_array($consulta)) {
        $mensajeData['acciones'][] = [
            'id_ticket' => $row['id_ticket'],
            'accion' => $row['accion'],
            'fecha_avance' => $row['fecha_avance'],
            'hora_avance' => $row['hora_avance']
        ];
    }
}
echo json_encode($mensajeData);
?>
