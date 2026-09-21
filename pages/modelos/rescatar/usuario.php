<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
    date_default_timezone_set('America/Santiago');

    // Recato  las variables que vienen del js
    $idUsuario = $_POST["id"];

    $listaUsuario = []; // Cambiado a un arreglo vacío
    $db = new MySQL("","","");
    $st = "SELECT * FROM usuarios WHERE id = '$idUsuario'";
    $consulta = $db->consulta($st);
    if($db->num_rows($consulta) > 0) {
        $row = $db->fetch_array($consulta);
        $listaUsuario['id']                 = $row['id'];
        $listaUsuario['nombre']             = $row['nombre'];
        $listaUsuario['apellido_paterno']   = $row['apellido_paterno'];
        $listaUsuario['apellido_materno']   = $row['apellido_materno'];
        $listaUsuario['anexo']              = $row['anexo'];
        $listaUsuario['email']              = $row['email'];
        $listaUsuario['id_area_trabajo']    = $row['id_area_trabajo'];

    }

    echo json_encode($listaUsuario); // Devuelve el arreglo con los datos del usuario
?>