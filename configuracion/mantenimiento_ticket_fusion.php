<?php
require_once __DIR__ . '/_inicio.php';
iniciar_layout_configuracion('Fusionar tickets', 'Fusionar tickets', 'mantenimiento_ticket_fusion');
?>
<div class="page-header"><div><h1>Fusionar tickets</h1><p>Base para comparar dos casos antes de unificarlos.</p></div><div class="page-header-actions"><a class="btn btn-outline" href="mantenimiento_tickets.php">Volver</a></div></div>
<div class="card"><div class="card-header"><h2 class="card-title">Seleccionar tickets</h2></div><div class="card-body"><div class="flex gap-3"><div style="flex:1"><label class="field-label" for="ticket-principal">Ticket principal</label><input class="input" id="ticket-principal" type="number" min="1"></div><div style="flex:1"><label class="field-label" for="ticket-secundario">Ticket a fusionar</label><input class="input" id="ticket-secundario" type="number" min="1"></div></div><p class="text-sm text-muted mt-3">La ejecución permanecerá deshabilitada hasta disponer del backend transaccional de fusión.</p></div></div>
<?php finalizar_layout_configuracion(); ?>
