<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
header('Content-Type: application/json');

$db = new MySQL('', '', '');

header('Content-Type: application/json; charset=utf-8');

if(isset($_GET['estado'])) {
    $estado = $db->escape_string($_GET['estado']);
    $query = "SELECT COUNT(*) as total FROM tickets WHERE id_estado = '$estado'";
    $result = $db->consulta($query);
    if ($result) {
        $data = $db->fetch_array($result);
        $totalEstado = $data['total'];

        // Supongamos que también recuperas el nombre del estado
        $queryNombre = "SELECT nombre FROM estados_ticket WHERE id = '$estado'";
        $resultNombre = $db->consulta($queryNombre);
        $nombreEstado = $db->fetch_array($resultNombre)['nombre'];

        $queryTotal = "SELECT COUNT(*) as total FROM tickets";
        $resultTotal = $db->consulta($queryTotal);
        $totalTickets = $db->fetch_array($resultTotal)['total'];

        $porcentaje = ($totalEstado / $totalTickets) * 100;
        $response = [
            'total'     => $totalEstado,
            'porcentaje' => round($porcentaje, 2),
            'estadoTexto' => $nombreEstado
        ];
        echo json_encode($response);
    }
} else {
    // Devuelve conteos para todos los estados para la carga inicial del gráfico
    $query = "SELECT id_estado, COUNT(*) as total FROM tickets GROUP BY id_estado ORDER BY id_estado";
    $result = $db->consulta($query);
    $data = [];
    while ($row = $db->fetch_array($result)) {
        $data[] = $row['total'];
    }
    echo json_encode($data);
}
?>

