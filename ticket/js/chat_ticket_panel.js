/*
 * Mensajería de tickets: panel de chat + número de no leídos en el botón "Chat".
 * Lógica: ticket/logica/LOGICA_MENSAJERIA.txt
 *
 *  - Con el panel abierto se consulta la conversación cada 3 s (casi en tiempo real).
 *  - En la bandeja se consultan los no leídos cada 8 s (solo vistas usuario y técnico).
 *  - data-vista del panel dice desde qué bandeja se escribe: usuario | tecnico | admin.
 */
(() => {
  'use strict';

  const panel = document.getElementById('ticket-chat-panel');
  if (!panel || panel.dataset.ready === '1') return;
  panel.dataset.ready = '1';

  const API = '../ajax/';
  const csrf = panel.dataset.csrf || '';
  const vista = panel.dataset.vista || 'usuario';
  const $ = (id) => document.getElementById(id);
  const drawer = panel.querySelector('.ticket-chat__drawer');
  const title = $('ticket-chat-title');
  const folio = $('ticket-chat-folio');
  const withWho = $('ticket-chat-with');
  const status = $('ticket-chat-status');
  const thread = $('ticket-chat-thread');
  const form = $('ticket-chat-form');
  const message = $('ticket-chat-message');
  const fileInput = $('ticket-chat-files');
  const fileList = $('ticket-chat-file-list');
  const fileCount = $('ticket-chat-file-count');
  const closed = $('ticket-chat-closed');
  const sendButton = form.querySelector('.tc-send');
  const LADOS = {usuario: 'Solicitante', tecnico: 'Técnico', admin: 'Administración'};
  const MAX_FILES = 5;

  let ticketId = 0;
  let timer = 0;
  let loading = false;
  let controller = null;
  let closeTimer = 0;
  let lastTrigger = null;
  let firma = '';
  let firmaArchivos = '';
  let modo = 'chat';
  let archivosActuales = 0;

  /* ---------- utilidades ---------- */
  function setStatus(text = '', type = '') {
    status.textContent = text;
    status.className = `ticket-chat__status${type ? ` is-${type}` : ''}`;
    status.hidden = text === '';
    if (type === 'success') window.setTimeout(() => { if (status.textContent === text) setStatus(); }, 2500);
  }

  async function post(url, body, signal) {
    const response = await fetch(url, {method: 'POST', body, credentials: 'same-origin', signal});
    let data;
    try { data = await response.json(); } catch (_) { throw new Error('El servidor devolvió una respuesta no válida.'); }
    if (!response.ok || !data.ok) throw new Error(data.error || data.mensaje || 'No fue posible completar la solicitud.');
    return data;
  }

  function el(tag, cls, text) {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (text !== undefined) n.textContent = text;
    return n;
  }

  const ICONOS = {pdf: 'bi-file-earmark-pdf', doc: 'bi-file-earmark-word', docx: 'bi-file-earmark-word',
    xls: 'bi-file-earmark-excel', xlsx: 'bi-file-earmark-excel', jpg: 'bi-file-earmark-image', jpeg: 'bi-file-earmark-image',
    png: 'bi-file-earmark-image', webp: 'bi-file-earmark-image', gif: 'bi-file-earmark-image', txt: 'bi-file-earmark-text', zip: 'bi-file-earmark-zip'};
  const esImagen = (ext) => ['jpg', 'jpeg', 'png', 'webp', 'gif'].includes(ext);

  function fecha(txt) {
    const d = txt ? new Date(String(txt).replace(' ', 'T')) : null;
    return d && !Number.isNaN(d.getTime()) ? d : null;
  }

  function etiquetaDia(d) {
    const hoy = new Date(); hoy.setHours(0, 0, 0, 0);
    const dia = new Date(d); dia.setHours(0, 0, 0, 0);
    const diff = Math.round((hoy - dia) / 86400000);
    if (diff === 0) return 'Hoy';
    if (diff === 1) return 'Ayer';
    return d.toLocaleDateString('es-CL', {weekday: 'long', day: 'numeric', month: 'long', year: hoy.getFullYear() === d.getFullYear() ? undefined : 'numeric'});
  }

  /* ---------- conversación ---------- */
  function renderMensajes(mensajes) {
    const cercaDelFinal = thread.scrollHeight - thread.scrollTop - thread.clientHeight < 90;
    thread.replaceChildren();
    if (!mensajes.length) {
      const vacio = el('div', 'ticket-chat__empty');
      vacio.innerHTML = '<i class="bi bi-chat-square-dots"></i><strong>Aún no hay mensajes</strong>';
      vacio.append(el('span', '', vista === 'tecnico'
        ? 'Escríbele a quien pidió el ticket para comenzar.'
        : 'Escribe el primero: el técnico lo verá al instante.'));
      thread.append(vacio);
      return;
    }
    let diaAnterior = '';
    let ladoAnterior = '';
    mensajes.forEach((m) => {
      const d = fecha(m.fecha);
      const dia = d ? d.toDateString() : '';
      if (d && dia !== diaAnterior) {
        thread.append(el('div', 'tc-day', etiquetaDia(d)));
        diaAnterior = dia;
        ladoAnterior = '';
      }
      const fila = el('article', `tc-msg${m.propio ? ' is-own' : ''}${m.lado === ladoAnterior ? ' is-cont' : ''}`);
      fila.dataset.lado = m.lado;
      const burbuja = el('div', 'tc-bubble');
      if (!m.propio && m.lado !== ladoAnterior) {
        const quien = el('div', 'tc-author');
        quien.append(el('strong', '', m.autor), el('span', `tc-role tc-role--${m.lado}`, LADOS[m.lado] || ''));
        burbuja.append(quien);
      }
      if (m.adjunto) {
        const a = el('a', 'tc-file');
        a.href = m.adjunto.url;
        a.target = '_blank';
        a.rel = 'noopener';
        if (esImagen(m.adjunto.extension)) {
          a.classList.add('is-image');
          const img = el('img');
          img.src = m.adjunto.url;
          img.alt = m.adjunto.nombre;
          img.loading = 'lazy';
          a.append(img);
        } else {
          const i = el('i', `bi ${ICONOS[m.adjunto.extension] || 'bi-file-earmark'}`);
          a.append(i, el('span', '', m.adjunto.nombre), el('small', '', (m.adjunto.extension || '').toUpperCase()));
        }
        burbuja.append(a);
      } else {
        burbuja.append(el('p', 'tc-text', m.texto));
      }
      const pie = el('div', 'tc-meta');
      pie.append(el('time', '', d ? d.toLocaleTimeString('es-CL', {hour: '2-digit', minute: '2-digit'}) : ''));
      if (m.propio) {
        const check = el('span', `tc-check${m.visto ? ' is-seen' : ''}`);
        check.innerHTML = m.visto ? '<i class="bi bi-check2-all"></i>' : '<i class="bi bi-check2"></i>';
        check.title = m.visto ? 'Visto' : 'Enviado';
        pie.append(check);
      }
      burbuja.append(pie);
      fila.append(burbuja);
      thread.append(fila);
      ladoAnterior = m.lado;
    });
    if (cercaDelFinal || thread.dataset.initial !== '1') {
      thread.scrollTop = thread.scrollHeight;
      thread.dataset.initial = '1';
      thread.querySelectorAll('img').forEach((img) => img.addEventListener('load', () => { thread.scrollTop = thread.scrollHeight; }, {once: true}));
    }
  }

  function renderArchivos(archivos) {
    fileList.replaceChildren();
    archivosActuales = archivos.length;
    fileCount.textContent = `${archivos.length}/${MAX_FILES}`;
    if (!archivos.length) {
      fileList.append(el('span', 'ticket-chat__no-files', 'Sin archivos adjuntos.'));
      return;
    }
    archivos.forEach((f) => {
      const a = el('a');
      a.href = f.url;
      a.target = '_blank';
      a.rel = 'noopener';
      a.append(el('i', `bi ${ICONOS[f.extension] || 'bi-file-earmark'}`), el('span', '', f.nombre),
        el('small', '', f.tamano ? `${Math.max(1, Math.round(f.tamano / 1024))} KB` : (f.extension || '').toUpperCase()));
      fileList.append(a);
    });
  }

  async function refresh({silent = false} = {}) {
    if (!ticketId || loading || panel.hidden) return;
    const pedido = ticketId;
    loading = true;
    controller = new AbortController();
    if (!silent) {
      thread.setAttribute('aria-busy', 'true');
      setStatus(modo === 'files' ? 'Cargando archivos…' : 'Cargando conversación…');
    }
    try {
      const body = new URLSearchParams({ticket_id: String(ticketId), csrf, vista, marcar: document.hidden ? '0' : '1'});
      const data = await post(`${API}obtener_conversacion_ticket.php`, body, controller.signal);
      if (pedido !== ticketId || panel.hidden) return;
      const t = data.ticket;
      title.textContent = t.asunto || `Ticket #${ticketId}`;
      folio.textContent = modo === 'files' ? `Archivos · Ticket #${ticketId}` : `Ticket #${ticketId}`;
      const otro = data.vista === 'tecnico' ? `con ${t.solicitante || 'el solicitante'}`
        : (data.vista === 'usuario' ? (t.tecnico ? `con ${t.tecnico} (técnico)` : 'Aún sin técnico asignado: tu mensaje quedará para quien lo tome')
          : `${t.solicitante || 'Solicitante'} ↔ ${t.tecnico || 'sin técnico'}`);
      withWho.textContent = modo === 'chat' ? otro : '';
      const chat = modo === 'chat';
      form.hidden = !chat || !data.puede_escribir;
      closed.hidden = !chat || data.puede_escribir;
      const mensajes = Array.isArray(data.mensajes) ? data.mensajes : [];
      const archivos = Array.isArray(data.adjuntos) ? data.adjuntos : [];
      const f1 = JSON.stringify(mensajes.map((m) => [m.id, m.visto]));
      if (f1 !== firma) { renderMensajes(mensajes); firma = f1; }
      const f2 = JSON.stringify(archivos.map((a) => a.id));
      if (f2 !== firmaArchivos) { renderArchivos(archivos); firmaArchivos = f2; }
      fileInput.disabled = archivosActuales >= MAX_FILES;
      form.querySelector('.tc-attach').classList.toggle('is-disabled', fileInput.disabled);
      if (!document.hidden) Badges.poner(ticketId, 0);
      if (data.falta_sql) setStatus('Falta ejecutar ticket/sql/01_mensajeria.sql en la base: sin eso, si pides y atiendes el mismo ticket, todos los mensajes se ven como del solicitante.', 'error');
      else if (!silent) setStatus();
    } catch (error) {
      if (error.name !== 'AbortError') setStatus(error.message, 'error');
    } finally {
      loading = false;
      controller = null;
      thread.removeAttribute('aria-busy');
    }
  }

  function close() {
    window.clearInterval(timer);
    timer = 0;
    controller?.abort();
    loading = false;
    panel.classList.remove('is-open');
    document.body.classList.remove('ticket-chat-open');
    window.clearTimeout(closeTimer);
    closeTimer = window.setTimeout(() => {
      panel.hidden = true;
      panel.classList.remove('is-files-only');
      ticketId = 0;
      lastTrigger?.focus();
      Badges.actualizar();
    }, 180);
  }

  function open(id, trigger, mode = 'chat') {
    window.clearTimeout(closeTimer);
    controller?.abort();
    loading = false;
    ticketId = Number(id) || 0;
    if (!ticketId) return;
    modo = mode === 'files' ? 'files' : 'chat';
    panel.classList.toggle('is-files-only', modo === 'files');
    firma = '';
    firmaArchivos = '';
    lastTrigger = trigger || null;
    thread.dataset.initial = '0';
    title.textContent = 'Cargando ticket…';
    withWho.textContent = '';
    folio.textContent = modo === 'files' ? `Archivos · Ticket #${ticketId}` : `Ticket #${ticketId}`;
    thread.replaceChildren();
    fileList.replaceChildren();
    form.hidden = true;
    closed.hidden = true;
    panel.hidden = false;
    document.body.classList.add('ticket-chat-open');
    requestAnimationFrame(() => panel.classList.add('is-open'));
    drawer.focus({preventScroll: true});
    refresh().then(() => { if (modo === 'chat' && !form.hidden) message.focus({preventScroll: true}); });
    window.clearInterval(timer);
    timer = window.setInterval(() => refresh({silent: true}), 3000);
  }

  /* ---------- enviar ---------- */
  function ajustarAlto() {
    message.style.height = 'auto';
    message.style.height = `${Math.min(message.scrollHeight, 150)}px`;
  }
  message.addEventListener('input', ajustarAlto);
  message.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
      e.preventDefault();
      form.requestSubmit();
    }
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const texto = message.value.trim();
    if (!texto || !ticketId || sendButton.disabled) return;
    sendButton.disabled = true;
    try {
      await post(`${API}guardar_comentario.php`, new URLSearchParams({ticket_id: String(ticketId), csrf, vista, comentario: texto}));
      message.value = '';
      ajustarAlto();
      thread.dataset.initial = '0';   // baja al último mensaje
      loading = false;
      await refresh({silent: true});
    } catch (error) {
      setStatus(error.message, 'error');
    } finally {
      sendButton.disabled = false;
      message.focus();
    }
  });

  fileInput.addEventListener('change', async () => {
    const archivos = Array.from(fileInput.files || []);
    fileInput.value = '';
    if (!archivos.length || !ticketId) return;
    if (archivos.some((f) => f.size > 5 * 1024 * 1024)) { setStatus('Cada archivo puede pesar como máximo 5 MB.', 'error'); return; }
    if (archivosActuales + archivos.length > MAX_FILES) { setStatus(`El ticket admite ${MAX_FILES} archivos en total (ya tiene ${archivosActuales}).`, 'error'); return; }
    const body = new FormData();
    body.append('ticket_id', String(ticketId));
    body.append('csrf', csrf);
    body.append('vista', vista);
    archivos.forEach((f) => body.append('archivos[]', f));
    fileInput.disabled = true;
    setStatus(archivos.length === 1 ? `Subiendo ${archivos[0].name}…` : `Subiendo ${archivos.length} archivos…`);
    try {
      const data = await post(`${API}guardar_adjunto_ticket.php`, body);
      setStatus(data.mensaje, 'success');
      thread.dataset.initial = '0';
      await refresh({silent: true});
    } catch (error) {
      setStatus(error.message, 'error');
    } finally {
      fileInput.disabled = archivosActuales >= MAX_FILES;
    }
  });

  /* ---------- número de no leídos en el botón "Chat" ---------- */
  const Badges = (() => {
    const previos = new Map();
    const tituloBase = document.title.replace(/^\(\d+\+?\)\s*/, '');
    let enCurso = false;

    function poner(id, n) {
      document.querySelectorAll(`.js-ticket-chat[data-ticket-id="${id}"]`).forEach((b) => {
        let s = b.querySelector('.ticket-chat-unread');
        if (!s) { s = el('span', 'ticket-chat-unread'); b.append(s); }
        s.textContent = n > 99 ? '99+' : String(n);
        s.hidden = n <= 0;
        b.classList.toggle('has-unread', n > 0);
        const t = n === 0 ? 'Abrir chat' : (n === 1 ? '1 mensaje sin leer' : `${n} mensajes sin leer`);
        b.title = t;
        b.setAttribute('aria-label', `Chat: ${t}`);
        if (n > (previos.get(id) || 0)) {
          b.classList.remove('is-new');
          void b.offsetWidth;   // reinicia la animación
          b.classList.add('is-new');
        }
      });
      previos.set(id, n);
      const total = [...previos.values()].reduce((a, v) => a + v, 0);
      document.title = total ? `(${total}) ${tituloBase}` : tituloBase;
    }

    // Avances nuevos de la bitácora interna en el botón "Abrir" (técnico y administración)
    async function actualizarBitacora() {
      if (!['tecnico', 'admin'].includes(vista)) return;
      const ids = [...new Set([...document.querySelectorAll('.js-ticket-open[data-ticket-id]')].map((b) => Number(b.dataset.ticketId)).filter(Boolean))];
      if (!ids.length) return;
      try {
        const body = new URLSearchParams({csrf, accion: 'no_leidos', rol: vista});
        ids.forEach((id) => body.append('ids[]', String(id)));
        const r = await fetch(`${API}ticket_bitacora.php`, {method: 'POST', credentials: 'same-origin', body});
        const data = await r.json();
        if (!data.ok) return;
        ids.forEach((id) => {
          const n = Number(data.no_leidos[id] || 0);
          document.querySelectorAll(`.js-ticket-open[data-ticket-id="${id}"]`).forEach((b) => {
            const s = b.querySelector('.ticket-bitacora-unread');
            if (!s) return;
            s.hidden = n <= 0;
            s.lastChild.textContent = n > 99 ? '99+' : String(n);
            b.classList.toggle('has-unread', n > 0);
            b.title = n === 0 ? 'Abrir el detalle' : (n === 1 ? '1 avance nuevo en la bitácora' : `${n} avances nuevos en la bitácora`);
          });
        });
      } catch (_) { /* se reintenta */ }
    }

    async function actualizar() {
      if (enCurso || document.hidden) return;
      actualizarBitacora();
      if (!['usuario', 'tecnico'].includes(vista)) return;
      const ids = [...new Set([...document.querySelectorAll('.js-ticket-chat[data-ticket-id]')].map((b) => Number(b.dataset.ticketId)).filter(Boolean))];
      if (!ids.length) return;
      enCurso = true;
      try {
        const r = await fetch(`${API}ticket_no_leidos.php`, {
          method: 'POST', credentials: 'same-origin',
          headers: {'Content-Type': 'application/json'},
          body: JSON.stringify({csrf, vista, ids}),
        });
        const data = await r.json();
        if (!data.ok) return;
        ids.forEach((id) => poner(id, id === ticketId && !panel.hidden ? 0 : Number(data.no_leidos[id] || 0)));
      } catch (_) { /* sin red: se reintenta en el próximo ciclo */ } finally { enCurso = false; }
    }

    // valores que vienen ya dibujados desde PHP
    document.querySelectorAll('.js-ticket-chat .ticket-chat-unread').forEach((s) => {
      const b = s.closest('.js-ticket-chat');
      previos.set(Number(b.dataset.ticketId), s.hidden ? 0 : parseInt(s.textContent, 10) || 0);
    });
    // La bandeja de "Ticket Nuevo" se vuelve a dibujar sola cada 3 s con datos
    // frescos del servidor: se toman esos valores (y se anima si subieron).
    new MutationObserver(() => {
      document.querySelectorAll('.js-ticket-chat[data-ticket-id]').forEach((b) => {
        const id = Number(b.dataset.ticketId);
        const s = b.querySelector('.ticket-chat-unread');
        let n = s && !s.hidden ? parseInt(s.textContent, 10) || 0 : 0;
        if (id === ticketId && !panel.hidden) n = 0;
        if (n !== (previos.get(id) || 0) || (s && (s.hidden ? 0 : parseInt(s.textContent, 10) || 0) !== n)) poner(id, n);
      });
    }).observe(document.body, {childList: true, subtree: true});

    window.setInterval(actualizar, 8000);
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) return;
      actualizar();
      if (!panel.hidden) refresh({silent: true});
    });
    window.setTimeout(actualizar, 600);
    return {poner, actualizar};
  })();

  /* ---------- eventos ---------- */
  document.addEventListener('click', (event) => {
    const archivos = event.target.closest('.js-ticket-files');
    if (archivos) { event.preventDefault(); open(archivos.dataset.ticketId, archivos, 'files'); return; }
    const chat = event.target.closest('.js-ticket-chat');
    if (chat) { event.preventDefault(); open(chat.dataset.ticketId, chat); return; }
    if (event.target.closest('[data-chat-close]')) close();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !panel.hidden) close();
  });

  window.TicketChatPanel = {open, openFiles: (id, t) => open(id, t, 'files'), close, refresh, badges: Badges};
})();
