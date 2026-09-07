(() => {
  'use strict';

  const API = '../ajax';
  const state = { areas: [], colegios: [], menus: [] };
  let _todosUsuarios = [];
  let _pag;
  let _estadoFiltro = '';
  const tableBody = document.querySelector('#tabla-usuarios tbody');
  const searchInput = document.getElementById('buscar-usuario');
  const areaFilter = document.getElementById('filtro-area');
  const statusFilter = document.getElementById('filtro-estado');
  const total = document.getElementById('total-usuarios');
  const addButton = document.getElementById('btn-agregar-usuario');

  const escapeHtml = value => String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');

  async function request(url, options = {}) {
    const response = await fetch(url, { credentials: 'same-origin', ...options });
    let payload;
    try {
      payload = await response.json();
    } catch (_) {
      throw new Error('El servidor entregó una respuesta inválida.');
    }
    if (response.status === 401) {
      window.location.href = '../index.php';
      throw new Error('La sesión expiró.');
    }
    if (!response.ok || !payload.ok) {
      throw new Error(payload.mensaje || 'No fue posible completar la operación.');
    }
    return payload.data ?? payload;
  }

  function send(data) {
    return request(`${API}/usuarios_guardar.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify(data),
    });
  }

  function showFeedback(message, isError = false) {
    const feedback = document.getElementById('usuarios-feedback');
    feedback.hidden = false;
    feedback.style.color = isError ? 'var(--danger)' : 'var(--success)';
    feedback.textContent = message;
    window.setTimeout(() => { feedback.hidden = true; }, 4500);
  }

  function setBusy(button, busy, busyText = 'Guardando…') {
    if (busy) {
      button.dataset.label = button.textContent;
      button.textContent = busyText;
    } else if (button.dataset.label) {
      button.textContent = button.dataset.label;
    }
    button.disabled = busy;
  }

  function fullName(user) {
    return [user.nombre, user.apellido_paterno, user.apellido_materno].filter(Boolean).join(' ');
  }

  function initials(user) {
    const parts = fullName(user).trim().split(/\s+/).filter(Boolean);
    return parts.slice(0, 2).map(part => part.charAt(0).toUpperCase()).join('') || 'U';
  }

  function badgeClass(status) {
    const normalized = String(status || '').toLowerCase();
    if (normalized === 'activo') return 'badge-active';
    if (normalized === 'bloqueado') return 'badge-bloqueado';
    if (normalized === 'pendiente') return 'badge-pendiente';
    return 'badge-inactivo';
  }

  function filteredUsers() {
    const query = searchInput.value.trim().toLocaleLowerCase('es');
    const area = areaFilter.value;
    const status = _estadoFiltro.toLocaleLowerCase('es');
    return _todosUsuarios.filter(user => {
      const searchable = `${fullName(user)} ${user.email || ''} ${user.nombre_area || ''} ${user.nom_colegio || ''}`.toLocaleLowerCase('es');
      return (!query || searchable.includes(query))
        && (!area || String(user.id_area_trabajo || '') === area)
        && (!status || String(user.estado || '').toLocaleLowerCase('es') === status);
    });
  }

  function renderPagina(pagina, porPagina) {
    const filtered = filteredUsers();
    const start = (pagina - 1) * porPagina;
    const users = filtered.slice(start, start + porPagina);
    total.textContent = String(filtered.length);
    if (!users.length) {
      tableBody.innerHTML = '<tr><td colspan="7" class="text-muted">No hay usuarios para los filtros seleccionados.</td></tr>';
      return;
    }

    tableBody.innerHTML = users.map((user, index) => {
      const active = String(user.estado || '').toLowerCase() === 'activo';
      return `<tr>
        <td class="mono">${start + index + 1}</td>
        <td><div class="flex items-center gap-3"><span class="user-avatar">${escapeHtml(initials(user))}</span><strong>${escapeHtml(fullName(user))}</strong></div></td>
        <td>${escapeHtml(user.email)}</td>
        <td>${escapeHtml(user.nombre_area || 'Sin área')}</td>
        <td>${escapeHtml(user.nom_colegio || 'Sin colegio')}</td>
        <td><span class="badge ${badgeClass(user.estado)}">${escapeHtml(user.estado || 'Sin estado')}</span></td>
        <td><div class="flex gap-2">
          <button type="button" class="btn-icon" data-action="editar" data-id="${Number(user.id)}" title="Editar"><i class="bi bi-pencil"></i></button>
          <button type="button" class="btn-icon" data-action="permisos" data-id="${Number(user.id)}" title="Permisos"><i class="bi bi-shield-lock"></i></button>
          <button type="button" class="${active ? 'btn-danger-ghost' : 'btn-icon'}" data-action="estado" data-id="${Number(user.id)}" title="${active ? 'Bloquear' : 'Activar'}"><i class="bi ${active ? 'bi-person-slash' : 'bi-person-check'}"></i></button>
        </div></td>
      </tr>`;
    }).join('');
  }

  function aplicarFiltros() {
    const count = filteredUsers().length;
    total.textContent = String(count);
    _pag.actualizar(count);
    renderPagina(1, _pag.porPagina);
  }

  function fillSelect(select, items, valueKey, labelKey, firstLabel, selected = '') {
    select.replaceChildren();
    const first = document.createElement('option');
    first.value = '';
    first.textContent = firstLabel;
    select.appendChild(first);
    items.forEach(item => {
      const option = document.createElement('option');
      option.value = String(item[valueKey]);
      option.textContent = String(item[labelKey] ?? '');
      option.selected = String(item[valueKey]) === String(selected ?? '');
      select.appendChild(option);
    });
  }

  function renderCreateMenus() {
    const container = document.getElementById('u-menus');
    container.replaceChildren();
    state.menus.forEach(menu => {
      const label = document.createElement('label');
      label.className = 'perm-item';
      const name = document.createElement('span');
      name.className = 'perm-item-name';
      name.textContent = menu.nombre;
      const checkbox = document.createElement('input');
      checkbox.type = 'checkbox';
      checkbox.value = menu.id_menu;
      label.append(name, checkbox);
      container.appendChild(label);
    });
  }

  function normalizeSex(value) {
    const sex = String(value || '').toLowerCase();
    if (sex === 'm' || sex === 'masculino') return 'M';
    if (sex === 'f' || sex === 'femenino') return 'F';
    return '';
  }

  function abrirModalUsuario(datos = null) {
    const editing = Boolean(datos);
    document.getElementById('modal-usuario-titulo').textContent = editing ? 'Editar usuario' : 'Nuevo usuario';
    document.getElementById('modal-usuario-id').value = datos?.id ?? '';
    document.getElementById('u-nombre').value = datos?.nombre ?? '';
    document.getElementById('u-apellido-pat').value = datos?.apellido_paterno ?? '';
    document.getElementById('u-apellido-mat').value = datos?.apellido_materno ?? '';
    document.getElementById('u-email').value = datos?.email ?? '';
    document.getElementById('u-email').disabled = editing;
    document.getElementById('u-telefono').value = datos?.telefono ?? '';
    document.getElementById('u-sexo').value = normalizeSex(datos?.sexo);

    fillSelect(document.getElementById('u-area'), state.areas, 'id_area', 'nombre_area', 'Seleccionar', datos?.id_area_trabajo);
    const currentSchool = datos?.id_colegio ?? datos?.colegios?.[0]?.id_colegio ?? '';
    const schoolSelect = document.getElementById('u-colegio');
    fillSelect(schoolSelect, state.colegios, 'id_colegio', 'nom_colegio', 'Sin colegio', currentSchool);
    schoolSelect.disabled = editing;

    document.getElementById('u-menus-wrap').hidden = editing;
    document.getElementById('modal-usuario-error').hidden = true;
    if (!editing) renderCreateMenus();
    openModal('modal-usuario');
    document.getElementById('u-nombre').focus();
  }

  function readUserModal() {
    const id = Number(document.getElementById('modal-usuario-id').value || 0);
    const data = {
      action: id ? 'editar' : 'crear', id,
      nombre: document.getElementById('u-nombre').value.trim(),
      apellido_paterno: document.getElementById('u-apellido-pat').value.trim(),
      apellido_materno: document.getElementById('u-apellido-mat').value.trim(),
      email: document.getElementById('u-email').value.trim(),
      telefono: document.getElementById('u-telefono').value.trim(),
      id_area_trabajo: Number(document.getElementById('u-area').value),
      sexo: document.getElementById('u-sexo').value,
    };
    if (!id) {
      data.id_colegio = Number(document.getElementById('u-colegio').value || 0);
      data.menus = [...document.querySelectorAll('#u-menus input:checked')].map(input => Number(input.value));
    }
    return data;
  }

  async function guardarUsuario() {
    const data = readUserModal();
    const error = document.getElementById('modal-usuario-error');
    error.hidden = true;
    if (!data.nombre || !data.apellido_paterno || !data.email || !data.id_area_trabajo || !data.sexo) {
      error.textContent = 'Completa nombre, apellido paterno, email, área y sexo.';
      error.hidden = false;
      return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
      error.textContent = 'Ingresa un email válido.';
      error.hidden = false;
      return;
    }

    const button = document.getElementById('modal-usuario-guardar');
    setBusy(button, true);
    try {
      const result = await send(data);
      closeModal('modal-usuario');
      showFeedback(result.mensaje);
      await loadUsers();
    } catch (requestError) {
      error.textContent = requestError.message;
      error.hidden = false;
    } finally {
      setBusy(button, false);
    }
  }

  function cambiarEstado(id, nombre, estadoActual) {
    const esActivo = String(estadoActual).toLowerCase() === 'activo';
    document.getElementById('modal-estado-titulo').textContent = esActivo ? '¿Bloquear usuario?' : '¿Activar usuario?';
    document.getElementById('modal-estado-nombre').textContent = nombre;
    const button = document.getElementById('modal-estado-confirmar');
    button.className = esActivo ? 'btn btn-danger' : 'btn btn-primary';
    button.textContent = esActivo ? 'Sí, bloquear' : 'Sí, activar';
    button.onclick = () => ejecutarCambioEstado(id, esActivo ? 'bloquear' : 'activar');
    openModal('modal-estado-usuario');
  }

  async function ejecutarCambioEstado(id, action) {
    const button = document.getElementById('modal-estado-confirmar');
    setBusy(button, true, action === 'bloquear' ? 'Bloqueando…' : 'Activando…');
    try {
      const result = await send({ action, id: Number(id) });
      closeModal('modal-estado-usuario');
      showFeedback(result.mensaje);
      await loadUsers();
    } catch (error) {
      showFeedback(error.message, true);
    } finally {
      setBusy(button, false);
    }
  }

  async function verPermisos(id, nombre) {
    document.getElementById('modal-permisos-titulo').textContent = `Permisos de ${nombre}`;
    document.getElementById('modal-permisos-nombre').textContent = '';
    const content = document.getElementById('modal-permisos-contenido');
    const saveButton = document.getElementById('modal-permisos-guardar');
    content.innerHTML = '<p class="text-muted">Cargando…</p>';
    saveButton.disabled = true;
    openModal('modal-permisos');

    try {
      const menus = await request(`${API}/menus_listar.php?id_usuario=${encodeURIComponent(id)}`);
      content.replaceChildren();
      const grid = document.createElement('div');
      grid.className = 'permisos-grid';
      grid.style.cssText = 'display:grid;grid-template-columns:1fr 1fr;gap:8px';
      menus.forEach(menu => {
        const label = document.createElement('label');
        label.className = 'perm-item';
        label.style.cssText = 'display:flex;align-items:center;gap:8px;padding:10px 12px;border:1px solid var(--border);border-radius:8px;cursor:pointer;font-size:13px';
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.value = menu.id_menu;
        checkbox.checked = Number(menu.permitido ?? menu.id_tipo_permiso) === 1;
        const text = document.createElement('span');
        text.textContent = menu.nombre;
        label.append(checkbox, text);
        grid.appendChild(label);
      });
      content.appendChild(grid);
      saveButton.disabled = false;
      saveButton.onclick = () => guardarPermisos(id);
    } catch (error) {
      content.textContent = error.message;
    }
  }

  async function guardarPermisos(id) {
    const button = document.getElementById('modal-permisos-guardar');
    const menus = [...document.querySelectorAll('#modal-permisos-contenido input:checked')].map(input => Number(input.value));
    setBusy(button, true);
    try {
      const result = await send({ action: 'permisos', id_usuario: Number(id), menus });
      closeModal('modal-permisos');
      showFeedback(result.mensaje);
    } catch (error) {
      const content = document.getElementById('modal-permisos-contenido');
      const message = document.createElement('p');
      message.style.color = 'var(--danger)';
      message.style.marginTop = '12px';
      message.textContent = error.message;
      content.appendChild(message);
    } finally {
      setBusy(button, false);
    }
  }

  async function loadUsers() {
    tableBody.innerHTML = '<tr><td colspan="7" class="text-muted">Cargando usuarios…</td></tr>';
    try {
      _todosUsuarios = await request(`${API}/usuarios_listar.php`);
      aplicarFiltros();
    } catch (error) {
      _todosUsuarios = [];
      total.textContent = '0';
      _pag.actualizar(0);
      tableBody.innerHTML = `<tr><td colspan="7">${escapeHtml(error.message)}</td></tr>`;
    }
  }

  async function initialize() {
    _pag = new Paginacion({
      contenedor: 'usr-paginacion',
      totalItems: 0,
      porPagina: 10,
      onCambio: (pagina, porPagina) => renderPagina(pagina, porPagina),
    });
    _pag.render();
    addButton.disabled = true;
    try {
      [state.areas, state.colegios, state.menus] = await Promise.all([
        request(`${API}/areas_listar.php`), request(`${API}/colegios_listar.php`), request(`${API}/menus_listar.php`),
      ]);
      fillSelect(areaFilter, state.areas, 'id_area', 'nombre_area', 'Todas las áreas');
      await loadUsers();
      addButton.disabled = false;
    } catch (error) {
      tableBody.innerHTML = `<tr><td colspan="7">${escapeHtml(error.message)}</td></tr>`;
    }
  }

  addButton.addEventListener('click', () => abrirModalUsuario());
  document.getElementById('modal-usuario-guardar').addEventListener('click', guardarUsuario);
  searchInput.addEventListener('input', aplicarFiltros);
  areaFilter.addEventListener('change', aplicarFiltros);
  statusFilter.addEventListener('click', event => {
    const pill = event.target.closest('.pill');
    if (!pill) return;
    statusFilter.querySelectorAll('.pill').forEach(item => item.classList.remove('active'));
    pill.classList.add('active');
    _estadoFiltro = pill.dataset.val || '';
    aplicarFiltros();
  });
  tableBody.addEventListener('click', event => {
    const button = event.target.closest('[data-action]');
    if (!button) return;
    const user = _todosUsuarios.find(item => Number(item.id) === Number(button.dataset.id));
    if (!user) return;
    if (button.dataset.action === 'editar') abrirModalUsuario(user);
    if (button.dataset.action === 'permisos') verPermisos(user.id, fullName(user));
    if (button.dataset.action === 'estado') cambiarEstado(user.id, fullName(user), user.estado);
  });

  window.abrirModalUsuario = abrirModalUsuario;
  window.verPermisos = verPermisos;
  window.guardarPermisos = guardarPermisos;
  window.cambiarEstado = cambiarEstado;
  window.ejecutarCambioEstado = ejecutarCambioEstado;

  initialize();
})();
