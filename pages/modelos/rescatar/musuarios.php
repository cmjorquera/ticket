<?php

header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
ob_start(); // Inicia control de buffer de salida
date_default_timezone_set('America/Santiago');

$listaUsuarios = [];
$db = new MySQL("", "", "");
$st = "SELECT * FROM usuarios ORDER BY nombre ASC ";
$consulta = $db->consulta($st);

while ($row = $db->fetch_array($consulta)) {
    $listaUsuarios[] = [
        'id'                => $row['id'],
        'nombre'            => $row['nombre'],
        'apellido_paterno'  => $row['apellido_paterno'],
        'apellido_materno'  => $row['apellido_materno'],
        'anexo'             => $row['anexo'],
        'email'             => $row['email']
    ];
}

ob_end_clean(); // Limpia el buffer de salida
echo json_encode($listaUsuarios);
