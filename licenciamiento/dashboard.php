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
    inventario_responder_error('Error al cargar el dashboard del inventario de software: ' . inventario_mensaje_error($e));
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
<?php if ($modo === 'consulta'): ?>
    <?php require __DIR__ . '/componentes/consulta_view.inc'; ?>
<?php else: ?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="inv-kicker">Módulo Licenciamiento</span>
            <h1 class="inv-title mb-2">Dashboard de licenciamiento</h1>
            <p class="inv-subtitle mb-0">Gráficos y resumen por colegio de software, licencias, webs, apps y clientes.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="consulta_nueva.php" class="module-btn module-btn--primary"><i class="bi bi-search"></i>Consulta</a>
            <a href="sofware_licenciamiento.php" class="module-btn module-btn--secondary"><i class="bi bi-window-stack"></i>Software</a>
            <a href="sitios_web_apps.php" class="module-btn module-btn--secondary"><i class="bi bi-globe2"></i>Sitios web / apps</a>
        </div>
    </div>
</div>

    <?php require __DIR__ . '/componentes/resumen.php'; ?>
    <?php require __DIR__ . '/componentes/dashboard_colegios.php'; ?>
<?php endif; ?>

<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
