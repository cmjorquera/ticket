<?php
session_start();

require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';
require_once __DIR__ . '/dashboard_inventario_data.php';

$idUsuarioSession = (int)($_SESSION['id'] ?? 0);
if ($idUsuarioSession <= 0) {
    header('Location: ../index.php');
    exit;
}

$db = new MySQL('', '', '');
$db->set_charset('utf8mb4');
$funciones = new Funciones();
$idPagActual = '11';
$filters = di_parse_filters($_GET);
$data = di_dashboard_data($db, $funciones, $idUsuarioSession, $filters);
$ctx = $data['ctx'];
$kpis = $data['kpis'];

$chartColegioLabels = array_map(static fn($r) => (string)$r['nom_colegio'], $data['resumen_colegio']);
$chartPc = array_map(static fn($r) => (int)$r['pc'], $data['resumen_colegio']);
$chartMonitores = array_map(static fn($r) => (int)$r['monitores'], $data['resumen_colegio']);
$chartValores = array_map(static fn($r) => (float)$r['valor'], $data['resumen_colegio']);
$chartEstadosLabels = array_map(static fn($r) => (string)$r['estado'], $data['estado_general']);
$chartEstadosValues = array_map(static fn($r) => (int)$r['total'], $data['estado_general']);
$chartUbicLabels = array_map(static fn($r) => trim((string)$r['ubicacion'] . ' · ' . (string)$r['nom_colegio']), $data['top_ubicaciones']);
$chartUbicValues = array_map(static fn($r) => (int)$r['total'], $data['top_ubicaciones']);
$antiguedadOrden = ['Menos de 3 años', '3 a 5 años', 'Más de 5 años', 'Sin fecha'];
$antiguedadMap = [];
foreach ($data['antiguedad'] as $row) {
    $antiguedadMap[(string)$row['bucket']] = (int)$row['total'];
}
$chartAntiguedad = array_map(static fn($label) => $antiguedadMap[$label] ?? 0, $antiguedadOrden);

$pdfParams = http_build_query([
    'id_colegio' => $ctx['id_colegio'],
    'desde' => $filters['desde'],
    'hasta' => $filters['hasta'],
]);
$versionCss = @filemtime(__DIR__ . '/css/dashboard_inventario.css') ?: time();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="../">
    <?php $funciones->header(); ?>
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <link href="estadistica/css/estadistica.css" rel="stylesheet">
    <link href="estadistica/css/dashboard_inventario.css?v=<?= di_h($versionCss) ?>" rel="stylesheet">
    <script src="vendor/chart.js/Chart.min.js"></script>
</head>
<body id="page-top">
<input type="hidden" id="idUsuario" value="<?= di_h($idUsuarioSession) ?>">

