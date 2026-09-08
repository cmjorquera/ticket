<?php
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

$id_ticket = $_POST["id"];

// Crear un arreglo para almacenar los datos del ticket
$ticketData = [];

$db = new MySQL("", "", "");

// Realizar la consulta para obtener los datos del ticket sin las acciones
$consulta = $db->consulta("SELECT 
    t.*, 
    et.nombre                  AS nombreEstado,
    u1.nombre                  AS nombre_usuario, 
    u1.apellido_paterno        AS apellido_paterno_usuario, 
    pt.fecha_creacion_inicio,
    pt.hora_creacion_inicio,
    pt.fecha_estimada_admin,
    pt.dias_estimada_admin,
    pt.fecha_asignacion_tecnico,
    pt.hora_asignacion_tecnico,
    pt.fecha_comienzo_ticket,
    pt.hora_comienzo_ticket,
    pt.fecha_termino_ticket,
    pt.hora_termino_ticket,
    p.nombre                   AS nombre_prioridad,
    p.orden                    AS orden_prioridad,
    c.nombre_categoria         AS nombre_categoria,
    c.abreviacion              AS abreviacion_categoria,
    c.orden                    AS orden_categoria
FROM tickets AS t
JOIN usuarios AS u1 ON t.id_usuario = u1.id
JOIN estados_ticket AS et ON et.id = t.id_estado
LEFT JOIN proceso_tickets AS pt ON pt.id_ticket = t.id_ticket
LEFT JOIN prioridad AS p ON p.id = t.id_prioridad
LEFT JOIN categoria_de_ticket AS c ON c.id_categoria = t.id_categoria_ticket
WHERE t.id_ticket = $id_ticket");

if ($db->num_rows($consulta) > 0) {
    // echo $consulta;
    $row = $db->fetch_array($consulta);

    // Construir el arreglo con los datos del ticket
    $ticketData = array(
        'id_ticket'                      => $row['id_ticket'],
        'fecha_creacion'                 => $row['fecha_creacion_inicio'],
        'hora_creacion'                  => $row['hora_creacion_inicio'],
        'id_usuario'                     => $row['id_usuario'],
        'nombre_usuario'                 => $row['nombre_usuario'],
        'apellido_paterno_usuario'       => $row['apellido_paterno_usuario'],
        'asunto'                         => $row['asunto'],
        'descripcion_ticket'             => $row['descripcion_ticket'], 
        'comentario_administrador'       => $row['comentario_administrador'],
        'fecha_estimada_admin'           => $row['fecha_estimada_admin'],
        'id_categoria_ticket'            => $row['id_categoria_ticket'],
        'id_prioridad'                   => $row['id_prioridad'],
        'id_tecnico'                     => $row['id_tecnico'],
        'id_estado'                      => $row['id_estado'],
        'nombreEstado'                   => $row['nombreEstado'],
        // Datos del proceso del ticket
        'fecha_creacion_inicio'          => $row['fecha_creacion_inicio'],
        'hora_creacion_inicio'           => $row['hora_creacion_inicio'],
        'fecha_estimada_admin_proceso'   => $row['fecha_estimada_admin'],
        'dias_estimada_admin'            => $row['dias_estimada_admin'],
        'fecha_asignacion_tecnico'       => $row['fecha_asignacion_tecnico'],
        'hora_asignacion_tecnico'        => $row['hora_asignacion_tecnico'],
        'fecha_comienzo_ticket'          => $row['fecha_comienzo_ticket'],
        'hora_comienzo_ticket'           => $row['hora_comienzo_ticket'],
        'fecha_termino_ticket'           => $row['fecha_termino_ticket'],
        'hora_termino_ticket'            => $row['hora_termino_ticket'],
        'nombre_prioridad'              => $row['nombre_prioridad'],
        'nombre_categoria'              => $row['nombre_categoria']


    );

    // Consulta para obtener todas las acciones de avance_tecnicos
    $accionesConsulta = $db->consulta("SELECT accion, fecha_avance, hora_avance FROM avance_tecnicos WHERE id_ticket = '$id_ticket'");
    $acciones = [];
    while ($accion = $db->fetch_array($accionesConsulta)) {
        $acciones[] = $accion;
    }
    $ticketData['acciones'] = $acciones;


    
}

echo json_encode($ticketData);
?>
