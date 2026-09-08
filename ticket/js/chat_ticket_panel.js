(() => {
  'use strict';

  const panel = document.getElementById('ticket-chat-panel');
  if (!panel || panel.dataset.ready === '1') return;
  panel.dataset.ready = '1';

  const csrf = panel.dataset.csrf || '';
  const drawer = panel.querySelector('.ticket-chat__drawer');
  const title = document.getElementById('ticket-chat-title');
  const folio = document.getElementById('ticket-chat-folio');
  const status = document.getElementById('ticket-chat-status');
  const thread = document.getElementById('ticket-chat-thread');
  const form = document.getElementById('ticket-chat-form');
  const message = document.getElementById('ticket-chat-message');
  const fileInput = document.getElementById('ticket-chat-files');
  const fileList = document.getElementById('ticket-chat-file-list');
  const fileCount = document.getElementById('ticket-chat-file-count');
  const picker = panel.querySelector('.ticket-chat__picker');
  const closed = document.getElementById('ticket-chat-closed');
  let ticketId = 0;
  let timer = 0;
  let loading = false;
  let refreshController = null;
  let closeTimer = 0;
  let lastTrigger = null;
  let messageSignature = '';
  let fileSignature = '';
  let panelMode = 'chat';

  function setStatus(text = '', type = '') {
    status.textContent = text;
    status.className = `ticket-chat__status${type ? ` is-${type}` : ''}`;
    status.hidden = text === '';
  }

  async function post(url, body, signal = undefined) {
    const response = await fetch(url, {method: 'POST', body, credentials: 'same-origin', signal});
    let data;
    try { data = await response.json(); }
    catch (_) { throw new Error('El servidor devolvió una respuesta no válida.'); }
    if (!response.ok || !data.ok) throw new Error(data.error || 'No fue posible completar la solicitud.');
    return data;
  }

  function emptyNode(node) {
    while (node.firstChild) node.removeChild(node.firstChild);
  }

  function fileIcon(path = '') {
    const extension = String(path).split('?')[0].split('.').pop().toLowerCase();
    const icons = {
      pdf: 'bi-file-earmark-pdf',
      doc: 'bi-file-earmark-word',
      docx: 'bi-file-earmark-word',
      xls: 'bi-file-earmark-excel',
      xlsx: 'bi-file-earmark-excel',
      jpg: 'bi-file-earmark-image',
      jpeg: 'bi-file-earmark-image',
      png: 'bi-file-earmark-image',
      webp: 'bi-file-earmark-image',
      txt: 'bi-file-earmark-text',
      zip: 'bi-file-earmark-zip',
    };
    return `bi ${icons[extension] || 'bi-file-earmark'}`;
  }

  function renderMessages(messages) {
    const nearBottom = thread.scrollHeight - thread.scrollTop - thread.clientHeight < 80;
    emptyNode(thread);
    if (!messages.length) {
      const empty = document.createElement('div');
      empty.className = 'ticket-chat__empty';
      empty.innerHTML = '<i class="bi bi-chat-square-dots"></i><strong>Aún no hay mensajes</strong><span>Escribe el primero para iniciar el seguimiento.</span>';
      thread.appendChild(empty);
      return;
    }
    messages.forEach((item) => {
      const own = Number(item.id_usuario) === Number(panel.dataset.userId);
      const row = document.createElement('article');
      row.className = `ticket-message-row${own ? ' is-own' : ''}`;
      const avatar = document.createElement('div');
      avatar.className = 'ticket-message-avatar';
      avatar.textContent = String(item.autor_nombre || 'U').trim().charAt(0).toUpperCase() || 'U';
      const bubble = document.createElement('div');
      bubble.className = 'ticket-message-bubble';
      const meta = document.createElement('div');
      const author = document.createElement('strong');
      author.textContent = item.autor_nombre || 'Usuario';
      const time = document.createElement('time');
      const parsed = item.fecha_comentario ? new Date(String(item.fecha_comentario).replace(' ', 'T')) : null;
      time.textContent = parsed && !Number.isNaN(parsed.getTime())
        ? parsed.toLocaleString('es-CL', {day: '2-digit', month: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit'})
        : '';
      const copy = document.createElement('p');
      copy.textContent = item.comentario || '';
      meta.append(author, time);
      bubble.append(meta, copy);
      row.append(avatar, bubble);
      thread.appendChild(row);
    });
    if (nearBottom || thread.dataset.initial !== '1') {
      thread.scrollTop = thread.scrollHeight;
      thread.dataset.initial = '1';
    }
  }

  function renderFiles(files, enabled) {
    emptyNode(fileList);
    fileCount.textContent = `${files.length}/5`;
    picker.hidden = !enabled || files.length >= 5;
    fileInput.disabled = !enabled || files.length >= 5;
    if (!files.length) {
      const empty = document.createElement('span');
      empty.className = 'ticket-chat__no-files';
      empty.textContent = 'Sin archivos adjuntos.';
      fileList.appendChild(empty);
      return;
    }
    files.forEach((item) => {
      const link = document.createElement('a');
      const route = String(item.ruta_archivo || '').replace(/^\/+/, '');
      link.href = `../${route}`;
      link.target = '_blank';
      link.rel = 'noopener';
      const icon = document.createElement('i');
      icon.className = fileIcon(item.ruta_archivo || item.nombre_archivo);
      const text = document.createElement('span');
      text.textContent = item.nombre_archivo || 'Archivo';
      const size = document.createElement('small');
      size.textContent = `${Math.max(1, Math.round(Number(item.tamano || 0) / 1024))} KB`;
      link.append(icon, text, size);
      fileList.appendChild(link);
    });
  }

  async function refresh({silent = false} = {}) {
    if (!ticketId || loading || panel.hidden) return;
    const requestedTicketId = ticketId;
    loading = true;
    refreshController = new AbortController();
    if (!silent) {
      thread.setAttribute('aria-busy', 'true');
      setStatus(panelMode === 'files' ? 'Cargando archivos…' : 'Cargando conversación…');
    }
    try {
      const body = new URLSearchParams({ticket_id: String(ticketId), csrf});
      const data = await post('../ajax/obtener_conversacion_ticket.php', body, refreshController.signal);
      if (requestedTicketId !== ticketId || panel.hidden) return;
      title.textContent = data.ticket.asunto || `Ticket #${ticketId}`;
      const chatMode = panelMode === 'chat';
      folio.textContent = chatMode ? `Ticket #${ticketId}` : `Archivos · Ticket #${ticketId}`;
      const writable = Boolean(data.conversacion_disponible && !data.ticket.cerrado);
      form.hidden = !chatMode || !writable;
      closed.hidden = !chatMode || !data.ticket.cerrado;
      const messages = Array.isArray(data.mensajes) ? data.mensajes : [];
      const files = Array.isArray(data.adjuntos) ? data.adjuntos : [];
      const nextMessageSignature = JSON.stringify(messages.map((item) => [item.id_comentario, item.comentario, item.fecha_comentario]));
      const nextFileSignature = JSON.stringify(files.map((item) => [item.id_archivo, item.ruta_archivo, item.tamano]));
      if (nextMessageSignature !== messageSignature) {
        renderMessages(messages);
        messageSignature = nextMessageSignature;
      }
      if (nextFileSignature !== fileSignature) {
        renderFiles(files, Boolean(chatMode && data.adjuntos_disponibles && !data.ticket.cerrado));
        fileSignature = nextFileSignature;
      } else {
        picker.hidden = !chatMode || !data.adjuntos_disponibles || data.ticket.cerrado || files.length >= 5;
        fileInput.disabled = picker.hidden;
      }
      if (!silent) setStatus();
    } catch (error) {
      if (error.name !== 'AbortError') setStatus(error.message, 'error');
    } finally {
      loading = false;
      refreshController = null;
      thread.removeAttribute('aria-busy');
    }
  }

  function close() {
    window.clearInterval(timer);
    timer = 0;
    refreshController?.abort();
    refreshController = null;
    loading = false;
    panel.classList.remove('is-open');
    document.body.classList.remove('ticket-chat-open');
    window.clearTimeout(closeTimer);
    closeTimer = window.setTimeout(() => {
      panel.hidden = true;
      panel.classList.remove('is-files-only');
      ticketId = 0;
      lastTrigger?.focus();
    }, 180);
  }

  function open(id, trigger, mode = 'chat') {
    window.clearTimeout(closeTimer);
    refreshController?.abort();
    refreshController = null;
    loading = false;
    ticketId = Number(id) || 0;
    if (!ticketId) return;
    panelMode = mode === 'files' ? 'files' : 'chat';
    panel.classList.toggle('is-files-only', panelMode === 'files');
    messageSignature = '';
    fileSignature = '';
    lastTrigger = trigger || null;
    thread.dataset.initial = '0';
    title.textContent = 'Cargando ticket…';
    folio.textContent = panelMode === 'files' ? `Archivos · Ticket #${ticketId}` : `Ticket #${ticketId}`;
    emptyNode(thread);
    emptyNode(fileList);
    form.hidden = true;
    closed.hidden = true;
    panel.hidden = false;
    document.body.classList.add('ticket-chat-open');
    requestAnimationFrame(() => panel.classList.add('is-open'));
    drawer.focus({preventScroll: true});
    refresh();
    window.clearInterval(timer);
    timer = window.setInterval(() => refresh({silent: true}), 3000);
  }

  document.addEventListener('click', (event) => {
    const filesTrigger = event.target.closest('.js-ticket-files');
    if (filesTrigger) {
      event.preventDefault();
      open(filesTrigger.dataset.ticketId, filesTrigger, 'files');
      return;
    }
    const trigger = event.target.closest('.js-ticket-chat');
    if (trigger) {
      event.preventDefault();
      open(trigger.dataset.ticketId, trigger);
      return;
    }
    if (event.target.closest('[data-chat-close]')) close();
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !panel.hidden) close();
  });

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity() || !ticketId) return;
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    setStatus('Enviando mensaje…');
    try {
      const body = new URLSearchParams({ticket_id: String(ticketId), csrf, comentario: message.value.trim()});
      await post('../ajax/guardar_comentario.php', body);
      message.value = '';
      setStatus('Mensaje enviado.', 'success');
      await refresh({silent: true});
    } catch (error) {
      setStatus(error.message, 'error');
    } finally {
      button.disabled = false;
    }
  });

  fileInput.addEventListener('change', async () => {
    const files = Array.from(fileInput.files || []);
    if (!files.length || !ticketId) return;
    if (files.some((file) => file.size > 5 * 1024 * 1024)) {
      setStatus('Cada archivo debe pesar como máximo 5 MB.', 'error');
      fileInput.value = '';
      return;
    }
    const body = new FormData();
    body.append('ticket_id', String(ticketId));
    body.append('csrf', csrf);
    files.forEach((file) => body.append('archivos[]', file));
    fileInput.disabled = true;
    setStatus('Subiendo archivos…');
    try {
      const data = await post('../ajax/guardar_adjunto_ticket.php', body);
      setStatus(data.mensaje, 'success');
      await refresh({silent: true});
    } catch (error) {
      setStatus(error.message, 'error');
    } finally {
      fileInput.value = '';
      fileInput.disabled = false;
    }
  });

  window.TicketChatPanel = {
    open,
    openFiles: (id, trigger) => open(id, trigger, 'files'),
    close,
    refresh,
  };
})();
