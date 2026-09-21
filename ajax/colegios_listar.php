<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../clases/Usuario.php';
Sesion::requerir();

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $colegiosPermitidos = Usuario::colegiosPermitidosPara($db, (int) Sesion::get('id', 0));
    $sql = 'SELECT id_colegio, nom_colegio FROM colegio WHERE estado = 1';
    $parametros = [];
    if ($colegiosPermitidos !== null) {
        if ($colegiosPermitidos === []) {
            responder_json(['ok' => true, 'data' => []]);
        }
        $sql .= ' AND id_colegio IN (' . implode(',', array_fill(0, count($colegiosPermitidos), '?')) . ')';
        $parametros = $colegiosPermitidos;
    }
    $sql .= ' ORDER BY nom_colegio ASC';
    $colegios = $db->fetchAll($sql, $parametros);
    responder_json(['ok' => true, 'data' => $colegios]);
} catch (Throwable $e) {
    responder_json(['ok' => false, 'mensaje' => 'No fue posible consultar los colegios.'], 500);
}
