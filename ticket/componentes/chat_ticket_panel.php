<?php
/**
 * Panel lateral de mensajería del ticket (chat usuario ↔ técnico).
 * Antes de incluirlo, cada página define $vistaChat:
 *   ticket.php → 'usuario' · ticket_asignados.php → 'tecnico' · ticket_admin_v2.php → 'admin'
 * Lógica: ticket/logica/LOGICA_MENSAJERIA.txt
 */
declare(strict_types=1);
$csrf = isset($csrf) ? (string) $csrf : ticket_csrf_token();
$vistaChat = in_array($vistaChat ?? '', ['usuario', 'tecnico', 'admin'], true) ? $vistaChat : 'usuario';
$verCss = @filemtime(__DIR__ . '/../../css/ticket_chat.css') ?: 1;
$verJs = @filemtime(__DIR__ . '/../js/chat_ticket_panel.js') ?: 1;
?>
<link rel="stylesheet" href="../css/ticket_chat.css?v=<?= (int) $verCss ?>">
<div class="ticket-chat" id="ticket-chat-panel" data-csrf="<?= e($csrf) ?>" data-vista="<?= e($vistaChat) ?>" hidden>
  <button class="ticket-chat__backdrop" type="button" data-chat-close aria-label="Cerrar conversación"></button>
  <aside class="ticket-chat__drawer" role="dialog" aria-modal="true" aria-labelledby="ticket-chat-title" tabindex="-1">
    <header class="ticket-chat__header">
      <div>
        <span class="ticket-chat__folio" id="ticket-chat-folio">Conversación</span>
        <h2 id="ticket-chat-title">Cargando ticket…</h2>
        <p class="ticket-chat__with" id="ticket-chat-with"></p>
      </div>
      <button class="ticket-chat__close" type="button" data-chat-close aria-label="Cerrar conversación"><i class="bi bi-x-lg"></i></button>
    </header>
    <div class="ticket-chat__status" id="ticket-chat-status" role="status" aria-live="polite" hidden></div>
    <div class="ticket-chat__thread" id="ticket-chat-thread" aria-live="polite"></div>
    <section class="ticket-chat__files" aria-labelledby="ticket-chat-files-title">
      <div class="ticket-chat__section-title"><h3 id="ticket-chat-files-title">Archivos del ticket</h3><span id="ticket-chat-file-count">0/5</span></div>
      <div class="ticket-chat__file-list" id="ticket-chat-file-list"></div>
    </section>
    <form class="ticket-chat__composer" id="ticket-chat-form" hidden>
      <label class="tc-sr" for="ticket-chat-message">Mensaje</label>
      <div class="tc-compose">
        <label class="tc-attach" for="ticket-chat-files" title="Adjuntar archivo (máx. 5 MB)" aria-label="Adjuntar archivo"><i class="bi bi-paperclip"></i></label>
        <input id="ticket-chat-files" type="file" multiple accept=".jpg,.jpeg,.png,.webp,.gif,.pdf,.txt,.doc,.docx,.xls,.xlsx,.zip" hidden>
        <textarea id="ticket-chat-message" name="comentario" rows="1" maxlength="5000" required placeholder="Escribe un mensaje…"></textarea>
        <button class="tc-send" type="submit" title="Enviar (Enter)" aria-label="Enviar mensaje"><i class="bi bi-send-fill"></i></button>
      </div>
      <small class="tc-hint">Enter envía · Shift + Enter salta de línea</small>
    </form>
    <div class="ticket-chat__closed" id="ticket-chat-closed" hidden><i class="bi bi-lock"></i><span>Este ticket está terminado: la conversación queda como registro y no admite nuevos mensajes.</span></div>
  </aside>
</div>
<script src="js/chat_ticket_panel.js?v=<?= (int) $verJs ?>"></script>
