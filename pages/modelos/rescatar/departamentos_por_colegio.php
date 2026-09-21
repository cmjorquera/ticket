<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../ajax/_bootstrap.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    responder_json(['success' => false, 'message' => 'Método no permitido.'], 405);
}

try {
    $idColegio = max(0, (int) ($_GET['id_colegio'] ?? 0));
    $db = Conexion::getInstance('sistema_panel_central');
    if ($idColegio <= 0 || !$db->fetchOne('SELECT id_colegio FROM colegio WHERE id_colegio = ? AND estado = 1 LIMIT 1', [$idColegio])) {
        responder_json(['success' => false, 'message' => 'El colegio indicado no existe.'], 404);
    }

    $filas = $db->fetchAll(
        'SELECT dc.id, dc.nombre_departamento, dc.sigla,
                COUNT(DISTINCT CASE WHEN jd.estado = 1 THEN jd.id_usuario END) AS usuarios_count
           FROM departamentos_colegio dc
      LEFT JOIN jefatura_departamento jd
             ON jd.id_departamento_colegio = dc.id
            AND jd.id_colegio = dc.id_colegio
          WHERE dc.id_colegio = ? AND dc.estado = 1
       GROUP BY dc.id, dc.nombre_departamento, dc.sigla
       ORDER BY dc.nombre_departamento ASC',
        [$idColegio]
    );

    responder_json(array_map(
        static fn (array $fila): array => [
            'id' => (int) $fila['id'],
            'nombre' => (string) $fila['nombre_departamento'],
            'sigla' => (string) ($fila['sigla'] ?? ''),
            'usuarios_count' => (int) ($fila['usuarios_count'] ?? 0),
        ],
        $filas
    ));
} catch (Throwable $ex) {
    error_log('Error al obtener departamentos por colegio: ' . $ex->getMessage());
    responder_json(['success' => false, 'message' => 'No fue posible cargar los departamentos.'], 500);
}
