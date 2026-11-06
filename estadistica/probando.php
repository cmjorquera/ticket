<?php
/** Estadística general v2: los mismos datos en gráficos (Chart.js). */
require_once __DIR__ . '/componentes/boot.php';

try {
    $kpis = $estadistica->kpis();
    $estados = $estadistica->estados();
    $mensual = $estadistica->mensual();
    $porTecnico = $estadistica->porTecnico();
    $estadosTecnico = $estadistica->estadosPorTecnico();
    $categorias = $estadistica->categoriasPorEstado();
} catch (Throwable $e) {
    error_log('[estadistica] v2: ' . $e->getMessage());
    est_error('No fue posible calcular la estadística.');
}

$datosGraficos = [
    'mensual' => $mensual,
    'tecnicos' => array_map(static fn (array $f): array => [
        'id' => (string) $f['id_tecnico'],
        'nombre' => $f['tecnico'] ?: 'Técnico #' . $f['id_tecnico'],
        'total' => (int) $f['total'],
        'dias' => $f['dias'] !== null ? (float) $f['dias'] : null,
    ], $porTecnico),
    'estadosTecnico' => $estadosTecnico,
    'categorias' => $categorias,
    'estados' => array_map(static fn (array $e): array => ['id' => $e['id'], 'nombre' => $e['nombre'], 'color' => $e['color']], $estados),
];

$tituloPagina = 'Estadística general v2';
$usaGraficos = true;
$scriptsModulo = ['graficos.js'];
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="inv-kicker">Módulo Estadística</span>
            <h1 class="inv-title mb-2">Métricas de tickets</h1>
            <p class="inv-subtitle mb-0">Evolución mensual, carga por técnico y categoría, y tiempos de resolución.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <?php require __DIR__ . '/componentes/selector_perfil.php'; ?>
            <a href="index.php" class="module-btn module-btn--secondary"><i class="bi bi-table"></i>Ver resumen</a>
            <a href="vida_ticket.php" class="module-btn module-btn--primary"><i class="bi bi-diagram-3"></i>Vida del ticket</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/componentes/kpis.php'; ?>
<?php require __DIR__ . '/componentes/estados_cards.php'; ?>

<div class="card shadow-sm border-0 inv-list-card mb-4">
    <div class="card-body">
        <div class="inv-card-head">
            <div><h5 class="mb-1">Evolución mensual</h5><p class="text-muted mb-0 small">Tickets ingresados cada mes y cuántos de ellos ya están resueltos.</p></div>
            <select id="filtroMeses" class="form-select form-select-sm est-card-select" aria-label="Rango de meses">
                <option value="6">Últimos 6 meses</option>
                <option value="12" selected>Últimos 12 meses</option>
                <option value="24">Últimos 24 meses</option>
            </select>
        </div>
        <div class="p-3"><div class="est-chart"><canvas id="gMensual"></canvas></div></div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0 inv-list-card h-100 mb-0">
            <div class="card-body">
                <div class="inv-card-head">
                    <div><h5 class="mb-1">Tickets por técnico</h5><p class="text-muted mb-0 small">Elige un técnico para ver sus tickets por estado.</p></div>
                    <select id="filtroTecnico" class="form-select form-select-sm est-card-select" aria-label="Técnico">
                        <option value="">Todos los técnicos</option>
                        <?php foreach ($datosGraficos['tecnicos'] as $t): ?><option value="<?= est_h($t['id']) ?>"><?= est_h($t['nombre']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="p-3"><div class="est-chart"><canvas id="gTecnicos"></canvas></div></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0 inv-list-card h-100 mb-0">
            <div class="card-body">
                <div class="inv-card-head">
                    <div><h5 class="mb-1">Categorías con más tickets</h5><p class="text-muted mb-0 small">Filtra por estado para ver dónde se concentran.</p></div>
                    <select id="filtroEstadoCategoria" class="form-select form-select-sm est-card-select" aria-label="Estado">
                        <option value="todos">Todos los estados</option>
                        <?php foreach ($estados as $e): ?><option value="<?= (int) $e['id'] ?>"><?= est_h($e['nombre']) ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="p-3"><div class="est-chart"><canvas id="gCategorias"></canvas></div></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0 inv-list-card h-100 mb-0">
            <div class="card-body">
                <div class="inv-card-head"><div><h5 class="mb-1">Días promedio de resolución</h5><p class="text-muted mb-0 small">Desde el ingreso hasta el término técnico, por técnico.</p></div></div>
                <div class="p-3"><div class="est-chart est-chart--sm"><canvas id="gResolucion"></canvas></div></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card shadow-sm border-0 inv-list-card h-100 mb-0">
            <div class="card-body">
                <div class="inv-card-head"><div><h5 class="mb-1">Abiertos vs resueltos por mes</h5><p class="text-muted mb-0 small">Estado actual de los tickets ingresados cada mes.</p></div></div>
                <div class="p-3"><div class="est-chart est-chart--sm"><canvas id="gAbiertos"></canvas></div></div>
            </div>
        </div>
    </div>
</div>
<script>window.EST_DATOS = <?= json_encode($datosGraficos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
