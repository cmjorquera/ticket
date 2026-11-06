<?php
require_once __DIR__ . '/componentes/boot.php';

$idProducto = (int) ($_GET['id_producto'] ?? 0);
$producto = $inventario->obtenerProductoCompleto($idProducto);
if (!$producto) {
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Editar producto de aseo';
$colegios = $inventario->obtenerColegios();
$categorias = $inventario->obtenerCategorias();
$unidades = $inventario->obtenerUnidades();
$modo = 'editar';

require __DIR__ . '/componentes/layout_top.php';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <h1 class="inv-title mb-2">Editar producto #<?= (int) $producto['id_producto'] ?></h1>
            <p class="inv-subtitle mb-0"><?= inventario_h($producto['nombre_producto']) ?> · <?= inventario_h($producto['categoria']) ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="ver_producto.php?id_producto=<?= (int) $producto['id_producto'] ?>" class="module-btn module-btn--secondary"><i class="bi bi-eye"></i>Ver ficha</a>
            <a href="index.php" class="module-btn module-btn--secondary"><i class="bi bi-arrow-left"></i>Volver al inventario</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/formulario_producto.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
