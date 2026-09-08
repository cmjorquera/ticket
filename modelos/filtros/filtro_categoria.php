<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
header('Content-Type: application/json');

$db = new MySQL('', '', '');


$areaId = isset($_GET['area']) ? $db->escape_string($_GET['area']) : null;

// Modifica la cláusula WHERE para que maneje el caso cuando areaId es nulo
$whereClause = $areaId ? "WHERE u.id_area_trabajo = '$areaId'" : "";

$query = "SELECT ct.nombre_categoria, COUNT(t.id_ticket) as total 
          FROM categoria_de_ticket ct 
          LEFT JOIN tickets t ON ct.id_categoria = t.id_categoria_ticket 
          LEFT JOIN usuarios u ON u.id = t.id_usuario
          $whereClause
          GROUP BY ct.nombre_categoria 
          ORDER BY ct.nombre_categoria";

$result = $db->consulta($query);
$data = [];
$categories = [];

while ($row = $db->fetch_array($result)) {
    $data[] = $row['total'];
    $categories[] = $row['nombre_categoria'];
}

echo json_encode(['data' => $data, 'categories' => $categories]);
?>