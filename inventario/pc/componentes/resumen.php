<?php
/**
 * Indicadores del listado de PC. Cada uno es un botón: al hacer clic filtra
 * la tabla (js/inventario.js → parámetro "kpi"); otro clic, o "Total equipos",
 * quita el filtro. $kpiActivo marca el indicador seleccionado.
 */
$resumen = $resumen ?? [];
$kpiActivo = (string)($kpiActivo ?? '');
$indicadores = [
    ['kpi' => '',                'clase' => 'inv-stat-primary', 'icono' => 'bi-hdd-stack',     'label' => 'Total equipos',     'valor' => (int)($resumen['total_equipos'] ?? 0),     'nota' => 'Inventario visible con filtros'],
    ['kpi' => 'notebooks',       'clase' => 'inv-stat-success', 'icono' => 'bi-laptop',        'label' => 'Notebooks',         'valor' => (int)($resumen['total_notebooks'] ?? 0),   'nota' => 'Equipos portables'],
    ['kpi' => 'desktop',         'clase' => 'inv-stat-info',    'icono' => 'bi-pc-display',    'label' => 'Desktop',           'valor' => (int)($resumen['total_desktop'] ?? 0),     'nota' => 'Puestos fijos'],
    ['kpi' => 'sin_asignar',     'clase' => 'inv-stat-warning', 'icono' => 'bi-person-dash',   'label' => 'Sin asignar',       'valor' => (int)($resumen['total_sin_asignar'] ?? 0), 'nota' => 'Sin responsable directo'],
    ['kpi' => 'activos',         'clase' => 'inv-stat-teal',    'icono' => 'bi-check2-circle', 'label' => 'Activos',           'valor' => (int)($resumen['total_activos'] ?? 0),     'nota' => 'Operativos hoy'],
    ['kpi' => 'reparacion_baja', 'clase' => 'inv-stat-danger',  'icono' => 'bi-tools',         'label' => 'Reparación / baja', 'valor' => (int)($resumen['total_reparacion'] ?? 0) . ' / ' . (int)($resumen['total_baja'] ?? 0), 'nota' => 'Seguimiento de estado'],
];
?>
<div class="row g-3 mb-3 inv-kpis">
    <?php foreach ($indicadores as $ind):
        $activo = $ind['kpi'] !== '' && $ind['kpi'] === $kpiActivo;
        $titulo = $ind['kpi'] === '' ? 'Ver todos los equipos' : ($activo ? 'Quitar filtro' : 'Ver solo: ' . $ind['label']);
    ?>
    <div class="col-6 col-md-4 col-xl-2">
        <button type="button"
                class="inv-stat-card inv-stat-btn <?= $ind['clase'] ?><?= $activo ? ' is-active' : '' ?>"
                data-kpi="<?= inventario_h($ind['kpi']) ?>"
                aria-pressed="<?= $activo ? 'true' : 'false' ?>"
                title="<?= inventario_h($titulo) ?>">
            <span class="d-flex justify-content-between align-items-start">
                <span>
                    <span class="inv-stat-label"><?= inventario_h($ind['label']) ?></span>
                    <strong><?= inventario_h($ind['valor']) ?></strong>
                    <small><?= $activo ? '<i class="bi bi-funnel-fill"></i> Filtrando · clic para quitar' : inventario_h($ind['nota']) ?></small>
                </span>
                <span class="inv-stat-icon"><i class="bi <?= $ind['icono'] ?>"></i></span>
            </span>
        </button>
    </div>
    <?php endforeach; ?>
</div>
