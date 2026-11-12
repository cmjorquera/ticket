<?php
/** Dashboard ejecutivo del inventario tecnológico (PC y monitores). */
require_once __DIR__ . '/componentes/boot.php';
require_once __DIR__ . '/dashboard_inventario_data.php';

try {
    $filters = di_parse_filters($_GET);
    $data = di_dashboard_data($db, $idUsuarioSession, $filters);
} catch (Throwable $e) {
    error_log('[estadistica] dashboard inventario: ' . $e->getMessage());
    est_error('No fue posible cargar el dashboard de inventario. ¿Está instalado el módulo Inventario PC?');
}
$ctx = $data['ctx'];
$kpis = $data['kpis'];

$antiguedadOrden = ['Menos de 3 años', '3 a 5 años', 'Más de 5 años', 'Sin fecha'];
$antiguedadMap = [];
foreach ($data['antiguedad'] as $row) {
    $antiguedadMap[(string) $row['bucket']] = (int) $row['total'];
}
$graficos = [
    'colegios' => array_map(static fn ($r) => (string) $r['nom_colegio'], $data['resumen_colegio']),
    'pc' => array_map(static fn ($r) => (int) $r['pc'], $data['resumen_colegio']),
    'monitores' => array_map(static fn ($r) => (int) $r['monitores'], $data['resumen_colegio']),
    'valores' => array_map(static fn ($r) => (float) $r['valor'], $data['resumen_colegio']),
    'estados' => array_map(static fn ($r) => (string) $r['estado'], $data['estado_general']),
    'estadosValores' => array_map(static fn ($r) => (int) $r['total'], $data['estado_general']),
    'ubicaciones' => array_map(static fn ($r) => trim((string) $r['ubicacion'] . ' · ' . (string) $r['nom_colegio']), $data['top_ubicaciones']),
    'ubicacionesValores' => array_map(static fn ($r) => (int) $r['total'], $data['top_ubicaciones']),
    'antiguedad' => $antiguedadOrden,
    'antiguedadValores' => array_map(static fn ($l) => $antiguedadMap[$l] ?? 0, $antiguedadOrden),
];
$pdfParams = http_build_query(['id_colegio' => $ctx['id_colegio'], 'desde' => $filters['desde'], 'hasta' => $filters['hasta']]);

$tituloPagina = 'Dashboard Inventario PC';
$usaGraficos = true;
$scriptsModulo = ['dashboard_inventario.js'];
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="inv-kicker">Módulo Estadística</span>
            <h1 class="inv-title mb-2">Dashboard Inventario PC</h1>
            <p class="inv-subtitle mb-0">PC y monitores por colegio, estado, valor, antigüedad y movimientos · <?= di_h($ctx['colegio_label']) ?> · <?= di_h(date('d-m-Y', strtotime($filters['desde']))) ?> al <?= di_h(date('d-m-Y', strtotime($filters['hasta']))) ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="../inventario/pc/index.php" class="module-btn module-btn--secondary"><i class="bi bi-pc-display-horizontal"></i>Ir a Inventario PC</a>
            <a href="descargar_dashboard_inventario_pdf.php?<?= di_h($pdfParams) ?>" class="module-btn module-btn--primary" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf"></i>Descargar PDF</a>
        </div>
    </div>
</div>

