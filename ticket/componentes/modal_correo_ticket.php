<?php
/**
 * Modal "Enviar ticket por correo" (PDF adjunto). Se abre con
 * .js-ticket-correo[data-ticket-id][data-email][data-solo-yo]. Necesita $csrf (ticket_csrf).
 * Lógica: ticket/logica/LOGICA_PDF_CORREO.txt
 */
$verCorreo = @filemtime(__DIR__ . '/../js/correo_ticket.js') ?: 1;
?>
<div class="modal-overlay" id="modal-correo-ticket" onclick="closeModalOutside(event,'modal-correo-ticket')">
  <div class="modal tk-com" role="dialog" aria-modal="true" aria-labelledby="tk-correo-titulo">
    <form id="tk-correo-form" novalidate data-csrf="<?= e($csrf) ?>">
      <div class="modal-header">
        <span class="tk-com__kicker" id="tk-correo-folio">Ticket</span>
        <h3 id="tk-correo-titulo">Enviar ticket por correo</h3>
        <p>Se envía un resumen con el enlace de seguimiento y el ticket en PDF adjunto.</p>
      </div>
      <div class="modal-body">
        <input type="hidden" id="tk-correo-id">
        <div class="tk-com__grupo">
          <label class="form-label" for="tk-correo-para">Para</label>
          <input class="form-input" type="email" id="tk-correo-para" maxlength="150" required autocomplete="email">
          <small class="tk-com__plazo" id="tk-correo-ayuda"></small>
        </div>
        <label class="tk-correo__copia"><input type="checkbox" id="tk-correo-copia" value="1"> Enviarme una copia</label>
        <p class="tk-com__nota"><i class="bi bi-shield-lock"></i><span>El PDF que se envía <strong>no incluye la bitácora interna</strong> (solo datos, ciclo, descripción y conversación).</span></p>
        <div class="ticket-message" id="tk-correo-mensaje" role="status"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-outline" type="button" onclick="closeModal('modal-correo-ticket')">Cancelar</button>
        <button class="btn btn-primary" type="submit" id="tk-correo-enviar"><i class="bi bi-send"></i> Enviar</button>
      </div>
    </form>
  </div>
</div>
<script src="js/correo_ticket.js?v=<?= (int) $verCorreo ?>"></script>
