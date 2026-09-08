<?php
// Iniciar sesión y requerir la conexión a la base de datos
session_start();
require_once '../../class/conexion.php';

$bdato = new MySQL('', '', ''); // Conexión a la base de datos

// Verificar si se ha proporcionado un ID a través de la URL
if (isset($_GET['id_equipo']) && is_numeric($_GET['id_equipo'])) {
    $id_equipo = intval($_GET['id_equipo']);

    // Consulta para obtener los módulos de memoria asociados al equipo
    $consultaMemoria = "
        SELECT 
            id_memoria,
            designacion_memoria,
            formato_memoria,
            tipo_memoria,
            tamano_memoria,
            frecuencia_memoria,
            marca_memoria,
            orden_memoria
        FROM equipo_memoria
        WHERE id_equipo = $id_equipo
        ORDER BY orden_memoria
    ";

    $resultadoMemoria = $bdato->consulta($consultaMemoria);

    $memorias = [];
    if ($bdato->num_rows($resultadoMemoria) > 0) {
        while ($memoria = $bdato->fetch_assoc($resultadoMemoria)) {
            $memorias[] = $memoria;
        }
    }

    // Devolver los datos en formato JSON
    echo json_encode([
        'success' => true,
        'memorias' => $memorias
    ]);
} else {
    // Si no se proporciona un ID o es inválido
    echo json_encode([
        'success' => false,
        'error' => 'ID de equipo no proporcionado o inválido'
    ]);
}
?>
