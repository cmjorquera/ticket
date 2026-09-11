(() => {
  'use strict';

  const API = '../ajax';
  const state = { areas: [], colegios: [], perfiles: [] };
  let _todosUsuarios = [];
  let _pag;
  let _estadoFiltro = '';
  let _vistaActual = 'tabla';
  const tableBody = document.querySelector('#tabla-usuarios tbody');
  const searchInput = document.getElementById('buscar-usuario');
  const areaFilter = document.getElementById('filtro-area');
  const statusFilter = document.getElementById('filtro-estado');
  const total = document.getElementById('total-usuarios');
  const addButton = document.getElementById('btn-agregar-usuario');
  const tableView = document.getElementById('vista-tabla');
  const orgView = document.getElementById('vista-organigrama');
  const viewButtons = [...document.querySelectorAll('.btn-view')];

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

  function profileBadgeClass(profile) {
    const normalized = String(profile || '').normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .replace(/[\s-]+/g, '_');
    if (normalized === 'super_admin' || normalized === 'superadmin') return 'badge-bloqueado';
    if (normalized === 'tecnico') return 'badge-pendiente';
    if (normalized === 'admin_area') return 'badge-active';
    if (normalized === 'admin_colegio') return 'badge-realizado';
    return 'badge-realizado';
  }

  function profileBadges(profiles) {
    const values = Array.isArray(profiles)
      ? profiles
      : String(profiles || '').split(',').map(value => value.trim()).filter(Boolean);
    if (!values.length) {
      return '<span class="badge badge-inactivo">Sin perfil</span>';
    }
    return values.map(profile =>
      `<span class="badge ${profileBadgeClass(profile)}">${escapeHtml(String(profile).replace(/_/g, ' '))}</span>`
    ).join(' ');
  }

  function filteredUsers() {
    const query = searchInput.value.trim().toLocaleLowerCase('es');
    const area = areaFilter.value;
    const status = _estadoFiltro.toLocaleLowerCase('es');
    return _todosUsuarios.filter(user => {
      const profiles = Array.isArray(user.perfiles) ? user.perfiles.join(' ') : (user.perfiles || '');
      const searchable = `${fullName(user)} ${user.email || ''} ${profiles} ${user.nombre_departamento || ''} ${user.nombre_area || ''} ${user.nom_colegio || ''}`.toLocaleLowerCase('es');
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
      tableBody.innerHTML = '<tr><td colspan="9" class="text-muted">No hay usuarios para los filtros seleccionados.</td></tr>';
      return;
    }

    tableBody.innerHTML = users.map((user, index) => {
      const active = String(user.estado || '').toLowerCase() === 'activo';
      return `<tr>
        <td class="mono">${start + index + 1}</td>
        <td><div class="flex items-center gap-3"><span class="user-avatar">${escapeHtml(initials(user))}</span><strong>${escapeHtml(fullName(user))}</strong></div></td>
        <td>${escapeHtml(user.email)}</td>
        <td>${profileBadges(user.perfiles)}</td>
        <td>${escapeHtml(user.nombre_departamento || '—')}</td>
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

  function renderOrganigrama() {
    const users = filteredUsers();
    if (!users.length) {
      orgView.innerHTML = '<div class="org-empty"><i class="bi bi-diagram-3"></i><p>No hay usuarios para los filtros seleccionados.</p></div>';
      return;
    }

    const departments = users.reduce((groups, user) => {
      const department = String(user.nombre_departamento || '').trim();
      const key = department && department !== '—' ? department : 'Sin departamento';
      (groups[key] ||= []).push(user);
      return groups;
    }, {});

    orgView.innerHTML = Object.entries(departments)
      .sort(([left], [right]) => left.localeCompare(right, 'es'))
      .map(([department, members]) => {
        const orderedMembers = [...members].sort((left, right) => fullName(left).localeCompare(fullName(right), 'es'));
        return `<section class="org-department">
          <div class="org-department__header">
            <h2>${escapeHtml(department)}</h2>
            <span>${orderedMembers.length} usuario${orderedMembers.length === 1 ? '' : 's'}</span>
          </div>
          <div class="org-users">
            ${orderedMembers.map(user => `<article class="org-user" title="${escapeHtml(fullName(user))}">
              <span class="user-avatar" aria-hidden="true">${escapeHtml(initials(user))}</span>
              <div class="org-user__info">
                <strong>${escapeHtml(fullName(user))}</strong>
                <small>${escapeHtml(user.email || 'Sin email')}</small>
                <div class="org-user__profiles">${profileBadges(user.perfiles)}</div>
              </div>
            </article>`).join('')}
          </div>
        </section>`;
      }).join('');
  }

  function cambiarVista(view) {
    _vistaActual = view === 'organigrama' ? 'organigrama' : 'tabla';
    const mostrarTabla = _vistaActual === 'tabla';
    tableView.hidden = !mostrarTabla;
    orgView.hidden = mostrarTabla;
    tableView.style.display = mostrarTabla ? 'block' : 'none';
    orgView.style.display = mostrarTabla ? 'none' : 'block';
    tableView.setAttribute('aria-hidden', String(!mostrarTabla));
    orgView.setAttribute('aria-hidden', String(mostrarTabla));
    viewButtons.forEach(button => {
      const active = button.dataset.view === _vistaActual;
      button.classList.toggle('active', active);
      button.setAttribute('aria-pressed', String(active));
    });
    if (_vistaActual === 'organigrama') renderOrganigrama();
  }

  function aplicarFiltros() {
    const count = filteredUsers().length;
    total.textContent = String(count);
    _pag.actualizar(count);
    renderPagina(1, _pag.porPagina);
    renderOrganigrama();
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

  function profileType(profile) {
    const name = String(profile?.nombre || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    if ((name.includes('administrador') || Number(profile?.id_perfil) === 3) && !name.includes('colegio') && !name.includes('area')) return 'administrador';
    if ((name.includes('admin') && name.includes('colegio')) || Number(profile?.id_perfil) === 4) return 'admin_colegio';
    if ((name.includes('admin') && name.includes('area')) || Number(profile?.id_perfil) === 5) return 'admin_area';
    if (name.includes('tecn') || Number(profile?.id_perfil) === 2) return 'tecnico';
    return 'usuario';
  }

  function renderProfilePermissions() {
    const container = document.getElementById('u-perfil-permisos');
    const id = Number(document.getElementById('u-perfil').value || 0);
    const profile = state.perfiles.find(item => Number(item.id_perfil) === id);
    container.replaceChildren();
    if (!profile) {
      container.innerHTML = '<div class="perfil-permisos-empty"><i class="bi bi-person-badge"></i><p>Selecciona un perfil para revisar los accesos que recibirá.</p></div>';
      return;
    }
    const permissions = {
      usuario: ['Inicio', 'Colaboradores', 'Crear ticket', 'Mis tickets'],
      tecnico: ['Inicio', 'Colaboradores', 'Crear ticket', 'Mis tickets', 'Tickets asignados', 'Administración · Categorías'],
      administrador: ['Todos los menús', 'Todos los submenús'],
      admin_colegio: ['Inicio', 'Colaboradores', 'Crear ticket', 'Mis tickets', 'Administración · Usuarios', 'Administración · Categorías', 'Administración · Permisos'],
      admin_area: ['Inicio', 'Colaboradores', 'Crear ticket', 'Mis tickets', 'Administración · Categorías'],
    }[profileType(profile)];
    const heading = document.createElement('div');
    heading.className = 'perfil-permisos-heading';
    heading.innerHTML = '<i class="bi bi-shield-check"></i><div><strong></strong><small>Se asignarán al crear la cuenta</small></div>';
    heading.querySelector('strong').textContent = profile.nombre;
    const list = document.createElement('div');
    list.className = 'perfil-permisos-list';
    permissions.forEach(permission => {
      const item = document.createElement('span');
      item.innerHTML = '<i class="bi bi-check-circle-fill"></i><span></span>';
      item.querySelector('span').textContent = permission;
      list.appendChild(item);
    });
    container.append(heading, list);
  }

  function normalizeSex(value) {
    const sex = String(value || '').toLowerCase();
    if (sex === 'm' || sex === 'masculino') return 'M';
    if (sex === 'f' || sex === 'femenino') return 'F';
    return '';
  }

  function abrirModalUsuario(datos = null) {
    const editing = Boolean(datos);
    document.querySelector('#modal-usuario .modal').classList.toggle('is-editing', editing);
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
    fillSelect(document.getElementById('u-perfil'), state.perfiles, 'id_perfil', 'nombre', 'Seleccionar');
    const currentSchool = datos?.id_colegio ?? datos?.colegios?.[0]?.id_colegio ?? '';
    const schoolSelect = document.getElementById('u-colegio');
    fillSelect(schoolSelect, state.colegios, 'id_colegio', 'nom_colegio', 'Sin colegio', currentSchool);
    schoolSelect.disabled = editing;

    document.getElementById('u-menus-wrap').hidden = editing;
    document.getElementById('modal-usuario-error').hidden = true;
    if (!editing) renderProfilePermissions();
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
      id_perfil: Number(document.getElementById('u-perfil').value),
      sexo: document.getElementById('u-sexo').value,
    };
    if (!id) {
      data.id_colegio = Number(document.getElementById('u-colegio').value || 0);
    }
    return data;
  }

  async function guardarUsuario() {
    const data = readUserModal();
    const error = document.getElementById('modal-usuario-error');
    error.hidden = true;
    if (!data.nombre || !data.apellido_paterno || !data.email || !data.id_area_trabajo || (!data.id && !data.id_perfil) || !data.sexo) {
      error.textContent = 'Completa nombre, apellido paterno, email, área, perfil y sexo.';
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
    tableBody.innerHTML = '<tr><td colspan="9" class="text-muted">Cargando usuarios…</td></tr>';
    try {
      _todosUsuarios = await request(`${API}/usuarios_listar.php`);
      aplicarFiltros();
    } catch (error) {
      _todosUsuarios = [];
      total.textContent = '0';
      _pag.actualizar(0);
      tableBody.innerHTML = `<tr><td colspan="9">${escapeHtml(error.message)}</td></tr>`;
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
      [state.areas, state.colegios, state.perfiles] = await Promise.all([
        request(`${API}/areas_listar.php`), request(`${API}/colegios_listar.php`), request(`${API}/perfiles_listar.php`),
      ]);
      fillSelect(areaFilter, state.areas, 'id_area', 'nombre_area', 'Todas las áreas');
      await loadUsers();
      addButton.disabled = false;
    } catch (error) {
      tableBody.innerHTML = `<tr><td colspan="9">${escapeHtml(error.message)}</td></tr>`;
    }
  }

  addButton.addEventListener('click', () => abrirModalUsuario());
  document.getElementById('modal-usuario-guardar').addEventListener('click', guardarUsuario);
  document.getElementById('u-perfil').addEventListener('change', renderProfilePermissions);
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
  viewButtons.forEach(button => {
    button.addEventListener('click', () => cambiarVista(button.dataset.view));
  });
  tableBody.addEventListener('click', event => {
    const button = event.target.closest('[data-action]');
    if (!button) return;
    const user = _todosUsuarios.find(item => Number(item.id) === Number(button.dataset.id));
    if (!user) return;
    if (button.dataset.action === 'editar') abrirModalUsuario(user);
    if (button.dataset.action === 'permisos') window.location.href = `usuarios_permisos.php?usuario_id=${encodeURIComponent(user.id)}`;
    if (button.dataset.action === 'estado') cambiarEstado(user.id, fullName(user), user.estado);
  });

  window.abrirModalUsuario = abrirModalUsuario;
  window.verPermisos = verPermisos;
  window.guardarPermisos = guardarPermisos;
  window.cambiarEstado = cambiarEstado;
  window.ejecutarCambioEstado = ejecutarCambioEstado;

  initialize();
})();
