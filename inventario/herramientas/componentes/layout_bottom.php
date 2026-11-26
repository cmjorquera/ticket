<?php
/**
 * Cierre de página del Inventario de Herramientas: scripts en el orden
 * que necesitan (jQuery → DataTables → SweetAlert → QR → módulo).
 */
?>
</div><!-- /.inventario-body -->
<?php
$pagina_scripts = [
    'https://code.jquery.com/jquery-3.7.1.min.js',
    'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js',
    'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js',
    'inventario/herramientas/js/inventario.js?v=' . inventario_asset_version('js/inventario.js'),
];
require dirname(__DIR__, 3) . '/includes/footer.php';
