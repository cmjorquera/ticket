<?php
$modo = $modo ?? 'crear';
$equipo = $equipo ?? [];
$compra = $equipo['compra'] ?? [];
$almacenamiento = $equipo['almacenamiento'] ?? [];
$procesador = $equipo['procesador'] ?? [];
$software = $equipo['software'] ?? [];
$memorias = !empty($equipo['memorias']) ? $equipo['memorias'] : [[]];
$monitores = !empty($equipo['monitores']) ? $equipo['monitores'] : [[]];
$fotos = $equipo['fotos'] ?? [];
$action = $modo === 'editar' ? 'actualizar_equipo.php' : 'guardar_equipo.php';
?>

<form id="formInventario" action="<?= inventario_h($action) ?>" method="post" enctype="multipart/form-data" class="inv-form">
    <?php if ($modo === 'editar'): ?>
        <input type="hidden" name="id_equipo" value="<?= (int)$equipo['id_equipo'] ?>">
    <?php endif; ?>

    <div class="accordion inv-accordion" id="accordionInventario">
        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#datosGenerales">Datos generales</button></h2>
            <div id="datosGenerales" class="accordion-collapse collapse show" data-bs-parent="#accordionInventario">
                <div class="accordion-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Colegio</label>
                            <select name="id_colegio" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($colegios as $colegio): ?>
                                    <option value="<?= (int)$colegio['id_colegio'] ?>" <?= (int)($equipo['id_colegio'] ?? 0) === (int)$colegio['id_colegio'] ? 'selected' : '' ?>>
                                        <?= inventario_h($colegio['nom_colegio']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Usuario asignado</label>
                            <select name="id_usuario_asignado" class="form-select">
                                <option value="0">Sin asignar</option>
                                <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= (int)$usuario['id'] ?>" <?= (int)($equipo['id_usuario_asignado'] ?? 0) === (int)$usuario['id'] ? 'selected' : '' ?>>
                                        <?= inventario_h($usuario['nombre_completo']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <select name="id_estado" class="form-select" required>
                                <?php foreach ($estados as $estado): ?>
                                    <option value="<?= (int)$estado['id_estado'] ?>" <?= (int)($equipo['id_estado'] ?? 1) === (int)$estado['id_estado'] ? 'selected' : '' ?>>
                                        <?= inventario_h($estado['nombre_estado']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nombre equipo</label>
                            <input type="text" name="nombre_equipo" class="form-control" value="<?= inventario_h($equipo['nombre_equipo'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Fabricante</label>
                            <input type="text" name="fabricante" class="form-control" value="<?= inventario_h($equipo['fabricante'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Producto / modelo</label>
                            <input type="text" name="producto" class="form-control" value="<?= inventario_h($equipo['producto'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Numero de serie</label>
                            <input type="text" name="numero_serie" class="form-control" value="<?= inventario_h($equipo['numero_serie'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tipo de PC</label>
                            <select name="tipo_pc" class="form-select">
                                <option value="">Seleccione</option>
                                <?php foreach ($tiposPc as $tipo): ?>
                                    <option value="<?= inventario_h($tipo) ?>" <?= ($equipo['tipo_pc'] ?? '') === $tipo ? 'selected' : '' ?>><?= inventario_h($tipo) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">QR / codigo interno</label>
                            <input type="text" name="qr_code" class="form-control" value="<?= inventario_h($equipo['qr_code'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosCompra">Compra y adquisicion</button></h2>
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

        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosHardware">Almacenamiento, procesador y software</button></h2>
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

        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosRam">Memorias RAM</button></h2>
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

        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosMonitores">Monitores</button></h2>
            <div id="datosMonitores" class="accordion-collapse collapse" data-bs-parent="#accordionInventario">
                <div class="accordion-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">Monitores asociados</h6>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarMonitor">Agregar monitor</button>
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

        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosFotos">Fotos del equipo</button></h2>
            <div id="datosFotos" class="accordion-collapse collapse" data-bs-parent="#accordionInventario">
                <div class="accordion-body">
                    <label class="form-label">Fotos del equipo</label>
                    <input type="file" name="fotos_equipo[]" id="fotos_equipo" class="form-control" accept="image/*" multiple>
                    <div id="previewFotos" class="inv-preview-grid mt-3"></div>

                    <?php if (!empty($fotos)): ?>
                        <div class="inv-existing-gallery mt-3">
                            <?php foreach ($fotos as $foto): ?>
                                <label class="inv-photo-check">
                                    <img src="<?= inventario_h(inventario_url(ltrim($foto['ruta_foto'], '/'))) ?>" alt="Foto equipo">
                                    <span><input type="checkbox" name="fotos_eliminar[]" value="<?= (int)$foto['id_foto'] ?>"> Eliminar</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="index.php" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary"><?= $modo === 'editar' ? 'Actualizar equipo' : 'Guardar equipo' ?></button>
    </div>
</form>
