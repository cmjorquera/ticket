<?php
require_once __DIR__ . '/componentes/boot.php';
$idSoftware = (int)($_GET['id_software'] ?? 0);
$software = $inventario->obtenerSoftwareCompleto($idSoftware);
if (!$software) { header('Location: sofware_licenciamiento.php'); exit; }
$tituloPagina = 'Editar software';
$colegios = $inventario->obtenerColegios();
$usuarios = $inventario->obtenerUsuarios();
$tiposLicenciamiento = $inventario->obtenerTiposLicenciamiento();
$pagadores = $inventario->obtenerPagadores();
$monedas = $inventario->obtenerMonedas();
$datosSensiblesCatalogo = $inventario->obtenerDatosSensiblesCatalogo();
$tiposUsuarioCatalogo = $inventario->obtenerTiposUsuarioCatalogo();
$modo = 'editar';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="inv-kicker">Módulo Licenciamiento</span>
            <h1 class="inv-title mb-2">Editar software #<?= (int)$software['id_software'] ?></h1>
            <p class="inv-subtitle mb-0"><?= inventario_h($software['nombre_software']) ?> · <?= inventario_h($software['version_software'] ?: 'Sin versión') ?></p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="ver_software.php?id_software=<?= (int)$software['id_software'] ?>" class="module-btn module-btn--primary"><i class="bi bi-eye"></i>Ver ficha</a>
            <a href="sofware_licenciamiento.php" class="module-btn module-btn--secondary"><i class="bi bi-arrow-left"></i>Volver a software</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/formulario_software.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
