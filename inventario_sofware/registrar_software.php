<?php
require_once __DIR__ . '/componentes/boot.php';
$tituloPagina = 'Registrar software';
$colegios = $inventario->obtenerColegios();
$usuarios = $inventario->obtenerUsuarios();
$tiposLicenciamiento = $inventario->obtenerTiposLicenciamiento();
$pagadores = $inventario->obtenerPagadores();
$monedas = $inventario->obtenerMonedas();
$datosSensiblesCatalogo = $inventario->obtenerDatosSensiblesCatalogo();
$modo = 'crear';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 mb-1">Registrar software</h1><p class="text-muted mb-0">Crea un registro de licencias, costo, responsable y cuentas asociadas.</p></div><a href="index.php" class="btn btn-light border">Volver</a></div>
<?php require __DIR__ . '/componentes/formulario_software.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
