<?php
/**
 * Modal "Comenzar ticket" (técnico asignado): prioridad + fecha estimada → En proceso.
 * Se abre con cualquier botón .js-comenzar-ticket[data-ticket-id][data-asunto].
 * Lo usan ticket_asignados.php y ticket_detalle.php. Necesita $csrf.
 * Lógica: ticket/logica/LOGICA_COMENZAR_TICKET.txt
 */
$verCom = @filemtime(__DIR__ . '/../js/comenzar_ticket.js') ?: 1;
?>
<div class="modal-overlay" id="modal-comenzar" onclick="closeModalOutside(event,'modal-comenzar')">
  <div class="modal tk-com" role="dialog" aria-modal="true" aria-labelledby="tk-com-titulo">
    <form id="tk-com-form" novalidate data-csrf="<?= e($csrf) ?>">
      <div class="modal-header">
        <span class="tk-com__kicker" id="tk-com-folio">Ticket</span>
        <h3 id="tk-com-titulo">Comenzar ticket</h3>
        <p id="tk-com-asunto"></p>
      </div>
      <div class="modal-body">
        <input type="hidden" name="ticket_id" id="tk-com-id">
        <fieldset class="tk-com__grupo">
          <legend>Prioridad <span aria-hidden="true">*</span></legend>
          <div class="tk-com__prioridades">
            <?php foreach (ticket_prioridades() as $idP => $nombreP): ?>
              <label class="tk-com__prio tk-com__prio--<?= e(ticket_prioridad_clase($nombreP)) ?>">
                <input type="radio" name="prioridad" value="<?= (int) $idP ?>" required>
                <span><i class="bi <?= $idP === 1 ? 'bi-arrow-up-circle-fill' : ($idP === 2 ? 'bi-dash-circle-fill' : 'bi-arrow-down-circle-fill') ?>"></i> <?= e($nombreP) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </fieldset>
        <div class="tk-com__grupo">
          <label class="form-label" for="tk-com-fecha">¿Para cuándo crees que estará listo? <span aria-hidden="true">*</span></label>
          <div class="tk-com__atajos" role="group" aria-label="Plazos rápidos">
            <button type="button" class="btn btn-outline btn-sm" data-dias="0">Hoy</button>
            <button type="button" class="btn btn-outline btn-sm" data-dias="1">Mañana</button>
            <button type="button" class="btn btn-outline btn-sm" data-dias="3">3 días</button>
            <button type="button" class="btn btn-outline btn-sm" data-dias="7">1 semana</button>
            <button type="button" class="btn btn-outline btn-sm" data-dias="14">2 semanas</button>
          </div>
          <input class="form-input" type="date" id="tk-com-fecha" name="fecha_estimada" required>
          <small class="tk-com__plazo" id="tk-com-plazo"></small>
        </div>
        <p class="tk-com__nota"><i class="bi bi-info-circle"></i><span>El ticket pasará a <strong>En proceso</strong>. La prioridad y la fecha quedan en la bitácora y se ven en el seguimiento por QR.</span></p>
        <div class="ticket-message" id="tk-com-mensaje" role="status"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" type="button" onclick="closeModal('modal-comenzar')">Cancelar</button>
        <button class="btn btn-primary" type="submit" id="tk-com-enviar"><i class="bi bi-play-fill"></i> Comenzar ticket</button>
      </div>
    </form>
  </div>
</div>
<script src="js/comenzar_ticket.js?v=<?= (int) $verCom ?>"></script>
