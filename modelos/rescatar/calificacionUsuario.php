<?php
include("../../class/conexion.php");
header('Content-Type: application/json; charset=utf-8');

// Validar que se reciba el ID del ticket
$id_ticket = isset($_POST['id_ticket']) ? intval($_POST['id_ticket']) : 0;

if ($id_ticket <= 0) {
    echo json_encode(["success" => false, "message" => "ID de ticket inv¨¢lido"]);
    exit;
}

// Conectar con la base
$db = new MySQL("", "", "");

// Consultar calificaci¨®n
$sql = "SELECT id, id_ticket, id_usuario, id_tecnico, id_calificacion, comentario, fecha, hora,ip_usuario
        FROM calificacion_tickett 
        WHERE id_ticket = $id_ticket";

$result = $db->consulta($sql);

if ($row = $db->fetch_array($result)) {
    echo json_encode([
        "success" => true,
        "data" => [
            "id"              => $row['id'],
            "id_ticket"       => $row['id_ticket'],
            "id_usuario"      => $row['id_usuario'],
            "id_tecnico"      => $row['id_tecnico'],
            "id_calificacion" => $row['id_calificacion'],
            "comentario"      => $row['comentario'],
            "fecha"           => $row['fecha'],
            "hora"            => $row['hora'],
            "ip_usuario"        => $row['ip_usuario']

        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "No se encontr¨® calificaci¨®n para este ticket"]);
}
?>
