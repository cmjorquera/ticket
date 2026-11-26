<?php
require_once __DIR__ . '/componentes/boot.php';

$idProducto = (int) ($_GET['id_producto'] ?? 0);
$producto = $inventario->obtenerProductoCompleto($idProducto);
if (!$producto) {
    header('Location: index.php');
    exit;
}

/** Fecha 'Y-m-d H:i:s' → 'dd-mm-aaaa hh:mm' */
function aseo_fecha(?string $fecha, string $vacio = '—'): string
{
    if (!$fecha) {
        return $vacio;
    }
    $t = strtotime($fecha);
    return $t ? date('d-m-Y H:i', $t) : $fecha;
}

$tituloPagina = 'Ficha producto de aseo';
$unidad = (string) $producto['unidad_medida'];
require __DIR__ . '/componentes/layout_top.php';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <h1 class="inv-title mb-2"><?= inventario_h($producto['nombre_producto']) ?></h1>
            <p class="inv-subtitle mb-0"><?= inventario_h($producto['nom_colegio']) ?> · <?= inventario_h($producto['categoria']) ?> · <?= inventario_h($unidad) ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="editar_producto.php?id_producto=<?= (int) $producto['id_producto'] ?>" class="module-btn module-btn--primary"><i class="bi bi-pencil"></i>Editar</a>
            <a href="index.php" class="module-btn module-btn--secondary"><i class="bi bi-arrow-left"></i>Volver al inventario</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card inv-ficha-card mt-0">
            <div class="card-body">
                <div class="inv-card-head">
                    <div>
                        <h5 class="mb-1">Datos del producto</h5>
                        <p class="text-muted mb-0 small">Stock calculado desde los movimientos registrados.</p>
                    </div>
                    <?= $inventario->renderBadgeStock((float) $producto['stock_actual'], (float) $producto['stock_minimo']) ?>
                </div>
                <div class="inv-ficha-body">
                    <div class="row g-3">
                        <div class="col-md-4 inv-dato"><span>Colegio</span><strong><?= inventario_h($producto['nom_colegio']) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Categoría</span><strong><?= inventario_h($producto['categoria']) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Unidad</span><strong><?= inventario_h($unidad) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Stock actual</span><strong><?= inventario_num($producto['stock_actual']) ?> <?= inventario_h($unidad) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Stock mínimo</span><strong><?= inventario_num($producto['stock_minimo']) ?> <?= inventario_h($unidad) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Último movimiento</span><strong><?= inventario_h(aseo_fecha($producto['ultimo_movimiento'], 'Sin movimientos')) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Ingresos del mes</span><strong><?= inventario_num($producto['ingresos_mes']) ?></strong></div>
                        <div class="col-md-4 inv-dato"><span>Salidas del mes</span><strong><?= inventario_num($producto['salidas_mes']) ?></strong></div>
                        <div class="col-12 inv-dato"><span>Descripción</span><strong><?= nl2br(inventario_h($producto['descripcion'] ?: 'Sin descripción')) ?></strong></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card inv-ficha-card">
            <div class="card-body">
                <div class="inv-card-head">
                    <div>
                        <h5 class="mb-1">Historial de movimientos</h5>
                        <p class="text-muted mb-0 small">Ingresos y salidas del producto, del más reciente al más antiguo.</p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr><th>Tipo</th><th>Cantidad</th><th>Fecha</th><th>Responsable</th><th>Registrado por</th><th>Observación</th></tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($producto['movimientos'])): ?>
                                <?php foreach ($producto['movimientos'] as $movimiento): ?>
                                    <?php $tipo = (string) $movimiento['tipo_movimiento']; ?>
                                    <tr>
                                        <td><span class="inv-mov inv-mov--<?= $tipo === 'ingreso' ? 'ingreso' : 'salida' ?>"><i class="bi bi-arrow-<?= $tipo === 'ingreso' ? 'down' : 'up' ?>-circle"></i><?= inventario_h(ucfirst($tipo)) ?></span></td>
                                        <td><?= inventario_num($movimiento['cantidad']) ?></td>
                                        <td><?= inventario_h(aseo_fecha($movimiento['fecha_movimiento'])) ?></td>
                                        <td><?= inventario_h($movimiento['responsable'] ?: '—') ?></td>
                                        <td><?= inventario_h($movimiento['usuario_nombre'] ?: '—') ?></td>
                                        <td><?= inventario_h($movimiento['observacion'] ?: '—') ?></td>
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
        <div class="card inv-ficha-card mt-0">
            <div class="card-body">
                <div class="inv-card-head"><div><h5 class="mb-1">Resumen rápido</h5></div></div>
                <div class="inv-ficha-body">
                    <ul class="list-unstyled inv-list mb-0">
                        <li><span>Activo</span><strong><?= (int) $producto['activo'] === 1 ? 'Sí' : 'No' ?></strong></li>
                        <li><span>Registrado por</span><strong><?= inventario_h($producto['usuario_registra'] ?: '—') ?></strong></li>
                        <li><span>Creado</span><strong><?= inventario_h(aseo_fecha($producto['creado_en'])) ?></strong></li>
                        <li><span>Actualizado</span><strong><?= inventario_h(aseo_fecha($producto['actualizado_en'])) ?></strong></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
