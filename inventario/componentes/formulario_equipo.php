<?php
$modo              = $modo ?? 'crear';
$equipo            = $equipo ?? [];
$compra            = $equipo['compra'] ?? [];
$almacenamiento    = $equipo['almacenamiento'] ?? [];
$procesador        = $equipo['procesador'] ?? [];
$software          = $equipo['software'] ?? [];
$memorias          = !empty($equipo['memorias']) ? $equipo['memorias'] : [[]];
$monitores         = !empty($equipo['monitores']) ? $equipo['monitores'] : [[]];
$fotos             = $equipo['fotos'] ?? [];
$colegioUsuario    = $colegioUsuario ?? [];
$ubicaciones       = $ubicaciones ?? [];
$idUbicacionActual = (int)($equipo['id_ubicacion'] ?? 0);
$idUsuarioAsignadoActual = (int)($equipo['id_usuario_asignado'] ?? 0);
$responsableEnListado = false;
foreach (($usuarios ?? []) as $usuarioTmp) {
    if ((int)($usuarioTmp['id'] ?? 0) === $idUsuarioAsignadoActual) {
        $responsableEnListado = true;
        break;
    }
}
$action            = $modo === 'editar' ? 'actualizar_equipo.php' : 'guardar_equipo.php';
?>

<form id="formInventario" action="<?= inventario_h($action) ?>" method="post" enctype="multipart/form-data" class="inv-form">
    <?php if ($modo === 'editar'): ?>
        <input type="hidden" name="id_equipo" value="<?= (int)$equipo['id_equipo'] ?>">
    <?php endif; ?>

    <div class="accordion inv-accordion" id="accordionInventario">

        <!-- ===== DATOS GENERALES ===== -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#datosGenerales">
                    Datos generales
                </button>
            </h2>
            <div id="datosGenerales" class="accordion-collapse collapse show" data-bs-parent="#accordionInventario">
                <div class="accordion-body">
                    <div class="row g-3">

                        <!-- Colegio (solo lectura, viene del usuario de sesion) -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Colegio</label>
                            <?php if (!empty($colegioUsuario['nom_colegio'])): ?>
                                <div class="form-control bg-light text-truncate" title="<?= inventario_h($colegioUsuario['nom_colegio']) ?>">
                                    <?= inventario_h($colegioUsuario['nom_colegio']) ?>
                                </div>
                            <?php else: ?>
                                <div class="form-control bg-light text-danger">Sin colegio asignado</div>
                            <?php endif; ?>
                        </div>

                        <!-- Usuario asignado -->
                        <div class="col-md-4">
                            <label class="form-label">Usuario asignado</label>
                            <select name="id_usuario_asignado" class="form-select">
                                <option value="0" <?= $idUsuarioAsignadoActual === 0 ? 'selected' : '' ?>>Sin asignar</option>
                                <?php if ($idUsuarioAsignadoActual > 0 && !$responsableEnListado): ?>
                                    <option value="<?= $idUsuarioAsignadoActual ?>" selected>
                                        Responsable actual no disponible para este colegio
                                    </option>
                                <?php endif; ?>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= (int)$usuario['id'] ?>" <?= $idUsuarioAsignadoActual === (int)$usuario['id'] ? 'selected' : '' ?>>
                                        <?= inventario_h($usuario['nombre_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div class="col-md-4">
                            <label class="form-label">Estado <span class="text-danger">*</span></label>
                            <select name="id_estado" class="form-select" required>
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?= (int)$estado['id_estado'] ?>" <?= (int)($equipo['id_estado'] ?? 1) === (int)$estado['id_estado'] ? 'selected' : '' ?>>
                                        <?= inventario_h($estado['nombre_estado']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Ubicacion filtrada por colegio del usuario -->
                        <div class="col-md-4">
                            <label class="form-label">Ubicacion <span class="text-danger">*</span></label>
                            <select name="id_ubicacion" id="selectUbicacion" class="form-select" required>
                                <option value="">Seleccione ubicacion</option>
                                <?php foreach ($ubicaciones as $ub): ?>
                                    <?php
                                    $label = $ub['tipo_ubicacion']
                                        ? $ub['tipo_ubicacion'] . ' — ' . $ub['nombre_ubicacion']
                                        : $ub['nombre_ubicacion'];
                                    ?>
                                    <option value="<?= (int)$ub['id_ubicacion'] ?>" <?= $idUbicacionActual === (int)$ub['id_ubicacion'] ? 'selected' : '' ?>>
                                        <?= inventario_h($label) ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="__nueva__">Agregar nueva ubicacion...</option>
                            </select>
                        </div>

                        <div class="col-md-4 d-none" id="grupoNuevaUbicacion">
                            <label class="form-label">Nueva ubicacion <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_ubicacion_nueva" id="nombreUbicacionNueva" class="form-control" placeholder="Ej: Sala A1">
                        </div>

                        <!-- Nombre del equipo (amigable, escrito por el usuario) -->
                        <div class="col-md-4">
                            <label class="form-label">Nombre del equipo</label>
                            <input type="text" name="nombre_personalizado" class="form-control"
                                   value="<?= inventario_h($equipo['nombre_personalizado'] ?? '') ?>"
                                   placeholder="Ej: PC Direccion, Notebook Inspectoria, Equipo Sala A3">
                            <div class="form-text">Nombre amigable, opcional. No reemplaza el identificador tecnico.</div>
                        </div>

                        <!-- Numero de serie -->
                        <div class="col-md-4">
                            <label class="form-label">Numero de serie <span class="text-danger">*</span></label>
                            <input type="text" name="numero_serie" id="numeroSerieEquipo" class="form-control" value="<?= inventario_h($equipo['numero_serie'] ?? '') ?>" required>
                        </div>

                        <!-- Identificador tecnico calculado -->
                        <div class="col-md-4">
                            <label class="form-label">Identificador tecnico</label>
                            <div class="form-control bg-light fw-semibold" id="nombreEquipoPreview">
                                <?= inventario_h($equipo['nombre_equipo'] ?? 'Se genera desde la serie') ?>
                            </div>
                            <input type="hidden" name="nombre_equipo" id="nombreEquipoAuto" value="<?= inventario_h($equipo['nombre_equipo'] ?? '') ?>">
                            <div class="form-text">Se genera automaticamente como PC-{SERIE}.</div>
                        </div>

                        <!-- Tipo de PC -->
                        <div class="col-md-4">
                            <label class="form-label">Tipo de PC <span class="text-danger">*</span></label>
                            <select name="tipo_pc" class="form-select" required>
                                <option value="">Seleccione tipo</option>
                                <?php foreach ($tiposPc as $tipo): ?>
                                    <option value="<?= inventario_h($tipo) ?>" <?= ($equipo['tipo_pc'] ?? '') === $tipo ? 'selected' : '' ?>>
                                        <?= inventario_h($tipo) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Fabricante -->
                        <div class="col-md-4">
                            <label class="form-label">Fabricante</label>
                            <input type="text" name="fabricante" class="form-control" value="<?= inventario_h($equipo['fabricante'] ?? '') ?>">
                        </div>

                        <!-- Producto / modelo -->
                        <div class="col-md-4">
                            <label class="form-label">Producto / modelo</label>
                            <input type="text" name="producto" class="form-control" value="<?= inventario_h($equipo['producto'] ?? '') ?>">
                        </div>

                        <!-- QR / codigo interno -->
                        <div class="col-md-4">
                            <label class="form-label">QR / codigo interno</label>
                            <input type="text" name="qr_code" class="form-control" value="<?= inventario_h($equipo['qr_code'] ?? '') ?>">
                        </div>

                        <?php if ($modo === 'editar'): ?>
                        <!-- Observacion de traslado (si cambia la ubicacion) -->
                        <div class="col-12">
                            <label class="form-label">Observacion de traslado <span class="text-muted fw-normal">(completar solo si cambia de ubicacion)</span></label>
                            <input type="text" name="observacion_movimiento" class="form-control" placeholder="Ej: Se traslado para reparacion en bodega informatica...">
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>

        <!-- ===== COMPRA ===== -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosCompra">
                    Compra y adquisicion
                </button>
            </h2>
            <div id="datosCompra" class="accordion-collapse collapse" data-bs-parent="#accordionInventario">
                <div class="accordion-body">
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Valor equipo</label><input type="number" step="0.01" name="valor_equipo" class="form-control" value="<?= inventario_h($compra['valor_equipo'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Proveedor</label><input type="text" name="proveedor" class="form-control" value="<?= inventario_h($compra['proveedor'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Numero factura</label><input type="text" name="numero_factura" class="form-control" value="<?= inventario_h($compra['numero_factura'] ?? '') ?>"></div>
                        <div class="col-md-3"><label class="form-label">Fecha compra</label><input type="date" name="fecha_compra" class="form-control" value="<?= inventario_h($compra['fecha_compra'] ?? '') ?>"></div>
                        <div class="col-12"><label class="form-label">Observacion</label><textarea name="observacion_compra" class="form-control" rows="3"><?= inventario_h($compra['observacion'] ?? '') ?></textarea></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== HARDWARE ===== -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosHardware">
                    Almacenamiento, procesador y software
                </button>
            </h2>
            <div id="datosHardware" class="accordion-collapse collapse" data-bs-parent="#accordionInventario">
                <div class="accordion-body">
                    <div class="row g-4">
                        <div class="col-lg-4">
                            <div class="inv-section-card">
                                <h6>Almacenamiento</h6>
                                <label class="form-label">Modelo</label>
                                <input type="text" name="equipo_modelo" class="form-control mb-2" value="<?= inventario_h($almacenamiento['equipo_modelo'] ?? '') ?>">
                                <label class="form-label">Capacidad</label>
                                <input type="text" name="equipo_capacidad" class="form-control mb-2" value="<?= inventario_h($almacenamiento['equipo_capacidad'] ?? '') ?>">
                                <label class="form-label">Tamano / formato</label>
                                <input type="text" name="equipo_tamano" class="form-control" value="<?= inventario_h($almacenamiento['equipo_tamano'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="inv-section-card">
                                <h6>Procesador</h6>
                                <label class="form-label">Fabricante</label>
                                <input type="text" name="procesador_fabricante" class="form-control mb-2" value="<?= inventario_h($procesador['equipo_fabricante'] ?? '') ?>">
                                <label class="form-label">Modelo</label>
                                <input type="text" name="procesador_modelo" class="form-control mb-2" value="<?= inventario_h($procesador['equipo_modelo'] ?? '') ?>">
                                <label class="form-label">Velocidad</label>
                                <input type="text" name="procesador_velocidad" class="form-control" value="<?= inventario_h($procesador['equipo_velocidad'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="inv-section-card">
                                <h6>Software</h6>
                                <label class="form-label">Windows</label>
                                <input type="text" name="windows" class="form-control mb-2" value="<?= inventario_h($software['windows'] ?? '') ?>">
                                <label class="form-label">Office</label>
                                <input type="text" name="office" class="form-control mb-2" value="<?= inventario_h($software['office'] ?? '') ?>">
                                <label class="form-label">Antivirus</label>
                                <input type="text" name="antivirus" class="form-control" value="<?= inventario_h($software['antivirus'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== RAM ===== -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosRam">
                    Memorias RAM
                </button>
            </h2>
            <div id="datosRam" class="accordion-collapse collapse" data-bs-parent="#accordionInventario">
                <div class="accordion-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Modulos de memoria</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarMemoria">Agregar memoria</button>
                    </div>
                    <div id="contenedorMemorias">
                        <?php foreach ($memorias as $index => $memoria): ?>
                            <div class="inv-repeat-card memoria-item">
                                <div class="row g-3">
                                    <div class="col-md-2"><label class="form-label">Slot</label><input type="text" name="memoria[<?= $index ?>][designacion_memoria]" class="form-control" value="<?= inventario_h($memoria['designacion_memoria'] ?? '') ?>"></div>
                                    <div class="col-md-2"><label class="form-label">Formato</label><input type="text" name="memoria[<?= $index ?>][formato_memoria]" class="form-control" value="<?= inventario_h($memoria['formato_memoria'] ?? '') ?>"></div>
                                    <div class="col-md-2"><label class="form-label">Tipo</label><input type="text" name="memoria[<?= $index ?>][tipo_memoria]" class="form-control" value="<?= inventario_h($memoria['tipo_memoria'] ?? '') ?>"></div>
                                    <div class="col-md-2"><label class="form-label">Tamano</label><input type="text" name="memoria[<?= $index ?>][tamano_memoria]" class="form-control" value="<?= inventario_h($memoria['tamano_memoria'] ?? '') ?>"></div>
                                    <div class="col-md-2"><label class="form-label">Frecuencia</label><input type="text" name="memoria[<?= $index ?>][frecuencia_memoria]" class="form-control" value="<?= inventario_h($memoria['frecuencia_memoria'] ?? '') ?>"></div>
                                    <div class="col-md-1"><label class="form-label">Marca</label><input type="text" name="memoria[<?= $index ?>][marca_memoria]" class="form-control" value="<?= inventario_h($memoria['marca_memoria'] ?? '') ?>"></div>
                                    <div class="col-md-1"><label class="form-label">Orden</label><input type="number" name="memoria[<?= $index ?>][orden_memoria]" class="form-control" value="<?= inventario_h($memoria['orden_memoria'] ?? ($index + 1)) ?>"></div>
                                </div>
                                <button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== MONITORES ASOCIADOS AL PC ===== -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosMonitores">
                    Monitores asociados al PC
                </button>
            </h2>
            <div id="datosMonitores" class="accordion-collapse collapse" data-bs-parent="#accordionInventario">
                <div class="accordion-body">
                    <p class="text-muted small mb-3">
                        Registra aqui los monitores fisicamente conectados o asignados a este equipo PC.<br>
                        Los monitores comprados de forma independiente se administran en la pestana <strong>Monitores</strong> del inventario.
                    </p>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Monitores del equipo</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarMonitorPC">Agregar monitor</button>
                    </div>
                    <div id="contenedorMonitores">
                        <?php foreach ($monitores as $index => $monitor): ?>
                            <div class="inv-repeat-card monitor-item">
                                <div class="row g-3">
                                    <div class="col-md-3"><label class="form-label">Modelo</label><input type="text" name="monitor[<?= $index ?>][modelo_monitor]" class="form-control" value="<?= inventario_h($monitor['modelo_monitor'] ?? '') ?>"></div>
                                    <div class="col-md-2"><label class="form-label">Codigo</label><input type="text" name="monitor[<?= $index ?>][codigo_monitor]" class="form-control" value="<?= inventario_h($monitor['codigo_monitor'] ?? '') ?>"></div>
                                    <div class="col-md-2"><label class="form-label">Serie</label><input type="text" name="monitor[<?= $index ?>][serie_monitor]" class="form-control" value="<?= inventario_h($monitor['serie_monitor'] ?? '') ?>"></div>
                                    <div class="col-md-2"><label class="form-label">Tamano</label><input type="text" name="monitor[<?= $index ?>][tamano_monitor]" class="form-control" value="<?= inventario_h($monitor['tamano_monitor'] ?? '') ?>"></div>
                                    <div class="col-md-2"><label class="form-label">Resolucion</label><input type="text" name="monitor[<?= $index ?>][resolucion_monitor]" class="form-control" value="<?= inventario_h($monitor['resolucion_monitor'] ?? '') ?>"></div>
                                    <div class="col-md-1"><label class="form-label">Orden</label><input type="number" name="monitor[<?= $index ?>][orden_monitor]" class="form-control" value="<?= inventario_h($monitor['orden_monitor'] ?? ($index + 1)) ?>"></div>
                                </div>
                                <button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== FOTOS ===== -->
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosFotos">
                    Fotos del equipo
                </button>
            </h2>
            <div id="datosFotos" class="accordion-collapse collapse" data-bs-parent="#accordionInventario">
                <div class="accordion-body">

                    <?php if (!empty($fotos)): ?>
                    <!-- Fotos existentes (solo en edicion) -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Fotos actuales</label>
                        <div class="inv-photo-grid">
                            <?php foreach ($fotos as $foto): ?>
                            <div class="inv-photo-item <?= $foto['principal'] ? 'inv-photo-principal' : '' ?>">
                                <img src="<?= inventario_h(inventario_url(ltrim($foto['ruta_foto'], '/'))) ?>"
                                     alt="Foto equipo" class="inv-photo-thumb">
                                <?php if ($foto['principal']): ?>
                                    <div class="inv-badge-principal-overlay">&#9733; Principal</div>
                                <?php endif; ?>
                                <div class="inv-photo-actions">
                                    <label class="inv-photo-action-lbl" title="Marcar como principal">
                                        <input type="radio" name="foto_principal" value="<?= (int)$foto['id_foto'] ?>"
                                               class="d-none" <?= $foto['principal'] ? 'checked' : '' ?>>
                                        <span class="btn btn-xs btn-outline-warning <?= $foto['principal'] ? 'active' : '' ?>">&#9733;</span>
                                    </label>
                                    <label class="inv-photo-action-lbl" title="Eliminar foto">
                                        <input type="checkbox" name="fotos_eliminar[]" value="<?= (int)$foto['id_foto'] ?>"
                                               class="d-none inv-chk-eliminar">
                                        <span class="btn btn-xs btn-outline-danger inv-btn-eliminar">&#10005;</span>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-muted small mt-2 mb-0">
                            <strong>&#9733;</strong> = marcar como principal &nbsp;|&nbsp;
                            <strong>&#10005;</strong> = marcar para eliminar (se aplica al guardar)
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Subir nuevas fotos -->
                    <label class="form-label fw-semibold">
                        <?= $modo === 'editar' ? 'Agregar nuevas fotos' : 'Fotos del equipo' ?>
                        <?php if ($modo === 'crear'): ?>
                            <span class="text-muted fw-normal small">(la primera foto sera la principal)</span>
                        <?php endif; ?>
                    </label>
                    <input type="file" name="fotos_equipo[]" id="fotos_equipo" class="form-control"
                           accept="image/jpeg,image/png,image/webp,image/gif" multiple>

                    <!-- Preview dinamico de fotos nuevas -->
                    <div id="previewFotos" class="mt-3 d-none"></div>

                </div>
            </div>
        </div>

    </div><!-- /accordion -->

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="index.php" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary">
            <?= $modo === 'editar' ? 'Actualizar equipo' : 'Guardar equipo' ?>
        </button>
    </div>
</form>

<script>
(function () {
    /* ---- Checkbox eliminar foto: toggle visual ---- */
    document.querySelectorAll('.inv-chk-eliminar').forEach(function (chk) {
        chk.addEventListener('change', function () {
            var btn  = this.parentElement.querySelector('.inv-btn-eliminar');
            var item = this.closest('.inv-photo-item');
            if (btn)  btn.classList.toggle('active', this.checked);
            if (item) item.style.opacity = this.checked ? '0.4' : '';
        });
    });

    /* ---- Radio foto principal: toggle visual ---- */
    document.querySelectorAll('[name="foto_principal"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('[name="foto_principal"]').forEach(function (r) {
                var btn  = r.parentElement.querySelector('span');
                var item = r.closest('.inv-photo-item');
                if (btn)  btn.classList.remove('active');
                if (item) item.classList.remove('inv-photo-principal');
            });
            var btnActivo  = this.parentElement.querySelector('span');
            var itemActivo = this.closest('.inv-photo-item');
            if (btnActivo)  btnActivo.classList.add('active');
            if (itemActivo) itemActivo.classList.add('inv-photo-principal');
        });
    });

    /* ---- Preview de fotos nuevas (grid compacto) ---- */
    var inputFotos = document.getElementById('fotos_equipo');
    var previewEl  = document.getElementById('previewFotos');
    if (!inputFotos || !previewEl) return;

    inputFotos.addEventListener('change', function () {
        var files = Array.from(this.files).filter(function (f) {
            return f.type.startsWith('image/');
        });
        previewEl.innerHTML = '';
        previewEl.classList.toggle('d-none', files.length === 0);
        if (!files.length) return;

        var grid = document.createElement('div');
        grid.className = 'inv-new-photo-grid';
        previewEl.appendChild(grid);

        files.forEach(function (file, i) {
            var reader = new FileReader();
            reader.onload = function (e) {
                var card = document.createElement('div');
                card.className = 'inv-new-photo-card';
                card.innerHTML =
                    '<div class="inv-new-photo-wrap">' +
                        '<img src="' + e.target.result + '" alt="">' +
                        (i === 0 ? '<span class="inv-badge-principal-overlay">&#9733; Principal</span>' : '') +
                    '</div>' +
                    '<span class="inv-new-photo-name">' + (file.name.length > 20 ? file.name.substring(0, 18) + '…' : file.name) + '</span>';
                grid.appendChild(card);
            };
            reader.readAsDataURL(file);
        });
    });

    /* ---- Ubicacion nueva: mostrar input solo cuando corresponde ---- */
    var selectUbicacion = document.getElementById('selectUbicacion');
    var grupoNuevaUbicacion = document.getElementById('grupoNuevaUbicacion');
    var inputNuevaUbicacion = document.getElementById('nombreUbicacionNueva');

    function sincronizarNuevaUbicacion() {
        if (!selectUbicacion || !grupoNuevaUbicacion || !inputNuevaUbicacion) return;
        var creando = selectUbicacion.value === '__nueva__';
        grupoNuevaUbicacion.classList.toggle('d-none', !creando);
        inputNuevaUbicacion.required = creando;
        if (!creando) {
            inputNuevaUbicacion.value = '';
        }
    }

    if (selectUbicacion) {
        selectUbicacion.addEventListener('change', sincronizarNuevaUbicacion);
        sincronizarNuevaUbicacion();
    }
}());
</script>
