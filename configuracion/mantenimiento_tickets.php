<?php
require_once __DIR__ . '/_inicio.php';
iniciar_layout_configuracion('Mantenimiento de tickets', 'Mantenimiento de tickets', 'mantenimiento_tickets');
?>
<div class="page-header"><div><h1>Mantenimiento de tickets</h1><p>Operaciones administrativas con revisión previa.</p></div></div>
<div class="quick-grid">
  <a class="quick-card" href="mantenimiento_ticket_eliminar.php"><div class="top"><strong>Eliminar ticket</strong></div><p>Revisar tickets antes de eliminarlos.</p><small>Operación administrativa</small></a>
  <a class="quick-card" href="mantenimiento_ticket_recuperar.php"><div class="top"><strong>Recuperar ticket</strong></div><p>Consultar tickets eliminados.</p><small>Restaurar al flujo activo</small></a>
  <a class="quick-card" href="mantenimiento_ticket_fusion.php"><div class="top"><strong>Fusionar tickets</strong></div><p>Preparar la unificación de casos.</p><small>Vista comparativa</small></a>
</div>
<div class="card mt-3"><div class="card-header"><h2 class="card-title">Criterios de seguridad</h2></div><div class="card-body text-sm text-muted">Antes de ejecutar una operación se debe previsualizar el impacto y registrar el usuario, la fecha y el motivo para auditoría.</div></div>
<?php finalizar_layout_configuracion(); ?>
