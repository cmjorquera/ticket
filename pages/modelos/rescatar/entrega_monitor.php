<?php
// Iniciar sesión y requerir la conexión a la base de datos
session_start();
require_once '../../class/conexion.php';

$bdato = new MySQL('', '', ''); // Conexión a la base de datos

// Verificar si se ha proporcionado un ID a través de la URL
if (isset($_GET['id_equipo']) && is_numeric($_GET['id_equipo'])) {
    $id_equipo = intval($_GET['id_equipo']);

    // Consulta para obtener los monitores asociados al equipo
    $consultaMonitor = "
        SELECT 
            id_monitor,
            id_equipo,
            modelo_monitor,
            codigo_monitor,
            serie_monitor,
            tamano_monitor,
            resolucion_monitor,
            orden_monitor
        FROM equipo_monitor
        WHERE id_equipo = $id_equipo
        ORDER BY orden_monitor
    ";

    $resultadoMonitor = $bdato->consulta($consultaMonitor);

    $monitores = [];
    if ($bdato->num_rows($resultadoMonitor) > 0) {
        while ($monitor = $bdato->fetch_assoc($resultadoMonitor)) {
            $monitores[] = $monitor;
        }
    }

    // Devolver los datos en formato JSON
    echo json_encode([
        'success' => true,
        'monitores' => $monitores
    ]);
} else {
    // Si no se proporciona un ID o es inválido
    echo json_encode([
        'success' => false,
        'error' => 'ID de equipo no proporcionado o inválido'
    ]);
}
?>
