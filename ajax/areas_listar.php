<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
Sesion::requerir();

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $areas = $db->fetchAll('SELECT id_area, nombre_area FROM area_trabajo ORDER BY nombre_area ASC');
    responder_json(['ok' => true, 'data' => $areas]);
} catch (Throwable $e) {
    responder_json(['ok' => false, 'mensaje' => 'No fue posible consultar las áreas.'], 500);
}

