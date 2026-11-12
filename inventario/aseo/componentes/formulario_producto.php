<?php
$modo = $modo ?? 'crear';
$producto = $producto ?? [];
$movimientos = [[]];
$action = $modo === 'editar' ? 'actualizar_producto.php' : 'guardar_producto.php';
?>

<form id="formInventario" action="<?= inventario_h($action) ?>" method="post" class="inv-form">
    <?php if ($modo === 'editar'): ?>
        <input type="hidden" name="id_producto" value="<?= (int) $producto['id_producto'] ?>">
    <?php endif; ?>

    <div class="accordion inv-accordion" id="accordionInventarioAseo">
        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#datosProducto">Producto</button></h2>
            <div id="datosProducto" class="accordion-collapse collapse show" data-bs-parent="#accordionInventarioAseo">
                <div class="accordion-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Colegio</label>
                            <select name="id_colegio" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($colegios as $colegio): ?>
                                    <option value="<?= (int) $colegio['id_colegio'] ?>" <?= (int) ($producto['id_colegio'] ?? 0) === (int) $colegio['id_colegio'] ? 'selected' : '' ?>><?= inventario_h($colegio['nom_colegio']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Nombre producto</label>
                            <input type="text" name="nombre_producto" class="form-control" value="<?= inventario_h($producto['nombre_producto'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Categoria</label>
                            <select name="categoria" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($categorias as $categoria): ?>
                                    <option value="<?= inventario_h($categoria) ?>" <?= ($producto['categoria'] ?? '') === $categoria ? 'selected' : '' ?>><?= inventario_h($categoria) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unidad de medida</label>
                            <select name="unidad_medida" class="form-select" required>
                                <option value="">Seleccione</option>
                                <?php foreach ($unidades as $unidad): ?>
                                    <option value="<?= inventario_h($unidad) ?>" <?= ($producto['unidad_medida'] ?? '') === $unidad ? 'selected' : '' ?>><?= inventario_h($unidad) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Stock minimo</label>
                            <input type="number" step="0.01" min="0" name="stock_minimo" class="form-control" value="<?= inventario_h($producto['stock_minimo'] ?? 0) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado</label>
                            <select name="activo" class="form-select">
                                <option value="1" <?= (int) ($producto['activo'] ?? 1) === 1 ? 'selected' : '' ?>>Activo</option>
                                <option value="0" <?= isset($producto['activo']) && (int) $producto['activo'] === 0 ? 'selected' : '' ?>>Inactivo</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Descripcion</label>
                            <textarea name="descripcion" class="form-control" rows="3"><?= inventario_h($producto['descripcion'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosMovimientos">Movimientos</button></h2>
            <div id="datosMovimientos" class="accordion-collapse collapse" data-bs-parent="#accordionInventarioAseo">
                <div class="accordion-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">Ingresos y salidas</h6>
                            <small class="text-muted">Registra reposiciones y consumos para calcular el stock real.</small>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarMovimiento">Agregar movimiento</button>
                    </div>
                    <?php if ($modo === 'editar'): ?>
                        <div class="alert alert-light border mb-3">Los movimientos ya registrados se consultan en la ficha del producto. Aqui solo agregas movimientos nuevos.</div>
                    <?php endif; ?>
                    <div id="contenedorMovimientos">
                        <?php foreach ($movimientos as $index => $movimiento): ?>
                            <div class="inv-repeat-card movimiento-item">
                                <div class="row g-3">
                                    <div class="col-md-2">
                                        <label class="form-label">Tipo</label>
                                        <select name="movimiento[<?= $index ?>][tipo_movimiento]" class="form-select">
                                            <option value="">Seleccione</option>
                                            <option value="ingreso" <?= ($movimiento['tipo_movimiento'] ?? '') === 'ingreso' ? 'selected' : '' ?>>Ingreso</option>
                                            <option value="salida" <?= ($movimiento['tipo_movimiento'] ?? '') === 'salida' ? 'selected' : '' ?>>Salida</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Cantidad</label>
                                        <input type="number" step="0.01" min="0" name="movimiento[<?= $index ?>][cantidad]" class="form-control" value="<?= inventario_h($movimiento['cantidad'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Fecha y hora</label>
                                        <input type="datetime-local" name="movimiento[<?= $index ?>][fecha_movimiento]" class="form-control" value="<?= inventario_h(isset($movimiento['fecha_movimiento']) ? str_replace(' ', 'T', substr($movimiento['fecha_movimiento'], 0, 16)) : '') ?>">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Responsable</label>
                                        <input type="text" name="movimiento[<?= $index ?>][responsable]" class="form-control" value="<?= inventario_h($movimiento['responsable'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Observacion</label>
                                        <input type="text" name="movimiento[<?= $index ?>][observacion]" class="form-control" value="<?= inventario_h($movimiento['observacion'] ?? '') ?>">
                                    </div>
                                </div>
                                <button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="index.php" class="btn btn-light border">Volver</a>
        <button type="submit" class="btn btn-primary"><?= $modo === 'editar' ? 'Actualizar producto' : 'Guardar producto' ?></button>
    </div>
</form>
