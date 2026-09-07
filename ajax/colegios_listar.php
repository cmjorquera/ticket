<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
Sesion::requerir();

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $colegios = $db->fetchAll(
        'SELECT id_colegio, nom_colegio, logo FROM colegio WHERE estado = 1 ORDER BY nom_colegio ASC'
    );
    responder_json(['ok' => true, 'data' => $colegios]);
} catch (Throwable $e) {
    responder_json(['ok' => false, 'mensaje' => 'No fue posible consultar los colegios.'], 500);
}

