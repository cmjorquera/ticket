<?php
$detalleOffcanvas = !empty($detalleOffcanvas);
$fotoPrincipal = null;
$fotosGaleria  = [];
foreach (($monitor['fotos'] ?? []) as $foto) {
    if (!empty($foto['principal']) && !$fotoPrincipal) {
        $fotoPrincipal = $foto;
    } else {
        $fotosGaleria[] = $foto;
    }
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="<?= $detalleOffcanvas ? 'h4' : 'h3' ?> mb-1"><?= inventario_h($monitor['nombre_monitor']) ?></h1>
        <p class="text-muted mb-0">
            <?= inventario_h($monitor['nom_colegio']) ?>
            <?php if ($monitor['marca'] || $monitor['modelo']): ?>
                | <?= inventario_h(trim(($monitor['marca'] ?? '') . ' ' . ($monitor['modelo'] ?? ''))) ?>
            <?php endif; ?>
            | Serie <?= inventario_h($monitor['numero_serie']) ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="editar_monitor.php?id_monitor=<?= (int)$monitor['id_monitor'] ?>" class="btn btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Editar
        </a>
        <?php if (!$detalleOffcanvas): ?>
            <a href="index.php?tab=monitores" class="btn btn-light border">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="<?= $detalleOffcanvas ? 'col-12' : 'col-lg-8' ?>">
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><span class="text-muted d-block small">Colegio</span><strong><?= inventario_h($monitor['nom_colegio']) ?></strong></div>
                    <div class="col-md-4">
                        <span class="text-muted d-block small">Estado</span>
                        <?= $inventario->renderBadgeEstado($monitor['id_estado'], $monitor['nombre_estado'] ?? '', $monitor['color_badge'] ?? 'dark') ?>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted d-block small">Ubicacion actual</span>
                        <strong>
                            <?php
                            $nomUbic = $monitor['nombre_ubicacion'] ?? '';
                            $tipUbic = $monitor['tipo_ubicacion'] ?? '';
                            echo $nomUbic !== ''
                                ? inventario_h($tipUbic ? $tipUbic . ' - ' . $nomUbic : $nomUbic)
                                : '<span class="text-muted">Sin ubicacion</span>';
                            ?>
                        </strong>
                    </div>
                    <div class="col-md-4"><span class="text-muted d-block small">Usuario asignado</span><strong><?= inventario_h($monitor['usuario_asignado'] ?: 'Sin asignar') ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Registrado por</span><strong><?= inventario_h($monitor['nombre_usuario_registra'] ?: '-') ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Codigo interno</span><strong><?= inventario_h($monitor['codigo_interno'] ?: '-') ?></strong></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <h5 class="mb-3">Especificaciones tecnicas</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <ul class="list-unstyled inv-list">
                            <li><span>Marca</span><strong><?= inventario_h($monitor['marca'] ?: '-') ?></strong></li>
                            <li><span>Modelo</span><strong><?= inventario_h($monitor['modelo'] ?: '-') ?></strong></li>
                            <li><span>Tamano</span><strong><?= inventario_h($monitor['tamano_monitor'] ?: '-') ?></strong></li>
                            <li><span>Resolucion</span><strong><?= inventario_h($monitor['resolucion_monitor'] ?: '-') ?></strong></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled inv-list">
                            <li><span>Tipo de panel</span><strong><?= inventario_h($monitor['tipo_panel'] ?: '-') ?></strong></li>
                            <li><span>Conexiones</span><strong><?= inventario_h($monitor['tipo_conexion'] ?: '-') ?></strong></li>
                            <li><span>N de serie</span><strong><?= inventario_h($monitor['numero_serie']) ?></strong></li>
                            <li><span>Fecha registro</span><strong><?= inventario_h($monitor['fecha_registro'] ?? '-') ?></strong></li>
                        </ul>
                    </div>
                    <?php if (!empty($monitor['observacion'])): ?>
                    <div class="col-12">
                        <span class="text-muted d-block small">Observacion</span>
                        <p class="mb-0"><?= nl2br(inventario_h($monitor['observacion'])) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php $compra = $monitor['compra'] ?? []; if (!empty($compra)): ?>
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <h5 class="mb-3">Datos de compra</h5>
                <ul class="list-unstyled inv-list">
                    <li><span>Proveedor</span><strong><?= inventario_h($compra['proveedor'] ?? '-') ?></strong></li>
                    <li><span>N factura</span><strong><?= inventario_h($compra['numero_factura'] ?? '-') ?></strong></li>
                    <li><span>Fecha compra</span><strong><?= inventario_h($compra['fecha_compra'] ?? '-') ?></strong></li>
                    <li><span>Valor</span><strong>$<?= number_format((int)($compra['valor_monitor'] ?? 0), 0, ',', '.') ?></strong></li>
                    <?php if (!empty($compra['observacion'])): ?>
                    <li><span>Observacion</span><strong><?= inventario_h($compra['observacion']) ?></strong></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($monitor['movimientos'])): ?>
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <h5 class="mb-3">Historial de movimientos</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Origen</th>
                                <th></th>
                                <th>Destino</th>
                                <th>Motivo</th>
                                <th>Usuario</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monitor['movimientos'] as $mov): ?>
                            <tr>
                                <td class="text-nowrap"><?= inventario_h($mov['fecha_movimiento']) ?></td>
                                <td><?= inventario_h($mov['ubicacion_origen'] ?? 'Alta inicial') ?></td>
                                <td><i class="bi bi-arrow-right text-muted"></i></td>
                                <td><?= inventario_h($mov['ubicacion_destino'] ?? '-') ?></td>
                                <td>
                                    <?= inventario_h($mov['motivo']) ?>
                                    <?php if (!empty($mov['observacion'])): ?>
                                        <div class="text-muted small"><?= inventario_h($mov['observacion']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= inventario_h($mov['usuario_movimiento']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="<?= $detalleOffcanvas ? 'col-12' : 'col-lg-4' ?>">
        <?php if ($fotoPrincipal): ?>
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body p-2">
                <div class="inv-ver-foto-principal-wrap">
                    <img src="<?= inventario_h(inventario_url(ltrim($fotoPrincipal['ruta_foto'], '/'))) ?>"
                         alt="Foto principal" class="inv-ver-foto-principal">
                    <span class="inv-badge-principal-overlay">&#9733; Principal</span>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($fotosGaleria)): ?>
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <h6 class="mb-3">Galeria</h6>
                <div class="inv-photo-grid">
                    <?php foreach ($fotosGaleria as $foto): ?>
                        <div class="inv-photo-item">
                            <img src="<?= inventario_h(inventario_url(ltrim($foto['ruta_foto'], '/'))) ?>"
                                 alt="Foto monitor" class="inv-photo-thumb">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php elseif (!$fotoPrincipal): ?>
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body text-center text-muted py-4">
                <i class="bi bi-image fs-2 d-block mb-2"></i>
                Sin fotografias registradas
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
