<?php
$resumen = $resumen ?? [];
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
                <!-- <div class="inv-stat-icon">
                    <i class="bi bi-hdd-stack"></i>
                </div> -->
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
                <!-- <div class="inv-stat-icon">
                    <i class="bi bi-laptop"></i>
                </div> -->
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
                <!-- <div class="inv-stat-icon">
                    <i class="bi bi-pc-display"></i>
                </div> -->
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
                <!-- <div class="inv-stat-icon">
                    <i class="bi bi-person-dash"></i>
                </div> -->
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
                <!-- <div class="inv-stat-icon">
                    <i class="bi bi-check2-circle"></i>
                </div> -->
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
                <!-- <div class="inv-stat-icon">
                    <i class="bi bi-tools"></i>
                </div> -->  
            </div>
        </div>
    </div>
</div>
