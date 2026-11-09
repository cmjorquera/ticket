/*
 * Modal "Comenzar ticket": el técnico define prioridad y fecha estimada → En proceso.
 * Lógica: ticket/logica/LOGICA_COMENZAR_TICKET.txt
 */
(() => {
  'use strict';
  const form = document.getElementById('tk-com-form');
  if (!form || form.dataset.ready) return;
  form.dataset.ready = '1';
  const fecha = document.getElementById('tk-com-fecha');
  const plazo = document.getElementById('tk-com-plazo');
  const aviso = document.getElementById('tk-com-mensaje');
  const enviar = document.getElementById('tk-com-enviar');

  const iso = (d) => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
  const hoy = () => { const d = new Date(); d.setHours(0, 0, 0, 0); return d; };

  function mostrarPlazo() {
    form.querySelectorAll('[data-dias]').forEach((b) => b.classList.remove('is-active'));
    if (!fecha.value) { plazo.textContent = ''; return; }
    const dias = Math.round((new Date(`${fecha.value}T00:00:00`) - hoy()) / 86400000);
    form.querySelector(`[data-dias="${dias}"]`)?.classList.add('is-active');
    plazo.textContent = dias < 0 ? 'La fecha no puede ser anterior a hoy.'
      : dias === 0 ? 'Para hoy.' : `En ${dias} día${dias === 1 ? '' : 's'} · ${new Date(`${fecha.value}T00:00:00`).toLocaleDateString('es-CL', {weekday: 'long', day: 'numeric', month: 'long'})}`;
    plazo.classList.toggle('is-error', dias < 0);
  }

  function abrir(id, asunto) {
    form.reset();
    document.getElementById('tk-com-id').value = id;
    document.getElementById('tk-com-folio').textContent = `Ticket #${id}`;
    document.getElementById('tk-com-asunto').textContent = asunto || '';
    const min = hoy();
    const max = hoy(); max.setFullYear(max.getFullYear() + 1);
    fecha.min = iso(min);
    fecha.max = iso(max);
    aviso.className = 'ticket-message';
    aviso.textContent = '';
    mostrarPlazo();
    enviar.disabled = false;
    openModal('modal-comenzar');
    window.setTimeout(() => form.querySelector('input[name="prioridad"]')?.focus(), 60);
  }

  form.querySelectorAll('[data-dias]').forEach((b) => b.addEventListener('click', () => {
    const d = hoy();
    d.setDate(d.getDate() + Number(b.dataset.dias));
    fecha.value = iso(d);
    mostrarPlazo();
  }));
  fecha.addEventListener('input', mostrarPlazo);
  form.addEventListener('change', () => { if (aviso.classList.contains('error')) { aviso.className = 'ticket-message'; aviso.textContent = ''; } });
  form.querySelectorAll('[data-dias]').forEach((b) => b.addEventListener('click', () => form.dispatchEvent(new Event('change'))));

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const prioridad = form.querySelector('input[name="prioridad"]:checked');
    const error = !prioridad ? 'Elige la prioridad.' : (!fecha.value ? 'Indica la fecha estimada.' : (fecha.value < fecha.min ? 'La fecha no puede ser anterior a hoy.' : ''));
    if (error) { aviso.className = 'ticket-message error'; aviso.textContent = error; return; }
    enviar.disabled = true;
    try {
      const r = await fetch('../ajax/guardar_ticket.php', {
        method: 'POST', credentials: 'same-origin',
        body: new URLSearchParams({accion: 'comenzar', csrf: form.dataset.csrf, ticket_id: document.getElementById('tk-com-id').value,
          prioridad: prioridad.value, fecha_estimada: fecha.value}),
      });
      const d = await r.json();
      if (!r.ok || !d.ok) throw new Error(d.error || 'No fue posible comenzar el ticket.');
      aviso.className = 'ticket-message ok';
      aviso.textContent = 'Ticket comenzado.';
      window.setTimeout(() => window.location.reload(), 500);
    } catch (err) {
      aviso.className = 'ticket-message error';
      aviso.textContent = err.message;
      enviar.disabled = false;
    }
  });

  // Botones "Comenzar ticket" (bandeja técnica y detalle)
  document.addEventListener('click', (e) => {
    const b = e.target.closest('.js-comenzar-ticket');
    if (!b) return;
    e.preventDefault();
    abrir(b.dataset.ticketId, b.dataset.asunto);
  });

  // "Actualizar estado" del detalle: Recibido/Asignado → En proceso abre este modal
  document.getElementById('ticket-state-form')?.addEventListener('submit', (e) => {
    const f = e.currentTarget;
    if (f.estado?.value === '3' && ['1', '2'].includes(f.dataset.estadoActual)) {
      e.preventDefault();
      e.stopImmediatePropagation();
      abrir(f.dataset.ticketId, f.dataset.asunto);
    }
  }, true);

  window.ComenzarTicket = {abrir};
})();
