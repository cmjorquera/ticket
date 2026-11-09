/* Modal "Enviar ticket por correo" (ticket/componentes/modal_correo_ticket.php). */
(() => {
  'use strict';
  const form = document.getElementById('tk-correo-form');
  if (!form || form.dataset.ready) return;
  form.dataset.ready = '1';
  const para = document.getElementById('tk-correo-para');
  const ayuda = document.getElementById('tk-correo-ayuda');
  const aviso = document.getElementById('tk-correo-mensaje');
  const enviar = document.getElementById('tk-correo-enviar');

  function abrir(b) {
    form.reset();
    document.getElementById('tk-correo-id').value = b.dataset.ticketId;
    document.getElementById('tk-correo-folio').textContent = `Ticket #${b.dataset.ticketId}`;
    para.value = b.dataset.email || '';
    const soloYo = b.dataset.soloYo === '1';
    para.readOnly = soloYo;
    ayuda.textContent = soloYo ? 'Se envía a tu correo registrado.' : 'Por defecto, el correo de quien pidió el ticket. Puedes cambiarlo.';
    document.querySelector('.tk-correo__copia').hidden = soloYo;
    aviso.className = 'ticket-message';
    aviso.textContent = '';
    enviar.disabled = false;
    openModal('modal-correo-ticket');
    window.setTimeout(() => (soloYo ? enviar : para).focus(), 60);
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!para.value.trim() || !para.checkValidity()) { aviso.className = 'ticket-message error'; aviso.textContent = 'Escribe un correo válido.'; return; }
    enviar.disabled = true;
    aviso.className = 'ticket-message';
    aviso.textContent = 'Enviando…';
    try {
      const r = await fetch('../ajax/ticket_correo.php', {
        method: 'POST', credentials: 'same-origin',
        body: new URLSearchParams({csrf: form.dataset.csrf, ticket_id: document.getElementById('tk-correo-id').value,
          para: para.value.trim(), copia: document.getElementById('tk-correo-copia').checked ? '1' : '0'}),
      });
      const d = await r.json().catch(() => ({ok: false, error: 'Respuesta no válida del servidor.'}));
      if (!r.ok || !d.ok) throw new Error(d.error || 'No fue posible enviar el correo.');
      aviso.className = 'ticket-message ok';
      aviso.textContent = d.mensaje;
      window.setTimeout(() => closeModal('modal-correo-ticket'), 1800);
    } catch (err) {
      aviso.className = 'ticket-message error';
      aviso.textContent = err.message;
      enviar.disabled = false;
    }
  });

  document.addEventListener('click', (e) => {
    const b = e.target.closest('.js-ticket-correo');
    if (!b) return;
    e.preventDefault();
    abrir(b);
  });
  window.CorreoTicket = {abrir};
})();
