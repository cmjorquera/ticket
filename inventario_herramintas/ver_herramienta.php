<?php
require_once __DIR__ . '/componentes/boot.php';
$idHerramienta = (int)($_GET['id_herramienta'] ?? 0);
$equipo = $inventario->obtenerHerramientaCompleta($idHerramienta);
if (!$equipo) { header('Location: index.php'); exit; }
$tituloPagina = 'Ficha tecnica herramienta';
require __DIR__ . '/componentes/layout_top.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= inventario_h($equipo['nombre_herramienta']) ?></h1>
        <p class="text-muted mb-0"><?= inventario_h($equipo['nom_colegio']) ?> | <?= inventario_h($equipo['categoria']) ?> | Serie <?= inventario_h($equipo['numero_serie']) ?></p>
    </div>
    <div class="d-flex gap-2"><a href="editar_herramienta.php?id_herramienta=<?= (int)$equipo['id_herramienta'] ?>" class="btn btn-outline-primary">Editar</a><a href="index.php" class="btn btn-light border">Volver</a></div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 inv-panel mb-4"><div class="card-body"><div class="row g-3">
            <div class="col-md-4"><span class="text-muted d-block small">Colegio</span><strong><?= inventario_h($equipo['nom_colegio']) ?></strong></div>
            <div class="col-md-4"><span class="text-muted d-block small">Marca</span><strong><?= inventario_h($equipo['marca']) ?></strong></div>
            <div class="col-md-4"><span class="text-muted d-block small">Modelo</span><strong><?= inventario_h($equipo['modelo']) ?></strong></div>
            <div class="col-md-4"><span class="text-muted d-block small">Estado</span><strong><?= $inventario->renderBadgeEstado($equipo['id_estado'], $equipo['nombre_estado'], $equipo['color_badge']) ?></strong></div>
            <div class="col-md-4"><span class="text-muted d-block small">Responsable</span><strong><?= inventario_h($equipo['usuario_asignado'] ?: 'Sin asignar') ?></strong></div>
            <div class="col-md-4"><span class="text-muted d-block small">Registrado por</span><strong><?= inventario_h($equipo['usuario_registra']) ?></strong></div>
            <div class="col-md-3"><span class="text-muted d-block small">Cantidad</span><strong><?= (int)($equipo['cantidad'] ?? 0) ?></strong></div>
            <div class="col-md-3"><span class="text-muted d-block small">Stock minimo</span><strong><?= (int)($equipo['stock_minimo'] ?? 0) ?></strong></div>
            <div class="col-md-3"><span class="text-muted d-block small">Ubicacion</span><strong><?= inventario_h($equipo['ubicacion'] ?: '-') ?></strong></div>
            <div class="col-md-3"><span class="text-muted d-block small">QR</span><strong><?= inventario_h($equipo['qr_code']) ?></strong></div>
        </div></div></div>

        <div class="card shadow-sm border-0 inv-panel mb-4"><div class="card-body"><h5 class="mb-3">Ficha operativa</h5><div class="row g-4">
            <div class="col-md-6"><h6>Detalle</h6><ul class="list-unstyled inv-list"><li><span>Categoria</span><strong><?= inventario_h($equipo['categoria'] ?? '-') ?></strong></li><li><span>Tipo energia</span><strong><?= inventario_h($equipo['detalle']['tipo_energia'] ?? '-') ?></strong></li><li><span>Medida</span><strong><?= inventario_h($equipo['detalle']['medida'] ?? '-') ?></strong></li><li><span>Capacidad</span><strong><?= inventario_h($equipo['detalle']['capacidad'] ?? '-') ?></strong></li></ul></div>
            <div class="col-md-6"><h6>Mantencion</h6><ul class="list-unstyled inv-list"><li><span>Requiere mantencion</span><strong><?= !empty($equipo['detalle']['requiere_mantencion']) ? 'Si' : 'No' ?></strong></li><li><span>Frecuencia</span><strong><?= inventario_h($equipo['detalle']['frecuencia_mantencion_dias'] ?? 0) ?> dias</strong></li><li><span>Ultima mantencion</span><strong><?= inventario_h($equipo['detalle']['fecha_ultima_mantencion'] ?? '-') ?></strong></li><li><span>Proxima mantencion</span><strong><?= inventario_h($equipo['detalle']['fecha_proxima_mantencion'] ?? '-') ?></strong></li><li><span>Garantia</span><strong><?= inventario_h($equipo['detalle']['garantia_hasta'] ?? '-') ?></strong></li></ul></div>
            <div class="col-md-6"><h6>Compra</h6><ul class="list-unstyled inv-list"><li><span>Proveedor</span><strong><?= inventario_h($equipo['compra']['proveedor'] ?? '-') ?></strong></li><li><span>Factura</span><strong><?= inventario_h($equipo['compra']['numero_factura'] ?? '-') ?></strong></li><li><span>Fecha compra</span><strong><?= inventario_h($equipo['compra']['fecha_compra'] ?? '-') ?></strong></li><li><span>Valor</span><strong>$<?= number_format((float)($equipo['compra']['valor_herramienta'] ?? 0), 0, ',', '.') ?></strong></li></ul></div>
            <div class="col-md-6"><h6>Observaciones</h6><p class="mb-0 text-muted"><?= nl2br(inventario_h($equipo['observaciones'] ?? 'Sin observaciones.')) ?></p></div>
        </div></div></div>

        <div class="card shadow-sm border-0 inv-panel mb-4"><div class="card-body"><h5 class="mb-3">Prestamos</h5><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Entrega</th><th>Recibe</th><th>Salida</th><th>Prevista</th><th>Real</th><th>Estado</th></tr></thead><tbody><?php if (!empty($equipo['prestamos'])): foreach ($equipo['prestamos'] as $prestamo): ?><tr><td><?= inventario_h($prestamo['responsable_entrega']) ?></td><td><?= inventario_h($prestamo['responsable_recibe']) ?></td><td><?= inventario_h($prestamo['fecha_salida']) ?></td><td><?= inventario_h($prestamo['fecha_devolucion_prevista']) ?></td><td><?= inventario_h($prestamo['fecha_devolucion_real']) ?></td><td><?= inventario_h($prestamo['estado_prestamo']) ?></td></tr><?php endforeach; else: ?><tr><td colspan="6" class="text-center text-muted">Sin prestamos registrados.</td></tr><?php endif; ?></tbody></table></div></div></div>

        <div class="card shadow-sm border-0 inv-panel mb-4"><div class="card-body"><h5 class="mb-3">Mantenciones</h5><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Fecha</th><th>Tipo</th><th>Proveedor</th><th>Costo</th><th>Proxima</th><th>Observacion</th></tr></thead><tbody><?php if (!empty($equipo['mantenciones'])): foreach ($equipo['mantenciones'] as $mantencion): ?><tr><td><?= inventario_h($mantencion['fecha_mantencion']) ?></td><td><?= inventario_h($mantencion['tipo_mantencion']) ?></td><td><?= inventario_h($mantencion['proveedor']) ?></td><td>$<?= number_format((float)($mantencion['costo'] ?? 0), 0, ',', '.') ?></td><td><?= inventario_h($mantencion['proxima_fecha']) ?></td><td><?= inventario_h($mantencion['observacion']) ?></td></tr><?php endforeach; else: ?><tr><td colspan="6" class="text-center text-muted">Sin mantenciones registradas.</td></tr><?php endif; ?></tbody></table></div></div></div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 inv-panel mb-4"><div class="card-body"><h5 class="mb-3">QR de la herramienta</h5><div id="qrEquipo" class="d-flex justify-content-center"></div></div></div>
        <div class="card shadow-sm border-0 inv-panel"><div class="card-body"><h5 class="mb-3">Galeria</h5><div class="inv-gallery"><?php if (!empty($equipo['fotos'])): foreach ($equipo['fotos'] as $foto): ?><a href="<?= inventario_h(inventario_url(ltrim($foto['ruta_foto'], '/'))) ?>" target="_blank" class="inv-gallery-item"><img src="<?= inventario_h(inventario_url(ltrim($foto['ruta_foto'], '/'))) ?>" alt="Foto herramienta"></a><?php endforeach; else: ?><div class="alert alert-light border mb-0">Sin fotos cargadas.</div><?php endif; ?></div></div></div>
    </div>
</div>
<script>
window.INVENTARIO_CONFIG = window.INVENTARIO_CONFIG || {};
window.INVENTARIO_CONFIG.qrText = <?= json_encode($equipo['qr_code']) ?>;
</script>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
