<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");

try {
    $bdato = new MySQL("", "", ""); // Cambia los parámetros según tu configuración
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al conectar con la base de datos']);
    exit;
}

$id_ticket      = isset($_POST['id_ticket']) ? $_POST['id_ticket'] : '';
$contenido      = isset($_POST['contenido']) ? $_POST['contenido'] : '';
$tipo           = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$id_usuario     = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : '';
$id_tecnico     = isset($_POST['id_tecnico']) ? $_POST['id_tecnico'] : '';
$accion         = isset($_POST['accion']) ? $_POST['accion'] : '';

if (empty($id_ticket) || empty($id_usuario) || empty($id_tecnico)) {
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$fecha = date('Y-m-d');
$hora = date('H:i:s');

// Insertar el mensaje en la tabla de conversaciones solo si el contenido no está vacío
if (!empty(trim($contenido))) {
    $sql = "INSERT INTO conversaciones (id_ticket, id_usuario, id_tecnico, contenido, fecha, hora, tipo) VALUES (
        '".$bdato->escape_string($id_ticket)."', 
        '".$bdato->escape_string($id_usuario)."', 
        '".$bdato->escape_string($id_tecnico)."', 
        '".$bdato->escape_string($contenido)."', 
        '".$fecha."', 
        '".$hora."', 
        '".$bdato->escape_string($tipo)."'
    )";

    if ($bdato->guardar($sql) != 0) {
        echo json_encode(['error' => 'Error al enviar el mensaje']);
        exit;
    }
}

$adjuntos = [];
if (isset($_FILES['adjunto']) && count($_FILES['adjunto']['name']) > 0) {
    $uploadDir = '../../archivos/ticket/';

    foreach ($_FILES['adjunto']['name'] as $key => $name) {
        $tmpName = $_FILES['adjunto']['tmp_name'][$key];
        $name = "ticket_A00" . $id_ticket . "_" . basename($name);
        $uploadFile = $uploadDir . $name;

        if (move_uploaded_file($tmpName, $uploadFile)) {
            $adjuntos[] = $name;
            $sql = "INSERT INTO archivos_adjuntos_ticket (id_ticket, adjunto) VALUES ('".$bdato->escape_string($id_ticket)."', '".$bdato->escape_string($name)."')";
            $bdato->guardar($sql);
        }
    }
}

echo json_encode(['success' => 'Mensaje enviado', 'adjuntos' => $adjuntos]);
?>
