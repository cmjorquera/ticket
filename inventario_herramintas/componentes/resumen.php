<?php
$resumen = $resumen ?? [];
$porColegio = $resumen['por_colegio'] ?? [];
?>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-primary"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Herramientas</span><strong><?= (int)($resumen['total_herramientas'] ?? 0) ?></strong><small>Registros activos en vista</small></div><div class="inv-stat-icon"><i class="bi bi-tools"></i></div></div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-info"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Unidades</span><strong><?= (int)($resumen['total_cantidad'] ?? 0) ?></strong><small>Cantidad total disponible</small></div><div class="inv-stat-icon"><i class="bi bi-box-seam"></i></div></div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-teal"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Disponibles</span><strong><?= (int)($resumen['total_disponibles'] ?? 0) ?></strong><small>Operativas hoy</small></div><div class="inv-stat-icon"><i class="bi bi-check2-circle"></i></div></div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-warning"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Prestadas</span><strong><?= (int)($resumen['total_prestadas'] ?? 0) ?></strong><small>Fuera de bodega</small></div><div class="inv-stat-icon"><i class="bi bi-arrow-left-right"></i></div></div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-danger"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Reparacion / baja</span><strong><?= (int)($resumen['total_reparacion'] ?? 0) ?> / <?= (int)($resumen['total_baja'] ?? 0) ?></strong><small>Seguimiento operativo</small></div><div class="inv-stat-icon"><i class="bi bi-wrench-adjustable-circle"></i></div></div></div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-success"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Alertas</span><strong><?= (int)($resumen['total_bajo_stock'] ?? 0) ?> / <?= (int)($resumen['total_mantencion_vencida'] ?? 0) ?></strong><small>Stock / mantencion</small></div><div class="inv-stat-icon"><i class="bi bi-bell"></i></div></div></div>
    </div>
</div>

<div class="card shadow-sm border-0 inv-panel mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="mb-1">Distribucion por colegio</h6>
                <small class="text-muted">Cantidad total de herramientas por sede o colegio</small>
            </div>
        </div>
        <div class="row g-3">
            <?php if (!empty($porColegio)): ?>
                <?php foreach ($porColegio as $item): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="inv-colegio-pill"><span><?= inventario_h($item['nom_colegio']) ?></span><strong><?= (int)$item['cantidad'] ?></strong></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><div class="alert alert-light border mb-0">No hay datos para los filtros seleccionados.</div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
