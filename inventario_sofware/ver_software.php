<?php
require_once __DIR__ . '/componentes/boot.php';
$idSoftware = (int)($_GET['id_software'] ?? 0);
$software = $inventario->obtenerSoftwareCompleto($idSoftware);
if (!$software) { header('Location: index.php'); exit; }
$tituloPagina = 'Ficha software';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= inventario_h($software['nombre_software']) ?></h1>
        <p class="text-muted mb-0"><?= inventario_h($software['nom_colegio']) ?> | Version <?= inventario_h($software['version_software'] ?: '-') ?> | <?= inventario_h($software['tipo_licenciamiento']) ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="descargar_pdf.php?tipo=software_ficha&id_software=<?= (int)$software['id_software'] ?>" class="btn inv-top-btn inv-top-btn-danger" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
        <a href="ficha_qr.php?tipo=software&id=<?= (int)$software['id_software'] ?>" class="btn inv-top-btn inv-top-btn-dark" target="_blank"><i class="bi bi-box-arrow-up-right me-1"></i>Ficha QR</a>
        <a href="editar_software.php?id_software=<?= (int)$software['id_software'] ?>" class="btn inv-top-btn inv-top-btn-primary">Editar</a>
        <a href="index.php" class="btn inv-top-btn inv-top-btn-light">Volver</a>
    </div>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 inv-panel mb-4"><div class="card-body"><div class="row g-3">
            <div class="col-md-4"><span class="text-muted d-block small">Colegio</span><strong><?= inventario_h($software['nom_colegio']) ?></strong></div>
            <div class="col-md-4"><span class="text-muted d-block small">Responsable</span><strong><?= inventario_h($software['responsable'] ?: 'Sin asignar') ?></strong></div>
            <div class="col-md-4"><span class="text-muted d-block small">Licenciamiento</span><strong><?= inventario_h($software['tipo_licenciamiento']) ?></strong></div>
            <div class="col-md-3"><span class="text-muted d-block small">Version</span><strong><?= inventario_h($software['version_software'] ?: '-') ?></strong></div>
            <div class="col-md-3"><span class="text-muted d-block small">Cantidad</span><strong><?= (int)$software['cantidad_licencias'] ?></strong></div>
            <div class="col-md-3"><span class="text-muted d-block small">Pagado por</span><strong><?= inventario_h($software['pagado_por']) ?></strong></div>
            <div class="col-md-3"><span class="text-muted d-block small">Costo</span><strong><?= inventario_h($software['moneda']) ?> <?= number_format((float)$software['costo'], 2, ',', '.') ?></strong></div>
            <div class="col-md-6"><span class="text-muted d-block small">Proveedor</span><strong><?= inventario_h($software['proveedor'] ?: '-') ?></strong></div>
            <div class="col-md-6"><span class="text-muted d-block small">URL o referencia</span><strong class="text-break"><?= inventario_h($software['url_referencia'] ?: '-') ?></strong></div>
            <div class="col-12"><span class="text-muted d-block small">Observaciones</span><p class="mb-0"><?= nl2br(inventario_h($software['observaciones'] ?: 'Sin observaciones.')) ?></p></div>
        </div></div></div>
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <h5 class="mb-3">Datos sensibles</h5>
                <?php if (!empty($software['datos_sensibles'])): ?>
                    <div class="row g-3">
                        <?php foreach ($software['datos_sensibles'] as $fila): ?>
                            <div class="col-md-6">
                                <div class="inv-repeat-card py-3 h-100">
                                    <strong class="d-block"><?= inventario_h($fila['nombre']) ?></strong>
                                    <?php if (!empty($fila['descripcion'])): ?>
                                        <small class="text-muted d-block mt-2"><?= inventario_h($fila['descripcion']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-light border mb-0">No hay datos sensibles asociados.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body text-center">
                <h5 class="mb-3">Codigo QR</h5>
                <img src="qr_codigo.php?tipo=software&id_software=<?= (int)$software['id_software'] ?>" alt="QR <?= inventario_h($software['nombre_software']) ?>" class="img-fluid inv-qr-image mb-3">
                <p class="text-muted small mb-3">Al escanearlo se abre una ficha rapida con las caracteristicas principales.</p>
                <div class="d-grid gap-2">
                    <a href="qr_codigo.php?tipo=software&id_software=<?= (int)$software['id_software'] ?>" class="btn inv-top-btn inv-top-btn-light" target="_blank">Ver QR</a>
                    <a href="ficha_qr.php?tipo=software&id=<?= (int)$software['id_software'] ?>" class="btn inv-top-btn inv-top-btn-dark" target="_blank">Abrir ficha QR</a>
                </div>
            </div>
        </div>
        <div class="card shadow-sm border-0 inv-panel"><div class="card-body"><h5 class="mb-3">Datos de almacenamiento</h5>
            <?php if (!empty($software['almacenamiento'])): ?>
                <div class="d-grid gap-3">
                    <?php foreach ($software['almacenamiento'] as $fila): ?>
                        <div class="inv-repeat-card py-3">
                            <div><span class="text-muted d-block small">Nombre</span><strong><?= inventario_h($fila['nombre_contacto'] ?: '-') ?></strong></div>
                            <div class="mt-2"><span class="text-muted d-block small">RUT</span><strong><?= inventario_h($fila['rut_contacto'] ?: '-') ?></strong></div>
                            <div class="mt-2"><span class="text-muted d-block small">Email</span><strong><?= inventario_h($fila['email_contacto'] ?: '-') ?></strong></div>
                            <div class="mt-2"><span class="text-muted d-block small">Otros</span><strong><?= inventario_h($fila['otros_datos'] ?: '-') ?></strong></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-light border mb-0">No hay datos de almacenamiento asociados.</div>
            <?php endif; ?>
        </div></div>
    </div>
</div>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
