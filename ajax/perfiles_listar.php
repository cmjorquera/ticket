<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
Sesion::requerir();

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $perfiles = $db->fetchAll('SELECT id_perfil, nombre FROM perfiles ORDER BY id_perfil ASC');
    responder_json(['ok' => true, 'data' => $perfiles]);
} catch (Throwable $ex) {
    responder_json(['ok' => false, 'mensaje' => 'No fue posible consultar los perfiles.'], 500);
}
