<?php
header('Content-Type: application/json');
require_once '../../class/conexion.php';

// Reemplaza con tus credenciales reales o las que usas en todo el sistema
$bd = new MySQL("localhost", "acceso_usuario", "clave_secreta");

// Solo ejecuta si llegan los parámetros requeridos
if (isset($_POST['id_ticket']) && isset($_POST['receptor'])) {
    $id_ticket = intval($_POST['id_ticket']);
    $receptor = intval($_POST['receptor']);

$sql = "UPDATE ticket_conversaciones 
        SET leido = 1 
        WHERE id_ticket = $id_ticket 
          AND emisor = $receptor";

              
              echo $sql;

    if ($bd->consulta($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Consulta SQL fallida']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Faltan parámetros']);
}
