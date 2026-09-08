<?php
include("../../class/conexion.php");

$id_usuario = $_POST['id_usuario'];

$db = new MySQL("", "", "");

$query = "SELECT id, titulo, detalle, fecha, recordar, completada FROM recordatorio WHERE id_usuario = '$id_usuario'";
$result = $db->consulta($query);

$tasks = [];
while ($row = $db->fetch_array($result)) {
    $tasks[] = [
        'id'            => $row['id'],
        'titulo'        => $row['titulo'],
        'detalle'       => $row['detalle'],
        'fecha'         => $row['fecha'],
        'recordar'      => $row['recordar'],
        'completada'    => $row['completada']
    ];
}

echo json_encode(['success' => true, 'data' => $tasks]);

$db->CerrarConexion();
?>