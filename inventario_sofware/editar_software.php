<?php
require_once __DIR__ . '/componentes/boot.php';
$idSoftware = (int)($_GET['id_software'] ?? 0);
$software = $inventario->obtenerSoftwareCompleto($idSoftware);
if (!$software) { header('Location: index.php'); exit; }
$tituloPagina = 'Editar software';
$colegios = $inventario->obtenerColegios();
$usuarios = $inventario->obtenerUsuarios();
$tiposLicenciamiento = $inventario->obtenerTiposLicenciamiento();
$pagadores = $inventario->obtenerPagadores();
$datosSensiblesCatalogo = $inventario->obtenerDatosSensiblesCatalogo();
$modo = 'editar';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 mb-1">Editar software #<?= (int)$software['id_software'] ?></h1><p class="text-muted mb-0"><?= inventario_h($software['nombre_software']) ?> · <?= inventario_h($software['version_software']) ?></p></div><a href="ver_software.php?id_software=<?= (int)$software['id_software'] ?>" class="btn btn-outline-primary">Ver ficha</a></div>
<?php require __DIR__ . '/componentes/formulario_software.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
