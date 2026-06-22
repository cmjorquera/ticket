<?php
$detalleOffcanvas = !empty($detalleOffcanvas);
$qrFichaUrl = inventario_sistema_url_absoluta('codigosQR/inventario/equipoQRinformacion.php?id=' . (int)$equipo['id_equipo']);
$fotoPrincipal = null;
$fotosGaleria  = [];
foreach (($equipo['fotos'] ?? []) as $foto) {
    if (!empty($foto['principal']) && !$fotoPrincipal) {
        $fotoPrincipal = $foto;
    } else {
        $fotosGaleria[] = $foto;
    }
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="<?= $detalleOffcanvas ? 'h4' : 'h3' ?> mb-1"><?= inventario_h($equipo['nombre_equipo']) ?></h1>
        <p class="text-muted mb-0">
            <?= inventario_h($equipo['nom_colegio']) ?> | <?= inventario_h($equipo['tipo_pc']) ?> | Serie <?= inventario_h($equipo['numero_serie']) ?>
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="editar_equipo.php?id_equipo=<?= (int)$equipo['id_equipo'] ?>" class="btn btn-outline-primary">Editar</a>
        <?php if (!$detalleOffcanvas): ?>
            <a href="index.php" class="btn btn-light border">Volver</a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="<?= $detalleOffcanvas ? 'col-12' : 'col-lg-8' ?>">

        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><span class="text-muted d-block small">Colegio</span><strong><?= inventario_h($equipo['nom_colegio']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Fabricante</span><strong><?= inventario_h($equipo['fabricante'] ?: '-') ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Producto</span><strong><?= inventario_h($equipo['producto'] ?: '-') ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Estado</span><?= $inventario->renderBadgeEstado($equipo['id_estado'], $equipo['nombre_estado'], $equipo['color_badge']) ?></div>
                    <div class="col-md-4">
                        <span class="text-muted d-block small">Ubicacion actual</span>
                        <strong>
                            <?php
                            $nomUbic = $equipo['nombre_ubicacion'] ?? '';
                            $tipUbic = $equipo['tipo_ubicacion'] ?? '';
                            echo $nomUbic !== ''
                                ? inventario_h($tipUbic ? $tipUbic . ' - ' . $nomUbic : $nomUbic)
                                : '<span class="text-muted">Sin ubicacion</span>';
                            ?>
                        </strong>
                    </div>
                    <div class="col-md-4"><span class="text-muted d-block small">Usuario asignado</span><strong><?= inventario_h($equipo['usuario_asignado'] ?: 'Sin asignar') ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Registrado por</span><strong><?= inventario_h($equipo['nombre_usuario_registra'] ?: $equipo['usuario_registra'] ?: '-') ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">QR</span><strong><?= inventario_h($equipo['qr_code']) ?></strong></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <h5 class="mb-3">Ficha tecnica</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6>Compra</h6>
                        <ul class="list-unstyled inv-list">
                            <li><span>Proveedor</span><strong><?= inventario_h($equipo['compra']['proveedor'] ?? '-') ?></strong></li>
                            <li><span>Factura</span><strong><?= inventario_h($equipo['compra']['numero_factura'] ?? '-') ?></strong></li>
                            <li><span>Fecha compra</span><strong><?= inventario_h($equipo['compra']['fecha_compra'] ?? '-') ?></strong></li>
                            <li><span>Valor</span><strong>$<?= number_format((float)($equipo['compra']['valor_equipo'] ?? 0), 0, ',', '.') ?></strong></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Procesador y almacenamiento</h6>
                        <ul class="list-unstyled inv-list">
                            <li><span>CPU</span><strong><?= inventario_h(trim(($equipo['procesador']['equipo_fabricante'] ?? '') . ' ' . ($equipo['procesador']['equipo_modelo'] ?? '')) ?: '-') ?></strong></li>
                            <li><span>Velocidad</span><strong><?= inventario_h($equipo['procesador']['equipo_velocidad'] ?? '-') ?></strong></li>
                            <li><span>Disco</span><strong><?= inventario_h(trim(($equipo['almacenamiento']['equipo_modelo'] ?? '') . ' ' . ($equipo['almacenamiento']['equipo_capacidad'] ?? '')) ?: '-') ?></strong></li>
                            <li><span>Formato</span><strong><?= inventario_h($equipo['almacenamiento']['equipo_tamano'] ?? '-') ?></strong></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Software</h6>
                        <ul class="list-unstyled inv-list">
                            <li><span>Windows</span><strong><?= inventario_h($equipo['software']['windows'] ?? '-') ?></strong></li>
                            <li><span>Office</span><strong><?= inventario_h($equipo['software']['office'] ?? '-') ?></strong></li>
                            <li><span>Antivirus</span><strong><?= inventario_h($equipo['software']['antivirus'] ?? '-') ?></strong></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Observacion compra</h6>
                        <p class="mb-0 text-muted"><?= nl2br(inventario_h($equipo['compra']['observacion'] ?? 'Sin observaciones.')) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <h5 class="mb-3">Memorias RAM</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Slot</th><th>Formato</th><th>Tipo</th><th>Tamano</th><th>Frecuencia</th><th>Marca</th></tr></thead>
                        <tbody>
                        <?php if (!empty($equipo['memorias'])): foreach ($equipo['memorias'] as $memoria): ?>
                            <tr>
                                <td><?= inventario_h($memoria['designacion_memoria']) ?></td>
                                <td><?= inventario_h($memoria['formato_memoria']) ?></td>
                                <td><?= inventario_h($memoria['tipo_memoria']) ?></td>
                                <td><?= inventario_h($memoria['tamano_memoria']) ?></td>
                                <td><?= inventario_h($memoria['frecuencia_memoria']) ?></td>
                                <td><?= inventario_h($memoria['marca_memoria']) ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center text-muted">Sin memorias registradas.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <h5 class="mb-3 mt-4">Monitores</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Modelo</th><th>Codigo</th><th>Serie</th><th>Tamano</th><th>Resolucion</th></tr></thead>
                        <tbody>
                        <?php if (!empty($equipo['monitores'])): foreach ($equipo['monitores'] as $monitor): ?>
                            <tr>
                                <td><?= inventario_h($monitor['modelo_monitor']) ?></td>
                                <td><?= inventario_h($monitor['codigo_monitor']) ?></td>
                                <td><?= inventario_h($monitor['serie_monitor']) ?></td>
                                <td><?= inventario_h($monitor['tamano_monitor']) ?></td>
                                <td><?= inventario_h($monitor['resolucion_monitor']) ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center text-muted">Sin monitores asociados.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if (!empty($equipo['movimientos'])): ?>
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <h5 class="mb-3">Historial de ubicaciones</h5>
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
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
                        <?php foreach ($equipo['movimientos'] as $mov): ?>
                            <tr>
                                <td class="text-nowrap small"><?= inventario_h(date('d/m/Y H:i', strtotime($mov['fecha_movimiento']))) ?></td>
                                <td><?= inventario_h($mov['ubicacion_origen'] ?: '-') ?></td>
                                <td class="text-muted px-1">&rarr;</td>
                                <td><strong><?= inventario_h($mov['ubicacion_destino'] ?: '-') ?></strong></td>
                                <td><?= inventario_h($mov['motivo']) ?><?= $mov['observacion'] ? ' <span class="text-muted small">- ' . inventario_h($mov['observacion']) . '</span>' : '' ?></td>
                                <td class="small text-muted"><?= inventario_h($mov['usuario_movimiento']) ?></td>
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
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <?php if ($fotoPrincipal): ?>
                    <div class="inv-ver-foto-principal-wrap">
                        <a href="<?= inventario_h(inventario_url(ltrim($fotoPrincipal['ruta_foto'], '/'))) ?>" target="_blank">
                            <img src="<?= inventario_h(inventario_url(ltrim($fotoPrincipal['ruta_foto'], '/'))) ?>"
                                 alt="Foto principal" class="inv-ver-foto-principal">
                        </a>
                        <div class="inv-badge-principal-overlay">&#9733; Principal</div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3 text-muted">
                        <i class="bi bi-image" style="font-size:2.5rem;opacity:.3"></i>
                        <p class="small mt-2 mb-0">Sin foto principal</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($fotosGaleria)): ?>
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <h6 class="mb-3">Galeria</h6>
                <div class="inv-photo-grid">
                    <?php foreach ($fotosGaleria as $foto): ?>
                        <a href="<?= inventario_h(inventario_url(ltrim($foto['ruta_foto'], '/'))) ?>" target="_blank" class="inv-photo-item d-block">
                            <img src="<?= inventario_h(inventario_url(ltrim($foto['ruta_foto'], '/'))) ?>"
                                 alt="Foto equipo" class="inv-photo-thumb">
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="card shadow-sm border-0 inv-panel">
            <div class="card-body">
                <h5 class="mb-3">QR del equipo</h5>
                <div<?= $detalleOffcanvas ? '' : ' id="qrEquipo"' ?> class="d-flex justify-content-center js-qr-equipo" data-qr-text="<?= inventario_h($qrFichaUrl) ?>"></div>
                <p class="small text-muted text-center mt-2 mb-0"><?= inventario_h($qrFichaUrl) ?></p>
            </div>
        </div>
    </div>
</div>
