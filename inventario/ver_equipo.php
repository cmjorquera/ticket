<?php
require_once __DIR__ . '/componentes/boot.php';

$idEquipo = (int)($_GET['id_equipo'] ?? 0);
$equipo = $inventario->obtenerEquipoCompleto($idEquipo);

if (!$equipo) {
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Ficha tecnica equipo';
require __DIR__ . '/componentes/layout_top.php';

$detalleOffcanvas = false;
require __DIR__ . '/componentes/detalle_equipo.php';
?>

<script>
window.INVENTARIO_CONFIG = window.INVENTARIO_CONFIG || {};
window.INVENTARIO_CONFIG.qrText = <?= json_encode($equipo['qr_code']) ?>;
</script>

<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
