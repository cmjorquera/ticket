<?php
require_once __DIR__ . '/componentes/boot.php';

$tituloPagina   = 'Registrar monitor';
$colegioUsuario = $inventario->obtenerColegioDelUsuario($idUsuarioSession);
$idColegioForm  = (int)($colegioUsuario['id_colegio'] ?? 0);
$usuarios       = $inventario->obtenerUsuarios();
$estados        = $inventario->obtenerEstados();
$ubicaciones    = $inventario->obtenerUbicacionesPorColegio($idColegioForm);
$modo = 'crear';

require __DIR__ . '/componentes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Registrar monitor</h1>
        <p class="text-muted mb-0">Carga estructurada de la ficha técnica del monitor.</p>
    </div>
    <a href="index.php?tab=monitores" class="btn btn-light border">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<div class="card shadow-sm border-0 inv-panel">
    <div class="card-body">
        <?php require __DIR__ . '/componentes/formulario_monitor.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
