<?php
require_once __DIR__ . '/componentes/boot.php';
$idSitio = (int)($_GET['id_sitio'] ?? 0);
$sitio = $inventario->obtenerSitioWeb($idSitio);
if (!$sitio) { header('Location: sitios_web_apps.php'); exit; }
$tituloPagina = 'Ficha sitio web';
$idReg = (int)$sitio['id_sitio'];
$urlFichaQr = inventario_url('ficha_qr.php?tipo=sitio&id=' . $idReg);
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="inv-kicker">Ficha de sitio web / app</span>
            <h1 class="inv-title mb-2"><?= inventario_h($sitio['nombre_sitio']) ?></h1>
            <p class="inv-subtitle mb-0"><?= inventario_h($sitio['nom_colegio']) ?> · <?= inventario_h($sitio['tipo_sitio']) ?> · <?= inventario_h($sitio['estado_sitio']) ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="editar_sitio_web.php?id_sitio=<?= $idReg ?>" class="module-btn module-btn--primary"><i class="bi bi-pencil"></i>Editar</a>
            <a href="descargar_pdf.php?tipo=sitio_ficha&id_sitio=<?= $idReg ?>" class="module-btn module-btn--secondary" target="_blank"><i class="bi bi-file-earmark-pdf"></i>PDF</a>
            <a href="sitios_web_apps.php" class="module-btn module-btn--secondary"><i class="bi bi-arrow-left"></i>Volver a sitios</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card inv-ficha-card mt-0">
            <div class="card-body">
                <div class="inv-card-head">
                    <div>
                        <h5 class="mb-1">Datos del sitio</h5>
                        <p class="text-muted mb-0 small">Colegio, responsable, URL, hosting y estado.</p>
                    </div>
                </div>
                <div class="inv-ficha-body">
                    <div class="row g-3">
                        <div class="col-md-4 inv-dato"><span>Colegio</span><strong><?= inventario_h($sitio['nom_colegio']) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Responsable</span><strong><?= inventario_h($sitio['responsable'] ?: 'Sin asignar') ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Tipo</span><strong><?= inventario_h($sitio['tipo_sitio']) ?></strong></div>
                        <div class="col-md-6 inv-dato"><span>URL / dominio</span><strong class="text-break"><?= inventario_link_externo($sitio['url_sitio'] ?? '') ?></strong></div>
                        <div class="col-md-6 inv-dato"><span>Hosting / proveedor</span><strong><?= inventario_h($sitio['proveedor_hosting'] ?: '—') ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Estado</span><strong><?= inventario_h($sitio['estado_sitio']) ?></strong></div>
                        <div class="col-md-8 inv-dato"><span>Observaciones</span><strong><?= nl2br(inventario_h($sitio['observaciones'] ?: 'Sin observaciones.')) ?></strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card inv-ficha-card mt-0">
            <div class="card-body text-center">
                <div class="inv-card-head"><div><h5 class="mb-1">Código QR</h5></div></div>
                <div class="inv-ficha-body">
                    <div class="inv-qr-box d-inline-block p-2 bg-white rounded" data-qr-url="<?= inventario_h($urlFichaQr) ?>"></div>
                    <p class="text-muted small my-3">Al escanearlo se abre la ficha rápida (requiere iniciar sesión).</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="module-btn module-btn--secondary justify-content-center btnDescargarQr" data-nombre="qr_sitio_<?= $idReg ?>"><i class="bi bi-download"></i>Descargar QR</button>
                        <a href="ficha_qr.php?tipo=sitio&id=<?= $idReg ?>" class="module-btn module-btn--primary justify-content-center" target="_blank"><i class="bi bi-box-arrow-up-right"></i>Abrir ficha QR</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
