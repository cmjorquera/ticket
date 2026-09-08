<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include_once('../../class/conexion.php');
    $bd = new MySQL('', '', '');

    $id_ticket = $_POST['id_ticket'] ?? null;
    $emisor    = $_POST['emisor']   ?? null;
    $receptor  = $_POST['receptor'] ?? null;
    $mensaje   = $_POST['mensaje'] ?? null;

    if (!$id_ticket || !$emisor || !$receptor || !$mensaje) {
        echo json_encode(['success' => false, 'error' => 'Faltan datos']);
        exit;
    }

    $fecha = date('Y-m-d');
    $hora = date('H:i:s');

    $sql = "INSERT INTO ticket_conversaciones (id_ticket, emisor, receptor, mensaje, fecha, hora, leido, eliminado, creado_en)
            VALUES ('$id_ticket', '$emisor', '$receptor', '$mensaje', '$fecha', '$hora', 0, 0, NOW())";

    if ($bd->consulta($sql)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al guardar en la BD']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}