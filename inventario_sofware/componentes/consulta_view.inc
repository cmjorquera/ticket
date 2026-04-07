<?php
$colegios = $colegios ?? [];
$softwaresDisponibles = $softwaresDisponibles ?? [];
$vista = $vista ?? 'software';
$softwareSeleccionado = $softwareSeleccionado ?? '';
$idColegioSeleccionado = (int)($idColegioSeleccionado ?? 0);
$idDatoSensibleSeleccionado = (int)($idDatoSensibleSeleccionado ?? 0);
$consultaSoftware = $consultaSoftware ?? ['software' => '', 'resumen' => ['total_licencias' => 0, 'total_colegios' => 0, 'costo_total' => 0], 'colegios' => []];
$consultaColegio = $consultaColegio ?? ['colegio' => null, 'resumen' => ['total_softwares' => 0, 'total_licencias' => 0, 'total_webs' => 0, 'total_apps' => 0, 'total_clientes' => 0, 'total_sitios' => 0, 'costo_total' => 0], 'softwares' => [], 'sitios' => []];
$datosSensiblesDisponibles = $datosSensiblesDisponibles ?? [];
$consultaDatoSensible = $consultaDatoSensible ?? ['dato' => null, 'resumen' => ['total_softwares' => 0, 'total_licencias' => 0, 'total_colegios' => 0, 'costo_total' => 0], 'softwares' => []];
$logoColegio = $logoColegio ?? '';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Consulta rapida</h1>
        <p class="text-muted mb-0">Consulta distribucion por software o revisa el resumen completo de un colegio.</p>
    </div>
    <a href="index.php" class="btn btn-light border">Volver</a>
</div>