<form method="get" class="card shadow-sm border-0 inv-list-card est-filtro" id="formDashboardInventario">
    <div class="card-body">
        <div class="inv-toolbar row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label" for="filtroColegio">Colegio</label>
                <select name="id_colegio" id="filtroColegio" class="form-select">
                    <option value="0">Todos mis colegios</option>
                    <?php foreach ($ctx['colegios'] as $c): ?>
                        <option value="<?= (int) $c['id_colegio'] ?>" <?= $ctx['id_colegio'] === (int) $c['id_colegio'] ? 'selected' : '' ?>><?= di_h($c['nom_colegio']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2"><label class="form-label" for="filtroDesde">Compra desde</label><input type="date" name="desde" id="filtroDesde" class="form-control" value="<?= di_h($filters['desde']) ?>"></div>
            <div class="col-6 col-md-2"><label class="form-label" for="filtroHasta">Compra hasta</label><input type="date" name="hasta" id="filtroHasta" class="form-control" value="<?= di_h($filters['hasta']) ?>"></div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="module-btn module-btn--primary"><i class="bi bi-funnel"></i>Aplicar</button>
                <a href="dashboard_inventario.php" class="module-btn module-btn--secondary"><i class="bi bi-eraser"></i>Limpiar</a>
            </div>
        </div>
        <p class="text-muted small mb-0 px-3 pb-2">Los equipos sin fecha de compra siempre se incluyen.</p>
    </div>
</form>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['Total PC', est_n($kpis['total_pc']), 'Equipos registrados', 'bi-pc-display', 'primary'],
        ['Monitores', est_n($kpis['total_monitores']), 'Pantallas registradas', 'bi-display', 'info'],
        ['Activos', est_n($kpis['activos']), 'PC + monitores en uso', 'bi-check-circle', 'success'],
        ['En reparación', est_n($kpis['reparacion']), 'PC + monitores', 'bi-tools', 'warning'],
        ['Dados de baja', est_n($kpis['baja']), 'PC + monitores', 'bi-x-circle', 'danger'],
        ['Valor inventario', di_money($kpis['valor']), 'Según compras registradas', 'bi-cash-stack', 'secondary'],
    ] as [$label, $valor, $detalle, $icono, $tono]): ?>
        <div class="col-12 col-md-6 col-xl-2">
            <div class="inv-stat-card inv-stat-<?= $tono ?>"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label"><?= di_h($label) ?></span><strong<?= strlen($valor) > 9 ? ' class="est-compacto"' : '' ?>><?= di_h($valor) ?></strong><small><?= di_h($detalle) ?></small></div><?php if (strlen($valor) <= 9): ?><div class="inv-stat-icon"><i class="bi <?= $icono ?>"></i></div><?php endif; ?></div></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-7"><div class="card shadow-sm border-0 inv-list-card h-100 mb-0"><div class="card-body">
        <div class="inv-card-head"><div><h5 class="mb-1">PC y monitores por colegio</h5><p class="text-muted mb-0 small">Cantidad de activos registrados.</p></div></div>
        <div class="p-3"><div class="est-chart"><canvas id="gColegios"></canvas></div></div>
    </div></div></div>
    <div class="col-12 col-xl-5"><div class="card shadow-sm border-0 inv-list-card h-100 mb-0"><div class="card-body">
        <div class="inv-card-head"><div><h5 class="mb-1">Estado general</h5><p class="text-muted mb-0 small">Distribución actual de PC y monitores.</p></div></div>
        <div class="p-3"><div class="est-chart"><canvas id="gEstados"></canvas></div></div>
    </div></div></div>
</div>
<div class="row g-4 mb-4">
    <div class="col-12 col-xl-4"><div class="card shadow-sm border-0 inv-list-card h-100 mb-0"><div class="card-body">
        <div class="inv-card-head"><div><h5 class="mb-1">Antigüedad</h5><p class="text-muted mb-0 small">Según fecha de compra.</p></div></div>
        <div class="p-3"><div class="est-chart est-chart--sm"><canvas id="gAntiguedad"></canvas></div></div>
    </div></div></div>
    <div class="col-12 col-xl-4"><div class="card shadow-sm border-0 inv-list-card h-100 mb-0"><div class="card-body">
        <div class="inv-card-head"><div><h5 class="mb-1">Valor por colegio</h5><p class="text-muted mb-0 small">Suma de compras registradas.</p></div></div>
        <div class="p-3"><div class="est-chart est-chart--sm"><canvas id="gValor"></canvas></div></div>
    </div></div></div>
    <div class="col-12 col-xl-4"><div class="card shadow-sm border-0 inv-list-card h-100 mb-0"><div class="card-body">
        <div class="inv-card-head"><div><h5 class="mb-1">Ubicaciones con más activos</h5><p class="text-muted mb-0 small">Top 10, PC y monitores activos.</p></div></div>
        <div class="p-3"><div class="est-chart est-chart--sm"><canvas id="gUbicaciones"></canvas></div></div>
    </div></div></div>
