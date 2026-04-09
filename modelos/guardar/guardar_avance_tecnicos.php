<?php
session_start();
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Inicializar conexión
$db = new MySQL("", "", "");

// Validar y escapar datos
$idTicket   = isset($_POST["id_ticket"]) ? $_POST["id_ticket"] : null;
$comentario = isset($_POST["avance"]) ? $_POST["avance"] : null;
$fecha      = date("Y-m-d");
$hora       = date("H:i:s");
$autorAvance = trim((string) (($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido_paterno'] ?? '')));

if (!$idTicket || !$comentario) {
    echo json_encode([
        "success" => false,
        "message" => "Datos incompletos: id_ticket o avance vacío."
    ]);
    exit;
}

$comentarioSeguro = $db->escape_string((string) $comentario);
$autorSeguro = $db->escape_string($autorAvance !== '' ? $autorAvance : 'Registro del sistema');
$avanceHtml = '<div data-avance-autor="' . $autorSeguro . '">' . $comentarioSeguro . '</div>';

// Ejecutar consulta
$sql = "INSERT INTO `avance_tecnicos` (`id_ticket`, `accion`, `fecha_avance`, `hora_avance`)
        VALUES ('$idTicket', '$avanceHtml', '$fecha', '$hora')";

$bl = $db->guardar($sql);

// Responder
if ($bl === 0) {
    echo json_encode([
        "success" => true,
        "message" => "Avance guardado correctamente",
        "avance" => [
            "accion" => $avanceHtml,
            "fecha_avance" => $fecha,
            "hora_avance" => $hora,
            "autor" => $autorAvance !== '' ? $autorAvance : 'Registro del sistema'
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
