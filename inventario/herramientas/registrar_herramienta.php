<?php
require_once __DIR__ . '/componentes/boot.php';
$tituloPagina = 'Registrar herramienta';
$colegios = $inventario->obtenerColegios();
$usuarios = $inventario->obtenerUsuarios();
$estados = $inventario->obtenerEstados();
$categorias = $inventario->obtenerCategorias();
$modo = 'crear';
require __DIR__ . '/componentes/layout_top.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 mb-1">Registrar herramienta</h1><p class="text-muted mb-0">Crea una ficha operativa con stock, mantencion, prestamos y fotos.</p></div><a href="index.php" class="btn btn-light border">Volver</a></div>
<?php require __DIR__ . '/componentes/formulario_herramienta.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
