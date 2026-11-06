<?php
require_once __DIR__ . '/componentes/boot.php';
$idSitio = (int)($_GET['id_sitio'] ?? 0);
$sitio = $inventario->obtenerSitioWeb($idSitio);
if (!$sitio) { header('Location: sitios_web_apps.php'); exit; }
$tituloPagina = 'Editar sitio web';
$colegios = $inventario->obtenerColegios();
$usuarios = $inventario->obtenerUsuarios();
$tiposSitio = $inventario->obtenerTiposSitio();
$estadosSitio = $inventario->obtenerEstadosSitio();
$modo = 'editar';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="inv-kicker">Módulo Licenciamiento</span>
            <h1 class="inv-title mb-2">Editar sitio #<?= (int)$sitio['id_sitio'] ?></h1>
            <p class="inv-subtitle mb-0"><?= inventario_h($sitio['nombre_sitio']) ?> · <?= inventario_h($sitio['tipo_sitio']) ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="ver_sitio_web.php?id_sitio=<?= (int)$sitio['id_sitio'] ?>" class="module-btn module-btn--primary"><i class="bi bi-eye"></i>Ver ficha</a>
            <a href="sitios_web_apps.php" class="module-btn module-btn--secondary"><i class="bi bi-arrow-left"></i>Volver a sitios</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/formulario_sitio_web.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
