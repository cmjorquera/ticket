<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $tituloPagina = 'Reportes de aseo';
    $resumen = $inventario->obtenerResumen(['activo' => 1]);
    $bajoMinimo = $inventario->obtenerProductosBajoMinimo(20);
    $movimientos = $inventario->obtenerMovimientosRecientes(40);
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar los reportes de aseo: ' . $e->getMessage());
}

require __DIR__ . '/componentes/layout_top.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Reportes de aseo</h1>
        <p class="text-muted mb-0">Visualiza estado del stock, productos criticos y movimientos recientes.</p>
    </div>
    <a href="index.php" class="btn btn-light border">Volver</a>
</div>

<?php require __DIR__ . '/componentes/resumen.php'; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 inv-panel h-100">
            <div class="card-body">
                <h5 class="mb-3">Productos bajo minimo</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Colegio</th>
                                <th>Stock</th>
                                <th>Min.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($bajoMinimo)): ?>
                                <?php foreach ($bajoMinimo as $item): ?>
                                    <tr>
                                        <td><?= inventario_h($item['nombre_producto']) ?></td>
                                        <td><?= inventario_h($item['nom_colegio']) ?></td>
                                        <td><?= inventario_h($item['stock_actual']) ?> <?= inventario_h($item['unidad_medida']) ?></td>
                                        <td><?= inventario_h($item['stock_minimo']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted">No hay productos criticos.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 inv-panel h-100">
            <div class="card-body">
                <h5 class="mb-3">Movimientos recientes</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Colegio</th>
                                <th>Responsable</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($movimientos)): ?>
                                <?php foreach ($movimientos as $movimiento): ?>
                                    <tr>
                                        <td><?= inventario_h($movimiento['fecha_movimiento']) ?></td>
                                        <td><?= inventario_h($movimiento['nombre_producto']) ?></td>
                                        <td><?= inventario_h(ucfirst($movimiento['tipo_movimiento'])) ?></td>
                                        <td><?= inventario_h($movimiento['cantidad']) ?> <?= inventario_h($movimiento['unidad_medida']) ?></td>
                                        <td><?= inventario_h($movimiento['nom_colegio']) ?></td>
                                        <td><?= inventario_h($movimiento['responsable']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center text-muted">No hay movimientos registrados.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
