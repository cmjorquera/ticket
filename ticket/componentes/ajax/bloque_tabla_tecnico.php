<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../configuracion/_inicio.php';
header('Content-Type: text/html; charset=UTF-8');

$usuarioId = (int) Sesion::get('id', 0);
$tickets = [];
$errorCarga = null;

try {
    $tickets = $db->fetchAll(
        "SELECT t.id_ticket, t.asunto, t.descripcion_ticket AS descripcion,
                t.id_estado, e.nombre AS estado_nombre, e.color AS estado_color,
                e.color_degradado AS estado_degradado,
                t.id_prioridad, t.id_tecnico,
                COALESCE(CONCAT(pt.fecha_creacion_inicio, ' ', COALESCE(pt.hora_creacion_inicio, '00:00:00')), '') AS fecha_creacion,
                COALESCE(CONCAT(pt.fecha_asignacion_tecnico, ' ', COALESCE(pt.hora_asignacion_tecnico, '00:00:00')), '') AS fecha_respuesta,
                CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS usuario_nombre,
                c.nombre_categoria AS categoria_nombre, col.nom_colegio AS colegio_nombre
           FROM tickets t
           JOIN usuarios u ON u.id = t.id_usuario
           JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria_ticket
      LEFT JOIN (
                    SELECT id_usuario, MIN(id_colegio) AS id_colegio
                      FROM usuario_colegio
                     WHERE estado = 1
                  GROUP BY id_usuario
                ) uc_ticket ON uc_ticket.id_usuario = t.id_usuario
      LEFT JOIN colegio col ON col.id_colegio = uc_ticket.id_colegio
      LEFT JOIN estados_ticket e ON e.id = t.id_estado
      LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
          WHERE t.id_tecnico = ? AND t.estado = 1
       ORDER BY t.id_estado ASC, pt.fecha_creacion_inicio DESC, pt.hora_creacion_inicio DESC",
        [$usuarioId]
    );
    $tickets = array_map('ticket_normalizar_fila', $tickets);
} catch (Throwable $ex) {
    error_log('Error AJAX al listar tickets del técnico: ' . $ex->getMessage());
    $errorCarga = 'No fue posible consultar los tickets asignados.';
}

require __DIR__ . '/../bloque_tabla_tecnico.php';
