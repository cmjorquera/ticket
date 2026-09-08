<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_ticket = $_POST['id_ticket'] ?? null;
    $usuario1 = $_POST['usuario1'] ?? null;
    $usuario2 = $_POST['usuario2'] ?? null;

    if (!$id_ticket || !$usuario1 || !$usuario2) {
        echo json_encode(['success' => false, 'error' => 'Faltan datos']);
        exit;
    }

    include_once('../../class/conexion.php');
    $bd = new MySQL("", "", ""); // Ajusta credenciales si las usas

    $sql = "SELECT id, emisor, receptor, mensaje,adjunto, fecha, hora 
            FROM ticket_conversaciones 
            WHERE id_ticket = '$id_ticket' 
              AND ((emisor = '$usuario1' AND receptor = '$usuario2') 
                OR (emisor = '$usuario2' AND receptor = '$usuario1')) 
              AND eliminado = 0
            ORDER BY id DESC";

    $resultado = $bd->consulta($sql);

    $mensajes = [];
    while ($row = $bd->fetch_array($resultado)) {
        $mensajes[] = [
            'id'      => $row['id'],
            'emisor'  => $row['emisor'],
            'receptor'=> $row['receptor'],
            'mensaje' => $row['mensaje'],
            'adjunto'=> $row['adjunto'],
            'fecha'   => $row['fecha'],
            'hora'    => $row['hora']
        ];
    }

    echo json_encode(['success' => true, 'mensajes' => $mensajes]);
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
