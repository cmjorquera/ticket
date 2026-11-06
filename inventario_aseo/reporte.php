<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $tituloPagina = 'Reportes de aseo';
    $resumen = $inventario->obtenerResumen(['activo' => 1]);
    $bajoMinimo = $inventario->obtenerProductosBajoMinimo(20);
    $movimientos = $inventario->obtenerMovimientosRecientes(40);
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar los reportes de aseo: ' . inventario_mensaje_error($e));
}

require __DIR__ . '/componentes/layout_top.php';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <h1 class="inv-title mb-2">Reportes de aseo</h1>
            <p class="inv-subtitle mb-0">Visualiza el estado del stock, los productos críticos y los movimientos recientes.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="index.php" class="module-btn module-btn--secondary"><i class="bi bi-arrow-left"></i>Volver al inventario</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/componentes/resumen.php'; ?>

<?php
function aseo_fecha_corta(?string $fecha): string
{
    $t = $fecha ? strtotime($fecha) : false;
    return $t ? date('d-m-Y H:i', $t) : '—';
}
?>
<div class="row g-4">
    <div class="col-lg-5">
        <div class="card inv-ficha-card h-100">
            <div class="card-body">
                <div class="inv-card-head">
                    <div>
                        <h5 class="mb-1">Productos bajo mínimo</h5>
                        <p class="text-muted mb-0 small">Productos activos que hay que reponer.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Producto</th><th>Colegio</th><th>Stock</th><th>Mín.</th></tr></thead>
                        <tbody>
                            <?php if (!empty($bajoMinimo)): ?>
                                <?php foreach ($bajoMinimo as $item): ?>
                                    <tr>
                                        <td><?= inventario_h($item['nombre_producto']) ?></td>
                                        <td><?= inventario_h($item['nom_colegio']) ?></td>
                                        <td><?= inventario_num($item['stock_actual']) ?> <?= inventario_h($item['unidad_medida']) ?></td>
                                        <td><?= inventario_num($item['stock_minimo']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center text-muted">No hay productos críticos.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card inv-ficha-card h-100">
            <div class="card-body">
                <div class="inv-card-head">
                    <div>
                        <h5 class="mb-1">Movimientos recientes</h5>
                        <p class="text-muted mb-0 small">Últimos 40 ingresos y salidas de todos los colegios.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Colegio</th><th>Responsable</th></tr></thead>
                        <tbody>
                            <?php if (!empty($movimientos)): ?>
                                <?php foreach ($movimientos as $movimiento): ?>
                                    <?php $tipo = (string) $movimiento['tipo_movimiento']; ?>
                                    <tr>
                                        <td><?= inventario_h(aseo_fecha_corta($movimiento['fecha_movimiento'])) ?></td>
                                        <td><?= inventario_h($movimiento['nombre_producto']) ?></td>
                                        <td><span class="inv-mov inv-mov--<?= $tipo === 'ingreso' ? 'ingreso' : 'salida' ?>"><?= inventario_h(ucfirst($tipo)) ?></span></td>
                                        <td><?= inventario_num($movimiento['cantidad']) ?> <?= inventario_h($movimiento['unidad_medida']) ?></td>
                                        <td><?= inventario_h($movimiento['nom_colegio']) ?></td>
                                        <td><?= inventario_h($movimiento['responsable'] ?: '—') ?></td>
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
