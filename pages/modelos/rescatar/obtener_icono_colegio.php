<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../ajax/_bootstrap.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    responder_json(['success' => false, 'message' => 'Método no permitido.', 'icono' => null, 'existe' => false, 'sigla' => '—'], 405);
}

function iniciales_colegio(string $nombre): string
{
    $palabras = preg_split('/\s+/', trim($nombre), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $iniciales = '';
    foreach (array_slice($palabras, 0, 2) as $palabra) {
        $iniciales .= function_exists('mb_substr')
            ? mb_strtoupper(mb_substr($palabra, 0, 1, 'UTF-8'), 'UTF-8')
            : strtoupper(substr($palabra, 0, 1));
    }
    return $iniciales !== '' ? $iniciales : 'C';
}

try {
    $idColegio = max(0, (int) ($_GET['id_colegio'] ?? 0));
    $db = Conexion::getInstance('sistema_panel_central');
    $colegio = $idColegio > 0
        ? $db->fetchOne('SELECT nom_colegio FROM colegio WHERE id_colegio = ? AND estado = 1 LIMIT 1', [$idColegio])
        : false;

    if (!$colegio) {
        responder_json(['success' => false, 'message' => 'El colegio indicado no existe.', 'icono' => null, 'existe' => false, 'sigla' => '—'], 404);
    }

    $nombreArchivo = 'colegio_' . $idColegio . '.png';
    $rutaFisica = __DIR__ . '/../../../img/colegios/' . $nombreArchivo;
    $existe = is_file($rutaFisica);

    $sigla = iniciales_colegio((string) $colegio['nom_colegio']);
    responder_json([
        'success' => true,
        'message' => 'Icono consultado',
        'icono' => $existe ? '/img/colegios/' . $nombreArchivo : null,
        'existe' => $existe,
        'sigla' => $sigla,
        'iniciales' => $sigla,
    ]);
} catch (Throwable $ex) {
    error_log('Error al obtener icono de colegio: ' . $ex->getMessage());
    responder_json(['success' => false, 'message' => 'No fue posible obtener el icono.', 'icono' => null, 'existe' => false, 'sigla' => 'C'], 500);
}
