<?php
require_once __DIR__ . '/componentes/boot.php';

$idProducto = (int) ($_GET['id_producto'] ?? 0);
$producto = $inventario->obtenerProductoCompleto($idProducto);
if (!$producto) {
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Ficha producto de aseo';
require __DIR__ . '/componentes/layout_top.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= inventario_h($producto['nombre_producto']) ?></h1>
        <p class="text-muted mb-0"><?= inventario_h($producto['nom_colegio']) ?> | <?= inventario_h($producto['categoria']) ?> | <?= inventario_h($producto['unidad_medida']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="editar_producto.php?id_producto=<?= (int) $producto['id_producto'] ?>" class="btn btn-outline-primary">Editar</a>
        <a href="index.php" class="btn btn-light border">Volver</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><span class="text-muted d-block small">Colegio</span><strong><?= inventario_h($producto['nom_colegio']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Categoria</span><strong><?= inventario_h($producto['categoria']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Unidad</span><strong><?= inventario_h($producto['unidad_medida']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Stock actual</span><strong><?= inventario_h($producto['stock_actual']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Stock minimo</span><strong><?= inventario_h($producto['stock_minimo']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Estado stock</span><strong><?= $inventario->renderBadgeStock((float) $producto['stock_actual'], (float) $producto['stock_minimo']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Ingresos mes</span><strong><?= inventario_h($producto['ingresos_mes']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Salidas mes</span><strong><?= inventario_h($producto['salidas_mes']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Ultimo movimiento</span><strong><?= inventario_h($producto['ultimo_movimiento'] ?: 'Sin movimientos') ?></strong></div>
                    <div class="col-md-12"><span class="text-muted d-block small">Descripcion</span><strong><?= nl2br(inventario_h($producto['descripcion'] ?: 'Sin descripcion')) ?></strong></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 inv-panel">
            <div class="card-body">
                <h5 class="mb-3">Historial de movimientos</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <th>Responsable</th>
                                <th>Registrado por</th>
                                <th>Observacion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($producto['movimientos'])): ?>
                                <?php foreach ($producto['movimientos'] as $movimiento): ?>
                                    <tr>
                                        <td><?= inventario_h(ucfirst($movimiento['tipo_movimiento'])) ?></td>
                                        <td><?= inventario_h($movimiento['cantidad']) ?></td>
                                        <td><?= inventario_h($movimiento['fecha_movimiento']) ?></td>
                                        <td><?= inventario_h($movimiento['responsable']) ?></td>
                                        <td><?= inventario_h($movimiento['usuario_nombre']) ?></td>
                                        <td><?= inventario_h($movimiento['observacion']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">Sin movimientos registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 inv-panel">
            <div class="card-body">
                <h5 class="mb-3">Resumen rapido</h5>
                <ul class="list-unstyled inv-list mb-0">
                    <li><span>Activo</span><strong><?= (int) $producto['activo'] === 1 ? 'Si' : 'No' ?></strong></li>
                    <li><span>Registrado por</span><strong><?= inventario_h($producto['usuario_registra']) ?></strong></li>
                    <li><span>Creado</span><strong><?= inventario_h($producto['creado_en']) ?></strong></li>
                    <li><span>Actualizado</span><strong><?= inventario_h($producto['actualizado_en']) ?></strong></li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
