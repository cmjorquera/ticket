<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $tituloPagina = 'Consulta de software';
    $colegios = $inventario->obtenerColegios();
    $softwaresDisponibles = $inventario->obtenerOpcionesSoftwareConsulta();
    $datosSensiblesDisponibles = $inventario->obtenerDatosSensiblesCatalogo();
    $vistaSolicitada = trim((string)($_GET['vista'] ?? 'software'));
    $vistasPermitidas = ['software', 'colegio', 'dato_sensible'];
    $vista = in_array($vistaSolicitada, $vistasPermitidas, true) ? $vistaSolicitada : 'software';
    $softwareSeleccionado = trim((string)($_GET['software'] ?? ''));
    $idColegioSeleccionado = (int)($_GET['id_colegio'] ?? 0);
    $idDatoSensibleSeleccionado = (int)($_GET['id_dato_sensible'] ?? 0);
    $consultaSoftware = $inventario->obtenerConsultaPorSoftware($softwareSeleccionado);
    $consultaColegio = $inventario->obtenerConsultaPorColegio($idColegioSeleccionado);
    $consultaDatoSensible = $inventario->obtenerConsultaPorDatoSensible($idDatoSensibleSeleccionado);
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

$idPagActual = '15';
require __DIR__ . '/componentes/layout_top.inc';
?>
<?php require __DIR__ . '/componentes/consulta_view.inc'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
