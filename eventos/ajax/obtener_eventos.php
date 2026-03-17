<?php
require_once __DIR__ . '/../bootstrap.php';

eventos_requiere_login(true);

$start = eventos_trim($_GET['start'] ?? '');
$end = eventos_trim($_GET['end'] ?? '');
$fechaInicio = $start !== '' ? substr($start, 0, 10) : null;
$fechaFin = $end !== '' ? substr($end, 0, 10) : null;

$eventos = eventos_obtener_eventos($fechaInicio, $fechaFin);
$respuesta = [];

foreach ($eventos as $evento) {
    $respuesta[] = eventos_formatear_calendario($evento);
}

eventos_responder_json($respuesta);
