<?php
/** Arma las 5 etapas de la vida de un ticket a partir de proceso_tickets. */
function est_etapas_ticket(array $t): array
{
    $creacion   = est_fecha_hora($t['fecha_creacion_inicio'] ?? '', $t['hora_creacion_inicio'] ?? '');
    $asignacion = est_fecha_hora($t['fecha_asignacion_tecnico'] ?? '', $t['hora_asignacion_tecnico'] ?? '');
    $comienzo   = est_fecha_hora($t['fecha_comienzo_ticket'] ?? '', $t['hora_comienzo_ticket'] ?? '');
    $termino    = est_fecha_hora($t['fecha_termino_ticket'] ?? '', $t['hora_termino_ticket'] ?? '');
    $cierre     = est_fecha_hora($t['fecha_cierre_ticket'] ?? '', $t['hora_cierre_ticket'] ?? '');

    $etapas = [
        ['Ingreso', 'Solicitud registrada en mesa de ayuda.', $creacion, $asignacion, 'bi-inbox'],
        ['Asignación', 'Derivado a un técnico responsable.', $asignacion, $comienzo, 'bi-person-badge'],
        ['Inicio de atención', 'Comienza el trabajo sobre el requerimiento.', $comienzo, $termino, 'bi-play-circle'],
        ['Término técnico', 'Resolución técnica o entrega del trabajo.', $termino, $cierre, 'bi-tools'],
        ['Cierre', 'Confirmación final y cierre del ticket.', $cierre, null, 'bi-check2-circle'],
    ];

    $resultado = [];
    $actualMarcado = false;
    foreach ($etapas as [$titulo, $descripcion, $fecha, $siguiente, $icono]) {
        if ($fecha) {
            $estado = 'is-done';
        } elseif (!$actualMarcado) {
            $estado = 'is-current';
            $actualMarcado = true;
        } else {
            $estado = 'is-pending';
        }
        $resultado[] = [
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'fecha' => $fecha,
            'duracion' => $titulo === 'Cierre' ? ($fecha ? 'Completado' : 'Pendiente') : ($fecha && $siguiente ? est_duracion($fecha, $siguiente) : 'Pendiente'),
            'estado' => $estado,
            'icono' => $icono,
        ];
    }
    // Tiempo total desde el ingreso hasta el cierre (o hasta ahora si sigue abierto)
    $fin = $cierre ?: ($termino ?: null);
    return [
        'etapas' => $resultado,
        'total' => $creacion ? est_duracion($creacion, $fin ?: new DateTime()) : 'Sin fecha de ingreso',
        'abierto' => !$fin,
        'creacion' => $creacion,
    ];
}
