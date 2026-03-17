<?php
$modo = $modo ?? 'crear';
$equipo = $equipo ?? [];
$detalle = $equipo['detalle'] ?? [];
$compra = $equipo['compra'] ?? [];
$prestamos = !empty($equipo['prestamos']) ? $equipo['prestamos'] : [[]];
$mantenciones = !empty($equipo['mantenciones']) ? $equipo['mantenciones'] : [[]];
$fotos = $equipo['fotos'] ?? [];
$action = $modo === 'editar' ? 'actualizar_herramienta.php' : 'guardar_herramienta.php';
?>
<form id="formInventario" action="<?= inventario_h($action) ?>" method="post" enctype="multipart/form-data" class="inv-form">
    <?php if ($modo === 'editar'): ?>
        <input type="hidden" name="id_herramienta" value="<?= (int)$equipo['id_herramienta'] ?>">
    <?php endif; ?>

    <div class="accordion inv-accordion" id="accordionInventario">
        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#datosGenerales">Datos generales</button></h2>
            <div id="datosGenerales" class="accordion-collapse collapse show" data-bs-parent="#accordionInventario"><div class="accordion-body"><div class="row g-3">
                <div class="col-md-4"><label class="form-label">Colegio</label><select name="id_colegio" class="form-select" required><option value="">Seleccione</option><?php foreach ($colegios as $colegio): ?><option value="<?= (int)$colegio['id_colegio'] ?>" <?= (int)($equipo['id_colegio'] ?? 0) === (int)$colegio['id_colegio'] ? 'selected' : '' ?>><?= inventario_h($colegio['nom_colegio']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Responsable actual</label><select name="id_usuario_asignado" class="form-select"><option value="0">Sin asignar</option><?php foreach ($usuarios as $usuario): ?><option value="<?= (int)$usuario['id'] ?>" <?= (int)($equipo['id_usuario_asignado'] ?? 0) === (int)$usuario['id'] ? 'selected' : '' ?>><?= inventario_h($usuario['nombre_completo']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Estado</label><select name="id_estado" class="form-select" required><?php foreach ($estados as $estado): ?><option value="<?= (int)$estado['id_estado'] ?>" <?= (int)($equipo['id_estado'] ?? 1) === (int)$estado['id_estado'] ? 'selected' : '' ?>><?= inventario_h($estado['nombre_estado']) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Nombre herramienta</label><input type="text" name="nombre_herramienta" class="form-control" value="<?= inventario_h($equipo['nombre_herramienta'] ?? '') ?>" required></div>
                <div class="col-md-4"><label class="form-label">Marca</label><input type="text" name="marca" class="form-control" value="<?= inventario_h($equipo['marca'] ?? '') ?>"></div>
                <div class="col-md-4"><label class="form-label">Modelo</label><input type="text" name="modelo" class="form-control" value="<?= inventario_h($equipo['modelo'] ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label">Numero de serie</label><input type="text" name="numero_serie" class="form-control" value="<?= inventario_h($equipo['numero_serie'] ?? '') ?>" required></div>
                <div class="col-md-3"><label class="form-label">Categoria</label><select name="categoria" class="form-select" required><option value="">Seleccione</option><?php foreach ($categorias as $categoria): ?><option value="<?= inventario_h($categoria) ?>" <?= ($equipo['categoria'] ?? '') === $categoria ? 'selected' : '' ?>><?= inventario_h($categoria) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-2"><label class="form-label">Cantidad</label><input type="number" min="1" name="cantidad" class="form-control" value="<?= inventario_h($equipo['cantidad'] ?? 1) ?>"></div>
                <div class="col-md-2"><label class="form-label">Stock minimo</label><input type="number" min="0" name="stock_minimo" class="form-control" value="<?= inventario_h($equipo['stock_minimo'] ?? 0) ?>"></div>
                <div class="col-md-2"><label class="form-label">QR / codigo</label><input type="text" name="qr_code" class="form-control" value="<?= inventario_h($equipo['qr_code'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Ubicacion fisica</label><input type="text" name="ubicacion" class="form-control" value="<?= inventario_h($equipo['ubicacion'] ?? '') ?>" placeholder="Ej.: Bodega central, taller movil, colegio, oficina..."></div>
                <div class="col-md-6"><label class="form-label">Observaciones</label><textarea name="observaciones" class="form-control" rows="2"><?= inventario_h($equipo['observaciones'] ?? '') ?></textarea></div>
            </div></div></div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosOperacion">Operacion, mantencion y compra</button></h2>
            <div id="datosOperacion" class="accordion-collapse collapse" data-bs-parent="#accordionInventario"><div class="accordion-body"><div class="row g-4">
                <div class="col-lg-4"><div class="inv-section-card"><h6>Detalle operativo</h6><label class="form-label">Tipo energia</label><input type="text" name="tipo_energia" class="form-control mb-2" value="<?= inventario_h($detalle['tipo_energia'] ?? '') ?>" placeholder="Manual, electrica, bateria, combustible"><label class="form-label">Medida</label><input type="text" name="medida" class="form-control mb-2" value="<?= inventario_h($detalle['medida'] ?? '') ?>" placeholder="Ej.: 18V, 10mm, 2m"><label class="form-label">Capacidad</label><input type="text" name="capacidad" class="form-control" value="<?= inventario_h($detalle['capacidad'] ?? '') ?>"></div></div>
                <div class="col-lg-4"><div class="inv-section-card"><h6>Mantencion</h6><div class="form-check mb-2"><input class="form-check-input" type="checkbox" value="1" id="requiere_mantencion" name="requiere_mantencion" <?= !empty($detalle['requiere_mantencion']) ? 'checked' : '' ?>><label class="form-check-label" for="requiere_mantencion">Requiere mantencion periodica</label></div><label class="form-label">Frecuencia en dias</label><input type="number" min="0" name="frecuencia_mantencion_dias" class="form-control mb-2" value="<?= inventario_h($detalle['frecuencia_mantencion_dias'] ?? 0) ?>"><label class="form-label">Ultima mantencion</label><input type="date" name="fecha_ultima_mantencion" class="form-control mb-2" value="<?= inventario_h($detalle['fecha_ultima_mantencion'] ?? '') ?>"><label class="form-label">Proxima mantencion</label><input type="date" name="fecha_proxima_mantencion" class="form-control mb-2" value="<?= inventario_h($detalle['fecha_proxima_mantencion'] ?? '') ?>"><label class="form-label">Garantia hasta</label><input type="date" name="garantia_hasta" class="form-control" value="<?= inventario_h($detalle['garantia_hasta'] ?? '') ?>"></div></div>
                <div class="col-lg-4"><div class="inv-section-card"><h6>Compra</h6><label class="form-label">Valor</label><input type="number" step="0.01" name="valor_herramienta" class="form-control mb-2" value="<?= inventario_h($compra['valor_herramienta'] ?? '') ?>"><label class="form-label">Proveedor</label><input type="text" name="proveedor" class="form-control mb-2" value="<?= inventario_h($compra['proveedor'] ?? '') ?>"><label class="form-label">Numero factura</label><input type="text" name="numero_factura" class="form-control mb-2" value="<?= inventario_h($compra['numero_factura'] ?? '') ?>"><label class="form-label">Fecha compra</label><input type="date" name="fecha_compra" class="form-control mb-2" value="<?= inventario_h($compra['fecha_compra'] ?? '') ?>"><label class="form-label">Observacion compra</label><textarea name="observacion_compra" class="form-control" rows="2"><?= inventario_h($compra['observacion'] ?? '') ?></textarea></div></div>
            </div></div></div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosPrestamos">Prestamos</button></h2>
            <div id="datosPrestamos" class="accordion-collapse collapse" data-bs-parent="#accordionInventario"><div class="accordion-body">
                <div class="d-flex justify-content-between align-items-center mb-3"><h6 class="mb-0">Historial de prestamos</h6><button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarPrestamo">Agregar prestamo</button></div>
                <div id="contenedorPrestamos"><?php foreach ($prestamos as $index => $prestamo): ?><div class="inv-repeat-card prestamo-item"><div class="row g-3"><div class="col-md-3"><label class="form-label">Entrega</label><input type="text" name="prestamo[<?= $index ?>][responsable_entrega]" class="form-control" value="<?= inventario_h($prestamo['responsable_entrega'] ?? '') ?>"></div><div class="col-md-3"><label class="form-label">Recibe</label><input type="text" name="prestamo[<?= $index ?>][responsable_recibe]" class="form-control" value="<?= inventario_h($prestamo['responsable_recibe'] ?? '') ?>"></div><div class="col-md-2"><label class="form-label">Salida</label><input type="date" name="prestamo[<?= $index ?>][fecha_salida]" class="form-control" value="<?= inventario_h($prestamo['fecha_salida'] ?? '') ?>"></div><div class="col-md-2"><label class="form-label">Devolucion prevista</label><input type="date" name="prestamo[<?= $index ?>][fecha_devolucion_prevista]" class="form-control" value="<?= inventario_h($prestamo['fecha_devolucion_prevista'] ?? '') ?>"></div><div class="col-md-2"><label class="form-label">Devolucion real</label><input type="date" name="prestamo[<?= $index ?>][fecha_devolucion_real]" class="form-control" value="<?= inventario_h($prestamo['fecha_devolucion_real'] ?? '') ?>"></div><div class="col-md-3"><label class="form-label">Estado prestamo</label><input type="text" name="prestamo[<?= $index ?>][estado_prestamo]" class="form-control" value="<?= inventario_h($prestamo['estado_prestamo'] ?? '') ?>" placeholder="Prestada, devuelta, retrasada..."></div><div class="col-md-9"><label class="form-label">Observacion</label><input type="text" name="prestamo[<?= $index ?>][observacion]" class="form-control" value="<?= inventario_h($prestamo['observacion'] ?? '') ?>"></div></div><button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button></div><?php endforeach; ?></div>
            </div></div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosMantenciones">Mantenciones</button></h2>
            <div id="datosMantenciones" class="accordion-collapse collapse" data-bs-parent="#accordionInventario"><div class="accordion-body">
                <div class="d-flex justify-content-between align-items-center mb-3"><h6 class="mb-0">Historial de mantenciones</h6><button type="button" class="btn btn-outline-primary btn-sm" id="btnAgregarMantencion">Agregar mantencion</button></div>
                <div id="contenedorMantenciones"><?php foreach ($mantenciones as $index => $mantencion): ?><div class="inv-repeat-card mantencion-item"><div class="row g-3"><div class="col-md-2"><label class="form-label">Fecha</label><input type="date" name="mantencion[<?= $index ?>][fecha_mantencion]" class="form-control" value="<?= inventario_h($mantencion['fecha_mantencion'] ?? '') ?>"></div><div class="col-md-3"><label class="form-label">Tipo</label><input type="text" name="mantencion[<?= $index ?>][tipo_mantencion]" class="form-control" value="<?= inventario_h($mantencion['tipo_mantencion'] ?? '') ?>"></div><div class="col-md-3"><label class="form-label">Proveedor o tecnico</label><input type="text" name="mantencion[<?= $index ?>][proveedor]" class="form-control" value="<?= inventario_h($mantencion['proveedor'] ?? '') ?>"></div><div class="col-md-2"><label class="form-label">Costo</label><input type="number" step="0.01" name="mantencion[<?= $index ?>][costo]" class="form-control" value="<?= inventario_h($mantencion['costo'] ?? '') ?>"></div><div class="col-md-2"><label class="form-label">Proxima fecha</label><input type="date" name="mantencion[<?= $index ?>][proxima_fecha]" class="form-control" value="<?= inventario_h($mantencion['proxima_fecha'] ?? '') ?>"></div><div class="col-md-12"><label class="form-label">Observacion</label><input type="text" name="mantencion[<?= $index ?>][observacion]" class="form-control" value="<?= inventario_h($mantencion['observacion'] ?? '') ?>"></div></div><button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button></div><?php endforeach; ?></div>
            </div></div>
        </div>

        <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#datosFotos">Fotos</button></h2>
            <div id="datosFotos" class="accordion-collapse collapse" data-bs-parent="#accordionInventario"><div class="accordion-body">
                <label class="form-label">Fotos de la herramienta</label>
                <input type="file" name="fotos_herramienta[]" id="fotos_equipo" class="form-control" accept="image/*" multiple>
                <div id="previewFotos" class="inv-preview-grid mt-3"></div>
                <?php if (!empty($fotos)): ?><div class="inv-existing-gallery mt-3"><?php foreach ($fotos as $foto): ?><label class="inv-photo-check"><img src="<?= inventario_h(inventario_url(ltrim($foto['ruta_foto'], '/'))) ?>" alt="Foto herramienta"><span><input type="checkbox" name="fotos_eliminar[]" value="<?= (int)$foto['id_foto'] ?>"> Eliminar</span></label><?php endforeach; ?></div><?php endif; ?>
            </div></div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4"><a href="index.php" class="btn btn-light border">Volver</a><button type="submit" class="btn btn-primary"><?= $modo === 'editar' ? 'Actualizar herramienta' : 'Guardar herramienta' ?></button></div>
</form>
