/* Galería compartida de tickets: overlay propio para evitar reglas globales de dialog. */
(() => {
  const overlay = document.createElement('div');
  overlay.id = 'ticket-gallery-overlay';
  overlay.setAttribute('role', 'presentation');
  overlay.hidden = true;
  overlay.innerHTML = `
    <section class="ticket-gallery-window" role="dialog" aria-modal="true" aria-labelledby="ticket-gallery-title" tabindex="-1">
      <header class="ticket-gallery-header">
        <strong id="ticket-gallery-title">Archivos adjuntos</strong>
        <button type="button" id="ticket-gallery-close" aria-label="Cerrar galería">✕</button>
      </header>
      <iframe id="ticket-gallery-frame" title="Carrusel de imágenes adjuntas"></iframe>
    </section>`;
  const style = document.createElement('style');
  style.textContent = `
    #ticket-gallery-overlay[hidden]{display:none!important}
    #ticket-gallery-overlay{
      position:fixed!important;inset:0!important;
      width:100vw!important;height:100vh!important;height:100dvh!important;
      margin:0!important;padding:16px!important;box-sizing:border-box!important;
      display:flex!important;align-items:center!important;justify-content:center!important;
      background:rgba(10,24,45,.6)!important;z-index:2147483647!important;
      transform:none!important;overflow:auto!important;
    }
    #ticket-gallery-overlay .ticket-gallery-window{
      position:relative!important;inset:auto!important;transform:none!important;
      margin:auto!important;padding:0!important;box-sizing:border-box!important;
      display:flex!important;flex-direction:column!important;
      width:min(920px,100%)!important;height:min(760px,100%)!important;
      max-width:920px!important;max-height:100%!important;min-height:0!important;
      background:#fff!important;border:1px solid #dce3eb!important;border-radius:14px!important;
      box-shadow:0 20px 70px rgba(15,35,60,.25)!important;overflow:hidden!important;
    }
    #ticket-gallery-overlay .ticket-gallery-header{
      display:flex!important;align-items:center!important;justify-content:space-between!important;
      gap:12px!important;flex:0 0 auto!important;padding:12px 16px!important;
      border-bottom:1px solid #e2e8f0!important;background:#fff!important;
      color:#20334b!important;font:600 14px system-ui,sans-serif!important;
    }
    #ticket-gallery-overlay #ticket-gallery-close{
      border:0!important;background:#f1f4f8!important;border-radius:7px!important;
      padding:7px 11px!important;cursor:pointer!important;color:#20334b!important;
    }
    #ticket-gallery-overlay #ticket-gallery-frame{
      display:block!important;flex:1 1 auto!important;min-height:0!important;
      width:100%!important;height:100%!important;border:0!important;
    }
    @media(max-width:640px){
      #ticket-gallery-overlay{padding:8px!important}
      #ticket-gallery-overlay .ticket-gallery-window{height:100%!important;border-radius:10px!important}
    }
  `;
  document.head.appendChild(style);
  // Colgarlo del documento, no del contenedor del panel o de una tabla.
  document.body.appendChild(overlay);
  const windowElement = overlay.querySelector('.ticket-gallery-window');
  const frame = overlay.querySelector('#ticket-gallery-frame');
  const title = overlay.querySelector('#ticket-gallery-title');
  const closeButton = overlay.querySelector('#ticket-gallery-close');
  let previousFocus = null;
  let previousOverflow = '';

  function closeGallery() {
    if (overlay.hidden) return;
    overlay.hidden = true;
    frame.removeAttribute('src');
    document.body.style.overflow = previousOverflow;
    if (previousFocus && previousFocus.isConnected) previousFocus.focus();
  }

  closeButton.addEventListener('click', closeGallery);
  overlay.addEventListener('click', event => {
    if (event.target === overlay) closeGallery();
  });
  document.addEventListener('keydown', event => {
    if (overlay.hidden) return;
    if (event.key === 'Escape') {
      event.preventDefault();
      closeGallery();
    } else if (event.key === 'Tab') {
      // Mantener el foco dentro de la ventana mientras está abierta.
      const focusable = [closeButton, frame];
      const current = focusable.indexOf(document.activeElement);
      if (event.shiftKey && current <= 0) {
        event.preventDefault();
        focusable[focusable.length - 1].focus();
      } else if (!event.shiftKey && current === focusable.length - 1) {
        event.preventDefault();
        closeButton.focus();
      }
    }
  });
  document.addEventListener('click', event => {
    const button = event.target.closest('.js-ticket-gallery');
    if (!button) return;
    event.preventDefault();
    const id = Number(button.dataset.ticketId);
    if (!Number.isSafeInteger(id) || id < 1) return;
    previousFocus = document.activeElement;
    previousOverflow = document.body.style.overflow;
    title.textContent = 'Adjuntos · Ticket #' + id;
    frame.src = 'galeria_adjuntos.php?id=' + encodeURIComponent(id);
    overlay.hidden = false;
    document.body.style.overflow = 'hidden';
    closeButton.focus();
  });
})();