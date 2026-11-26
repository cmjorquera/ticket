<?php
/**
 * Apertura de página del Inventario de Herramientas con la plantilla del
 * sistema nuevo (includes/header.php: menú lateral, barra superior y tema).
 */
$pagina_titulo = $tituloPagina ?? 'Inventario de herramientas';
$pagina_bootstrap = true;
$pagina_bootstrap_js = true;
$pagina_sin_componentes = true;
$pagina_estilos = [
    'https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css',
    'inventario/herramientas/css/inventario.css?v=' . inventario_asset_version('css/inventario.css'),
    'inventario/herramientas/css/estilo_sistema.css?v=' . inventario_asset_version('css/estilo_sistema.css'),
];
require dirname(__DIR__, 3) . '/includes/header.php';
?>
<input type="hidden" id="idUsuario" value="<?= (int)$idUsuarioSession ?>">
<div class="inventario-body">
