<?php
require_once __DIR__ . '/componentes/boot.php';

$idMonitor = (int)($_GET['id_monitor'] ?? 0);
$monitor   = $inventario->obtenerMonitorPorId($idMonitor);

if (!$monitor) {
    header('Location: index.php?tab=monitores');
    exit;
}

$tituloPagina = 'Ficha monitor';
require __DIR__ . '/componentes/layout_top.php';

$detalleOffcanvas = false;
require __DIR__ . '/componentes/detalle_monitor.php';

require __DIR__ . '/componentes/layout_bottom.php';
?>
