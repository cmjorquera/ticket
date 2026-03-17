<?php
require_once '../class/conexion.php';

/**
 * Obtiene todos los tickets asociados a un usuario (usando tu clase MySQL personalizada)
 * 
 * @param int $idUsuario ID del usuario
 * @return array Array de tickets
 */
function obtenerTicketsUsuario($idUsuario) {
    // Creamos la conexión usando tu clase personalizada
    $db = new MySQL('', '', '');

    $sql = "
        SELECT
            t.*,
            et.nombre AS nombreEstado,
            et.orden AS ordenEstado,
            et.color AS colorEstado,
            et.descripcion_estado AS descripcionEstado,
            pt.fecha_creacion_inicio,
            pt.dias_estimada_admin AS dias_administrador_estima,
            pt.hora_creacion_inicio,
            pt.fecha_estimada_admin,
            pt.fecha_asignacion_tecnico,
            pt.hora_asignacion_tecnico,
            pt.fecha_comienzo_ticket,
            pt.hora_comienzo_ticket,
            pt.fecha_termino_ticket,
            pt.hora_termino_ticket,
            COUNT(aa.id) AS cantidadArchivos,
            COUNT(c.id) AS cantidadConversaciones,
            t.id_usuario,
            t.id_tecnico,
            us.nombre,
            us.apellido_paterno,
            us.apellido_materno
        FROM tickets t
        JOIN estados_ticket AS et ON et.id = t.id_estado
        JOIN usuarios AS us ON us.id = t.id_usuario
        LEFT JOIN archivos_adjuntos_ticket AS aa ON aa.id_ticket = t.id_ticket
        LEFT JOIN proceso_tickets AS pt ON pt.id_ticket = t.id_ticket
        LEFT JOIN conversaciones AS c ON c.id_ticket = t.id_ticket
        WHERE t.id_usuario = '$idUsuario'
        GROUP BY t.id_ticket
        ORDER BY t.id_ticket DESC
    ";

    $resultado = $db->consulta($sql); // Ejecutamos con tu clase

    $tickets = [];

    if ($db->num_rows($resultado) > 0) {
        while ($row = $db->fetch_assoc($resultado)) {
            $tickets[] = $row;
        }
    }

    return $tickets;
}
