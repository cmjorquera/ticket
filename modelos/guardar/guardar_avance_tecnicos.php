<?php
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Inicializar conexión
$db = new MySQL("", "", "");

// Validar y escapar datos
$idTicket   = isset($_POST["id_ticket"]) ? $_POST["id_ticket"] : null;
$comentario = isset($_POST["avance"]) ? $_POST["avance"] : null;
$fecha      = date("Y-m-d");
$hora       = date("H:i:s");

if (!$idTicket || !$comentario) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos: id_ticket o avance vacío."
    ]);
    exit;
}

// Ejecutar consulta
$sql = "INSERT INTO `avance_tecnicos` (`id_ticket`, `accion`, `fecha_avance`, `hora_avance`)
        VALUES ('$idTicket', '$comentario', '$fecha', '$hora')";

$bl = $db->guardar($sql);

// Responder
if ($bl === 0) {
    echo json_encode([
        "success" => true,
        "message" => "Avance guardado correctamente",
        "avance" => [
            "accion" => $comentario,
            "fecha_avance" => $fecha,
            "hora_avance" => $hora
        ]
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Error al guardar el avance"
    ]);
}

$db->CerrarConexion();
?>
