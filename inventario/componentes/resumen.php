<?php
$resumen = $resumen ?? [];
$porColegio = $resumen['por_colegio'] ?? [];
?>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Total equipos</span>
                    <strong><?= (int)($resumen['total_equipos'] ?? 0) ?></strong>
                    <small>Inventario visible con filtros</small>
                </div>
                <div class="inv-stat-icon">
                    <i class="bi bi-hdd-stack"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Notebooks</span>
                    <strong><?= (int)($resumen['total_notebooks'] ?? 0) ?></strong>
                    <small>Equipos portables</small>
                </div>
                <div class="inv-stat-icon">
                    <i class="bi bi-laptop"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Desktop</span>
                    <strong><?= (int)($resumen['total_desktop'] ?? 0) ?></strong>
                    <small>Puestos fijos</small>
                </div>
                <div class="inv-stat-icon">
                    <i class="bi bi-pc-display"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Sin asignar</span>
                    <strong><?= (int)($resumen['total_sin_asignar'] ?? 0) ?></strong>
                    <small>Sin responsable directo</small>
                </div>
                <div class="inv-stat-icon">
                    <i class="bi bi-person-dash"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-teal">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Activos</span>
                    <strong><?= (int)($resumen['total_activos'] ?? 0) ?></strong>
                    <small>Operativos hoy</small>
                </div>
                <div class="inv-stat-icon">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-danger">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Reparacion / baja</span>
                    <strong><?= (int)($resumen['total_reparacion'] ?? 0) ?> / <?= (int)($resumen['total_baja'] ?? 0) ?></strong>
                    <small>Seguimiento de estado</small>
                </div>
                <div class="inv-stat-icon">
                    <i class="bi bi-tools"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 inv-panel mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h6 class="mb-1">Total por colegio</h6>
                <small class="text-muted">Distribucion del inventario en la red de colegios</small>
            </div>
        </div>
        <div class="row g-3">
            <?php if (!empty($porColegio)): ?>
                <?php foreach ($porColegio as $item): ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="inv-colegio-pill">
                            <span><?= inventario_h($item['nom_colegio']) ?></span>
                            <strong><?= (int)$item['total'] ?></strong>
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
