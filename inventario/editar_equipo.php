<?php
require_once __DIR__ . '/componentes/boot.php';

$idEquipo = (int)($_GET['id_equipo'] ?? 0);
$equipo = $inventario->obtenerEquipoCompleto($idEquipo);

if (!$equipo) {
    header('Location: index.php');
    exit;
}

$tituloPagina   = 'Editar equipo';
$idColegioForm  = (int)($equipo['id_colegio'] ?? 0);
$colegioUsuario = ['id_colegio' => $idColegioForm, 'nom_colegio' => ($equipo['nom_colegio'] ?? '')];
$usuarios       = $inventario->obtenerUsuarios();
$estados        = $inventario->obtenerEstados();
$tiposPc        = $inventario->obtenerTiposPc();
$ubicaciones    = $inventario->obtenerUbicacionesPorColegio($idColegioForm);
$modo = 'editar';

require __DIR__ . '/componentes/layout_top.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Editar equipo #<?= (int)$equipo['id_equipo'] ?></h1>
        <p class="text-muted mb-0">Actualiza la configuracion tecnica y los activos relacionados.</p>
    </div>
</div>

<div class="card shadow-sm border-0 inv-panel">
    <div class="card-body">
        <?php require __DIR__ . '/componentes/formulario_equipo.php'; ?>
    </div>
</div>

<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
