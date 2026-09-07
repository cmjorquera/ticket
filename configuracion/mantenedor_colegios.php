<?php
require_once __DIR__ . '/_inicio.php';
$error = null;
try {
    $colegios = $db->fetchAll(
        "SELECT c.id_colegio, c.nom_colegio, c.rza_colegio, c.dir_colegio, c.tel_colegio,
                COUNT(DISTINCT CASE WHEN uc.estado = 1 THEN uc.id_usuario END) AS total_usuarios
           FROM colegio c
      LEFT JOIN usuario_colegio uc ON uc.id_colegio = c.id_colegio
          WHERE c.estado = 1
       GROUP BY c.id_colegio, c.nom_colegio, c.rza_colegio, c.dir_colegio, c.tel_colegio
       ORDER BY c.nom_colegio ASC"
    );
} catch (Throwable $ex) {
    $colegios = [];
    $error = 'No fue posible consultar los colegios en este momento.';
}
iniciar_layout_configuracion('Mantenedor de colegios', 'Colegios', 'mantenedor_colegios');
?>
<div class="page-header"><div><h1>Mantenedor de colegios</h1><p>Establecimientos activos y usuarios asociados.</p></div></div>
<?php if ($error): ?>
  <div class="card"><div class="card-body text-muted"><?= e($error) ?></div></div>
<?php else: ?>
<div class="toolbar card" style="margin-bottom:16px"><input class="input" id="buscar-colegio" type="search" placeholder="Buscar colegio" style="max-width:360px"><span class="toolbar-count"><strong id="total-colegios"><?= count($colegios) ?></strong> colegios</span></div>
<div class="colegios-grid" id="colegios-grid">
<?php foreach ($colegios as $colegio):
    $palabras = preg_split('/\s+/', trim((string) $colegio['nom_colegio']), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $sigla = ''; foreach (array_slice($palabras, 0, 3) as $palabra) { $sigla .= strtoupper(substr($palabra, 0, 1)); }
?>
  <article class="colegio-card">
    <div class="header"><div class="colegio-avatar"><?= e($sigla ?: 'C') ?></div><div class="colegio-name"><h3><?= e($colegio['nom_colegio']) ?></h3><small><?= e($colegio['rza_colegio'] ?? '') ?></small></div></div>
    <div class="colegio-stats"><div class="colegio-stat"><div class="val"><?= (int) $colegio['total_usuarios'] ?></div><div class="lbl">Usuarios</div></div><div class="colegio-stat" style="grid-column:span 2"><div class="val text-sm"><?= e($colegio['tel_colegio'] ?: '—') ?></div><div class="lbl"><?= e($colegio['dir_colegio'] ?: 'Sin dirección') ?></div></div></div>
  </article>
<?php endforeach; ?>
</div>
<script>
document.getElementById('buscar-colegio').addEventListener('input', function () {
  let n = 0; const q = this.value.toLocaleLowerCase('es');
  document.querySelectorAll('#colegios-grid .colegio-card').forEach(c => { const visible = c.textContent.toLocaleLowerCase('es').includes(q); c.hidden = !visible; if (visible) n++; });
  document.getElementById('total-colegios').textContent = n;
});
</script>
<?php endif; ?>
<?php finalizar_layout_configuracion(); ?>
