<?php
session_start();

require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';

$funciones        = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id'] ?? '');
$nombre           = trim(htmlspecialchars($_SESSION['nombre'] ?? '') . ' ' . htmlspecialchars($_SESSION['apellido_paterno'] ?? ''));
$areaTrabajo      = htmlspecialchars($_SESSION['id_area_trabajo'] ?? '');
$idPagActual      = '11';

$db = new MySQL('acceso_sistema_panel', 'acceso_comun', 'jorquera86;');

// ── Total tickets ─────────────────────────────────────────────────────────────
$kpi_total = (int)($db->fetch_assoc($db->consulta("SELECT COUNT(*) AS n FROM tickets WHERE estado=1"))['n'] ?? 0);

// ── Etiquetas personalizadas por estado ───────────────────────────────────────
$labels_estado = [
    'Recibido'   => 'Sin técnico',
    'Asignado'   => 'Con técnico',
    'En proceso' => 'Técnico trabajando',
    'Terminado'  => 'Terminado',
    'Cerrado'    => 'Cerrado',
    'Demorado'   => 'Ticket demorado',
    'Borrador'   => 'Borrador',
];
$iconos_estado = [
    'Recibido'   => 'bi-person-slash',
    'Asignado'   => 'bi-person-check',
    'En proceso' => 'bi-tools',
    'Terminado'  => 'bi-check2',
    'Cerrado'    => 'bi-check2-circle',
    'Demorado'   => 'bi-exclamation-triangle',
    'Borrador'   => 'bi-file-earmark',
];

