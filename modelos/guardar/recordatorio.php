<?php
include("../../class/conexion.php");

$accion = $_POST['accion'];
$db = new MySQL("", "", "");

switch ($accion) {
    case 'crear_recordatorio':
        $id_usuario     = $_POST['id_usuario'];
        $titulo         = $_POST['titulo'];
        $detalle        = $_POST['detalle'];
        $fecha          = $_POST['fecha'];

        // Escapar las variables para evitar inyección SQL
        // $id_usuario = $db->escape_string($id_usuario);
        // $titulo = $db->escape_string($titulo);
        // $detalle = $db->escape_string($detalle);
        // $fecha = $db->escape_string($fecha);

        $query = "INSERT INTO recordatorio (id_usuario, titulo, detalle, fecha) 
                  VALUES ('$id_usuario', '$titulo', '$detalle', '$fecha')";

        $bl = $db->guardar($query);
        if ($bl == 0) {
            // Obtener el ID del nuevo registro
            $id = $db->consulta("SELECT LAST_INSERT_ID() AS id")->fetch_array()['id'];
            echo json_encode(['success' => true, 'id' => $id]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'modificar_recordatorio':
        $id         = $_POST['id'];
        $recordar   = $_POST['recordar'];

        // $id = $db->escape_string($id);
        // $recordar = $db->escape_string($recordar);

        $query = "UPDATE recordatorio SET recordar = '$recordar' WHERE id = '$id'";
        $bl = $db->guardar($query);

        if ($bl == 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
    case 'recordatorio_terminado':
        $id         = $_POST['id'];

        $query = "UPDATE recordatorio SET completada = 'SI' WHERE id = '$id'";

        $bl = $db->guardar($query);

        if ($bl == 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }

}

$db->CerrarConexion();
?>