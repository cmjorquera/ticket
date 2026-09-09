<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../configuracion/_inicio.php';
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo '<tbody></tbody>';
    exit;
}

$usuarioId = (int) Sesion::get('id', 0);
$misTickets = [];
$errorListado = null;
$etiquetasEstado = [
    'nuevo' => 'Nuevo',
    'asignado' => 'Asignado',
    'en_proceso' => 'En proceso',
    'borrador' => 'Borrador',
    'atrasado' => 'Demorado',
    'resuelto' => 'Terminado',
];

try {
    $columnasAdjuntos = ticket_columnas_tabla('archivos_adjuntos_ticket', $db);
    $sqlCantidadArchivos = isset($columnasAdjuntos['id_ticket'])
        ? '(SELECT COUNT(*) FROM archivos_adjuntos_ticket aa WHERE aa.id_ticket = t.id_ticket)'
        : '0';
    $tablaCalificacion = 'calificacion_ticket';
    $columnasCalificacion = ticket_columnas_tabla($tablaCalificacion, $db);
    if (!$columnasCalificacion) {
        $tablaCalificacion = 'calificacion_tickett';
        $columnasCalificacion = ticket_columnas_tabla($tablaCalificacion, $db);
    }
    $sqlTieneCalificacion = isset($columnasCalificacion['id_ticket'])
        ? "EXISTS (SELECT 1 FROM {$tablaCalificacion} ct WHERE ct.id_ticket = t.id_ticket)"
        : '0';
    $sqlPollingUsuario = "SELECT t.id_ticket, t.id_usuario, t.asunto, t.descripcion_ticket AS descripcion,
                t.id_estado, e.nombre AS estado_nombre, e.color AS estado_color,
                e.color_degradado AS estado_degradado,
                t.id_prioridad, t.id_tecnico, {$sqlCantidadArchivos} AS cantidad_archivos,
                {$sqlTieneCalificacion} AS tiene_calificacion,
                COALESCE(CONCAT(pt.fecha_creacion_inicio, ' ', COALESCE(pt.hora_creacion_inicio, '00:00:00')), '') AS fecha_creacion,
                COALESCE(CONCAT(pt.fecha_asignacion_tecnico, ' ', COALESCE(pt.hora_asignacion_tecnico, '00:00:00')), '') AS fecha_respuesta,
                CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS usuario_nombre,
                c.nombre_categoria AS categoria_nombre,
                col.nom_colegio AS colegio_nombre,
                CONCAT_WS(' ', ut.nombre, ut.apellido_paterno) AS tecnico_nombre
           FROM tickets t
           JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria_ticket
           JOIN usuarios u ON u.id = t.id_usuario
      LEFT JOIN (
                    SELECT id_usuario, MIN(id_colegio) AS id_colegio
                      FROM usuario_colegio
                     WHERE estado = 1
                  GROUP BY id_usuario
                ) uc_ticket ON uc_ticket.id_usuario = t.id_usuario
      LEFT JOIN colegio col ON col.id_colegio = uc_ticket.id_colegio
      LEFT JOIN usuarios ut ON ut.id = t.id_tecnico
      LEFT JOIN estados_ticket e ON e.id = t.id_estado
      LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
          WHERE t.id_usuario = ? AND t.estado = 1
       ORDER BY t.id_estado ASC, pt.fecha_creacion_inicio DESC, pt.hora_creacion_inicio DESC";
    $paramsPollingUsuario = [$usuarioId];
    $misTickets = $db->fetchAll($sqlPollingUsuario, $paramsPollingUsuario);
    $misTickets = array_map('ticket_normalizar_fila', $misTickets);
} catch (Throwable $ex) {
    error_log('Error AJAX al listar tickets del usuario: ' . $ex->getMessage());
    $ultimoDebug = (int) ($_SESSION['ticket_debug_polling'] ?? 0);
    if ($ultimoDebug < time() - 60) {
        $_SESSION['ticket_debug_polling'] = time();
        ticket_debug_sql('POLLING USUARIO ERROR', $sqlPollingUsuario ?? 'SQL no construida', $paramsPollingUsuario ?? [$usuarioId], $ex);
    }
    $errorListado = 'No fue posible consultar tus solicitudes en este momento.';
}

header('X-Ticket-Count: ' . count($misTickets));

if ($errorListado) {
    http_response_code(500);
    echo '<tbody></tbody>';
    exit;
}

if (!$misTickets) {
    echo '<tbody><tr class="ticket-poll-empty"><td colspan="8"><div class="ticket-empty"><i class="bi bi-inbox"></i>No hay solicitudes registradas.</div></td></tr></tbody>';
    exit;
}

ob_start();
require __DIR__ . '/../bloque_tabla_usuario.php';
$html = (string) ob_get_clean();

if (preg_match('/<tbody>(.*?)<\/tbody>/si', $html, $coincidencia) === 1) {
    echo '<tbody>' . $coincidencia[1] . '</tbody>';
    exit;
}

http_response_code(500);
echo '<tbody></tbody>';
