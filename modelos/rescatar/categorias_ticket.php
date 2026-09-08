<?php
include("../../class/conexion.php");

$bdato = new MySQL("", "", "");
$query = "SELECT id_categoria, nombre_categoria, abreviacion, orden FROM categoria_de_ticket";
$result = $bdato->consulta($query);

$categorias = [];
while ($row = $bdato->fetch_array($result)) {
    $categorias[] = [
        'id_categoria' => $row['id_categoria'],
        'nombre_categoria' => $row['nombre_categoria'],
        'abreviacion' => $row['abreviacion'],
        'orden' => $row['orden']
    ];
}

echo json_encode($categorias);
