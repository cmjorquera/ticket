<?php
require_once __DIR__ . '/componentes/boot.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $idEquipo = $inventario->guardarEquipo($_POST, $_FILES, $idUsuarioSession);
    echo json_encode([
        'ok' => true,
        'mensaje' => 'Equipo registrado correctamente.',
        'id_equipo' => $idEquipo,
        'redirect' => 'ver_equipo.php?id_equipo=' . $idEquipo
    ]);
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => $e->getMessage()]);
}
