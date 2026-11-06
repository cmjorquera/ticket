<?php $porColegio = $resumen['por_colegio'] ?? []; ?>
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-primary"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Softwares</span><strong><?= (int)($resumen['total_softwares'] ?? 0) ?></strong><small>Programas registrados</small></div><div class="inv-stat-icon"><i class="bi bi-window-stack"></i></div></div></div></div>
    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-info"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Licencias</span><strong><?= (int)($resumen['total_licencias'] ?? 0) ?></strong><small>Cantidad total declarada</small></div><div class="inv-stat-icon"><i class="bi bi-key"></i></div></div></div></div>
    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-warning"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Suscripción / perpetua</span><strong><?= (int)($resumen['total_suscripciones'] ?? 0) ?> / <?= (int)($resumen['total_perpetuas'] ?? 0) ?></strong><small>Gratuitas: <?= (int)($resumen['total_gratuitas'] ?? 0) ?></small></div><div class="inv-stat-icon"><i class="bi bi-credit-card"></i></div></div></div></div>
    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-success"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Web / app / cliente</span><strong><?= (int)($resumen['total_webs'] ?? 0) ?> / <?= (int)($resumen['total_apps'] ?? 0) ?> / <?= (int)($resumen['total_clientes'] ?? 0) ?></strong><small>Total sitios: <?= (int)($resumen['total_sitios'] ?? 0) ?></small></div><div class="inv-stat-icon"><i class="bi bi-globe2"></i></div></div></div></div>
</div>
<div class="card shadow-sm border-0 inv-panel inv-list-card mb-4">
    <div class="card-body">
        <div class="inv-card-head">
            <div>
                <h5 class="mb-1">Software por colegio</h5>
                <p class="text-muted mb-0 small">Cantidad de programas registrados en cada sede.</p>
            </div>
        </div>
        <div class="inv-colegio-grid">
            <?php if (!empty($porColegio)): ?>
                <?php foreach ($porColegio as $item): ?>
                    <div class="inv-colegio-pill"><span><?= inventario_h($item['nom_colegio']) ?></span><strong><?= (int)$item['total'] ?></strong></div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="inv-empty-text">Aún no hay registros para mostrar.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
