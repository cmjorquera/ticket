<?php
require_once __DIR__ . '/componentes/boot.php';
$tituloPagina = 'Registrar software';
$colegios = $inventario->obtenerColegios();
$usuarios = $inventario->obtenerUsuarios();
$tiposLicenciamiento = $inventario->obtenerTiposLicenciamiento();
$pagadores = $inventario->obtenerPagadores();
$monedas = $inventario->obtenerMonedas();
$datosSensiblesCatalogo = $inventario->obtenerDatosSensiblesCatalogo();
$tiposUsuarioCatalogo = $inventario->obtenerTiposUsuarioCatalogo();
$modo = 'crear';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="inv-kicker">Módulo Licenciamiento</span>
            <h1 class="inv-title mb-2">Registrar software</h1>
            <p class="inv-subtitle mb-0">Crea un registro de licencias, costo, responsable y cuentas asociadas.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="sofware_licenciamiento.php" class="module-btn module-btn--secondary"><i class="bi bi-arrow-left"></i>Volver a software</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/formulario_software.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
