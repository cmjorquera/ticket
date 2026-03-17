<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$listaUsuario = [];
$db = new MySQL("","","");
$st = "SELECT * FROM usuarios WHERE id_area_trabajo = 1 AND id <> 27";

$consulta = $db->consulta($st);
if($db->num_rows($consulta) > 0) {
  while($row = $db->fetch_array($consulta)) {
    $listaUsuario[] = [
      'id'                 => $row['id'],
      'nombre'             => $row['nombre'],
      'apellido_paterno'   => $row['apellido_paterno'],
      'apellido_materno'   => $row['apellido_materno'],
      'anexo'              => $row['anexo'],
      'email'              => $row['email'],
      'id_area_trabajo'    => $row['id_area_trabajo']
    ];
  }
}

echo json_encode($listaUsuario);
?>