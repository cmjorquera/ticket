<?php
require_once __DIR__ . '/componentes/boot.php';

$tituloPagina = 'Registrar producto de aseo';
$colegios = $inventario->obtenerColegios();
$categorias = $inventario->obtenerCategorias();
$unidades = $inventario->obtenerUnidades();
$modo = 'crear';

require __DIR__ . '/componentes/layout_top.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Registrar producto de aseo</h1>
        <p class="text-muted mb-0">Define el producto y registra de inmediato sus primeros ingresos o consumos.</p>
    </div>
    <a href="index.php" class="btn btn-light border">Volver</a>
</div>
<?php require __DIR__ . '/componentes/formulario_producto.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
