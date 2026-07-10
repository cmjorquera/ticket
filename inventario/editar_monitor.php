<?php
require_once __DIR__ . '/componentes/boot.php';

$idMonitor = (int)($_GET['id_monitor'] ?? 0);
$monitor   = $inventario->obtenerMonitorPorId($idMonitor);

if (!$monitor) {
    header('Location: index.php?tab=monitores');
    exit;
}

$alcanceInventario = $inventario->obtenerAlcanceInventario($idUsuarioSession);
if (!$inventario->colegioPermitidoPorAlcance((int)($monitor['id_colegio'] ?? 0), $alcanceInventario)) {
    header('Location: index.php?tab=monitores');
    exit;
}

$tituloPagina   = 'Editar monitor';
$idColegioForm  = (int)($monitor['id_colegio'] ?? 0);
$colegioUsuario = ['id_colegio' => $idColegioForm, 'nom_colegio' => ($monitor['nom_colegio'] ?? '')];
$usuarios       = $inventario->obtenerUsuarios();
$estados        = $inventario->obtenerEstados();
$ubicaciones    = $inventario->obtenerUbicacionesPorColegio($idColegioForm);
$modo = 'editar';

require __DIR__ . '/componentes/layout_top.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Editar monitor</h1>
        <p class="text-muted mb-0"><?= inventario_h($monitor['nombre_monitor'] ?? '') ?> — <?= inventario_h($monitor['nom_colegio'] ?? '') ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="ver_monitor.php?id_monitor=<?= $idMonitor ?>" class="btn btn-outline-secondary">Ver ficha</a>
        <a href="index.php?tab=monitores" class="btn btn-light border">Volver</a>
    </div>
</div>

<div class="card shadow-sm border-0 inv-panel">
    <div class="card-body">
        <?php require __DIR__ . '/componentes/formulario_monitor.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
