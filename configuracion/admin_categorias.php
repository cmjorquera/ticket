<?php
require_once __DIR__ . '/_inicio.php';
$error = null;
try {
    $categorias = $db->fetchAll(
        "SELECT c.id_categoria, c.nombre_categoria, c.icono, c.orden,
                COUNT(DISTINCT ct.id_tecnico) AS total_tecnicos
           FROM categoria_de_ticket c
      LEFT JOIN categoria_tecnico ct ON ct.id_categoria = c.id_categoria
          WHERE c.estado = 1 AND c.id_categoria <> 10
       GROUP BY c.id_categoria, c.nombre_categoria, c.icono, c.orden
       ORDER BY c.orden ASC, c.id_categoria ASC"
    );
    $tecnicos = $db->fetchAll(
        "SELECT u.id, CONCAT_WS(' ', u.nombre, u.apellido_paterno) AS nombre,
                GROUP_CONCAT(DISTINCT c.nombre_categoria ORDER BY c.orden SEPARATOR ', ') AS categorias
           FROM usuarios u
      LEFT JOIN categoria_tecnico ct ON ct.id_tecnico = u.id
      LEFT JOIN categoria_de_ticket c ON c.id_categoria = ct.id_categoria
          WHERE u.id_area_trabajo = 1 AND u.id <> 27 AND u.estado = 'Activo'
       GROUP BY u.id, u.nombre, u.apellido_paterno
       ORDER BY u.nombre ASC"
    );
} catch (Throwable $ex) {
    $categorias = $tecnicos = [];
    $error = 'No fue posible consultar las categorías y técnicos.';
}
iniciar_layout_configuracion('Categorías y técnicos', 'Categorías', 'admin_categorias');
?>
<div class="page-header"><div><h1>Categorías y técnicos</h1><p>Asignaciones vigentes para la atención de tickets.</p></div></div>
<?php if ($error): ?><div class="card"><div class="card-body text-muted"><?= e($error) ?></div></div><?php else: ?>
<div class="stat-grid" style="margin-bottom:16px">
  <div class="stat-card"><div class="row"><div><div class="label">Categorías activas</div><div class="value"><?= count($categorias) ?></div></div></div></div>
  <div class="stat-card"><div class="row"><div><div class="label">Técnicos activos</div><div class="value"><?= count($tecnicos) ?></div></div></div></div>
</div>
<div class="bottom-grid">
  <section class="card"><div class="card-header"><div><h2 class="card-title">Categorías</h2><p class="card-desc">Cantidad de técnicos asignados</p></div></div><div class="table-wrap"><table><thead><tr><th>Orden</th><th>Categoría</th><th>Técnicos</th></tr></thead><tbody><?php foreach ($categorias as $categoria): ?><tr><td class="mono"><?= (int) $categoria['orden'] ?></td><td><?= e($categoria['nombre_categoria']) ?></td><td><?= (int) $categoria['total_tecnicos'] ?></td></tr><?php endforeach; ?></tbody></table></div></section>
  <section class="card"><div class="card-header"><div><h2 class="card-title">Técnicos</h2><p class="card-desc">Categorías asociadas</p></div></div><div class="card-body space-y-4"><?php foreach ($tecnicos as $tecnico): ?><div><strong class="text-sm"><?= e($tecnico['nombre']) ?></strong><div class="text-xs text-muted"><?= e($tecnico['categorias'] ?: 'Sin categorías') ?></div></div><?php endforeach; ?></div></section>
</div>
<?php endif; ?>
<?php finalizar_layout_configuracion(); ?>
