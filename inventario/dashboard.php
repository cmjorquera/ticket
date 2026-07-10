<?php
require_once __DIR__ . '/componentes/boot.php';

function inv_dash_money($valor): string
{
    return '$' . number_format((int)$valor, 0, ',', '.');
}

function inv_dash_int($valor): string
{
    return number_format((int)$valor, 0, ',', '.');
}

try {
    $tituloPagina = 'Dashboard Inventario';
    $alcanceInventario = $inventario->obtenerAlcanceInventario($idUsuarioSession);
    $perfilInventario = (int)($alcanceInventario['id_perfil'] ?? 1);

    if ($perfilInventario < 2) {
        header('Location: index.php');
        exit;
    }

    $filtrosDashboard = $inventario->normalizarFiltrosDashboard([
        'id_colegio' => (int)($_GET['id_colegio'] ?? 0),
        'fecha_desde' => trim((string)($_GET['fecha_desde'] ?? '')),
        'fecha_hasta' => trim((string)($_GET['fecha_hasta'] ?? '')),
    ], $alcanceInventario);

    $dashboard = $inventario->obtenerDashboardInventario($filtrosDashboard);
    $coloresColegio = $inventario->obtenerColoresColegio($idUsuarioSession);
    $colegios = $alcanceInventario['colegios'] ?? [];
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar el dashboard de inventario: ' . $e->getMessage());
}

$_c1 = preg_match('/^#[0-9a-fA-F]{3,8}$/', $coloresColegio['color_principal'] ?? '') ? $coloresColegio['color_principal'] : '';
$_c2 = preg_match('/^#[0-9a-fA-F]{3,8}$/', $coloresColegio['color_secundario'] ?? '') ? $coloresColegio['color_secundario'] : '';
$_heroBranded = $_c1 !== '';
$_heroStyle = $_heroBranded ? ' style="background: linear-gradient(135deg, ' . $_c1 . ' 0%, ' . ($_c2 ?: $_c1) . ' 100%)"' : '';

$kpis = $dashboard['kpis'] ?? [];
$valor = $dashboard['valor'] ?? [];
$valorPorColegio = $dashboard['valor_por_colegio'] ?? [];
$resumenPorColegio = $dashboard['resumen_por_colegio'] ?? [];
$alertas = $dashboard['alertas'] ?? [];
$ultimos = $dashboard['ultimos'] ?? [];
$detalleValor = $dashboard['detalle_valor'] ?? [];
$hardware = $dashboard['hardware'] ?? [];
$fechaRegistroDisponible = (bool)($dashboard['fecha_registro_disponible'] ?? false);

$totalGeneral = [
    'total_equipos' => 0,
    'activos' => 0,
    'sin_ubicacion' => 0,
    'sin_responsable' => 0,
    'en_reparacion' => 0,
    'dados_baja' => 0,
    'valor_total' => 0,
];
foreach ($resumenPorColegio as $filaResumen) {
    foreach ($totalGeneral as $clave => $valorInicial) {
        $totalGeneral[$clave] += (int)($filaResumen[$clave] ?? 0);
    }
}

$colegioSeleccionado = 'Todos los colegios visibles';
foreach ($colegios as $colegio) {
    if ((int)$colegio['id_colegio'] === (int)($filtrosDashboard['id_colegio'] ?? 0)) {
        $colegioSeleccionado = $colegio['nom_colegio'];
        break;
    }
}

$colegioMayor = $valor['colegio_mayor_valor'] ?? null;
$chartEstados = [
    'labels' => array_map(static fn($f) => (string)$f['etiqueta'], $dashboard['estados'] ?? []),
    'values' => array_map(static fn($f) => (int)$f['total'], $dashboard['estados'] ?? []),
];
$chartTipos = [
    'labels' => array_map(static fn($f) => (string)$f['etiqueta'], $dashboard['tipos'] ?? []),
    'values' => array_map(static fn($f) => (int)$f['total'], $dashboard['tipos'] ?? []),
];
$chartRam = [
    'labels' => array_map(static fn($f) => (string)$f['etiqueta'], $hardware['ram'] ?? []),
    'values' => array_map(static fn($f) => (int)$f['total'], $hardware['ram'] ?? []),
];
$chartSistemas = [
    'labels' => array_map(static fn($f) => (string)$f['etiqueta'], $hardware['sistemas'] ?? []),
    'values' => array_map(static fn($f) => (int)$f['total'], $hardware['sistemas'] ?? []),
];
$maxValorColegio = max(1, ...array_map(static fn($f) => (int)($f['valor_total'] ?? 0), $valorPorColegio ?: [['valor_total' => 1]]));

