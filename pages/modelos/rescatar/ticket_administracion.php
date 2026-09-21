<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
include("../../class/conexion.php");
date_default_timezone_set('America/Santiago');

// 1) Sanitiza el id y evita inyección
$id_ticket = isset($_POST["id"]) ? (int)$_POST["id"] : 0;
$idUsuarioSession = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;

$ticketData = [];
$db = new MySQL("", "", "");

// 2) Asegura charset de la conexión (si no lo hiciste en el constructor)
$db->set_charset('utf8mb4');


// ====== AGREGA ESTA FUNCIÓN AQUÍ (antes de usar $row) ======
function to_utf8($s) {
  if ($s === null) return "";
  return mb_check_encoding($s, 'UTF-8') ? $s : mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1');
}

$consulta = $db->consulta("
    SELECT 
        t.*, 
        u.nombre                AS nombreUsuario, 
        u.apellido_paterno      AS apePaternoUsuario,
        u.id_area_trabajo       AS IdAreaTrabajoUsuario,
        uss.nombre              AS nombreTecnico, 
        uss.apellido_paterno    AS apePaternoTecnico, 
        uss.apellido_materno    AS apeMaternoTecnico, 
        uss.id_area_trabajo     AS IdAreaTrabajoTecnico,
        et.nombre               AS nombreEstado,
        pr.nombre               AS nombrePrioridad,
        pr.id                   AS id_prioridad,
        ct.nombre_categoria,   
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
        pt.fecha_cierre_ticket,
        pt.hora_cierre_ticket,
        COUNT(at.id) AS avance_count
    FROM tickets AS t
    LEFT JOIN usuarios AS u ON t.id_usuario = u.id
    LEFT JOIN usuarios AS uss ON t.id_tecnico = uss.id
    LEFT JOIN prioridad AS pr ON t.id_prioridad = pr.id
    LEFT JOIN proceso_tickets AS pt ON t.id_ticket = pt.id_ticket
    LEFT JOIN estados_ticket AS et ON et.id = t.id_estado
    LEFT JOIN categoria_de_ticket AS ct ON t.id_categoria_ticket = ct.id_categoria
    LEFT JOIN avance_tecnicos AS at ON t.id_ticket = at.id_ticket
    WHERE t.id_ticket = {$id_ticket}
    GROUP BY t.id_ticket
");

if ($db->num_rows($consulta) > 0) {
    $row = $db->fetch_array($consulta);

    // 3) NO usar utf8_encode: ya estamos en UTF-8 si la conexión/tabla lo están
    $ticketData = array(
        'id_ticket'                 => (int)$row['id_ticket'],
        'avance_count'              => (int)$row['avance_count'],
        'id_usuario'                => (int)$row['id_usuario'],
        'id_tecnico'                => (int)($row['id_tecnico'] ?? 0),
        'asunto'                    => $row['asunto'],
        'descripcion_ticket'        => $row['descripcion_ticket'],
        'comentario_final'          => $row['comentario_final'],
        'comentario_administrador'  => $row['comentario_administrador'],
        'identificador'             => $row['identificador'],
        'id_prioridad'              => (int)$row['id_prioridad'],
        'nombrePrioridad'           => $row['nombrePrioridad'],
        'id_estado'                 => (int)$row['id_estado'],
        'nombreEstado'              => $row['nombreEstado'],
        'nombreTecnico'             => $row['nombreTecnico'],
        'apePaternoTecnico'         => $row['apePaternoTecnico'],
        'apeMaternoTecnico'         => $row['apeMaternoTecnico'],
        'IdAreaTrabajoTecnico'      => (int)$row['IdAreaTrabajoTecnico'],
        'nombreUsuario'             => $row['nombreUsuario'],
        'apePaternoUsuario'         => $row['apePaternoUsuario'],
        'IdAreaTrabajoUsuario'      => (int)$row['IdAreaTrabajoUsuario'],
        'id_categoria_ticket'       => (int)$row['id_categoria_ticket'],
        'nombre_categoria'          => $row['nombre_categoria'],
        'fecha_creacion_inicio'     => $row['fecha_creacion_inicio'],
        'hora_creacion_inicio'      => $row['hora_creacion_inicio'],
        'fecha_estimada_admin'      => $row['fecha_estimada_admin'],
        'dias_estimada_admin'       => $row['dias_estimada_admin'],
        'fecha_asignacion_tecnico'  => $row['fecha_asignacion_tecnico'],
        'hora_asignacion_tecnico'   => $row['hora_asignacion_tecnico'],
        'fecha_comienzo_ticket'     => $row['fecha_comienzo_ticket'],
        'hora_comienzo_ticket'      => $row['hora_comienzo_ticket'],
        'fecha_termino_ticket'      => $row['fecha_termino_ticket'],
        'hora_termino_ticket'       => $row['hora_termino_ticket'],
    );

    $accionesConsulta = $db->consulta("
        SELECT accion, fecha_avance, hora_avance
        FROM avance_tecnicos
        WHERE id_ticket = {$id_ticket}
        ORDER BY fecha_avance, hora_avance
    ");
    $acciones = [];
    while ($accion = $db->fetch_array($accionesConsulta)) {
        $acciones[] = $accion; // vienen en UTF-8 por la conexión
    }
    $ticketData['acciones'] = $acciones;
}

// 4) JSON sin escapar unicode y sustituyendo bytes inválidos si los hubiera
echo json_encode($ticketData, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

