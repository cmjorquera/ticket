<?php
$resumenMon = $resumen ?? [];
?>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-primary">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Total monitores</span>
                    <strong><?= (int)($resumenMon['total_monitores'] ?? 0) ?></strong>
                    <small>Inventario visible</small>
                </div>
                <div class="inv-stat-icon"><i class="bi bi-display"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-success">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Activos</span>
                    <strong><?= (int)($resumenMon['total_activos'] ?? 0) ?></strong>
                    <small>Operativos hoy</small>
                </div>
                <div class="inv-stat-icon"><i class="bi bi-check2-circle"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Bodega</span>
                    <strong><?= (int)($resumenMon['total_bodega'] ?? 0) ?></strong>
                    <small>En almacenamiento</small>
                </div>
                <div class="inv-stat-icon"><i class="bi bi-archive"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Sin asignar</span>
                    <strong><?= (int)($resumenMon['total_sin_asignar'] ?? 0) ?></strong>
                    <small>Sin responsable</small>
                </div>
                <div class="inv-stat-icon"><i class="bi bi-person-dash"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-teal">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Reparación</span>
                    <strong><?= (int)($resumenMon['total_reparacion'] ?? 0) ?></strong>
                    <small>En mantención</small>
                </div>
                <div class="inv-stat-icon"><i class="bi bi-tools"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-2">
        <div class="inv-stat-card inv-stat-danger">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="inv-stat-label">Baja</span>
                    <strong><?= (int)($resumenMon['total_baja'] ?? 0) ?></strong>
                    <small>Dados de baja</small>
                </div>
                <div class="inv-stat-icon"><i class="bi bi-x-circle"></i></div>
            </div>
        </div>
    </div>
</div>
