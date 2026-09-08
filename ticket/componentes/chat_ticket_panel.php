<?php
declare(strict_types=1);
$csrf = isset($csrf) ? (string) $csrf : ticket_csrf_token();
?>
<div class="ticket-chat" id="ticket-chat-panel" data-csrf="<?= e($csrf) ?>" data-user-id="<?= (int) Sesion::get('id', 0) ?>" hidden>
  <button class="ticket-chat__backdrop" type="button" data-chat-close aria-label="Cerrar conversación"></button>
  <aside class="ticket-chat__drawer" role="dialog" aria-modal="true" aria-labelledby="ticket-chat-title" tabindex="-1">
    <header class="ticket-chat__header">
      <div>
        <span class="ticket-chat__folio" id="ticket-chat-folio">Conversación</span>
        <h2 id="ticket-chat-title">Cargando ticket…</h2>
      </div>
      <button class="ticket-chat__close" type="button" data-chat-close aria-label="Cerrar conversación"><i class="bi bi-x-lg"></i></button>
    </header>
    <div class="ticket-chat__status" id="ticket-chat-status" role="status" aria-live="polite"></div>
    <div class="ticket-chat__thread" id="ticket-chat-thread" aria-live="polite"></div>
    <section class="ticket-chat__files" aria-labelledby="ticket-chat-files-title">
      <div class="ticket-chat__section-title"><h3 id="ticket-chat-files-title">Archivos adjuntos</h3><span id="ticket-chat-file-count">0/5</span></div>
      <div class="ticket-chat__file-list" id="ticket-chat-file-list"></div>
      <label class="ticket-chat__picker" for="ticket-chat-files"><i class="bi bi-paperclip"></i><span>Adjuntar archivos</span><small>Máximo 5 MB por archivo</small></label>
      <input id="ticket-chat-files" type="file" multiple accept=".jpg,.jpeg,.png,.webp,.pdf,.txt,.doc,.docx,.xls,.xlsx,.zip" hidden>
    </section>
    <form class="ticket-chat__composer" id="ticket-chat-form">
      <label class="form-label" for="ticket-chat-message">Nuevo mensaje</label>
      <textarea class="form-input" id="ticket-chat-message" name="comentario" minlength="3" maxlength="5000" required placeholder="Escribe una actualización…"></textarea>
      <div><small>Máximo 5000 caracteres</small><button class="btn btn-primary" type="submit"><i class="bi bi-send"></i> Enviar</button></div>
    </form>
    <div class="ticket-chat__closed" id="ticket-chat-closed" hidden><i class="bi bi-lock"></i><span>Este ticket está cerrado y no admite nuevos mensajes.</span></div>
  </aside>
</div>
<script src="js/chat_ticket_panel.js"></script>
