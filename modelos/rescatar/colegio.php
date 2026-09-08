<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");

$db = new MySQL("", "", "");
$st = "SELECT id_colegio, nom_colegio FROM colegio WHERE estado = 1 ORDER BY nom_colegio ASC";
$consulta = $db->consulta($st);

$colegios = [];
while ($row = $db->fetch_array($consulta)) {
    $colegios[] = [
        'id' => $row['id_colegio'],
        'nombre' => $row['nom_colegio']
    ];
}

echo json_encode($colegios);
?>
