<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $modo = ($_GET['modo'] ?? 'dashboard') === 'consulta' ? 'consulta' : 'dashboard';
    $tituloPagina = $modo === 'consulta' ? 'Consulta de software' : 'Dashboard de software';
    $resumen = $inventario->obtenerResumen();
    $dashboardColegios = $inventario->obtenerDashboardColegios();
    $colegios = $inventario->obtenerColegios();
    $softwaresDisponibles = $inventario->obtenerOpcionesSoftwareConsulta();
    $vista = ($_GET['vista'] ?? 'software') === 'colegio' ? 'colegio' : 'software';
    $softwareSeleccionado = trim((string)($_GET['software'] ?? ''));
    $idColegioSeleccionado = (int)($_GET['id_colegio'] ?? 0);
    $consultaSoftware = $inventario->obtenerConsultaPorSoftware($softwareSeleccionado);
    $consultaColegio = $inventario->obtenerConsultaPorColegio($idColegioSeleccionado);
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar el dashboard del inventario de software: ' . $e->getMessage());
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
<?php if ($modo === 'consulta'): ?>
    <?php require __DIR__ . '/componentes/consulta_view.inc'; ?>
<?php else: ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Dashboard de software</h1>
            <p class="text-muted mb-0">Graficos y resumen por colegio de software, licencias, webs, apps y clientes.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="consulta_.php" class="btn btn-outline-primary">Consulta</a>
            <a href="index.php" class="btn btn-light border">Volver</a>
        </div>
    </div>

    <?php require __DIR__ . '/componentes/resumen.php'; ?>
    <?php require __DIR__ . '/componentes/dashboard_colegios.php'; ?>
<?php endif; ?>

<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
