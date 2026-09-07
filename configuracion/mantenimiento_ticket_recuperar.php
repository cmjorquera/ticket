<?php
require_once __DIR__ . '/_inicio.php';
iniciar_layout_configuracion('Tickets eliminados', 'Tickets eliminados', 'mantenimiento_ticket_recuperar');
?>
<div class="page-header"><div><h1>Tickets eliminados</h1><p>Consulta y recuperación administrativa de casos.</p></div><div class="page-header-actions"><a class="btn btn-outline" href="mantenimiento_ticket_eliminar.php">Volver</a></div></div>
<div class="card"><div class="card-body text-sm text-muted">El listado dependía de <code>Funciones::ticketAdministrador()</code> y del endpoint de recuperación del sistema anterior. Esos componentes no existen en este proyecto, por lo que la acción queda deshabilitada de forma segura.</div></div>
<?php finalizar_layout_configuracion(); ?>
