<?php
require_once __DIR__ . '/componentes/boot.php';
$idSitio = (int)($_GET['id_sitio'] ?? 0);
$sitio = $inventario->obtenerSitioWeb($idSitio);
if (!$sitio) { header('Location: index.php'); exit; }
$tituloPagina = 'Editar sitio web';
$colegios = $inventario->obtenerColegios();
$usuarios = $inventario->obtenerUsuarios();
$tiposSitio = $inventario->obtenerTiposSitio();
$estadosSitio = $inventario->obtenerEstadosSitio();
$modo = 'editar';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 mb-1">Editar sitio #<?= (int)$sitio['id_sitio'] ?></h1><p class="text-muted mb-0"><?= inventario_h($sitio['nombre_sitio']) ?> · <?= inventario_h($sitio['tipo_sitio']) ?></p></div><a href="ver_sitio_web.php?id_sitio=<?= (int)$sitio['id_sitio'] ?>" class="btn btn-primary inv-system-btn">Ver ficha</a></div>
<?php require __DIR__ . '/componentes/formulario_sitio_web.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
