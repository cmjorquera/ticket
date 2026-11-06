<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $kpis = $estadistica->kpis();
    $estados = $estadistica->estados();
    $porCategoria = $estadistica->estadosPorCategoria();
    $porColegio = $estadistica->porColegio();
    $tiempos = $estadistica->tiemposPorEtapa();
    $porTecnico = $estadistica->porTecnico();
} catch (Throwable $e) {
    error_log('[estadistica] general: ' . $e->getMessage());
    est_error('No fue posible calcular la estadística.');
}
$colorEstado = [];
foreach ($estados as $e) { $colorEstado[$e['id']] = $e; }

$tituloPagina = 'Estadística general';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="inv-kicker">Módulo Estadística</span>
            <h1 class="inv-title mb-2">Estadística general</h1>
            <p class="inv-subtitle mb-0">Resumen de la mesa de ayuda: estados, categorías, colegios, tiempos por etapa y carga de cada técnico.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <?php require __DIR__ . '/componentes/selector_perfil.php'; ?>
            <a href="probando.php" class="module-btn module-btn--secondary"><i class="bi bi-bar-chart-line"></i>Ver gráficos</a>
            <a href="vida_ticket.php" class="module-btn module-btn--primary"><i class="bi bi-diagram-3"></i>Vida del ticket</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/componentes/kpis.php'; ?>
<?php require __DIR__ . '/componentes/estados_cards.php'; ?>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-7">
        <div class="card shadow-sm border-0 inv-list-card h-100 mb-0">
            <div class="card-body">
                <div class="inv-card-head">
                    <div><h5 class="mb-1">Estados por categoría</h5><p class="text-muted mb-0 small">Cómo se reparten los tickets de cada categoría.</p></div>
                </div>
                <div class="px-3 pt-3 est-leyenda">
                    <?php foreach ($estados as $e): ?><span><i style="background:<?= est_h($e['color']) ?>"></i><?= est_h($e['nombre']) ?></span><?php endforeach; ?>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Categoría</th><th class="text-end">Tickets</th><th style="width:45%">Distribución</th></tr></thead>
                        <tbody>
                        <?php if (!$porCategoria): ?>
                            <tr><td colspan="3" class="text-center text-muted">Sin tickets para mostrar.</td></tr>
                        <?php endif; ?>
                        <?php foreach ($porCategoria as $categoria => $info): ?>
                            <tr>
                                <td><?= est_h($categoria) ?></td>
                                <td class="text-end"><?= est_n($info['total']) ?></td>
                                <td>
                                    <div class="est-stack">
                                        <?php foreach ($info['estados'] as $idEstado => $cantidad):
                                            $pct = $info['total'] > 0 ? $cantidad / $info['total'] * 100 : 0;
                                            $est = $colorEstado[$idEstado] ?? ['color' => '#cbd5e1', 'nombre' => 'Otro']; ?>
                                            <span style="width:<?= round($pct, 2) ?>%;background:<?= est_h($est['color']) ?>" title="<?= est_h($est['nombre'] . ': ' . $cantidad . ' (' . est_d($pct) . '%)') ?>"></span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-5">
        <div class="card shadow-sm border-0 inv-list-card mb-0">
            <div class="card-body">
                <div class="inv-card-head">
                    <div><h5 class="mb-1">Tiempo promedio por etapa</h5><p class="text-muted mb-0 small">Horas entre una etapa y la siguiente.</p></div>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th>Etapa</th><th class="text-end">Horas prom.</th><th class="text-end">Casos</th></tr></thead>
                        <tbody>
                        <?php foreach ($tiempos as $fila): ?>
                            <tr>
                                <td><?= est_h($fila['etapa']) ?></td>
                                <td class="text-end"><strong><?= est_d($fila['horas']) ?></strong><?= $fila['horas'] !== null && $fila['horas'] >= 24 ? '<span class="est-sub">≈ ' . est_d($fila['horas'] / 24) . ' días</span>' : '' ?></td>
                                <td class="text-end"><?= est_n($fila['casos']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0 inv-list-card mb-0 mt-4">
            <div class="card-body">
                <div class="inv-card-head"><div><h5 class="mb-1">Tickets por colegio</h5><p class="text-muted mb-0 small">Según el colegio de quien creó el ticket.</p></div></div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead><tr><th>Colegio</th><th class="text-end">Total</th><th class="text-end">Abiertos</th><th class="text-end">Resueltos</th><th class="text-end">Demorados</th></tr></thead>
                        <tbody>
                        <?php if (!$porColegio): ?><tr><td colspan="5" class="text-center text-muted">Sin tickets para mostrar.</td></tr><?php endif; ?>
                        <?php foreach ($porColegio as $f): ?>
                            <tr><td><?= est_h($f['colegio']) ?></td><td class="text-end"><strong><?= est_n($f['total']) ?></strong></td><td class="text-end"><?= est_n($f['abiertos']) ?></td><td class="text-end"><?= est_n($f['resueltos']) ?></td><td class="text-end"><?= est_n($f['demorados']) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 inv-list-card mb-0">
            <div class="card-body">
                <div class="inv-card-head"><div><h5 class="mb-1">Carga por técnico</h5><p class="text-muted mb-0 small">Tickets asignados y días promedio de resolución.</p></div></div>
                <div class="table-responsive">
                    <table class="table table-striped align-middle mb-0">
                        <thead><tr><th>Técnico</th><th class="text-end">Asignados</th><th class="text-end">Abiertos</th><th class="text-end">Resueltos</th><th class="text-end">Demorados</th><th class="text-end">Días prom.</th></tr></thead>
                        <tbody>
                        <?php if (!$porTecnico): ?><tr><td colspan="6" class="text-center text-muted">Aún no hay tickets asignados.</td></tr><?php endif; ?>
                        <?php foreach ($porTecnico as $f): ?>
                            <tr><td><?= est_h($f['tecnico'] ?: 'Técnico #' . $f['id_tecnico']) ?></td><td class="text-end"><strong><?= est_n($f['total']) ?></strong></td><td class="text-end"><?= est_n($f['abiertos']) ?></td><td class="text-end"><?= est_n($f['resueltos']) ?></td><td class="text-end"><?= est_n($f['demorados']) ?></td><td class="text-end"><?= est_d($f['dias'] !== null ? (float) $f['dias'] : null) ?></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
