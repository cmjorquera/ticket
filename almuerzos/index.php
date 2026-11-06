<?php
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../validar_sesion.php';
$pagina_titulo = 'Almuerzos';

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-header">
  <div>
    <h1><i class="bi bi-cup-hot" aria-hidden="true"></i> Almuerzos</h1>
    <p>Módulo de almuerzos de SEDUC.</p>
  </div>
</div>

<div class="card">
  <div class="card-body" style="text-align:center;padding:56px 24px">
    <div style="width:64px;height:64px;margin:0 auto 16px;display:grid;place-items:center;border-radius:16px;background:var(--yellow-light);color:var(--yellow);font-size:28px">
      <i class="bi bi-cup-hot" aria-hidden="true"></i>
    </div>
    <h2 style="font-size:16px;margin-bottom:6px">Módulo en construcción</h2>
    <p style="color:var(--muted);font-size:13px">Pronto podrás gestionar los almuerzos desde aquí.</p>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
