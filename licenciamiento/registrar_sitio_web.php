<?php
require_once __DIR__ . '/componentes/boot.php';
$tituloPagina = 'Registrar sitio web';
$colegios = $inventario->obtenerColegios();
$usuarios = $inventario->obtenerUsuarios();
$tiposSitio = $inventario->obtenerTiposSitio();
$estadosSitio = $inventario->obtenerEstadosSitio();
$modo = 'crear';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 mb-1">Registrar sitio web</h1><p class="text-muted mb-0">Crea una ficha para web, app o cliente conectado a servidor.</p></div><a href="index.php" class="btn btn-secondary inv-system-btn">Volver</a></div>
<?php require __DIR__ . '/componentes/formulario_sitio_web.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
