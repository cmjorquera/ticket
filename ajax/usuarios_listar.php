<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../class/Usuarios.php';

Sesion::requerir();

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $filtros = [
        'estado'     => trim((string) ($_GET['estado'] ?? '')),
        'id_area'    => (int) ($_GET['id_area'] ?? 0),
        'id_colegio' => (int) ($_GET['id_colegio'] ?? 0),
        'buscar'     => trim((string) ($_GET['buscar'] ?? '')),
    ];
    responder_json(['ok' => true, 'data' => Usuarios::listar($db, $filtros)]);
} catch (Throwable $e) {
    responder_json([
        'ok'      => false,
        'mensaje' => $e->getMessage(),
        'archivo' => $e->getFile(),
        'linea'   => $e->getLine(),
    ], 500);
}