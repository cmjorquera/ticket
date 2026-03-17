<?php
require_once __DIR__ . '/componentes/boot.php';
$idHerramienta = (int)($_GET['id_herramienta'] ?? 0);
$equipo = $inventario->obtenerHerramientaCompleta($idHerramienta);
if (!$equipo) { header('Location: index.php'); exit; }
$tituloPagina = 'Editar herramienta';
$colegios = $inventario->obtenerColegios();
$usuarios = $inventario->obtenerUsuarios();
$estados = $inventario->obtenerEstados();
$categorias = $inventario->obtenerCategorias();
$modo = 'editar';
require __DIR__ . '/componentes/layout_top.php';
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><h1 class="h3 mb-1">Editar herramienta #<?= (int)$equipo['id_herramienta'] ?></h1><p class="text-muted mb-0"><?= inventario_h($equipo['nombre_herramienta']) ?> · <?= inventario_h($equipo['categoria']) ?></p></div><a href="ver_herramienta.php?id_herramienta=<?= (int)$equipo['id_herramienta'] ?>" class="btn btn-outline-primary">Ver ficha</a></div>
<?php require __DIR__ . '/componentes/formulario_herramienta.php'; ?>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
