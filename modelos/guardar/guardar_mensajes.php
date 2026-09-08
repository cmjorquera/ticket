<?php



// echo "HOLA GUADALUPE";
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$mensaje = filter_input(INPUT_POST, 'mensaje', FILTER_SANITIZE_STRING);
$de = filter_input(INPUT_POST, 'de', FILTER_SANITIZE_NUMBER_INT);
$para = filter_input(INPUT_POST, 'para', FILTER_SANITIZE_NUMBER_INT);
$idTicket = filter_input(INPUT_POST, 'idTicket', FILTER_VALIDATE_INT);

if ($idTicket === false) {
    echo json_encode(['error' => 'ID de Ticket inválido.']);
    exit;
}


$db = new MySQL("", "", "");
$sql = "INSERT INTO `mensajes_chat` (`mensaje`, `de`, `para`, `id_conversacion`, `fecha_hora`, `leido`, `urgente`, `eliminado`, `prioridad`)
                             VALUES ('$mensaje', '$de', '$para', NULL, NOW(), 0, 0, 0, NULL)";

                echo $sql."*******";
$bl = $db->guardar($sql);

if ($bl === 0) {
    echo json_encode(['success' => 'Mensaje insertado correctamente.']);
} else {
    echo json_encode(['error' => 'Error al insertar el mensaje.']);
}

$db->CerrarConexion();



?>

