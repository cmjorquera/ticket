<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
Sesion::requerir();

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $departamentos = $db->fetchAll(
        'SELECT dc.id AS id_departamento_colegio, dc.id_colegio, dc.nombre_departamento
           FROM departamentos_colegio dc
          WHERE dc.estado = 1
          ORDER BY dc.id_colegio ASC, dc.nombre_departamento ASC'
    );
    responder_json(['ok' => true, 'data' => $departamentos]);
} catch (Throwable $e) {
    responder_json(['ok' => false, 'mensaje' => 'No fue posible consultar los departamentos.'], 500);
}
