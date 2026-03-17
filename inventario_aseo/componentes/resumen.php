<?php
$resumen = $resumen ?? [];
$porColegio = $resumen['por_colegio'] ?? [];
?>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Productos</span>
                    <strong><?= (int) ($resumen['total_productos'] ?? 0) ?></strong>
                    <small>Catalogo visible</small>
                </div>
                <div class="inv-stat-icon"><i class="bi bi-droplet-half"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Stock total</span>
                    <strong><?= (int) ($resumen['stock_total'] ?? 0) ?></strong>
                    <small>Unidades acumuladas</small>
                </div>
                <div class="inv-stat-icon"><i class="bi bi-box-seam"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Ingresos mes</span>
                    <strong><?= (int) ($resumen['ingresos_mes'] ?? 0) ?></strong>
                    <small>Reposiciones registradas</small>
                </div>
                <div class="inv-stat-icon"><i class="bi bi-arrow-down-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Salidas mes</span>
                    <strong><?= (int) ($resumen['salidas_mes'] ?? 0) ?></strong>
                    <small>Consumo registrado</small>
                </div>
                <div class="inv-stat-icon"><i class="bi bi-arrow-up-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-danger">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Bajo minimo</span>
                    <strong><?= (int) ($resumen['productos_bajo_minimo'] ?? 0) ?></strong>
                    <small>Productos por reponer</small>
                </div>
                <div class="inv-stat-icon"><i class="bi bi-exclamation-triangle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-teal">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Agotados / sin mov.</span>
                    <strong><?= (int) ($resumen['productos_agotados'] ?? 0) ?> / <?= (int) ($resumen['sin_movimientos'] ?? 0) ?></strong>
                    <small>Control operativo</small>
                </div>
                <div class="inv-stat-icon"><i class="bi bi-clipboard2-pulse"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 inv-panel mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="mb-1">Stock por colegio</h6>
                <small class="text-muted">Resumen para planificar reposiciones por sede</small>
            </div>
        </div>
        <div class="row g-3">
            <?php if (!empty($porColegio)): ?>
                <?php foreach ($porColegio as $item): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="inv-colegio-pill">
                            <span><?= inventario_h($item['nom_colegio']) ?></span>
                            <strong><?= (int) $item['stock'] ?></strong>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-light border mb-0">No hay datos para los filtros seleccionados.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
