<?php
/** Apertura de página de Estadística con la plantilla del sistema nuevo. */
$pagina_titulo = $tituloPagina ?? 'Estadística';
$pagina_bootstrap = true;
$pagina_bootstrap_js = true;
$pagina_sin_componentes = true;
$pagina_estilos = array_merge(
    !empty($usaDataTables) ? ['https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css'] : [],
    [
        'estadistica/css/inventario.css?v=' . est_asset_version('css/inventario.css'),
        'estadistica/css/estilo_sistema.css?v=' . est_asset_version('css/estilo_sistema.css'),
        'estadistica/css/estadistica.css?v=' . est_asset_version('css/estadistica.css'),
    ]
);
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="inventario-body est-body">
