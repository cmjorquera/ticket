<?php
$modo = $modo ?? 'crear';
$software = $software ?? [];
$almacenamiento = !empty($software['almacenamiento']) ? $software['almacenamiento'] : [[]];
$datosSensiblesCatalogo = $datosSensiblesCatalogo ?? [];
$tiposUsuarioCatalogo = $tiposUsuarioCatalogo ?? [];
$monedas = $monedas ?? ['CLP', 'USD'];
$datosSensiblesSeleccionados = array_map('intval', array_column(($software['datos_sensibles'] ?? []), 'id_dato_sensible'));
$tiposUsuarioSeleccionados = array_map('intval', array_column(($software['tipos_usuario'] ?? []), 'id_tipo_usuario'));
$action = $modo === 'editar' ? 'actualizar_software.php' : 'guardar_software.php';
?>
<form id="formInventario" action="<?= inventario_h($action) ?>" method="post" class="inv-form">
    <?php if ($modo === 'editar'): ?><input type="hidden" name="id_software" value="<?= (int)$software['id_software'] ?>"><?php endif; ?>
    <div class="accordion inv-accordion" id="accordionSoftware">
        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#datosSoftware"><span class="inv-accordion-step">1</span><span><strong class="d-block">Datos generales</strong><small class="text-muted">Colegio, responsable, licencias, costo y proveedor</small></span></button></h2>
            <div id="datosSoftware" class="accordion-collapse collapse show" data-bs-parent="#accordionSoftware"><div class="accordion-body"><div class="row g-3">
                <div class="col-md-3"><label class="form-label">Colegio</label><select name="id_colegio" class="form-select" required><option value="">Seleccione</option><?php foreach ($colegios as $colegio): ?><option value="<?= (int)$colegio['id_colegio'] ?>" <?= (int)($software['id_colegio'] ?? 0) === (int)$colegio['id_colegio'] ? 'selected' : '' ?>><?= inventario_h($colegio['nom_colegio']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Responsable</label><select name="id_usuario_responsable" class="form-select"><option value="0">Sin asignar</option><?php foreach ($usuarios as $usuario): ?><option value="<?= (int)$usuario['id'] ?>" <?= (int)($software['id_usuario_responsable'] ?? 0) === (int)$usuario['id'] ? 'selected' : '' ?>><?= inventario_h($usuario['nombre_completo']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Licenciamiento</label><select name="tipo_licenciamiento" class="form-select" required><?php foreach ($tiposLicenciamiento as $tipo): ?><option value="<?= inventario_h($tipo) ?>" <?= ($software['tipo_licenciamiento'] ?? 'Suscripcion') === $tipo ? 'selected' : '' ?>><?= inventario_h($tipo) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Nombre</label><input type="text" name="nombre_software" class="form-control" value="<?= inventario_h($software['nombre_software'] ?? '') ?>" required></div>
                <div class="col-md-3"><label class="form-label">Version</label><input type="text" name="version_software" class="form-control" value="<?= inventario_h($software['version_software'] ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label">Cantidad</label><input type="number" min="1" name="cantidad_licencias" class="form-control" value="<?= inventario_h($software['cantidad_licencias'] ?? 1) ?>"></div>
                <div class="col-md-3"><label class="form-label">Inicio licencia</label><input type="date" name="fecha_inicio_licencia" class="form-control" value="<?= inventario_h(!empty($software['fecha_inicio_licencia']) && $software['fecha_inicio_licencia'] !== '0000-00-00' ? $software['fecha_inicio_licencia'] : '') ?>"></div>
                <div class="col-md-3"><label class="form-label">Fin licencia</label><input type="date" name="fecha_fin_licencia" class="form-control" value="<?= inventario_h(!empty($software['fecha_fin_licencia']) && $software['fecha_fin_licencia'] !== '0000-00-00' ? $software['fecha_fin_licencia'] : '') ?>"></div>
                <div class="col-md-3"><label class="form-label">Pagado por</label><select name="pagado_por" class="form-select" required><?php foreach ($pagadores as $tipo): ?><option value="<?= inventario_h($tipo) ?>" <?= ($software['pagado_por'] ?? 'Colegio') === $tipo ? 'selected' : '' ?>><?= inventario_h($tipo) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Costo</label><input type="number" step="0.01" min="0" name="costo" class="form-control" value="<?= inventario_h($software['costo'] ?? 0) ?>"></div>
                <div class="col-md-3"><label class="form-label">Moneda</label><select name="moneda" class="form-select" required><?php foreach ($monedas as $moneda): ?><option value="<?= inventario_h($moneda) ?>" <?= ($software['moneda'] ?? 'USD') === $moneda ? 'selected' : '' ?>><?= $moneda === 'CLP' ? '🇨🇱 Peso chileno (CLP)' : '🇺🇸 Dolar estadounidense (USD)' ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Proveedor</label><input type="text" name="proveedor" class="form-control" value="<?= inventario_h($software['proveedor'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">URL o referencia</label><input type="text" name="url_referencia" class="form-control" value="<?= inventario_h($software['url_referencia'] ?? '') ?>" placeholder="Panel admin, web del proveedor, portal de licencias, etc."></div>
                <div class="col-md-12">
                    <div class="inv-user-types-panel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <label class="form-label mb-0">Usuarios</label>
                        <button type="button" class="btn inv-top-btn inv-top-btn-primary btn-sm" id="btnAgregarTipoUsuario" data-endpoint="ajax/guardar_tipo_usuario.php">
                            <i class="bi bi-plus-circle me-1"></i>Agregar tipo usuario
                        </button>
                    </div>
                    <?php if (!empty($tiposUsuarioCatalogo)): ?>
                        <div class="inv-user-types-grid" id="contenedorTiposUsuario">
                            <?php foreach ($tiposUsuarioCatalogo as $tipoUsuario): ?>
                                <div class="inv-user-type-item">
                                    <label class="inv-repeat-card inv-user-type-card py-3 h-100 d-block">
                                        <div class="form-check m-0">
                                            <input class="form-check-input" type="checkbox" name="tipos_usuario[]" value="<?= (int)$tipoUsuario['id_tipo_usuario'] ?>" <?= in_array((int)$tipoUsuario['id_tipo_usuario'], $tiposUsuarioSeleccionados, true) ? 'checked' : '' ?>>
                                            <span class="form-check-label fw-semibold"><?= inventario_h($tipoUsuario['nombre']) ?></span>
                                        </div>
                                        <?php if (!empty($tipoUsuario['descripcion'])): ?>
                                            <small class="text-muted d-block mt-2"><?= inventario_h($tipoUsuario['descripcion']) ?></small>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-light border mb-0">No hay tipos de usuario cargados todavia.</div>
                        <div class="inv-user-types-grid mt-3" id="contenedorTiposUsuario"></div>
                    <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-12"><label class="form-label">Observaciones</label><textarea name="observaciones" class="form-control" rows="3"><?= inventario_h($software['observaciones'] ?? '') ?></textarea></div>
            </div></div></div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosSensibles"><span class="inv-accordion-step">2</span><span><strong class="d-block">Datos sensibles</strong><small class="text-muted">Informacion personal o delicada que usa este software</small></span></button></h2>
            <div id="datosSensibles" class="accordion-collapse collapse" data-bs-parent="#accordionSoftware"><div class="accordion-body">
                <?php if (!empty($datosSensiblesCatalogo)): ?>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <p class="text-muted mb-0">Marca los datos sensibles que este software procesa, almacena o visualiza.</p>
                        <button type="button" class="btn inv-top-btn inv-top-btn-primary btn-sm" id="btnAgregarDatoSensible" data-endpoint="ajax/guardar_dato_sensible.php">
                            <i class="bi bi-plus-circle me-1"></i>Agregar dato sensible
                        </button>
                    </div>
                    <div class="row g-3" id="contenedorDatosSensibles">
                        <?php foreach ($datosSensiblesCatalogo as $dato): ?>
                            <div class="col-md-6">
                                <label class="inv-repeat-card py-3 h-100 d-block">
                                    <div class="form-check m-0">
                                        <input class="form-check-input" type="checkbox" name="datos_sensibles[]" value="<?= (int)$dato['id_dato_sensible'] ?>" <?= in_array((int)$dato['id_dato_sensible'], $datosSensiblesSeleccionados, true) ? 'checked' : '' ?>>
                                        <span class="form-check-label fw-semibold"><?= inventario_h($dato['nombre']) ?></span>
                                    </div>
                                    <?php if (!empty($dato['descripcion'])): ?>
                                        <small class="text-muted d-block mt-2"><?= inventario_h($dato['descripcion']) ?></small>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <div class="alert alert-light border mb-0 flex-grow-1">No hay catalogo de datos sensibles cargado todavia.</div>
                        <button type="button" class="btn inv-top-btn inv-top-btn-primary btn-sm" id="btnAgregarDatoSensible" data-endpoint="ajax/guardar_dato_sensible.php">
                            <i class="bi bi-plus-circle me-1"></i>Agregar dato sensible
                        </button>
                    </div>
                    <div class="row g-3" id="contenedorDatosSensibles"></div>
                <?php endif; ?>
            </div></div>
        </div>
        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosAlmacenamiento"><span class="inv-accordion-step">3</span><span><strong class="d-block">Datos de almacenamiento</strong><small class="text-muted">Personas, cuentas o accesos relacionados</small></span></button></h2>
            <div id="datosAlmacenamiento" class="accordion-collapse collapse" data-bs-parent="#accordionSoftware"><div class="accordion-body">
                <div class="d-flex justify-content-between align-items-center mb-3"><h6 class="mb-0">Personas o cuentas relacionadas</h6><button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarAlmacenamiento">Agregar fila</button></div>
                <div id="contenedorAlmacenamiento"><?php foreach ($almacenamiento as $index => $fila): ?><div class="inv-repeat-card almacenamiento-item"><div class="row g-3"><div class="col-md-3"><label class="form-label">Nombre</label><input type="text" name="almacenamiento[<?= $index ?>][nombre_contacto]" class="form-control" value="<?= inventario_h($fila['nombre_contacto'] ?? '') ?>"></div><div class="col-md-3"><label class="form-label">RUT</label><input type="text" name="almacenamiento[<?= $index ?>][rut_contacto]" class="form-control" value="<?= inventario_h($fila['rut_contacto'] ?? '') ?>"></div><div class="col-md-3"><label class="form-label">Email</label><input type="email" name="almacenamiento[<?= $index ?>][email_contacto]" class="form-control" value="<?= inventario_h($fila['email_contacto'] ?? '') ?>"></div><div class="col-md-3"><label class="form-label">Otros</label><input type="text" name="almacenamiento[<?= $index ?>][otros_datos]" class="form-control" value="<?= inventario_h($fila['otros_datos'] ?? '') ?>"></div></div><button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button></div><?php endforeach; ?></div>
            </div></div>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4"><a href="index.php" class="btn btn-light border">Volver</a><button type="submit" class="btn btn-primary"><?= $modo === 'editar' ? 'Actualizar software' : 'Guardar software' ?></button></div>
</form>
