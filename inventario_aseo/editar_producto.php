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
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Editar producto #<?= (int) $producto['id_producto'] ?></h1>
        <p class="text-muted mb-0"><?= inventario_h($producto['nombre_producto']) ?> · <?= inventario_h($producto['categoria']) ?></p>
    </div>
    <a href="ver_producto.php?id_producto=<?= (int) $producto['id_producto'] ?>" class="btn btn-outline-primary">Ver ficha</a>
</div>
<?php require __DIR__ . '/componentes/formulario_producto.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
