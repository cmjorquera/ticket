<?php
require_once __DIR__ . '/componentes/boot.php';
$idSoftware = (int)($_GET['id_software'] ?? 0);
$software = $inventario->obtenerSoftwareCompleto($idSoftware);
if (!$software) { header('Location: sofware_licenciamiento.php'); exit; }
$tituloPagina = 'Ficha software';
$idReg = (int)$software['id_software'];
$urlFichaQr = inventario_url('ficha_qr.php?tipo=software&id=' . $idReg);
$tiposUsuario = !empty($software['tipos_usuario']) ? implode(', ', array_column($software['tipos_usuario'], 'nombre')) : 'Sin tipos de usuario asociados.';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="inv-kicker">Ficha de software</span>
            <h1 class="inv-title mb-2"><?= inventario_h($software['nombre_software']) ?></h1>
            <p class="inv-subtitle mb-0"><?= inventario_h($software['nom_colegio']) ?> · Versión <?= inventario_h($software['version_software'] ?: '—') ?> · <?= inventario_h($software['tipo_licenciamiento']) ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="editar_software.php?id_software=<?= $idReg ?>" class="module-btn module-btn--primary"><i class="bi bi-pencil"></i>Editar</a>
            <a href="descargar_pdf.php?tipo=software_ficha&id_software=<?= $idReg ?>" class="module-btn module-btn--secondary" target="_blank"><i class="bi bi-file-earmark-pdf"></i>PDF</a>
            <a href="sofware_licenciamiento.php" class="module-btn module-btn--secondary"><i class="bi bi-arrow-left"></i>Volver a software</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card inv-ficha-card mt-0">
            <div class="card-body">
                <div class="inv-card-head">
                    <div>
                        <h5 class="mb-1">Datos generales</h5>
                        <p class="text-muted mb-0 small">Licencias, costo, proveedor y responsable.</p>
                    </div>
                </div>
                <div class="inv-ficha-body">
                    <div class="row g-3">
                        <div class="col-md-4 inv-dato"><span>Colegio</span><strong><?= inventario_h($software['nom_colegio']) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Responsable</span><strong><?= inventario_h($software['responsable'] ?: 'Sin asignar') ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Licenciamiento</span><strong><?= inventario_h($software['tipo_licenciamiento']) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Versión</span><strong><?= inventario_h($software['version_software'] ?: '—') ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Cantidad de licencias</span><strong><?= (int)$software['cantidad_licencias'] ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Pagado por</span><strong><?= inventario_h($software['pagado_por']) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Inicio licencia</span><strong><?= inventario_h(inventario_fecha($software['fecha_inicio_licencia'] ?? '')) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Fin licencia</span><strong><?= inventario_h(inventario_fecha($software['fecha_fin_licencia'] ?? '')) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Costo</span><strong><?= inventario_h(inventario_monto($software['costo'], $software['moneda'])) ?></strong></div>
                        <div class="col-md-6 inv-dato"><span>Proveedor</span><strong><?= inventario_h($software['proveedor'] ?: '—') ?></strong></div>
                        <div class="col-md-6 inv-dato"><span>URL o referencia</span><strong class="text-break"><?= inventario_link_externo($software['url_referencia'] ?? '') ?></strong></div>
                        <div class="col-12 inv-dato"><span>Tipos de usuario</span><strong><?= inventario_h($tiposUsuario) ?></strong></div>
                        <div class="col-12 inv-dato"><span>Observaciones</span><strong><?= nl2br(inventario_h($software['observaciones'] ?: 'Sin observaciones.')) ?></strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card inv-ficha-card">
            <div class="card-body">
                <div class="inv-card-head">
                    <div>
                        <h5 class="mb-1">Datos sensibles</h5>
                        <p class="text-muted mb-0 small">Información personal o delicada que este software procesa o almacena.</p>
                    </div>
                </div>
                <div class="inv-ficha-body">
                    <?php if (!empty($software['datos_sensibles'])): ?>
                        <div class="row g-3">
                            <?php foreach ($software['datos_sensibles'] as $fila): ?>
                                <div class="col-md-6 inv-dato">
                                    <span><i class="bi bi-shield-lock me-1"></i>Dato sensible</span>
                                    <strong><?= inventario_h($fila['nombre']) ?></strong>
                                    <?php if (!empty($fila['descripcion'])): ?><small class="text-muted d-block mt-1"><?= inventario_h($fila['descripcion']) ?></small><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="inv-empty-text mb-0">No hay datos sensibles asociados.</p>
                    <?php endif; ?>
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
                        <button type="button" class="module-btn module-btn--secondary justify-content-center btnDescargarQr" data-nombre="qr_software_<?= $idReg ?>"><i class="bi bi-download"></i>Descargar QR</button>
                        <a href="ficha_qr.php?tipo=software&id=<?= $idReg ?>" class="module-btn module-btn--primary justify-content-center" target="_blank"><i class="bi bi-box-arrow-up-right"></i>Abrir ficha QR</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card inv-ficha-card">
            <div class="card-body">
                <div class="inv-card-head"><div><h5 class="mb-1">Datos de almacenamiento</h5><p class="text-muted mb-0 small">Personas o cuentas relacionadas.</p></div></div>
                <div class="inv-ficha-body">
                    <?php if (!empty($software['almacenamiento'])): ?>
                        <?php foreach ($software['almacenamiento'] as $fila): ?>
                            <ul class="list-unstyled inv-list mb-3">
                                <li><span>Nombre</span><strong><?= inventario_h($fila['nombre_contacto'] ?: '—') ?></strong></li>
                                <li><span>RUT</span><strong><?= inventario_h($fila['rut_contacto'] ?: '—') ?></strong></li>
                                <li><span>Email</span><strong class="text-break"><?= inventario_h($fila['email_contacto'] ?: '—') ?></strong></li>
                                <li><span>Otros</span><strong><?= inventario_h($fila['otros_datos'] ?: '—') ?></strong></li>
                            </ul>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="inv-empty-text mb-0">No hay datos de almacenamiento asociados.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
