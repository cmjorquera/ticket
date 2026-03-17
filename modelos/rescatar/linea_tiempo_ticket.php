<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include("../../class/conexion.php");

// Validar ID
$id_ticket = isset($_POST['id_ticket']) ? intval($_POST['id_ticket']) : 0;
if ($id_ticket <= 0) {
    echo json_encode(["error" => "ID inválido"]);
    exit;
}

// Conexión
$bd = new MySQL("", "", ""); // Rellena si usas credenciales o ya vienen por config

// Consulta
$sql = "SELECT fecha_creacion_inicio, hora_creacion_inicio,
               fecha_asignacion_tecnico, hora_asignacion_tecnico,
               fecha_comienzo_ticket, hora_comienzo_ticket,
               fecha_termino_ticket, hora_termino_ticket,
               fecha_cierre_ticket, hora_cierre_ticket
        FROM proceso_tickets
        WHERE id_ticket = $id_ticket
        LIMIT 1";

$result = $bd->consulta($sql);

if (!$result || $bd->num_rows($result) === 0) {
    echo json_encode(["error" => "No se encontró información"]);
    exit;
}

// Obtener y devolver datos
$data = $bd->fetch_assoc($result);
echo json_encode($data);
