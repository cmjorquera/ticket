<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");  // Verifica que la ruta es correcta
ob_start(); // Inicia control de buffer de salida
date_default_timezone_set('America/Santiago');

$listaCategorias = [];
$db = new MySQL("", "", "");
$st = "SELECT id_categoria, nombre_categoria, abreviacion
FROM categoria_de_ticket
WHERE estado = 1
ORDER BY id_categoria ASC;
";

$consulta = $db->consulta($st);

while ($row = $db->fetch_array($consulta)) {
    $listaCategorias[] = [
        'id'                => $row['id_categoria'],
        'nombre_categoria'  => $row['nombre_categoria'],
        'abreviacion'       => $row['abreviacion']


        
    ];
}
ob_end_clean(); // Limpia el buffer de salida
echo json_encode($listaCategorias);
?>



