<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../clases/Usuario.php';
Sesion::requerir();

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $colegiosPermitidos = Usuario::colegiosPermitidosPara($db, (int) Sesion::get('id', 0));
    $sql = 'SELECT dc.id AS id_departamento_colegio, dc.id_colegio, dc.nombre_departamento
              FROM departamentos_colegio dc
             WHERE dc.estado = 1';
    $parametros = [];
    if ($colegiosPermitidos !== null) {
        if ($colegiosPermitidos === []) {
            responder_json(['ok' => true, 'data' => []]);
        }
        $sql .= ' AND dc.id_colegio IN (' . implode(',', array_fill(0, count($colegiosPermitidos), '?')) . ')';
        $parametros = $colegiosPermitidos;
    }
    $sql .= ' ORDER BY dc.id_colegio ASC, dc.nombre_departamento ASC';
    $departamentos = $db->fetchAll($sql, $parametros);
    responder_json(['ok' => true, 'data' => $departamentos]);
} catch (Throwable $e) {
    responder_json(['ok' => false, 'mensaje' => 'No fue posible consultar los departamentos.'], 500);
}
