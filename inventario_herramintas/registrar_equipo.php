<?php
require_once __DIR__ . '/componentes/boot.php';

$tituloPagina = 'Registrar equipo';
$colegios = $inventario->obtenerColegios();
$usuarios = $inventario->obtenerUsuarios();
$estados = $inventario->obtenerEstados();
$tiposPc = $inventario->obtenerTiposPc();
$modo = 'crear';

require __DIR__ . '/componentes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Registrar equipo</h1>
        <p class="text-muted mb-0">Carga estructurada de ficha tecnica, compra, componentes y evidencias fotograficas.</p>
    </div>
</div>

<div class="card shadow-sm border-0 inv-panel">
    <div class="card-body">
        <?php require __DIR__ . '/componentes/formulario_equipo.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
