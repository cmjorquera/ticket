<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $tituloPagina = 'Consulta de software';
    $colegios = $inventario->obtenerColegios();
    $softwaresDisponibles = $inventario->obtenerOpcionesSoftwareConsulta();
    $vista = ($_GET['vista'] ?? 'software') === 'colegio' ? 'colegio' : 'software';
    $softwareSeleccionado = trim((string)($_GET['software'] ?? ''));
    $idColegioSeleccionado = (int)($_GET['id_colegio'] ?? 0);
    $consultaSoftware = $inventario->obtenerConsultaPorSoftware($softwareSeleccionado);
    $consultaColegio = $inventario->obtenerConsultaPorColegio($idColegioSeleccionado);
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar la consulta del inventario de software: ' . $e->getMessage());
}

$logoColegio = '';
if (!empty($consultaColegio['colegio']['id_colegio'])) {
    $idLogoColegio = (int)$consultaColegio['colegio']['id_colegio'];
    foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
        $rutaFisica = dirname(__DIR__) . '/img/colegios/colegio_' . $idLogoColegio . '.' . $extension;
        if (is_file($rutaFisica)) {
            $logoColegio = inventario_sistema_url('img/colegios/colegio_' . $idLogoColegio . '.' . $extension);
            break;
        }
    }
}

$idPagActual = '9';
require __DIR__ . '/componentes/layout_top.inc';
?>
<?php require __DIR__ . '/componentes/consulta_view.inc'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
