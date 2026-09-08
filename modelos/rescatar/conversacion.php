<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");

try {
    $bdato = new MySQL("", "", ""); // Cambia los parámetros según tu configuración
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al conectar con la base de datos']);
    exit;
}

$id_ticket = isset($_GET['id_ticket']) ? $_GET['id_ticket'] : '';

if (empty($id_ticket)) {
    echo json_encode(['error' => 'ID de ticket no proporcionado']);
    exit;
}

$sql = "SELECT id_ticket, id_usuario, id_tecnico, contenido, fecha, hora, tipo FROM conversaciones 
        WHERE id_ticket = '".$bdato->escape_string($id_ticket)."' ORDER BY fecha DESC, hora DESC";
$consulta = $bdato->consulta($sql);

$mensajes = [];

if ($bdato->num_rows($consulta) > 0) {
    while ($row = $bdato->fetch_array($consulta)) {
        $mensaje = [
            'id_ticket'     => $row['id_ticket'],
            'id_usuario'    => $row['id_usuario'],
            'id_tecnico'    => $row['id_tecnico'],
            'contenido'     => $row['contenido'],
            'fecha'         => $row['fecha'],
            'hora'          => $row['hora'],
            'tipo'          => $row['tipo']
        ];

        $mensajes[] = $mensaje;
    }
}

// Obtener adjuntos solo una vez
$adjuntos = [];
$adjunto_sql = "SELECT adjunto FROM archivos_adjuntos_ticket WHERE id_ticket = '".$bdato->escape_string($id_ticket)."'";
$adjunto_resultado = $bdato->consulta($adjunto_sql);
while ($adjunto_row = $bdato->fetch_array($adjunto_resultado)) {
    $adjuntos[] = $adjunto_row['adjunto'];
}

echo json_encode([
    'id_usuario' => !empty($mensajes) ? $mensajes[0]['id_usuario'] : '',
    'id_tecnico' => !empty($mensajes) ? $mensajes[0]['id_tecnico'] : '',
    'mensajes' => $mensajes,
    'adjuntos' => $adjuntos
]);
?>
