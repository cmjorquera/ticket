<?php
/**
 * Inicio de página del módulo Inventario con el diseño del sistema nuevo:
 * menú lateral y cabecera de includes/header.php + Bootstrap 5 propio del módulo.
 */
$pagina_titulo = $tituloPagina ?? 'Inventario';
$pagina_bootstrap = true;
$pagina_bootstrap_js = true;
$pagina_sin_componentes = true;
$pagina_estilos = array_merge([
    'https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css',
    'inventario/css/inventario.css?v=' . inventario_asset_version('css/inventario.css'),
], array_map(
    static fn ($css) => 'inventario/' . ltrim((string) $css, '/') . '?v=' . inventario_asset_version($css),
    $cssExtraInventario ?? []
), [
    // Siempre al final: estructura visual del sistema nuevo para todo el módulo
    'inventario/css/estilo_sistema.css?v=' . inventario_asset_version('css/estilo_sistema.css'),
]);
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<input type="hidden" id="idUsuario" value="<?= (int) $idUsuarioSession ?>">
<div class="inventario-body">
