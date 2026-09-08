(() => {
  'use strict';

  const app = document.getElementById('ticket-detail-app');
  if (!app) return;

  const ticketId = app.dataset.ticketId;
  const csrf = app.dataset.csrf;

  async function enviar(url, datos) {
    const body = new URLSearchParams({ticket_id: ticketId, csrf, ...datos});
    const response = await fetch(url, {method: 'POST', body, credentials: 'same-origin'});
    let data;
    try {
      data = await response.json();
    } catch (_) {
      throw new Error('El servidor devolvió una respuesta no válida.');
    }
    if (!response.ok || !data.ok) throw new Error(data.error || 'No fue posible guardar los cambios.');
    return data;
  }

  function mostrar(elemento, tipo, texto) {
    if (!elemento) return;
    elemento.className = `ticket-message ${tipo}`;
    elemento.textContent = texto;
  }

  function bloquear(boton, activo, textoOriginal) {
    boton.disabled = activo;
    if (activo) boton.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando…';
    else boton.innerHTML = textoOriginal;
  }

  const commentForm = document.getElementById('ticket-comment-form');
  commentForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!commentForm.reportValidity()) return;
    const button = commentForm.querySelector('button[type="submit"]');
    const message = document.getElementById('ticket-comment-message');
    bloquear(button, true, '<i class="bi bi-send"></i> Enviar comentario');
    try {
      const data = await enviar('../ajax/guardar_comentario.php', {comentario: commentForm.comentario.value.trim()});
      mostrar(message, 'ok', data.mensaje);
      setTimeout(() => window.location.reload(), 450);
    } catch (error) {
      mostrar(message, 'error', error.message);
      bloquear(button, false, '<i class="bi bi-send"></i> Enviar comentario');
    }
  });

  const stateForm = document.getElementById('ticket-state-form');
  stateForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    const button = stateForm.querySelector('button[type="submit"]');
    const message = document.getElementById('ticket-state-message');
    bloquear(button, true, 'Guardar estado');
    try {
      const data = await enviar('../ajax/guardar_ticket.php', {accion: 'actualizar_estado', estado: stateForm.estado.value});
      mostrar(message, 'ok', data.mensaje);
      setTimeout(() => window.location.reload(), 450);
    } catch (error) {
      mostrar(message, 'error', error.message);
      bloquear(button, false, 'Guardar estado');
    }
  });

  const ratingForm = document.getElementById('ticket-rating-form');
  ratingForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!ratingForm.reportValidity()) return;
    const button = ratingForm.querySelector('button[type="submit"]');
    const message = document.getElementById('ticket-rating-message');
    const selected = ratingForm.querySelector('input[name="calificacion"]:checked');
    bloquear(button, true, '<i class="bi bi-star"></i> Guardar calificación');
    try {
      const data = await enviar('../ajax/guardar_calificacion.php', {
        calificacion: selected.value,
        comentario: ratingForm.comentario.value.trim()
      });
      mostrar(message, 'ok', data.mensaje);
      setTimeout(() => window.location.reload(), 450);
    } catch (error) {
      mostrar(message, 'error', error.message);
      bloquear(button, false, '<i class="bi bi-star"></i> Guardar calificación');
    }
  });

  const thread = document.getElementById('ticket-thread');
  if (thread) thread.scrollTop = thread.scrollHeight;
})();
