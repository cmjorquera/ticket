<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../configuracion/_inicio.php';
header('Content-Type: text/html; charset=UTF-8');

$usuarioId = (int) Sesion::get('id', 0);
$misTickets = [];
$errorListado = null;
$etiquetasEstado = [
    'nuevo' => 'Nuevo',
    'en_proceso' => 'En proceso',
    'atrasado' => 'Atrasado',
    'resuelto' => 'Resuelto',
    'cerrado' => 'Cerrado',
];

try {
    $misTickets = $db->fetchAll(
        "SELECT t.id_ticket, t.asunto, t.descripcion, t.estado, t.fecha_creacion,
                t.fecha_respuesta, t.prioridad,
                c.nombre_categoria AS categoria_nombre,
                col.nom_colegio AS colegio_nombre,
                CONCAT_WS(' ', tec.nombre, tec.apellido_paterno) AS tecnico_nombre
           FROM tickets t
           JOIN categoria_de_ticket c ON c.id_categoria = t.id_categoria
           JOIN colegio col ON col.id_colegio = t.id_colegio
      LEFT JOIN usuarios tec ON tec.id = t.id_tecnico_asignado
          WHERE t.id_usuario = ?
       ORDER BY FIELD(t.estado, 'nuevo', 'en_proceso', 'atrasado', 'resuelto', 'cerrado'), t.fecha_creacion DESC",
        [$usuarioId]
    );
} catch (Throwable $ex) {
    error_log('Error AJAX al listar tickets del usuario: ' . $ex->getMessage());
    $errorListado = 'No fue posible consultar tus solicitudes en este momento.';
}

require __DIR__ . '/../bloque_tabla_usuario.php';