$cssExtraInventario = ['css/dashboard.css'];
$jsExtraInventario = ['js/dashboard.js'];
$idPagActual = '9';
require __DIR__ . '/componentes/layout_top.php';
?>
<div class="row mx-1 mx-md-3">
    <div class="col-12">
        <div class="card shadow mb-4 px-0 border-0 inv-panel">
            <div class="card-body p-4 p-lg-5">

                <div class="inv-hero mb-4<?= $_heroBranded ? ' inv-hero--branded' : '' ?>"<?= $_heroStyle ?>>
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="inv-kicker">Módulo Inventario</span>
                            <h1 class="inv-title mb-2">Dashboard de Inventario</h1>
                            <p class="inv-subtitle mb-0">Resumen general, distribución, valor y estado del equipamiento tecnológico.</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-1"></i>Volver al Inventario
                            </a>
                        </div>
                    </div>
                </div>

                <form method="get" class="inv-dashboard-filters mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-lg-4">
                            <label for="id_colegio" class="form-label">Colegio</label>
                            <select name="id_colegio" id="id_colegio" class="form-select">
                                <option value="0">Todos los colegios</option>
                                <?php foreach ($colegios as $colegio): ?>
                                    <option value="<?= (int)$colegio['id_colegio'] ?>" <?= (int)($filtrosDashboard['id_colegio'] ?? 0) === (int)$colegio['id_colegio'] ? 'selected' : '' ?>>
                                        <?= inventario_h($colegio['nom_colegio']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label for="fecha_desde" class="form-label">Desde</label>
                            <input type="date" name="fecha_desde" id="fecha_desde" class="form-control"
                                   value="<?= inventario_h($filtrosDashboard['fecha_desde'] ?? '') ?>"
                                   <?= $fechaRegistroDisponible ? '' : 'disabled' ?>>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label for="fecha_hasta" class="form-label">Hasta</label>
                            <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control"
                                   value="<?= inventario_h($filtrosDashboard['fecha_hasta'] ?? '') ?>"
                                   <?= $fechaRegistroDisponible ? '' : 'disabled' ?>>
                        </div>
                        <div class="col-12 col-lg-4 d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i>Aplicar filtros
                            </button>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i>Limpiar
                            </a>
                            <?php if (!$fechaRegistroDisponible): ?>
                                <span class="inv-dashboard-note">Filtro de fecha no disponible: equipos.fecha_registro no existe en la estructura actual.</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>

                <div class="inv-dashboard-grid inv-dashboard-grid--kpis mb-4">
                    <?php
                    $cardsKpi = [
                        ['TOTAL DE PCS', $kpis['total_pcs'] ?? 0, 'Equipos visibles con filtros'],
                        ['ACTIVOS', $kpis['activos'] ?? 0, 'Operativos actualmente'],
                        ['SIN UBICACIÓN', $kpis['sin_ubicacion'] ?? 0, 'Pendientes de corregir'],
                        ['SIN RESPONSABLE', $kpis['sin_responsable'] ?? 0, 'Sin usuario asignado'],
                        ['EN REPARACIÓN', $kpis['en_reparacion'] ?? 0, 'Equipos en revisión'],
                        ['DADOS DE BAJA', $kpis['dados_baja'] ?? 0, 'Fuera de operación'],
                        ['INGRESADOS ESTE MES', $kpis['ingresados_mes'] ?? 0, $fechaRegistroDisponible ? 'Según fecha_registro' : 'Sin fecha_registro'],
                    ];
                    foreach ($cardsKpi as $card): ?>
                        <div class="inv-kpi-card">
                            <span><?= inventario_h($card[0]) ?></span>
                            <strong><?= inv_dash_int($card[1]) ?></strong>
                            <small><?= inventario_h($card[2]) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="inv-dashboard-grid inv-dashboard-grid--value mb-4">
                    <div class="inv-kpi-card inv-kpi-card--money">
                        <span>VALOR TOTAL DEL INVENTARIO</span>
                        <strong><?= inv_dash_money($valor['valor_total'] ?? 0) ?></strong>
                        <small>Suma de equipos_compra.valor_equipo</small>
                    </div>
                    <div class="inv-kpi-card inv-kpi-card--money">
                        <span>VALOR PROMEDIO POR EQUIPO</span>
                        <strong><?= inv_dash_money($valor['valor_promedio'] ?? 0) ?></strong>
                        <small>Promedio sobre equipos visibles</small>
                    </div>
                    <div class="inv-kpi-card inv-kpi-card--money">
                        <span>COLEGIO CON MAYOR VALOR EN PCS</span>
                        <strong><?= inventario_h($colegioMayor['nom_colegio'] ?? 'Sin datos') ?></strong>
                        <small><?= inv_dash_money($colegioMayor['valor_total'] ?? 0) ?></small>
                    </div>
                    <div class="inv-kpi-card inv-kpi-card--money">
                        <span>EQUIPOS SIN VALOR REGISTRADO</span>
                        <strong><?= inv_dash_int($valor['sin_valor'] ?? 0) ?></strong>
                        <small>Sin compra o con valor cero</small>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-xl-7">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <h2>Valor del inventario PC por colegio</h2>
                            </div>
                            <div class="inv-value-bars">
                                <?php if (empty($valorPorColegio)): ?>
                                    <div class="inv-empty-state">No hay equipos para los filtros seleccionados.</div>
                                <?php endif; ?>
                                <?php foreach ($valorPorColegio as $fila): ?>
                                    <?php $porcentaje = ((int)$fila['valor_total'] / $maxValorColegio) * 100; ?>
                                    <div class="inv-value-bar">
                                        <div class="inv-value-bar__label">
                                            <span><?= inventario_h($fila['nom_colegio']) ?></span>
                                            <strong><?= inv_dash_money($fila['valor_total']) ?></strong>
                                        </div>
                                        <div class="inv-value-bar__track">
                                            <div class="inv-value-bar__fill" style="width: <?= max(3, (int)$porcentaje) ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                    <div class="col-12 col-xl-5">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <h2>Estado del inventario</h2>
                            </div>
                            <div class="inv-chart-box">
                                <canvas id="chartEstados"></canvas>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-xl-8">
                        <section class="inv-dashboard-section">
                            <div class="inv-dashboard-section__head">
                                <h2>Resumen por colegio</h2>
                            </div>
                            <div class="table-responsive">
                                <table class="table inv-dashboard-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Colegio</th>
                                            <th>Total equipos</th>
                                            <th>Activos</th>
                                            <th>Sin ubicación</th>
                                            <th>Sin responsable</th>
                                            <th>En reparación</th>
                                            <th>Dados de baja</th>
                                            <th>Valor total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resumenPorColegio as $fila): ?>
                                        <tr>
                                            <td><?= inventario_h($fila['nom_colegio']) ?></td>
                                            <td><?= inv_dash_int($fila['total_equipos']) ?></td>
                                            <td><?= inv_dash_int($fila['activos']) ?></td>
                                            <td><?= inv_dash_int($fila['sin_ubicacion']) ?></td>
                                            <td><?= inv_dash_int($fila['sin_responsable']) ?></td>
                                            <td><?= inv_dash_int($fila['en_reparacion']) ?></td>
                                            <td><?= inv_dash_int($fila['dados_baja']) ?></td>
                                            <td><?= inv_dash_money($fila['valor_total']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>TOTAL GENERAL</th>
                                            <th><?= inv_dash_int($totalGeneral['total_equipos']) ?></th>
                                            <th><?= inv_dash_int($totalGeneral['activos']) ?></th>
                                            <th><?= inv_dash_int($totalGeneral['sin_ubicacion']) ?></th>
                                            <th><?= inv_dash_int($totalGeneral['sin_responsable']) ?></th>
                                            <th><?= inv_dash_int($totalGeneral['en_reparacion']) ?></th>
                                            <th><?= inv_dash_int($totalGeneral['dados_baja']) ?></th>
                                            <th><?= inv_dash_money($totalGeneral['valor_total']) ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </section>
                    </div>
                    <div class="col-12 col-xl-4">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <h2>Distribución de equipos</h2>
                            </div>
                            <div class="inv-chart-box">
                                <canvas id="chartTipos"></canvas>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-xl-5">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <h2>Alertas del inventario</h2>
                            </div>
                            <div class="inv-alert-list">
                                <?php
                                $itemsAlerta = [
                                    ['bi-geo-alt', $alertas['sin_ubicacion'] ?? 0, 'equipos sin ubicación asignada'],
                                    ['bi-person-x', $alertas['sin_responsable'] ?? 0, 'equipos sin responsable'],
                                    ['bi-qr-code', $alertas['sin_qr'] ?? 0, 'equipos sin código QR'],
                                    ['bi-image', $alertas['sin_fotografia'] ?? 0, 'equipos sin fotografía'],
                                    ['bi-cash-coin', $alertas['sin_valor'] ?? 0, 'equipos sin valor de compra'],
                                    ['bi-upc-scan', $alertas['sin_serie'] ?? 0, 'equipos sin número de serie'],
                                ];
                                foreach ($itemsAlerta as $alerta): ?>
                                    <div class="inv-alert-item">
                                        <span class="inv-alert-item__icon"><i class="bi <?= inventario_h($alerta[0]) ?>"></i></span>
                                        <div>
                                            <strong><?= inv_dash_int($alerta[1]) ?></strong>
                                            <span><?= inventario_h($alerta[2]) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                    <div class="col-12 col-xl-7">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <h2>Últimos equipos ingresados</h2>
                            </div>
                            <div class="table-responsive">
                                <table class="table inv-dashboard-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Equipo</th>
                                            <th>Identificador técnico</th>
                                            <th>Tipo</th>
                                            <th>Colegio</th>
                                            <th>Fecha de registro</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ultimos as $equipo): ?>
                                        <?php $nombreVisible = trim((string)($equipo['nombre_personalizado'] ?? '')); ?>
                                        <tr>
                                            <td><?= inventario_h($nombreVisible !== '' ? $nombreVisible : ($equipo['nombre_equipo'] ?? 'Sin nombre')) ?></td>
                                            <td><?= inventario_h($equipo['nombre_equipo'] ?? '-') ?></td>
                                            <td><?= inventario_h($equipo['tipo_pc'] ?? '-') ?></td>
                                            <td><?= inventario_h($equipo['nom_colegio'] ?? '-') ?></td>
                                            <td><?= inventario_h($equipo['fecha_registro'] ?? 'No disponible') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-xl-4">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <h2>Distribución de RAM</h2>
                                <span><?= inv_dash_int($hardware['ram_baja'] ?? 0) ?> con menos de 8 GB</span>
                            </div>
                            <div class="inv-chart-box inv-chart-box--sm">
                                <canvas id="chartRam"></canvas>
                            </div>
                        </section>
                    </div>
                    <div class="col-12 col-xl-4">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <h2>Sistemas operativos</h2>
                            </div>
                            <div class="inv-chart-box inv-chart-box--sm">
                                <canvas id="chartSistemas"></canvas>
                            </div>
                        </section>
                    </div>
                    <div class="col-12 col-xl-4">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <h2>Detalle de valor</h2>
                            </div>
                            <div class="inv-detail-value">
                                <span><?= inventario_h($colegioSeleccionado) ?></span>
                                <strong><?= inv_dash_int($totalGeneral['total_equipos']) ?> PC</strong>
                                <strong><?= inv_dash_money($valor['valor_total'] ?? 0) ?></strong>
                                <small>Promedio: <?= inv_dash_money($valor['valor_promedio'] ?? 0) ?></small>
                                <small>Sin valor: <?= inv_dash_int($valor['sin_valor'] ?? 0) ?></small>
                            </div>
                        </section>
                    </div>
                </div>

                <section class="inv-dashboard-section">
                    <div class="inv-dashboard-section__head">
                        <h2>Equipos valorizados</h2>
                        <span><?= inventario_h($colegioSeleccionado) ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table inv-dashboard-table align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre equipo</th>
                                    <th>Identificador técnico</th>
                                    <th>Serie</th>
                                    <th>Ubicación</th>
                                    <th>Responsable</th>
                                    <th>Estado</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalleValor as $equipo): ?>
                                <?php $nombreVisible = trim((string)($equipo['nombre_personalizado'] ?? '')); ?>
                                <tr>
                                    <td><?= inventario_h($nombreVisible !== '' ? $nombreVisible : ($equipo['nombre_equipo'] ?? 'Sin nombre')) ?></td>
                                    <td><?= inventario_h($equipo['nombre_equipo'] ?? '-') ?></td>
                                    <td><?= inventario_h($equipo['numero_serie'] ?? '-') ?></td>
                                    <td><?= inventario_h(trim((string)($equipo['nombre_ubicacion'] ?? '')) !== '' ? $equipo['nombre_ubicacion'] : 'Pendiente') ?></td>
                                    <td><?= inventario_h(trim((string)($equipo['usuario_asignado'] ?? '')) !== '' ? $equipo['usuario_asignado'] : 'Sin asignar') ?></td>
                                    <td><?= inventario_h($equipo['nombre_estado'] ?? 'Sin estado') ?></td>
                                    <td><?= inv_dash_money($equipo['valor_equipo'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
window.inventarioDashboardCharts = {
    estados: <?= json_encode($chartEstados, JSON_UNESCAPED_UNICODE) ?>,
    tipos: <?= json_encode($chartTipos, JSON_UNESCAPED_UNICODE) ?>,
    ram: <?= json_encode($chartRam, JSON_UNESCAPED_UNICODE) ?>,
    sistemas: <?= json_encode($chartSistemas, JSON_UNESCAPED_UNICODE) ?>
};
</script>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
