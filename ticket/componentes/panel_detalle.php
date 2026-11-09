<?php
/**
 * Panel lateral (derecha → izquierda, 80% del ancho) con el detalle del ticket.
 * Se abre con los enlaces .js-ticket-ver ("Abrir" en Tickets Asignados y Ticket
 * Administrador, "Ver" en Mesa de ayuda). Carga ticket_detalle.php?…&embed=1 en un
 * iframe, así el panel tiene todo lo del detalle (chat, bitácora, Comenzar, QR).
 * Ctrl/Cmd + clic o clic central siguen abriendo la página completa.
 */
$verPanel = @filemtime(__DIR__ . '/../js/panel_detalle.js') ?: 1;
?>
<div class="tk-panel" id="tk-panel" hidden>
  <button class="tk-panel__fondo" type="button" data-panel-cerrar aria-label="Cerrar detalle"></button>
  <section class="tk-panel__caja" role="dialog" aria-modal="true" aria-labelledby="tk-panel-titulo" tabindex="-1">
    <header class="tk-panel__cab">
      <h2 id="tk-panel-titulo">Ticket</h2>
      <div class="tk-panel__acciones">
        <a class="btn btn-outline btn-sm" id="tk-panel-pagina" href="#" target="_blank" rel="noopener" title="Abrir en una pestaña nueva"><i class="bi bi-box-arrow-up-right"></i> <span>Abrir en pestaña</span></a>
        <button class="tk-panel__x" type="button" data-panel-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
      </div>
    </header>
    <div class="tk-panel__cuerpo">
      <div class="tk-panel__cargando" id="tk-panel-cargando"><i class="bi bi-arrow-repeat"></i> Cargando ticket…</div>
      <iframe id="tk-panel-iframe" title="Detalle del ticket"></iframe>
    </div>
  </section>
</div>
<script src="js/panel_detalle.js?v=<?= (int) $verPanel ?>"></script>
