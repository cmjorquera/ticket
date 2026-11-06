<?php
/** Cierre de página: librerías que pidió la página y el JS propio. */
?>
</div><!-- /.est-body -->
<?php
$pagina_scripts = array_merge(
    ['https://code.jquery.com/jquery-3.7.1.min.js'],
    !empty($usaDataTables) ? [
        'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js',
        'https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js',
    ] : [],
    !empty($usaGraficos) ? ['https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js'] : [],
    array_map(static fn (string $js): string => 'estadistica/js/' . $js . '?v=' . est_asset_version('js/' . $js), $scriptsModulo ?? [])
);
require dirname(__DIR__, 2) . '/includes/footer.php';
