(() => {
  'use strict';

  window.ticketDynamicCreateEnabled = true;
  const form = document.getElementById('form-ticket');
  if (!form) return;

  const csrf = form.querySelector('input[name="csrf"]')?.value || '';
  const message = document.getElementById('ticket-mensaje');
  const createButton = document.getElementById('ticket-guardar');
  const draftButton = document.getElementById('ticket-borrador');
  const category = document.getElementById('ticket-categoria');
  const userSelect = form.querySelector('[data-dynamic-user]');
  const schoolContext = document.getElementById('ticket-colegio-contexto');
  const editor = document.getElementById('ticket-descripcion');
  const editorValue = document.getElementById('ticket-descripcion-value');
  const editorCount = document.getElementById('ticket-description-count');
  const files = document.getElementById('ticket-archivos');
  const fileCount = document.getElementById('ticket-file-count');
  const fileList = document.getElementById('ticket-file-list');
  let loadingForm = false;

  function showMessage(text = '', type = '') {
    message.className = `ticket-message${type ? ` ${type}` : ''}`;
    message.textContent = text;
  }

  async function request(url, body) {
    const response = await fetch(url, {method: 'POST', body, credentials: 'same-origin'});
    let data;
    try { data = await response.json(); }
    catch (_) { throw new Error('El servidor devolvió una respuesta no válida.'); }
    if (!response.ok || !data.ok) throw new Error(data.error || 'No fue posible completar la solicitud.');
    return data;
  }

  function fillSelect(select, rows, valueKey, labelKey, placeholder, selectedValue = '') {
    if (!select) return;
    const fragment = document.createDocumentFragment();
    const first = document.createElement('option');
    first.value = '';
    first.textContent = placeholder;
    fragment.appendChild(first);
    rows.forEach((row) => {
      const option = document.createElement('option');
      option.value = String(row[valueKey]);
      option.textContent = String(row[labelKey] || '');
      option.selected = String(row[valueKey]) === String(selectedValue);
      fragment.appendChild(option);
    });
    select.replaceChildren(fragment);
  }

  function setActionsEnabled(enabled) {
    createButton.disabled = !enabled;
    draftButton.disabled = !enabled;
  }

  async function loadFormData(selectedUserId = '') {
    if (loadingForm) return;
    loadingForm = true;
    setActionsEnabled(false);
    showMessage('Cargando datos del formulario…');
    try {
      const body = new URLSearchParams({csrf});
      if (selectedUserId) body.set('solicitante_id', String(selectedUserId));
      const data = await request('../ajax/datos_formulario_ticket.php', body);
      const selectedCategory = category.value;
      fillSelect(category, data.categorias || [], 'id_categoria', 'nombre_categoria', 'Seleccionar categoría', selectedCategory);

      if (userSelect && Array.isArray(data.usuarios)) {
        const selected = data.solicitante?.id || data.usuario_sesion_id;
        fillSelect(userSelect, data.usuarios, 'id', 'nombre', 'Seleccionar usuario…', selected);
      }

      const schoolName = String(data.solicitante?.colegio || '').trim();
      schoolContext.textContent = schoolName || 'sin colegio asociado';
      const available = Boolean(data.solicitante?.colegio_id && (data.categorias || []).length);
      setActionsEnabled(available);
      showMessage(available ? '' : 'El solicitante no tiene un colegio activo asociado.', available ? '' : 'error');
    } catch (error) {
      setActionsEnabled(false);
      showMessage(error.message, 'error');
    } finally {
      loadingForm = false;
    }
  }

  function syncEditor() {
    const textLength = (editor.innerText || '').trim().length;
    editorValue.value = editor.innerHTML.trim();
    editorCount.textContent = `${textLength}/10000`;
    editorCount.classList.toggle('is-limit', textLength > 10000);
  }

  function renderFiles() {
    const selected = Array.from(files.files || []);
    fileCount.textContent = selected.length
      ? `${selected.length} archivo${selected.length === 1 ? '' : 's'} seleccionado${selected.length === 1 ? '' : 's'}`
      : 'Sin archivos seleccionados';
    fileList.replaceChildren();
    selected.forEach((file) => {
      const item = document.createElement('div');
      item.className = 'ticket-file-item';
      const icon = document.createElement('i');
      icon.className = 'bi bi-file-earmark';
      const name = document.createElement('span');
      name.textContent = file.name;
      const size = document.createElement('small');
      size.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
      item.append(icon, name, size);
      fileList.appendChild(item);
    });
  }

  window.abrirModalTicket = async () => {
    form.reset();
    editor.replaceChildren();
    syncEditor();
    renderFiles();
    showMessage();
    openModal('modal-crear-ticket');
    await loadFormData();
    window.setTimeout(() => document.getElementById('ticket-asunto')?.focus(), 60);
  };

  userSelect?.addEventListener('change', () => {
    if (userSelect.value) loadFormData(userSelect.value);
  });

  editor.addEventListener('input', syncEditor);
  editor.addEventListener('paste', (event) => {
    event.preventDefault();
    const text = event.clipboardData?.getData('text/plain') || '';
    document.execCommand('insertText', false, text);
  });
  document.querySelectorAll('[data-editor-command]').forEach((control) => {
    control.addEventListener('mousedown', (event) => event.preventDefault());
    control.addEventListener('click', () => {
      editor.focus();
      document.execCommand(control.dataset.editorCommand, false);
      syncEditor();
    });
  });
  document.querySelector('[data-editor-format]')?.addEventListener('change', (event) => {
    editor.focus();
    document.execCommand('formatBlock', false, event.target.value);
    syncEditor();
  });
  document.querySelector('[data-editor-link]')?.addEventListener('click', () => {
    const url = window.prompt('Dirección del enlace (https://):', 'https://');
    if (!url) return;
    editor.focus();
    document.execCommand('createLink', false, url);
    syncEditor();
  });

  files.addEventListener('change', () => {
    const selected = Array.from(files.files || []);
    if (selected.length > 5 || selected.some((file) => file.size > 5 * 1024 * 1024)) {
      files.value = '';
      renderFiles();
      showMessage(selected.length > 5 ? 'Puedes adjuntar un máximo de 5 archivos.' : 'Cada archivo debe pesar como máximo 5 MB.', 'error');
      return;
    }
    showMessage();
    renderFiles();
  });

  form.addEventListener('reset', () => window.setTimeout(() => {
    editor.replaceChildren();
    syncEditor();
    renderFiles();
  }, 0));

  async function submit(mode) {
    syncEditor();
    const length = (editor.innerText || '').trim().length;
    if (!form.reportValidity()) return;
    if (length < 10 || length > 10000) {
      showMessage('La descripción debe tener entre 10 y 10000 caracteres.', 'error');
      editor.focus();
      return;
    }

    const originalCreate = createButton.innerHTML;
    const originalDraft = draftButton.innerHTML;
    createButton.disabled = true;
    draftButton.disabled = true;
    const activeButton = mode === 'borrador' ? draftButton : createButton;
    activeButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando…';
    showMessage();
    try {
      const body = new FormData(form);
      body.set('accion', 'crear');
      body.set('modo', mode);
      const data = await request('../ajax/guardar_ticket.php', body);
      showMessage(`${data.mensaje} Folio #${data.ticket_id}.`, 'ok');
      window.setTimeout(() => {
        closeModal('modal-crear-ticket');
        window.location.reload();
      }, 900);
    } catch (error) {
      showMessage(error.message, 'error');
      createButton.disabled = false;
      draftButton.disabled = false;
    } finally {
      createButton.innerHTML = originalCreate;
      draftButton.innerHTML = originalDraft;
    }
  }

  form.addEventListener('submit', (event) => {
    event.preventDefault();
    submit('crear');
  });
  draftButton.addEventListener('click', () => submit('borrador'));
})();
