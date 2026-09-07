<?php
require_once __DIR__ . '/_inicio.php';
iniciar_layout_configuracion('Eliminar tickets', 'Eliminar tickets', 'mantenimiento_ticket_eliminar');
?>
<div class="page-header"><div><h1>Eliminar tickets</h1><p>Busca el caso y revisa su impacto antes de eliminarlo.</p></div><div class="page-header-actions"><a class="btn btn-outline" href="mantenimiento_tickets.php">Volver</a></div></div>
<div class="card"><div class="card-header"><div><h2 class="card-title">Buscar ticket</h2><p class="card-desc">La eliminación requiere confirmación y registro de motivo.</p></div></div><div class="card-body"><form class="flex gap-2" onsubmit="buscarTicket(event)"><input class="input" id="ticket-id" type="number" min="1" required placeholder="ID del ticket"><button class="btn btn-primary" type="submit">Buscar</button></form><div id="resultado-ticket" class="text-sm text-muted mt-3"></div></div></div>
<script>function buscarTicket(e){e.preventDefault();document.getElementById('resultado-ticket').textContent='La consulta detallada requiere el endpoint de mantenimiento del sistema anterior, que no está disponible en este proyecto.'}</script>
<?php finalizar_layout_configuracion(); ?>
