<?php
require_once __DIR__ . '/componentes/boot.php';

$tituloPagina = 'Registrar producto de aseo';
$colegios = $inventario->obtenerColegios();
$categorias = $inventario->obtenerCategorias();
$unidades = $inventario->obtenerUnidades();
$modo = 'crear';

require __DIR__ . '/componentes/layout_top.php';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <h1 class="inv-title mb-2">Registrar producto de aseo</h1>
            <p class="inv-subtitle mb-0">Define el producto y registra de inmediato sus primeros ingresos o consumos.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="index.php" class="module-btn module-btn--secondary"><i class="bi bi-arrow-left"></i>Volver al inventario</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/formulario_producto.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
