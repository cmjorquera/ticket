<?php
header('Content-Type: application/json');  // Esto indicará explícitamente al navegador (y a jQuery) que la respuesta debe ser tratada como JSON.
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// Asumiendo que $_POST['id'] es siempre un entero (validación en el cliente)
$id_ticket = $_POST['id'] ?? null;

if (null === $id_ticket) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado.']);
    exit;
}

$db = new MySQL("", "", "");

// Eliminar archivos adjuntos asociados al ticket
$sqlArchivos = "DELETE FROM archivos_adjuntos_ticket WHERE id_ticket = '$id_ticket'";
$resultArchivos = $db->guardar($sqlArchivos);

// Eliminar proceso asociado al ticket
$sqlProceso = "DELETE FROM proceso_tickets WHERE id_ticket = '$id_ticket'";
$resultProceso = $db->guardar($sqlProceso);

// Eliminar el ticket
$sqlTicket = "DELETE FROM tickets WHERE id_ticket = '$id_ticket'";
$resultTicket = $db->guardar($sqlTicket);

if ($resultArchivos === 0 && $resultProceso === 0 && $resultTicket === 0) {
    echo json_encode(['success' => true, 'message' => "Ticket $id_ticket y todos sus datos asociados eliminados correctamente"]);
} else {
    $errors = [];
    if ($resultArchivos !== 0) {
        $errors[] = "Error al eliminar los archivos adjuntos, código de error: $resultArchivos";
    }
    if ($resultProceso !== 0) {
        $errors[] = "Error al eliminar el proceso del ticket, código de error: $resultProceso";
    }
    if ($resultTicket !== 0) {
        $errors[] = "Error al eliminar el ticket, código de error: $resultTicket";
    }
    echo json_encode(['success' => false, 'error' => implode(', ', $errors)]);
}

$db->CerrarConexion();
?>
