<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../configuracion/_inicio.php';
header('Content-Type: text/html; charset=UTF-8');

$usuarioId = (int) Sesion::get('id', 0);
$tickets = [];
$errorCarga = null;

try {
    $tickets = $db->fetchAll(
        "SELECT t.id_ticket, t.asunto, t.descripcion, t.estado, t.fecha_creacion,
                t.fecha_respuesta, t.prioridad,
                CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS usuario_nombre,
                c.nombre_categoria AS categoria_nombre, col.nom_colegio AS colegio_nombre
           FROM tickets t
           JOIN usuarios u ON u.id = t.id_usuario
           JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria
           JOIN colegio col ON col.id_colegio = t.id_colegio
          WHERE t.id_tecnico_asignado = ?
       ORDER BY FIELD(t.estado, 'nuevo', 'en_proceso', 'atrasado', 'resuelto', 'cerrado'), t.fecha_creacion DESC",
        [$usuarioId]
    );
} catch (Throwable $ex) {
    error_log('Error AJAX al listar tickets del técnico: ' . $ex->getMessage());
    $errorCarga = 'No fue posible consultar los tickets asignados.';
}

require __DIR__ . '/../bloque_tabla_tecnico.php';
