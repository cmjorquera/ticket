<?php
/**
 * Cierre de página de Licenciamiento: scripts en el orden que necesitan
 * (jQuery → DataTables → SweetAlert → QR → Chart.js → módulo).
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
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
    'licenciamiento/js/inventario.js?v=' . inventario_asset_version('js/inventario.js'),
];
require dirname(__DIR__, 2) . '/includes/footer.php';