</div>

<div class="card shadow-sm border-0 inv-list-card mb-4"><div class="card-body">
    <div class="inv-card-head"><div><h5 class="mb-1">Resumen por colegio</h5><p class="text-muted mb-0 small"><?= di_h($ctx['colegio_label']) ?></p></div></div>
    <div class="table-responsive"><table class="table table-striped align-middle mb-0">
        <thead><tr><th>Colegio</th><th class="text-end">PC</th><th class="text-end">Monitores</th><th class="text-end">Activos</th><th class="text-end">Reparación</th><th class="text-end">Baja</th><th class="text-end">Valor</th></tr></thead>
        <tbody>
        <?php if (!$data['resumen_colegio']): ?><tr><td colspan="7" class="text-center text-muted">Sin equipos registrados para estos filtros.</td></tr><?php endif; ?>
        <?php foreach ($data['resumen_colegio'] as $r): ?>
            <tr><td><?= di_h($r['nom_colegio']) ?></td><td class="text-end"><?= est_n($r['pc']) ?></td><td class="text-end"><?= est_n($r['monitores']) ?></td><td class="text-end"><?= est_n($r['activos']) ?></td><td class="text-end"><?= est_n($r['reparacion']) ?></td><td class="text-end"><?= est_n($r['baja']) ?></td><td class="text-end"><?= di_h(di_money($r['valor'])) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div></div>

<div class="row g-4">
    <div class="col-12 col-xl-7"><div class="card shadow-sm border-0 inv-list-card h-100 mb-0"><div class="card-body">
        <div class="inv-card-head"><div><h5 class="mb-1">Últimos movimientos</h5><p class="text-muted mb-0 small">Traslados recientes de PC y monitores.</p></div></div>
        <div class="table-responsive"><table class="table table-striped align-middle mb-0">
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Activo</th><th>Origen → destino</th><th>Usuario</th><th>Motivo</th></tr></thead>
            <tbody>
            <?php if (!$data['movimientos']): ?><tr><td colspan="6" class="text-center text-muted">Sin movimientos registrados.</td></tr><?php endif; ?>
            <?php foreach ($data['movimientos'] as $m): ?>
                <tr><td><?= $m['fecha'] ? di_h(date('d-m-Y H:i', strtotime((string) $m['fecha']))) : '—' ?></td><td><?= di_h($m['tipo_activo']) ?></td><td><?= di_h($m['activo'] ?: '—') ?></td><td><?= di_h($m['origen']) ?> → <?= di_h($m['destino']) ?></td><td><?= di_h(trim((string) $m['usuario']) ?: '—') ?></td><td><?= di_h($m['motivo'] ?: '—') ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div></div></div>
    <div class="col-12 col-xl-5"><div class="card shadow-sm border-0 inv-list-card h-100 mb-0"><div class="card-body">
        <div class="inv-card-head"><div><h5 class="mb-1">Próximos a 5 años</h5><p class="text-muted mb-0 small">PC comprados hace 4 a 5 años: planificar recambio.</p></div></div>
        <div class="table-responsive"><table class="table table-striped align-middle mb-0">
            <thead><tr><th>Equipo</th><th>Colegio</th><th>Compra</th><th class="text-end">Años</th></tr></thead>
            <tbody>
            <?php if (!$data['proximos5']): ?><tr><td colspan="4" class="text-center text-muted">Ningún equipo en ese rango.</td></tr><?php endif; ?>
            <?php foreach ($data['proximos5'] as $p): ?>
                <tr><td><?= di_h($p['equipo'] ?: '—') ?><span class="est-sub"><?= di_h(($p['tipo'] ?: 'PC') . ' · ' . $p['ubicacion']) ?></span></td><td><?= di_h($p['colegio'] ?: '—') ?></td><td><?= di_h(date('d-m-Y', strtotime((string) $p['fecha_compra']))) ?></td><td class="text-end"><?= (int) $p['antiguedad'] ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
    </div></div></div>
</div>
<script>window.DI_DATOS = <?= json_encode($graficos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
