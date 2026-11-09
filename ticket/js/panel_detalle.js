/*
 * Panel lateral con el detalle del ticket (ticket/componentes/panel_detalle.php).
 * Si dentro del panel se hizo algún cambio (comenzar, estado…), al cerrarlo se
 * recarga la bandeja para que la tabla muestre lo nuevo.
 */
(() => {
  'use strict';
  const panel = document.getElementById('tk-panel');
  if (!panel) return;
  const caja = panel.querySelector('.tk-panel__caja');
  const iframe = document.getElementById('tk-panel-iframe');
  const titulo = document.getElementById('tk-panel-titulo');
  const pagina = document.getElementById('tk-panel-pagina');
  const cargando = document.getElementById('tk-panel-cargando');
  let cargas = 0;
  let ultimo = null;
  let timer = 0;

  function urlEmbebida(href) {
    const u = new URL(href, window.location.href);
    u.searchParams.set('embed', '1');
    return u.toString();
  }

  function abrir(enlace) {
    window.clearTimeout(timer);
    ultimo = enlace;
    cargas = 0;
    titulo.textContent = `Ticket #${enlace.dataset.ticketId || ''}`;
    pagina.href = enlace.href;
    cargando.hidden = false;
    iframe.src = urlEmbebida(enlace.href);
    panel.hidden = false;
    document.body.classList.add('tk-panel-abierto');
    requestAnimationFrame(() => panel.classList.add('is-open'));
    caja.focus({preventScroll: true});
  }

  function cerrar() {
    if (panel.hidden) return;
    panel.classList.remove('is-open');
    document.body.classList.remove('tk-panel-abierto');
    const huboCambios = cargas > 1;   // el detalle se recargó = se guardó algo
    timer = window.setTimeout(() => {
      panel.hidden = true;
      iframe.src = 'about:blank';
      if (huboCambios) window.location.reload();
      else ultimo?.focus();
    }, 220);
  }

  iframe.addEventListener('load', () => {
    if (iframe.src === 'about:blank' || !iframe.src) return;
    cargas += 1;
    cargando.hidden = true;
    try {   // Esc dentro del detalle también cierra el panel (salvo que haya un modal abierto ahí)
      const doc = iframe.contentDocument;
      doc?.addEventListener('keydown', (e) => {
        const modalAbierto = doc.querySelector('.modal-overlay.open, .modal-overlay.active, .modal-overlay.show, .ticket-chat.is-open, .tk-ok');
        if (e.key === 'Escape' && !modalAbierto) cerrar();
      }, true);   // en captura: se revisa antes de que sidebar.js cierre el modal interno
    } catch (_) { /* otro origen: se ignora */ }
  });

  document.addEventListener('click', (e) => {
    const enlace = e.target.closest('a.js-ticket-ver');
    if (enlace && !(e.ctrlKey || e.metaKey || e.shiftKey || e.button === 1)) {
      e.preventDefault();
      abrir(enlace);
      return;
    }
    if (e.target.closest('[data-panel-cerrar]')) cerrar();
  });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !panel.hidden && !document.querySelector('.ticket-chat.is-open')) cerrar();
  });

  window.PanelDetalle = {abrir, cerrar};
})();
