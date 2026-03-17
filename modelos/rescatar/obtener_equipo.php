<?php
// Iniciar sesión y requerir la conexión a la base de datos
session_start();
require_once '../../class/conexion.php';

$bdato = new MySQL('', '', ''); // Conexión a la base de datos

// Verificar si se ha proporcionado un ID a través de la URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_equipo = intval($_GET['id']);

    // Consulta para obtener los datos del equipo y sus observaciones, excluyendo los datos de memoria
    $consulta = "
    SELECT 
        e.id_equipo, 
        e.id_usuario, 
        e.nombre_equipo, 
        e.fabricante, 
        e.producto, 
        e.qr_code, 
        e.numero_serie, 
        e.tipo_pc,
        ep.equipo_modelo AS modelo_procesador,        
        ep.equipo_fabricante,
        ep.equipo_velocidad,
        ea.equipo_modelo,
        ea.equipo_capacidad,
        ea.equipo_tamano,
        ec.valor_equipo,
        ec.proveedor,
        ec.numero_factura,
        ec.fecha_compra,
        ec.observacion,
        es.windows,
        es.office,
        es.antivirus,
        CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo_usuario,
        atr.*
  
    FROM equipos e
    LEFT JOIN equipos_compra            ec      ON e.id_equipo  = ec.id_equipo
    LEFT JOIN usuarios                  u       ON e.id_usuario = u.id
    LEFT JOIN area_trabajo              atr     ON atr.id_area  = u.id_area_trabajo
    LEFT JOIN equipo_procesador         ep      ON e.id_equipo  = ep.id_equipo
    LEFT JOIN equipo_almacenamiento     ea      ON e.id_equipo  = ea.id_equipo
    LEFT JOIN equipo_software           es      ON e.id_equipo = es.id_equipo
    WHERE e.id_equipo = $id_equipo
";


    $resultado = $bdato->consulta($consulta);

    // Verificar si hay resultados
    if ($bdato->num_rows($resultado) > 0) {
        $equipo = $bdato->fetch_assoc($resultado);
        
        // Devolver los datos en formato JSON
        echo json_encode([
            'success' => true,
            'equipo' => $equipo
        ]);
    } else {
        // Si no se encuentra el equipo
        echo json_encode([
            'success' => false,
            'error' => 'Equipo no encontrado'
        ]);
    }
} else {
    // Si no se proporciona un ID o es inválido
    echo json_encode([
        'success' => false,
        'error' => 'ID de equipo no proporcionado o inválido'
    ]);
}
?>