// ── Conteo por estado — todos aunque tengan 0 ────────────────────────────────
$q = $db->consulta("
    SELECT e.id, e.nombre, e.color,
           COUNT(t.id_ticket) AS total
    FROM estados_ticket e
    LEFT JOIN tickets t ON t.id_estado = e.id AND t.estado = 1
    WHERE e.id != 4
    GROUP BY e.id
    ORDER BY e.orden ASC
");
$estados_data = [];
while ($r = $db->fetch_assoc($q)) {
    $total  = (int)$r['total'];
    $nombre = $r['nombre'] ?? 'Sin estado';
    $pct    = $kpi_total > 0 ? round($total / $kpi_total * 100, 1) : 0;
    $estados_data[] = [
        'id'     => $r['id'],
        'nombre' => $nombre,
        'label'  => $labels_estado[$nombre] ?? $nombre,
        'icono'  => $iconos_estado[$nombre] ?? 'bi-circle',
        'color'  => $r['color'] ?? '#888',
        'total'  => $total,
        'pct'    => $pct,
    ];
}

// ── Técnicos para el filtro (id_area_trabajo = 2) ────────────────────────────
$q_tec = $db->consulta("SELECT id, CONCAT(nombre,' ',apellido_paterno) AS nombre_completo FROM usuarios WHERE id_area_trabajo = 1 AND estado = 'Activo' ORDER BY nombre ASC");
$lista_tecnicos = [];
while ($r = $db->fetch_assoc($q_tec)) {
    $lista_tecnicos[] = ['id' => $r['id'], 'nombre' => $r['nombre_completo']];
}

// ── Datos por técnico para el donut (todos los estados) ──────────────────────
$datos_por_tecnico = [];
foreach ($lista_tecnicos as $tec) {
    $q_d = $db->consulta("
        SELECT e.nombre, e.color, COUNT(*) AS total
        FROM tickets t
        LEFT JOIN estados_ticket e ON t.id_estado = e.id
        WHERE t.estado = 1 AND t.id_tecnico = " . (int)$tec['id'] . "
        GROUP BY t.id_estado
    ");
    $td = ['labels'=>[], 'values'=>[], 'colors'=>[]];
    while ($r = $db->fetch_assoc($q_d)) {
        $td['labels'][] = $labels_estado[$r['nombre']] ?? $r['nombre'];
        $td['values'][] = (int)$r['total'];
        $td['colors'][] = $r['color'] ?? '#888';
    }
    $datos_por_tecnico[$tec['id']] = $td;
}

// Días promedio resolución
$kpi_prom = (float)($db->fetch_assoc($db->consulta("
    SELECT ROUND(AVG(DATEDIFF(p.fecha_termino_ticket,p.fecha_creacion_inicio)),1) AS d
    FROM proceso_tickets p
    INNER JOIN tickets t ON p.id_ticket=t.id_ticket
    WHERE t.estado=1
      AND p.fecha_termino_ticket IS NOT NULL
      AND p.fecha_termino_ticket!='0000-00-00'
      AND DATEDIFF(p.fecha_termino_ticket,p.fecha_creacion_inicio) BETWEEN 0 AND 180
"))['d'] ?? 0);

// ── G1: Evolución mensual ─────────────────────────────────────────────────────
$q = $db->consulta("SELECT DATE_FORMAT(p.fecha_creacion_inicio,'%Y-%m') AS mes, COUNT(*) AS total FROM proceso_tickets p INNER JOIN tickets t ON p.id_ticket=t.id_ticket WHERE t.estado=1 AND p.fecha_creacion_inicio IS NOT NULL AND p.fecha_creacion_inicio!='0000-00-00' GROUP BY mes ORDER BY mes ASC");
$meses=[]; $vals_mes=[];
while($r=$db->fetch_assoc($q)){ $meses[]=$r['mes']; $vals_mes[]=(int)$r['total']; }

// ── G2: Técnicos ──────────────────────────────────────────────────────────────
$q = $db->consulta("SELECT t.id_tecnico, CONCAT(u.nombre,' ',u.apellido_paterno) AS tecnico, COUNT(*) AS total FROM tickets t LEFT JOIN usuarios u ON t.id_tecnico=u.id WHERE t.estado=1 GROUP BY t.id_tecnico ORDER BY total DESC");
$tecnicos=[]; $vals_tec=[]; $totales_tecnicos_por_id=[];
while($r=$db->fetch_assoc($q)){
    $tecnicos[]=$r['tecnico']??'Sin asignar';
    $vals_tec[]=(int)$r['total'];
    if ($r['id_tecnico'] !== null && $r['id_tecnico'] !== '') {
        $totales_tecnicos_por_id[(string)$r['id_tecnico']] = (int)$r['total'];
    }
}

$tecnicos_por_filtro = [
    'todos' => [
        'labels' => $tecnicos,
        'values' => $vals_tec,
    ],
];
foreach ($lista_tecnicos as $tec) {
    $tecnicos_por_filtro[(string)$tec['id']] = [
        'labels' => [$tec['nombre']],
        'values' => [$totales_tecnicos_por_id[(string)$tec['id']] ?? 0],
    ];
}

// ── G3: Categorías ────────────────────────────────────────────────────────────
$q = $db->consulta("SELECT c.nombre_categoria AS cat, COUNT(*) AS total FROM tickets t LEFT JOIN categoria_de_ticket c ON t.id_categoria_ticket=c.id_categoria WHERE t.estado=1 GROUP BY t.id_categoria_ticket ORDER BY total DESC");
$categorias=[]; $vals_cat=[];
while($r=$db->fetch_assoc($q)){ $categorias[]=$r['cat']??'Sin categoría'; $vals_cat[]=(int)$r['total']; }

$categorias_por_estado = [
    'todos' => [
        'label' => 'Todos los estados',
        'color' => '#0ea5e9',
        'labels' => $categorias,
        'values' => $vals_cat,
    ],
];

foreach ($estados_data as $estado) {
    $categorias_por_estado[(string)$estado['id']] = [
        'label' => $estado['nombre'],
        'color' => $estado['color'],
        'labels' => [],
        'values' => [],
    ];
}

$q = $db->consulta("
    SELECT
        COALESCE(c.nombre_categoria, 'Sin categoría') AS cat,
        t.id_estado,
        COUNT(*) AS total
    FROM tickets t
    LEFT JOIN categoria_de_ticket c ON t.id_categoria_ticket = c.id_categoria
    WHERE t.estado = 1
      AND t.id_estado != 4
    GROUP BY t.id_estado, t.id_categoria_ticket
    ORDER BY t.id_estado ASC, total DESC
");
while ($r = $db->fetch_assoc($q)) {
    $estadoId = (string)($r['id_estado'] ?? '');
    if (!isset($categorias_por_estado[$estadoId])) {
        continue;
    }
    $categorias_por_estado[$estadoId]['labels'][] = $r['cat'] ?? 'Sin categoría';
    $categorias_por_estado[$estadoId]['values'][] = (int)$r['total'];
}

// ── G4: Días promedio resolución ──────────────────────────────────────────────
$q = $db->consulta("SELECT CONCAT(u.nombre,' ',u.apellido_paterno) AS tecnico, ROUND(AVG(DATEDIFF(p.fecha_termino_ticket,p.fecha_creacion_inicio)),1) AS dias FROM proceso_tickets p INNER JOIN tickets t ON p.id_ticket=t.id_ticket LEFT JOIN usuarios u ON t.id_tecnico=u.id WHERE t.estado=1 AND p.fecha_termino_ticket IS NOT NULL AND p.fecha_termino_ticket!='0000-00-00' AND DATEDIFF(p.fecha_termino_ticket,p.fecha_creacion_inicio) BETWEEN 0 AND 180 GROUP BY t.id_tecnico ORDER BY dias ASC");
$tec_res=[]; $vals_res=[];
while($r=$db->fetch_assoc($q)){ $tec_res[]=$r['tecnico']??'Sin asignar'; $vals_res[]=(float)$r['dias']; }

// ── G5: Abiertos vs Cerrados mensual ─────────────────────────────────────────
$q = $db->consulta("SELECT DATE_FORMAT(p.fecha_creacion_inicio,'%Y-%m') AS mes, SUM(CASE WHEN e.nombre IN ('Cerrado','Terminado') THEN 1 ELSE 0 END) AS resueltos, SUM(CASE WHEN e.nombre NOT IN ('Cerrado','Terminado') THEN 1 ELSE 0 END) AS activos FROM proceso_tickets p INNER JOIN tickets t ON p.id_ticket=t.id_ticket LEFT JOIN estados_ticket e ON t.id_estado=e.id WHERE t.estado=1 AND p.fecha_creacion_inicio IS NOT NULL AND p.fecha_creacion_inicio!='0000-00-00' GROUP BY mes ORDER BY mes ASC LIMIT 18");
$meses5=[]; $resueltos5=[]; $activos5=[];
while($r=$db->fetch_assoc($q)){ $meses5[]=$r['mes']; $resueltos5[]=(int)$r['resueltos']; $activos5[]=(int)$r['activos']; }

$versionCss = @filemtime(__DIR__ . '/css/estadistica.css') ?: time();
$versionJs  = @filemtime(__DIR__ . '/js/estadistica.js') ?: time();

// Colores de iconos por estado
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="../">
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <link href="estadistica/css/estadistica.css?v=<?= $versionCss ?>" rel="stylesheet">
    <style>
        .estado-card {
            border: 1px solid rgba(15,76,129,0.08);
            border-radius: 16px;
            background: #fbfdff;
            padding: 1rem;
            height: 100%;
        }
        .estado-grid {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 1rem;
        }
        .estado-card__top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .estado-card__left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .estado-card__icon {
            width: 34px; height: 34px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1rem;
            color: #fff;
            flex-shrink: 0;
        }
        .estado-card__nombre {
            font-size: 0.78rem;
            font-weight: 700;
            color: #17324d;
            line-height: 1.15;
        }
        .estado-card__total {
            font-size: 1.45rem;
            font-weight: 800;
            line-height: 1;
            color: #17324d;
        }
        .estado-card__pct {
            font-size: 0.82rem;
            color: #6a8097;
            margin-top: 2px;
        }
        .estado-bar-wrap {
            background: #e9f0f7;
            border-radius: 999px;
            height: 8px;
            margin-top: 10px;
            overflow: hidden;
        }
        .estado-bar {
            height: 100%;
            border-radius: 999px;
            transition: width .5s ease;
        }
        .ticket-technician-state-view {
            min-height: 280px;
            align-items: center;
            grid-template-columns: minmax(220px, 0.85fr) minmax(240px, 1.15fr);
            gap: 1.25rem;
        }
        .ticket-technician-state-view__chart {
            display: flex;
            justify-content: center;
        }
        .ticket-technician-state-view__legend {
            min-width: 0;
        }
        @media (max-width: 1399.98px) {
            .estado-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        @media (max-width: 767.98px) {
            .estado-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .ticket-technician-state-view {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 575.98px) {
            .estado-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body id="page-top">

<input type="hidden" id="idUsuario" value="<?= htmlspecialchars((string)$idUsuarioSession, ENT_QUOTES, 'UTF-8') ?>">

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

            <div class="container-fluid pb-4">

                <!-- Hero -->
                <div class="inv-hero mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <span class="inv-kicker mb-2">Sistema de Tickets</span>
                        <h1 class="inv-title mb-1">Dashboard de métricas</h1>
                        <p class="inv-subtitle mb-0">Análisis de carga, rendimiento y evolución de tickets</p>
                    </div>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="stats-summary-badge">
                            <span>Total tickets</span>
                            <strong><?= number_format($kpi_total) ?></strong>
                        </div>
                        <div class="stats-summary-badge">
                            <span>Días prom. resolución</span>
                            <strong><?= $kpi_prom ?> días</strong>
                        </div>
                    </div>
                </div>

                <!-- Estados con barra y % -->
                <section class="panel-card mb-4">
                    <div class="panel-card__header">
                        <div>
                            <span class="panel-card__eyebrow">Estado actual</span>
                            <h2 class="panel-card__title">Distribución de tickets por estado</h2>
                        </div>
                        <span class="panel-chip"><?= number_format($kpi_total) ?> tickets</span>
                    </div>
                    <div class="estado-grid">
                        <?php foreach ($estados_data as $est): ?>
                        <div>
                            <div class="estado-card">
                                <div class="estado-card__top">
                                    <div class="estado-card__left">
                                        <div class="estado-card__icon" style="background:<?= htmlspecialchars($est['color']) ?>">
                                            <i class="bi <?= htmlspecialchars($est['icono']) ?>"></i>
                                        </div>
                                        <span class="estado-card__nombre"><?= htmlspecialchars($est['label']) ?></span>
                                    </div>
                                </div>
                                <div class="estado-card__total"><?= number_format($est['total']) ?></div>
                                <div class="estado-card__pct"><?= $est['pct'] ?>% del total</div>
                                <div class="estado-bar-wrap">
                                    <div class="estado-bar" style="width:<?= $est['pct'] ?>%;background:<?= htmlspecialchars($est['color']) ?>"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- G1: Evolución mensual -->
                <section class="panel-card mb-4">
                    <div class="panel-card__header">
                        <div>
                            <span class="panel-card__eyebrow">Tendencia temporal</span>
                            <h2 class="panel-card__title">Evolución mensual de tickets</h2>
                        </div>
                        <select id="filtroRangoMensual" class="form-select form-select-sm estadistica-select-estado" aria-label="Filtrar evolución mensual">
                            <option value="6">Últimos 6 meses</option>
                            <option value="12">Últimos 12 meses</option>
                            <option value="18" selected>Últimos 18 meses</option>
                            <option value="actual">Año actual</option>
                            <option value="anterior">Año anterior</option>
                        </select>
                    </div>
                    <div class="chart-shell chart-shell--compact">
                        <canvas id="gMensual"></canvas>
                    </div>
                </section>

                <!-- G2 + G3 -->
                <section class="row g-4 mb-4">
                    <div class="col-12 col-xl-6">
                        <div class="panel-card h-100">
                            <div class="panel-card__header">
                                <div>
                                    <span class="panel-card__eyebrow">Carga de trabajo</span>
                                    <h2 class="panel-card__title">Tickets por técnico</h2>
                                </div>
                                <select id="selectTecnico" class="form-select form-select-sm estadistica-select-estado" aria-label="Seleccionar técnico">
                                    <option value="todos">Todos los técnicos</option>
                                    <?php foreach ($lista_tecnicos as $tec): ?>
                                        <option value="<?= htmlspecialchars((string)$tec['id'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($tec['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div id="tecnicosBarView" class="chart-shell chart-shell--compact">
                                <canvas id="gTecnicos"></canvas>
                            </div>
                            <div id="tecnicoEstadoView" class="ticket-technician-state-view" style="display:none;">
                                <div class="ticket-technician-state-view__chart">
                                    <div style="position:relative;width:260px;height:260px;">
                                        <canvas id="gDonutTecnico"></canvas>
                                        <div id="donutCenter" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;display:none;">
                                            <div style="font-size:2rem;font-weight:800;color:#17324d;" id="donutTotal"></div>
                                            <div style="font-size:0.78rem;color:#6a8097;font-weight:700;text-transform:uppercase;">tickets</div>
                                        </div>
                                        <div id="donutEmpty" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;color:#94a3b8;font-size:0.85rem;">
                                            Sin datos
                                        </div>
                                    </div>
                                </div>
                                <div id="donutLeyenda" class="ticket-technician-state-view__legend d-flex flex-column gap-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-6">
                        <div class="panel-card h-100">
                            <div class="panel-card__header">
                                <div>
                                    <span class="panel-card__eyebrow">Carga por categoría</span>
                                    <h2 class="panel-card__title">Categorías con mayor volumen</h2>
                                </div>
                                <select id="filtroEstadoCategorias" class="form-select form-select-sm estadistica-select-estado" aria-label="Filtrar categorías por estado">
                                    <option value="todos">Todos los estados</option>
                                    <?php foreach ($estados_data as $estado): ?>
                                        <option value="<?= htmlspecialchars((string)$estado['id'], ENT_QUOTES, 'UTF-8') ?>">
                                            <?= htmlspecialchars($estado['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="chart-shell chart-shell--compact">
                                <canvas id="gCategorias"></canvas>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- G4 + G5 -->
                <section class="row g-4 mb-4">
                    <div class="col-12 col-xl-5">
                        <div class="panel-card h-100">
                            <div class="panel-card__header">
                                <div>
                                    <span class="panel-card__eyebrow">Rendimiento</span>
                                    <h2 class="panel-card__title">Días promedio de resolución</h2>
                                </div>
                                <span class="panel-chip">Por técnico</span>
                            </div>
                            <div class="chart-shell chart-shell--compact">
                                <canvas id="gResolucion"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-7">
                        <div class="panel-card h-100">
                            <div class="panel-card__header">
                                <div>
                                    <span class="panel-card__eyebrow">Seguimiento mensual</span>
                                    <h2 class="panel-card__title">Resueltos vs activos por mes</h2>
                                </div>
                            </div>
                            <div class="chart-shell chart-shell--compact">
                                <canvas id="gAbiertos"></canvas>
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
</div>

<script>
const PALETTE = ['#0f4c81','#0f766e','#d97706','#b91c1c','#0ea5e9','#7c3aed','#16a34a','#f97316'];
const datosMensuales = {
    labels: <?= json_encode($meses, JSON_UNESCAPED_UNICODE) ?>,
    values: <?= json_encode($vals_mes) ?>
};

function filtrarDatosMensuales(rango) {
    const labels = datosMensuales.labels || [];
    const values = datosMensuales.values || [];
    const yearActual = new Date().getFullYear();
    const yearAnterior = yearActual - 1;

    if (rango === 'actual' || rango === 'anterior') {
        const yearFiltro = rango === 'actual' ? yearActual : yearAnterior;
        const labelsFiltrados = [];
        const valuesFiltrados = [];

        labels.forEach(function (label, index) {
            if (String(label).startsWith(String(yearFiltro) + '-')) {
                labelsFiltrados.push(label);
                valuesFiltrados.push(values[index]);
            }
        });

        return { labels: labelsFiltrados, values: valuesFiltrados };
    }

    const limite = parseInt(rango, 10) || 18;
    return {
        labels: labels.slice(-limite),
        values: values.slice(-limite)
    };
}

const datosMensualesIniciales = filtrarDatosMensuales('18');
const chartMensual = new Chart(document.getElementById('gMensual'), {
    type: 'line',
    data: { labels: datosMensualesIniciales.labels, datasets: [{ label: 'Tickets', data: datosMensualesIniciales.values, borderColor: '#0f4c81', backgroundColor: 'rgba(15,76,129,0.08)', fill: true, tension: 0.4, pointBackgroundColor: '#0f4c81', pointRadius: 4 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { ticks: { maxRotation: 45 } }, y: { beginAtZero: true } } }
});

document.getElementById('filtroRangoMensual').addEventListener('change', function () {
    const data = filtrarDatosMensuales(this.value);
    chartMensual.data.labels = data.labels;
    chartMensual.data.datasets[0].data = data.values;
    chartMensual.update();
});

const tecnicosPorFiltro = <?= json_encode($tecnicos_por_filtro, JSON_UNESCAPED_UNICODE) ?>;
const chartTecnicos = new Chart(document.getElementById('gTecnicos'), {
    type: 'bar',
    data: { labels: <?= json_encode($tecnicos, JSON_UNESCAPED_UNICODE) ?>, datasets: [{ label: 'Tickets', data: <?= json_encode($vals_tec) ?>, backgroundColor: PALETTE, borderRadius: 8 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } }
});

const categoriasPorEstado = <?= json_encode($categorias_por_estado, JSON_UNESCAPED_UNICODE) ?>;
const filtroEstadoCategorias = document.getElementById('filtroEstadoCategorias');
const categoriasIniciales = categoriasPorEstado.todos || { label: 'Tickets', color: '#0ea5e9', labels: [], values: [] };
const chartCategorias = new Chart(document.getElementById('gCategorias'), {
    type: 'bar',
    data: {
        labels: categoriasIniciales.labels,
        datasets: [{
            label: categoriasIniciales.label,
            data: categoriasIniciales.values,
            backgroundColor: categoriasIniciales.color,
            borderRadius: 6
        }]
    },
    options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true }, y: { grid: { display: false } } } }
});

if (filtroEstadoCategorias) {
    filtroEstadoCategorias.addEventListener('change', function () {
        const data = categoriasPorEstado[this.value] || categoriasIniciales;
        chartCategorias.data.labels = data.labels;
        chartCategorias.data.datasets[0].label = data.label;
        chartCategorias.data.datasets[0].data = data.values;
        chartCategorias.data.datasets[0].backgroundColor = data.color || '#0ea5e9';
        chartCategorias.update();
    });
}

new Chart(document.getElementById('gResolucion'), {
    type: 'bar',
    data: { labels: <?= json_encode($tec_res, JSON_UNESCAPED_UNICODE) ?>, datasets: [{ label: 'Días promedio', data: <?= json_encode($vals_res) ?>, backgroundColor: '#d97706', borderRadius: 8 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true } } }
});

new Chart(document.getElementById('gAbiertos'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($meses5, JSON_UNESCAPED_UNICODE) ?>,
        datasets: [
            { label: 'Resueltos', data: <?= json_encode($resueltos5) ?>, backgroundColor: '#16a34a', borderRadius: 4 },
            { label: 'Activos',   data: <?= json_encode($activos5) ?>,   backgroundColor: '#ef4444', borderRadius: 4 }
        ]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { boxWidth: 12 } } }, scales: { x: { grid: { display: false }, ticks: { maxRotation: 45 } }, y: { beginAtZero: true } } }
});

// ── Donut por técnico ─────────────────────────────────────────────────────────
const datosTecnicos = <?= json_encode($datos_por_tecnico, JSON_UNESCAPED_UNICODE) ?>;

const donutCtx = document.getElementById('gDonutTecnico').getContext('2d');
let donutChart = new Chart(donutCtx, {
    type: 'doughnut',
    data: { labels: [], datasets: [{ data: [], backgroundColor: [], borderWidth: 0, hoverOffset: 8 }] },
    options: {
        responsive: false,
        cutout: '65%',
        plugins: { legend: { display: false }, tooltip: { callbacks: {
            label: ctx => ' ' + ctx.label + ': ' + ctx.raw + ' tickets (' + Math.round(ctx.raw / ctx.dataset.data.reduce((a,b)=>a+b,0) * 100) + '%)'
        }}}
    }
});

document.getElementById('selectTecnico').addEventListener('change', function() {
    const id = this.value;
    const barView = document.getElementById('tecnicosBarView');
    const estadoView = document.getElementById('tecnicoEstadoView');
    const empty  = document.getElementById('donutEmpty');
    const center = document.getElementById('donutCenter');
    const total  = document.getElementById('donutTotal');
    const leyenda = document.getElementById('donutLeyenda');

    if (id === 'todos') {
        const tecnicoData = tecnicosPorFiltro.todos;
        chartTecnicos.data.labels = tecnicoData.labels;
        chartTecnicos.data.datasets[0].data = tecnicoData.values;
        chartTecnicos.data.datasets[0].backgroundColor = PALETTE;
        chartTecnicos.update();
        barView.style.display = 'block';
        estadoView.style.display = 'none';
        chartTecnicos.resize();

        donutChart.data.labels = [];
        donutChart.data.datasets[0].data = [];
        donutChart.data.datasets[0].backgroundColor = [];
        donutChart.update();
        empty.style.display = 'block';
        center.style.display = 'none';
        leyenda.innerHTML = '';
        return;
    }

    barView.style.display = 'none';
    estadoView.style.display = 'grid';

    const d = datosTecnicos[id] || { labels: [], values: [], colors: [] };
    donutChart.data.labels = d.labels;
    donutChart.data.datasets[0].data   = d.values;
    donutChart.data.datasets[0].backgroundColor = d.colors;
    donutChart.update();

    const sum = d.values.reduce((a,b) => a+b, 0);
    total.textContent = sum;
    empty.style.display  = sum > 0 ? 'none' : 'block';
    center.style.display = sum > 0 ? 'block' : 'none';

    leyenda.innerHTML = d.labels.map((lbl, i) => {
        const pct = sum > 0 ? Math.round(d.values[i] / sum * 100) : 0;
        return `<div style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:10px;border:1px solid rgba(15,76,129,0.08);background:#fbfdff;">
            <span style="width:12px;height:12px;border-radius:3px;background:${d.colors[i]};flex-shrink:0;"></span>
            <span style="flex:1;font-size:0.85rem;color:#17324d;font-weight:600;">${lbl}</span>
            <strong style="font-size:0.9rem;color:#17324d;">${d.values[i]}</strong>
            <span style="font-size:0.78rem;color:#6a8097;">${pct}%</span>
        </div>`;
    }).join('');
});
</script>

<script src="js/funciones.js"></script>
<script src="js/mensajes.js"></script>
<script src="js/ticket.js"></script>
<script src="js/buscadores.js"></script>
<script src="js/validacionTicket.js"></script>
<script src="js/sb-admin-2.min.js"></script>
<script src="estadistica/js/estadistica.js?v=<?= $versionJs ?>"></script>
</body>
</html>