<div id="wrapper">
    <?php $funciones->menuLateral2($idUsuarioSession, $idPagActual); ?>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                    <i class="fa fa-bars"></i>
                </button>
                <?php $funciones->cabezera(); ?>
            </nav>

            <div class="container-fluid pb-4 dashboard-inventario">
                <div class="inv-dashboard-hero mb-4">
                    <div>
                        <span class="inv-dashboard-kicker">Inventario tecnologico</span>
                        <h1 class="inv-dashboard-title">Dashboard Inventario</h1>
                        <p class="inv-dashboard-subtitle">Vista ejecutiva de PC, monitores, estados, valor y movimientos del inventario.</p>
                    </div>
                    <div class="inv-dashboard-range">
                        <span>Periodo</span>
                        <strong><?= di_h(date('d-m-Y', strtotime($filters['desde']))) ?> al <?= di_h(date('d-m-Y', strtotime($filters['hasta']))) ?></strong>
                    </div>
                </div>

                <form method="get" class="inv-filter-card mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-4">
                            <label class="form-label">Colegio</label>
                            <select name="id_colegio" class="form-select">
                                <option value="0">Todos los colegios</option>
                                <?php foreach ($ctx['colegios'] as $colegio): ?>
                                    <option value="<?= (int)$colegio['id_colegio'] ?>" <?= $ctx['id_colegio'] === (int)$colegio['id_colegio'] ? 'selected' : '' ?>>
                                        <?= di_h($colegio['nom_colegio']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label">Desde</label>
                            <input type="date" name="desde" class="form-control" value="<?= di_h($filters['desde']) ?>">
                        </div>
                        <div class="col-md-3 col-lg-2">
                            <label class="form-label">Hasta</label>
                            <input type="date" name="hasta" class="form-control" value="<?= di_h($filters['hasta']) ?>">
                        </div>
                        <div class="col-md-6 col-lg-4 d-flex gap-2 justify-content-lg-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-arrow-repeat me-1"></i>Actualizar
                            </button>
                            <a href="estadistica/descargar_dashboard_inventario_pdf.php?<?= di_h($pdfParams) ?>" class="btn btn-outline-danger" target="_blank" rel="noopener">
                                <i class="bi bi-file-earmark-pdf me-1"></i>Descargar PDF
                            </a>
                        </div>
                    </div>
                </form>

                <div class="row g-3 mb-4">
                    <?php
                    $cards = [
                        ['Total PC', $kpis['total_pc'], 'bi-pc-display', 'primary'],
                        ['Total monitores', $kpis['total_monitores'], 'bi-display', 'info'],
                        ['Activos', $kpis['activos'], 'bi-check-circle', 'success'],
                        ['En reparacion', $kpis['reparacion'], 'bi-tools', 'warning'],
                        ['Dados de baja', $kpis['baja'], 'bi-x-circle', 'danger'],
                        ['Valor inventario', di_money($kpis['valor']), 'bi-cash-stack', 'primary'],
                    ];
                    ?>
                    <?php foreach ($cards as [$label, $value, $icon, $tone]): ?>
                        <div class="col-sm-6 col-xl-2">
                            <article class="inv-kpi-card inv-kpi-card--<?= di_h($tone) ?>">
                                <div class="inv-kpi-icon"><i class="bi <?= di_h($icon) ?>"></i></div>
                                <span><?= di_h($label) ?></span>
                                <strong><?= di_h($value) ?></strong>
                            </article>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-xl-7">
                        <section class="inv-chart-card">
                            <div class="inv-card-header">
                                <h2>PC y monitores por colegio</h2>
                                <span>Barras comparativas</span>
                            </div>
                            <canvas id="chartActivosColegio" height="130"></canvas>
                        </section>
                    </div>
                    <div class="col-xl-5">
                        <section class="inv-chart-card">
                            <div class="inv-card-header">
                                <h2>Estado general</h2>
                                <span>Distribucion actual</span>
                            </div>
                            <canvas id="chartEstadoGeneral" height="130"></canvas>
                        </section>
                    </div>
                    <div class="col-xl-6">
                        <section class="inv-chart-card">
                            <div class="inv-card-header">
                                <h2>Valor inventario por colegio</h2>
                                <span>Compra registrada</span>
                            </div>
                            <canvas id="chartValorColegio" height="150"></canvas>
                        </section>
                    </div>
                    <div class="col-xl-6">
                        <section class="inv-chart-card">
                            <div class="inv-card-header">
                                <h2>Top ubicaciones con más activos</h2>
                                <span>PC y monitores activos</span>
                            </div>
                            <canvas id="chartTopUbicaciones" height="150"></canvas>
                        </section>
                    </div>
                    <div class="col-xl-5">
                        <section class="inv-chart-card">
                            <div class="inv-card-header">
                                <h2>Antigüedad de equipos</h2>
                                <span>Según fecha de compra</span>
                            </div>
                            <canvas id="chartAntiguedad" height="130"></canvas>
                        </section>
                    </div>
                    <div class="col-xl-7">
                        <section class="inv-chart-card">
                            <div class="inv-card-header">
                                <h2>Resumen por colegio</h2>
                                <span><?= di_h($ctx['colegio_label']) ?></span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle inv-table">
                                    <thead>
                                        <tr>
                                            <th>Colegio</th>
                                            <th class="text-end">PC</th>
                                            <th class="text-end">Monitores</th>
                                            <th class="text-end">Activos</th>
                                            <th class="text-end">Reparacion</th>
                                            <th class="text-end">Baja</th>
                                            <th class="text-end">Valor</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['resumen_colegio'] as $row): ?>
                                            <tr>
                                                <td><?= di_h($row['nom_colegio']) ?></td>
                                                <td class="text-end"><?= (int)$row['pc'] ?></td>
                                                <td class="text-end"><?= (int)$row['monitores'] ?></td>
                                                <td class="text-end"><?= (int)$row['activos'] ?></td>
                                                <td class="text-end"><?= (int)$row['reparacion'] ?></td>
                                                <td class="text-end"><?= (int)$row['baja'] ?></td>
                                                <td class="text-end"><?= di_h(di_money($row['valor'])) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-xl-7">
                        <section class="inv-chart-card">
                            <div class="inv-card-header">
                                <h2>Últimos movimientos</h2>
                                <span>Traslados recientes</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle inv-table">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Tipo</th>
                                            <th>Activo</th>
                                            <th>Origen</th>
                                            <th>Destino</th>
                                            <th>Usuario</th>
                                            <th>Motivo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['movimientos'] as $row): ?>
                                            <tr>
                                                <td><?= di_h($row['fecha'] ? date('d-m-Y H:i', strtotime($row['fecha'])) : '') ?></td>
                                                <td><span class="badge bg-light text-primary border"><?= di_h($row['tipo_activo']) ?></span></td>
                                                <td><?= di_h($row['activo']) ?></td>
                                                <td><?= di_h($row['origen']) ?></td>
                                                <td><?= di_h($row['destino']) ?></td>
                                                <td><?= di_h(trim((string)$row['usuario']) ?: 'Sin usuario') ?></td>
                                                <td><?= di_h($row['motivo']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($data['movimientos'])): ?>
                                            <tr><td colspan="7" class="text-center text-muted py-4">Sin movimientos para el filtro seleccionado.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>

                    <div class="col-xl-5">
                        <section class="inv-chart-card">
                            <div class="inv-card-header">
                                <h2>Equipos próximos a cumplir 5 años</h2>
                                <span>Renovación preventiva</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle inv-table">
                                    <thead>
                                        <tr>
                                            <th>Equipo</th>
                                            <th>Tipo</th>
                                            <th>Colegio</th>
                                            <th>Ubicación</th>
                                            <th>Fecha compra</th>
                                            <th>Antig.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($data['proximos5'] as $row): ?>
                                            <tr>
                                                <td><?= di_h($row['equipo']) ?></td>
                                                <td><?= di_h($row['tipo']) ?></td>
                                                <td><?= di_h($row['colegio']) ?></td>
                                                <td><?= di_h($row['ubicacion']) ?></td>
                                                <td><?= di_h(date('d-m-Y', strtotime($row['fecha_compra']))) ?></td>
                                                <td><?= (int)$row['antiguedad'] ?> años</td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($data['proximos5'])): ?>
                                            <tr><td colspan="6" class="text-center text-muted py-4">Sin equipos próximos a cumplir 5 años.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.dashboardInventarioData = <?= json_encode([
    'colegios' => $chartColegioLabels,
    'pc' => $chartPc,
    'monitores' => $chartMonitores,
    'valores' => $chartValores,
    'estadosLabels' => $chartEstadosLabels,
    'estadosValues' => $chartEstadosValues,
    'ubicacionesLabels' => $chartUbicLabels,
    'ubicacionesValues' => $chartUbicValues,
    'antiguedadLabels' => $antiguedadOrden,
    'antiguedadValues' => $chartAntiguedad,
], JSON_UNESCAPED_UNICODE) ?>;
</script>
<script>
(function () {
    var d = window.dashboardInventarioData || {};
    var azul = '#0F4C81';
    var celeste = '#41b2c4';
    var verde = '#25b865';
    var amarillo = '#ffa21d';
    var rojo = '#ea4d4d';
    var gris = '#91a1b6';

    function chart(id, config) {
        var el = document.getElementById(id);
        if (!el || typeof Chart === 'undefined') return;
        return new Chart(el, config);
    }

    var grid = { gridLines: { color: 'rgba(15,76,129,.08)' }, ticks: { fontColor: '#64748b' } };
    chart('chartActivosColegio', {
        type: 'bar',
        data: { labels: d.colegios || [], datasets: [
            { label: 'PC', data: d.pc || [], backgroundColor: azul },
            { label: 'Monitores', data: d.monitores || [], backgroundColor: celeste }
        ]},
        options: { responsive: true, legend: { position: 'bottom' }, scales: { yAxes: [grid], xAxes: [grid] } }
    });

    chart('chartEstadoGeneral', {
        type: 'doughnut',
        data: { labels: d.estadosLabels || [], datasets: [{ data: d.estadosValues || [], backgroundColor: [verde, gris, amarillo, rojo, celeste, azul] }] },
        options: { responsive: true, legend: { position: 'bottom' }, cutoutPercentage: 62 }
    });

    chart('chartValorColegio', {
        type: 'horizontalBar',
        data: { labels: d.colegios || [], datasets: [{ label: 'Valor inventario', data: d.valores || [], backgroundColor: azul }] },
        options: { responsive: true, legend: { display: false }, scales: { xAxes: [grid], yAxes: [grid] } }
    });

    chart('chartTopUbicaciones', {
        type: 'horizontalBar',
        data: { labels: d.ubicacionesLabels || [], datasets: [{ label: 'Activos', data: d.ubicacionesValues || [], backgroundColor: celeste }] },
        options: { responsive: true, legend: { display: false }, scales: { xAxes: [grid], yAxes: [grid] } }
    });

    chart('chartAntiguedad', {
        type: 'bar',
        data: { labels: d.antiguedadLabels || [], datasets: [{ label: 'Activos', data: d.antiguedadValues || [], backgroundColor: [verde, amarillo, rojo, gris] }] },
        options: { responsive: true, legend: { display: false }, scales: { yAxes: [grid], xAxes: [grid] } }
    });
}());
</script>
<script src="js/funciones.js"></script>
<script src="js/sb-admin-2.min.js"></script>
</body>
</html>
