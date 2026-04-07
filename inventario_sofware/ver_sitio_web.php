<?php
require_once __DIR__ . '/componentes/boot.php';
$idSitio = (int)($_GET['id_sitio'] ?? 0);
$sitio = $inventario->obtenerSitioWeb($idSitio);
if (!$sitio) { header('Location: index.php'); exit; }
$tituloPagina = 'Ficha sitio web';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= inventario_h($sitio['nombre_sitio']) ?></h1>
        <p class="text-muted mb-0"><?= inventario_h($sitio['nom_colegio']) ?> | <?= inventario_h($sitio['tipo_sitio']) ?> | <?= inventario_h($sitio['estado_sitio']) ?></p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="descargar_pdf.php?tipo=sitio_ficha&id_sitio=<?= (int)$sitio['id_sitio'] ?>" class="btn inv-top-btn inv-top-btn-danger" target="_blank"><i class="bi bi-file-earmark-pdf me-1"></i>PDF</a>
        <a href="ficha_qr.php?tipo=sitio&id=<?= (int)$sitio['id_sitio'] ?>" class="btn inv-top-btn inv-top-btn-dark" target="_blank"><i class="bi bi-box-arrow-up-right me-1"></i>Ficha QR</a>
        <a href="editar_sitio_web.php?id_sitio=<?= (int)$sitio['id_sitio'] ?>" class="btn inv-top-btn inv-top-btn-primary">Editar</a>
        <a href="index.php" class="btn inv-top-btn inv-top-btn-light">Volver</a>
    </div>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 inv-panel">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><span class="text-muted d-block small">Colegio</span><strong><?= inventario_h($sitio['nom_colegio']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Responsable</span><strong><?= inventario_h($sitio['responsable'] ?: 'Sin asignar') ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Tipo</span><strong><?= inventario_h($sitio['tipo_sitio']) ?></strong></div>
                    <div class="col-md-6"><span class="text-muted d-block small">URL</span><strong class="text-break"><?= inventario_h($sitio['url_sitio'] ?: '-') ?></strong></div>
                    <div class="col-md-6"><span class="text-muted d-block small">Hosting / proveedor</span><strong><?= inventario_h($sitio['proveedor_hosting'] ?: '-') ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Estado</span><strong><?= inventario_h($sitio['estado_sitio']) ?></strong></div>
                    <div class="col-md-8"><span class="text-muted d-block small">Observaciones</span><p class="mb-0"><?= nl2br(inventario_h($sitio['observaciones'] ?: 'Sin observaciones.')) ?></p></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 inv-panel">
            <div class="card-body text-center">
                <h5 class="mb-3">Codigo QR</h5>
                <img src="qr_codigo.php?tipo=sitio&id_sitio=<?= (int)$sitio['id_sitio'] ?>" alt="QR <?= inventario_h($sitio['nombre_sitio']) ?>" class="img-fluid inv-qr-image mb-3">
                <p class="text-muted small mb-3">Al escanearlo se abre una ficha rapida con los datos principales.</p>
                <div class="d-grid gap-2">
                    <a href="qr_codigo.php?tipo=sitio&id_sitio=<?= (int)$sitio['id_sitio'] ?>" class="btn inv-top-btn inv-top-btn-light" target="_blank">Ver QR</a>
                    <a href="ficha_qr.php?tipo=sitio&id=<?= (int)$sitio['id_sitio'] ?>" class="btn inv-top-btn inv-top-btn-dark" target="_blank">Abrir ficha QR</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