<div class="card shadow-sm border-0 inv-panel">
    <div class="card-body">
        <ul class="nav nav-pills inv-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $vista === 'software' ? 'active' : '' ?>" href="consulta_nueva.php?vista=software">Ver por software</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $vista === 'colegio' ? 'active' : '' ?>" href="consulta_nueva.php?vista=colegio">Ver por colegio</a>
            </li>
            <li class="nav-item" role="presentation">
                <a class="nav-link <?= $vista === 'dato_sensible' ? 'active' : '' ?>" href="consulta_nueva.php?vista=dato_sensible">Ver por dato sensible</a>
            </li>
        </ul>

        <?php if ($vista === 'software'): ?>
            <div class="mb-4">
                <h4 class="mb-1">Distribucion por software</h4>
                <p class="text-muted mb-0">Ejemplo: ver cuantas licencias de AnyDesk hay y en que colegios estan distribuidas.</p>
            </div>

            <form method="get" class="row g-3 align-items-end mb-4">
                <input type="hidden" name="modo" value="consulta">
                <input type="hidden" name="vista" value="software">
                <div class="col-lg-8">
                    <label class="form-label">Software</label>
                    <select name="software" class="form-select js-auto-submit">
                        <option value="">Seleccione un software</option>
                        <?php foreach ($softwaresDisponibles as $nombreSoftware): ?>
                            <option value="<?= inventario_h($nombreSoftware) ?>" <?= $softwareSeleccionado === $nombreSoftware ? 'selected' : '' ?>><?= inventario_h($nombreSoftware) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-4 d-flex gap-2">
                    <?php if ($softwareSeleccionado !== ''): ?>
                        <a href="descargar_pdf.php?tipo=consulta_software&software=<?= urlencode($softwareSeleccionado) ?>" class="btn btn-outline-danger" target="_blank">
                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                        </a>
                    <?php endif; ?>
                    <a href="consulta_nueva.php?vista=software" class="btn btn-light border">Limpiar</a>
                </div>
            </form>

            <?php if ($softwareSeleccionado !== ''): ?>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-primary"><span class="inv-stat-label">Software</span><strong><?= inventario_h($consultaSoftware['software']) ?></strong><small>Nombre consultado</small></div></div>
                    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-info"><span class="inv-stat-label">Licencias totales</span><strong><?= (int)$consultaSoftware['resumen']['total_licencias'] ?></strong><small>Suma entre colegios</small></div></div>
                    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-warning"><span class="inv-stat-label">Colegios</span><strong><?= (int)$consultaSoftware['resumen']['total_colegios'] ?></strong><small>Colegios con este software</small></div></div>
                    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-success"><span class="inv-stat-label">Costo total</span><strong>USD <?= number_format((float)$consultaSoftware['resumen']['costo_total'], 0, ',', '.') ?></strong><small>Costo acumulado</small></div></div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Colegio</th>
                                <th>Licencias</th>
                                <th>Registros</th>
                                <th>Licenciamiento</th>
                                <th>Pagado por</th>
                                <th>Costo total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($consultaSoftware['colegios'])): ?>
                                <?php foreach ($consultaSoftware['colegios'] as $fila): ?>
                                    <tr>
                                        <td><?= inventario_h($fila['nom_colegio']) ?></td>
                                        <td><?= (int)$fila['licencias'] ?></td>
                                        <td><?= (int)$fila['registros'] ?></td>
                                        <td><?= inventario_h($fila['licenciamiento'] ?: '-') ?></td>
                                        <td><?= inventario_h($fila['pagado_por'] ?: '-') ?></td>
                                        <td>USD <?= number_format((float)$fila['costo_total'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">No se encontraron registros para ese software.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-light border mb-0">Selecciona un software para ver su distribucion entre colegios.</div>
            <?php endif; ?>
        <?php elseif ($vista === 'colegio'): ?>
            <div class="row g-4 align-items-start mb-4">
                <div class="col-lg-7">
                    <h4 class="mb-1">Resumen por colegio</h4>
                    <p class="text-muted mb-0">Selecciona un colegio y revisa cuantas licencias, softwares, webs, apps y clientes tiene.</p>
                </div>
                <div class="col-lg-5">
                    <?php if (!empty($consultaColegio['colegio'])): ?>
                        <div class="inv-section-card h-100">
                            <div class="d-flex align-items-center gap-3">
                                <?php if ($logoColegio !== ''): ?>
                                    <div class="inv-college-logo-wrap">
                                        <img src="<?= inventario_h($logoColegio) ?>" alt="<?= inventario_h($consultaColegio['colegio']['nom_colegio']) ?>" class="inv-college-logo">
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h5 class="mb-0"><?= inventario_h($consultaColegio['colegio']['nom_colegio']) ?></h5>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <form method="get" class="row g-3 align-items-end mb-4">
                <input type="hidden" name="modo" value="consulta">
                <input type="hidden" name="vista" value="colegio">
                <div class="col-lg-8">
                    <label class="form-label">Colegio</label>
                    <select name="id_colegio" class="form-select js-auto-submit">
                        <option value="">Seleccione un colegio</option>
                        <?php foreach ($colegios as $colegio): ?>
                            <option value="<?= (int)$colegio['id_colegio'] ?>" <?= $idColegioSeleccionado === (int)$colegio['id_colegio'] ? 'selected' : '' ?>><?= inventario_h($colegio['nom_colegio']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-4 d-flex gap-2">
                    <?php if ($idColegioSeleccionado > 0): ?>
                        <a href="descargar_pdf.php?tipo=consulta_colegio&id_colegio=<?= (int)$idColegioSeleccionado ?>" class="btn btn-outline-danger" target="_blank">
                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                        </a>
                    <?php endif; ?>
                    <a href="consulta_nueva.php?vista=colegio" class="btn btn-light border">Limpiar</a>
                </div>
            </form>

            <?php if (!empty($consultaColegio['colegio'])): ?>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-primary"><span class="inv-stat-label">Colegio</span><strong><?= inventario_h($consultaColegio['colegio']['nom_colegio']) ?></strong><small>RBD <?= inventario_h($consultaColegio['colegio']['rbd_colegio'] ?: '-') ?></small></div></div>
                    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-info"><span class="inv-stat-label">Softwares / licencias</span><strong><?= (int)$consultaColegio['resumen']['total_softwares'] ?> / <?= (int)$consultaColegio['resumen']['total_licencias'] ?></strong><small>Registros y licencias</small></div></div>
                    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-warning"><span class="inv-stat-label">Web / app / cliente</span><strong><?= (int)$consultaColegio['resumen']['total_webs'] ?> / <?= (int)$consultaColegio['resumen']['total_apps'] ?> / <?= (int)$consultaColegio['resumen']['total_clientes'] ?></strong><small>Total sitios: <?= (int)$consultaColegio['resumen']['total_sitios'] ?></small></div></div>
                    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-success"><span class="inv-stat-label">Costo total</span><strong>USD <?= number_format((float)$consultaColegio['resumen']['costo_total'], 0, ',', '.') ?></strong><small>Software declarado</small></div></div>
                </div>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="inv-section-card h-100">
                            <h5 class="mb-3">Softwares del colegio</h5>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead><tr><th>Software</th><th>Version</th><th>Licencias</th><th>Tipo</th><th>Pagado por</th></tr></thead>
                                    <tbody>
                                        <?php if (!empty($consultaColegio['softwares'])): ?>
                                            <?php foreach ($consultaColegio['softwares'] as $fila): ?>
                                                <tr>
                                                    <td><?= inventario_h($fila['nombre_software']) ?></td>
                                                    <td><?= inventario_h($fila['version_software'] ?: '-') ?></td>
                                                    <td><?= (int)$fila['cantidad_licencias'] ?></td>
                                                    <td><?= inventario_h($fila['tipo_licenciamiento']) ?></td>
                                                    <td><?= inventario_h($fila['pagado_por']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="5" class="text-center text-muted">Este colegio no tiene softwares registrados.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="inv-section-card h-100">
                            <h5 class="mb-3">Sitios del colegio</h5>
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead><tr><th>Nombre</th><th>Tipo</th><th>Estado</th></tr></thead>
                                    <tbody>
                                        <?php if (!empty($consultaColegio['sitios'])): ?>
                                            <?php foreach ($consultaColegio['sitios'] as $fila): ?>
                                                <tr>
                                                    <td><?= inventario_h($fila['nombre_sitio']) ?></td>
                                                    <td><?= inventario_h($fila['tipo_sitio']) ?></td>
                                                    <td><?= inventario_h($fila['estado_sitio']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="3" class="text-center text-muted">Este colegio no tiene sitios registrados.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-light border mb-0">Selecciona un colegio para ver su resumen digital.</div>
            <?php endif; ?>
        <?php else: ?>
            <div class="mb-4">
                <h4 class="mb-1">Consulta por dato sensible</h4>
                <p class="text-muted mb-0">Revisa que software utiliza o trata un dato sensible del catalogo.</p>
            </div>

            <form method="get" class="row g-3 align-items-end mb-4">
                <input type="hidden" name="modo" value="consulta">
                <input type="hidden" name="vista" value="dato_sensible">
                <div class="col-lg-8">
                    <label class="form-label">Dato sensible</label>
                    <select name="id_dato_sensible" class="form-select js-auto-submit">
                        <option value="">Seleccione un dato sensible</option>
                        <?php foreach ($datosSensiblesDisponibles as $dato): ?>
                            <option value="<?= (int)$dato['id_dato_sensible'] ?>" <?= $idDatoSensibleSeleccionado === (int)$dato['id_dato_sensible'] ? 'selected' : '' ?>><?= inventario_h($dato['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-4 d-flex gap-2">
                    <a href="consulta_nueva.php?vista=dato_sensible" class="btn btn-light border">Limpiar</a>
                </div>
            </form>

            <?php if (empty($datosSensiblesDisponibles)): ?>
                <div class="alert alert-warning border mb-0">Faltan datos en el catalogo de datos sensibles. Ejecuta el SQL del modulo y carga al menos un dato sensible.</div>
            <?php elseif (!empty($consultaDatoSensible['dato'])): ?>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-primary"><span class="inv-stat-label">Dato sensible</span><strong><?= inventario_h($consultaDatoSensible['dato']['nombre']) ?></strong><small><?= inventario_h($consultaDatoSensible['dato']['descripcion'] ?: 'Catalogo activo') ?></small></div></div>
                    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-info"><span class="inv-stat-label">Softwares</span><strong><?= (int)$consultaDatoSensible['resumen']['total_softwares'] ?></strong><small>Registros relacionados</small></div></div>
                    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-warning"><span class="inv-stat-label">Licencias</span><strong><?= (int)$consultaDatoSensible['resumen']['total_licencias'] ?></strong><small>Total asociado</small></div></div>
                    <div class="col-12 col-md-6 col-xl-3"><div class="inv-stat-card inv-stat-success"><span class="inv-stat-label">Costo total</span><strong>USD <?= number_format((float)$consultaDatoSensible['resumen']['costo_total'], 0, ',', '.') ?></strong><small><?= (int)$consultaDatoSensible['resumen']['total_colegios'] ?> colegios</small></div></div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Colegio</th>
                                <th>Software</th>
                                <th>Version</th>
                                <th>Licencias</th>
                                <th>Licenciamiento</th>
                                <th>Responsable</th>
                                <th>Costo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($consultaDatoSensible['softwares'])): ?>
                                <?php foreach ($consultaDatoSensible['softwares'] as $fila): ?>
                                    <tr>
                                        <td><?= inventario_h($fila['nom_colegio']) ?></td>
                                        <td><?= inventario_h($fila['nombre_software']) ?></td>
                                        <td><?= inventario_h($fila['version_software'] ?: '-') ?></td>
                                        <td><?= (int)$fila['cantidad_licencias'] ?></td>
                                        <td><?= inventario_h($fila['tipo_licenciamiento']) ?></td>
                                        <td><?= inventario_h(trim((string)($fila['responsable'] ?? '')) ?: 'Sin asignar') ?></td>
                                        <td><?= inventario_h($fila['moneda'] ?: 'USD') ?> <?= number_format((float)$fila['costo'], 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center text-muted">No hay software asociado a este dato sensible.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-light border mb-0">Selecciona un dato sensible para revisar los software relacionados.</div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.js-auto-submit').forEach(function (elemento) {
    elemento.addEventListener('change', function () {
        this.form.submit();
    });
});
</script>
