</div>
</div>
<?php $funciones->footer(); ?>
</div>
</div>
<a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- SCRIPTS EN ORDEN CORRECTO (SIN DUPLICADOS)                 -->
<!-- ═══════════════════════════════════════════════════════════ -->

<!-- jQuery (PRIMERO) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap (SEGUNDO) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery plugins necesarios para sb-admin -->
<script src="<?= inventario_h(inventario_sistema_url('vendor/jquery-easing/jquery.easing.min.js')) ?>"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

<!-- Chart.js (para gráficos) -->
<script src="<?= inventario_h(inventario_sistema_url('vendor/chart.js/Chart.min.js')) ?>"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- QR Code -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<!-- Admin template -->
<script src="<?= inventario_h(inventario_sistema_url('js/sb-admin-2.min.js')) ?>"></script>

<!-- Chart demos -->
<script src="<?= inventario_h(inventario_sistema_url('js/demo/chart-area-demo.js')) ?>"></script>
<script src="<?= inventario_h(inventario_sistema_url('js/demo/chart-pie-demo.js')) ?>"></script>

<!-- Sistema - Funciones globales -->
<script src="<?= inventario_h(inventario_sistema_url('js/funciones.js')) ?>"></script>
<script src="<?= inventario_h(inventario_sistema_url('js/mensajes.js')) ?>"></script>
<script src="<?= inventario_h(inventario_sistema_url('js/ticket.js')) ?>"></script>
<script src="<?= inventario_h(inventario_sistema_url('js/buscadores.js')) ?>"></script>
<script src="<?= inventario_h(inventario_sistema_url('js/validacionTicket.js')) ?>"></script>
<script src="<?= inventario_h(inventario_sistema_url('js/toast.js')) ?>"></script>

<!-- Inventario - Scripts del módulo -->
<script src="js/inventario.js?v=<?= inventario_h(inventario_asset_version('js/inventario.js')) ?>"></script>

<!-- Inventario - Scripts extra por página (ej: dashboard.php agrega js/dashboard.js) -->
<?php foreach (($jsExtraInventario ?? []) as $jsExtra): ?>
<script src="<?= inventario_h($jsExtra) ?>?v=<?= inventario_h(inventario_asset_version($jsExtra)) ?>"></script>
<?php endforeach; ?>

</body>
</html>