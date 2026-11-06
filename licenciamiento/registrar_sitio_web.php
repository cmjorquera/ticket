<?php
require_once __DIR__ . '/componentes/boot.php';
$tituloPagina = 'Registrar sitio web';
$colegios = $inventario->obtenerColegios();
$usuarios = $inventario->obtenerUsuarios();
$tiposSitio = $inventario->obtenerTiposSitio();
$estadosSitio = $inventario->obtenerEstadosSitio();
$modo = 'crear';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="inv-kicker">Módulo Licenciamiento</span>
            <h1 class="inv-title mb-2">Registrar sitio web o app</h1>
            <p class="inv-subtitle mb-0">Crea una ficha para web, app o cliente conectado a servidor.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="sitios_web_apps.php" class="module-btn module-btn--secondary"><i class="bi bi-arrow-left"></i>Volver a sitios</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/formulario_sitio_web.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
