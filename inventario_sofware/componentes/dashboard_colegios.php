<?php
$dashboardColegios = $dashboardColegios ?? [];
$labelsGrafico = [];
$softwareGrafico = [];
$sitiosGrafico = [];
$licenciasGrafico = [];
$totalesGlobales = [
    'webs' => 0,
    'apps' => 0,
    'clientes' => 0,
    'softwares' => 0,
];

foreach ($dashboardColegios as $fila) {
    $labelsGrafico[] = $fila['nom_colegio'];
    $softwareGrafico[] = (int)$fila['total_softwares'];
    $sitiosGrafico[] = (int)$fila['total_sitios'];
    $licenciasGrafico[] = (int)$fila['total_licencias'];
    $totalesGlobales['webs'] += (int)$fila['total_webs'];
    $totalesGlobales['apps'] += (int)$fila['total_apps'];
    $totalesGlobales['clientes'] += (int)$fila['total_clientes'];
    $totalesGlobales['softwares'] += (int)$fila['total_softwares'];
}

$logosColegios = [];
foreach ($dashboardColegios as $fila) {
    $idColegio = (int)($fila['id_colegio'] ?? 0);
    if ($idColegio <= 0) {
        continue;
    }

    foreach (['png', 'jpg', 'jpeg', 'webp'] as $extension) {
        $rutaFisica = dirname(__DIR__, 2) . '/img/colegios/colegio_' . $idColegio . '.' . $extension;
        if (is_file($rutaFisica)) {
            $logosColegios[$idColegio] = inventario_sistema_url('img/colegios/colegio_' . $idColegio . '.' . $extension);
            break;
        }
    }
}
?>
<div class="card shadow-sm border-0 inv-panel mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <h4 class="mb-1">Dashboard por colegio</h4>
                <p class="text-muted mb-0">Resumen de software, licencias, webs, apps y clientes por cada colegio.</p>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <div class="col-lg-4">
                <div class="inv-section-card h-100">
                    <h6 class="mb-3">Inventario digital global</h6>
                    <div class="inv-dashboard-chart-sm">
                        <canvas id="graficoDashboardResumen"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="inv-section-card h-100">
                    <h6 class="mb-3">Ranking por licencias</h6>
                    <div class="inv-dashboard-chart-md">
                        <canvas id="graficoDashboardLicencias"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="inv-section-card h-100">
                    <h6 class="mb-3">Lectura rapida</h6>
                    <?php if (!empty($dashboardColegios)): ?>
                        <?php
                        $topSoftware = $dashboardColegios;
                        usort($topSoftware, function ($a, $b) {
                            return ((int)$b['total_softwares']) <=> ((int)$a['total_softwares']);
                        });
                        $topLicencias = $dashboardColegios;
                        usort($topLicencias, function ($a, $b) {
                            return ((int)$b['total_licencias']) <=> ((int)$a['total_licencias']);
                        });
                        $topSitios = $dashboardColegios;
                        usort($topSitios, function ($a, $b) {
                            return ((int)$b['total_sitios']) <=> ((int)$a['total_sitios']);
                        });
                        ?>
                        <ul class="list-unstyled inv-list mb-0">
                            <li><span>Mas software</span><strong><?= inventario_h($topSoftware[0]['nom_colegio']) ?> (<?= (int)$topSoftware[0]['total_softwares'] ?>)</strong></li>
                            <li><span>Mas licencias</span><strong><?= inventario_h($topLicencias[0]['nom_colegio']) ?> (<?= (int)$topLicencias[0]['total_licencias'] ?>)</strong></li>
                            <li><span>Mas sitios</span><strong><?= inventario_h($topSitios[0]['nom_colegio']) ?> (<?= (int)$topSitios[0]['total_sitios'] ?>)</strong></li>
                        </ul>
                    <?php else: ?>
                        <div class="alert alert-light border mb-0">No hay datos para el dashboard.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>Colegio</th>
                        <!-- <th>RBD</th> -->
                        <th>Softwares</th>
                        <th>Licencias</th>
                        <th>Webs</th>
                        <th>Apps</th>
                        <th>Clientes</th>
                        <th>Total sitios</th>
                        <th>Costo total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($dashboardColegios)): ?>
                        <?php foreach ($dashboardColegios as $fila): ?>
                            <tr>
                                <td>
                                    <div class="inv-colegio-row">
                                        <?php if (!empty($logosColegios[(int)$fila['id_colegio']])): ?>
                                            <span class="inv-colegio-thumb">
                                                <img src="<?= inventario_h($logosColegios[(int)$fila['id_colegio']]) ?>" alt="<?= inventario_h($fila['nom_colegio']) ?>" class="inv-colegio-thumb-img">
                                            </span>
                                        <?php endif; ?>
                                        <span class="fw-semibold"><?= inventario_h($fila['nom_colegio']) ?></span>
                                    </div>
                                </td>
                                <!-- <td><?= inventario_h($fila['rbd_colegio'] ?: '-') ?></td> -->
                                <td><?= (int)$fila['total_softwares'] ?></td>
                                <td><?= (int)$fila['total_licencias'] ?></td>
                                <td><?= (int)$fila['total_webs'] ?></td>
                                <td><?= (int)$fila['total_apps'] ?></td>
                                <td><?= (int)$fila['total_clientes'] ?></td>
                                <td><?= (int)$fila['total_sitios'] ?></td>
                                <td>USD <?= number_format((float)$fila['costo_total'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted">Aun no hay datos para mostrar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
window.INVENTARIO_DASHBOARD = {
    labels: <?= json_encode($labelsGrafico, JSON_UNESCAPED_UNICODE) ?>,
    softwares: <?= json_encode($softwareGrafico) ?>,
    sitios: <?= json_encode($sitiosGrafico) ?>,
    licencias: <?= json_encode($licenciasGrafico) ?>,
    resumenGlobal: <?= json_encode([
        'Softwares' => $totalesGlobales['softwares'],
        'Webs' => $totalesGlobales['webs'],
        'Apps' => $totalesGlobales['apps'],
        'Clientes' => $totalesGlobales['clientes'],
    ], JSON_UNESCAPED_UNICODE) ?>
};
</script>
