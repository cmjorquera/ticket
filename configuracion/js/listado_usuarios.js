(() => {
  'use strict';

  const API = '../ajax';
  const state = { usuarios: [], areas: [], colegios: [], menus: [] };

  const tableBody = document.querySelector('#tabla-usuarios tbody');
  const searchInput = document.getElementById('buscar-usuario');
  const areaFilter = document.getElementById('filtro-area');
  const statusFilter = document.getElementById('filtro-estado');
  const total = document.getElementById('total-usuarios');

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
    const status = statusFilter.value.toLocaleLowerCase('es');
    return state.usuarios.filter(user => {
      const searchable = `${fullName(user)} ${user.email || ''} ${user.nombre_area || ''} ${user.nom_colegio || ''}`.toLocaleLowerCase('es');
      return (!query || searchable.includes(query))
        && (!area || String(user.id_area_trabajo || '') === area)
        && (!status || String(user.estado || '').toLocaleLowerCase('es') === status);
    });
  }

  function renderTable() {
    const users = filteredUsers();
    total.textContent = String(users.length);
    if (!users.length) {
      tableBody.innerHTML = '<tr><td colspan="7" class="text-muted">No hay usuarios para los filtros seleccionados.</td></tr>';
      return;
    }

    tableBody.innerHTML = users.map((user, index) => {
      const active = String(user.estado || '').toLowerCase() === 'activo';
      return `<tr>
        <td class="mono">${index + 1}</td>
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

  function options(items, valueKey, labelKey, selected = '') {
    return items.map(item => `<option value="${Number(item[valueKey])}" ${String(item[valueKey]) === String(selected) ? 'selected' : ''}>${escapeHtml(item[labelKey])}</option>`).join('');
  }

  function sexOptions(selected = '') {
    const values = ['Masculino', 'Femenino', 'Otro'];
    if (selected && !values.includes(selected)) values.push(selected);
    return values.map(value => `<option value="${escapeHtml(value)}" ${value === selected ? 'selected' : ''}>${escapeHtml(value)}</option>`).join('');
  }

  function userForm(user = null, includeMenus = false) {
    const menuHtml = includeMenus ? `<div style="margin-top:14px"><label class="field-label">Menús permitidos</label><div class="permisos-grid">${state.menus.map(menu => `<label class="perm-item"><span class="perm-item-name">${escapeHtml(menu.nombre)}</span><input type="checkbox" name="menus" value="${Number(menu.id_menu)}"></label>`).join('')}</div></div>` : '';
    return `<div style="text-align:left">
      <label class="field-label" for="usuario-nombre">Nombre</label><input class="input" id="usuario-nombre" value="${escapeHtml(user?.nombre || '')}">
      <label class="field-label mt-3" for="usuario-apellido-paterno">Apellido paterno</label><input class="input" id="usuario-apellido-paterno" value="${escapeHtml(user?.apellido_paterno || '')}">
      <label class="field-label mt-3" for="usuario-apellido-materno">Apellido materno</label><input class="input" id="usuario-apellido-materno" value="${escapeHtml(user?.apellido_materno || '')}">
      <label class="field-label mt-3" for="usuario-email">Email</label><input class="input" id="usuario-email" type="email" value="${escapeHtml(user?.email || '')}">
      <label class="field-label mt-3" for="usuario-telefono">Teléfono</label><input class="input" id="usuario-telefono" value="${escapeHtml(user?.telefono || '')}">
      <label class="field-label mt-3" for="usuario-area">Área de trabajo</label><select class="input" id="usuario-area"><option value="">Selecciona un área</option>${options(state.areas, 'id_area', 'nombre_area', user?.id_area_trabajo)}</select>
      <label class="field-label mt-3" for="usuario-sexo">Sexo</label><select class="input" id="usuario-sexo"><option value="">Selecciona</option>${sexOptions(user?.sexo || '')}</select>
      ${includeMenus ? `<label class="field-label mt-3" for="usuario-colegio">Colegio</label><select class="input" id="usuario-colegio"><option value="">Sin colegio</option>${options(state.colegios, 'id_colegio', 'nom_colegio')}</select>` : ''}
      ${menuHtml}
    </div>`;
  }

  function readUserForm(includeMenus = false) {
    return {
      nombre: document.getElementById('usuario-nombre').value.trim(),
      apellido_paterno: document.getElementById('usuario-apellido-paterno').value.trim(),
      apellido_materno: document.getElementById('usuario-apellido-materno').value.trim(),
      email: document.getElementById('usuario-email').value.trim(),
      telefono: document.getElementById('usuario-telefono').value.trim(),
      id_area_trabajo: Number(document.getElementById('usuario-area').value),
      sexo: document.getElementById('usuario-sexo').value,
      ...(includeMenus ? {
        id_colegio: Number(document.getElementById('usuario-colegio').value || 0),
        menus: [...document.querySelectorAll('input[name="menus"]:checked')].map(input => Number(input.value)),
      } : {}),
    };
  }

  function validateUser(data) {
    if (!data.nombre || !data.apellido_paterno || !data.email || !data.id_area_trabajo || !data.sexo) {
      Swal.showValidationMessage('Completa nombre, apellido paterno, email, área y sexo.');
      return false;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
      Swal.showValidationMessage('Ingresa un email válido.');
      return false;
    }
    return true;
  }

  async function createUser() {
    const result = await Swal.fire({
      title: 'Agregar usuario', html: userForm(null, true), width: 760,
      showCancelButton: true, confirmButtonText: 'Crear usuario', cancelButtonText: 'Cancelar',
      showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(),
      preConfirm: async () => {
        const data = readUserForm(true);
        if (!validateUser(data)) return false;
        try { return await send({ action: 'crear', ...data }); }
        catch (error) { Swal.showValidationMessage(error.message); return false; }
      },
    });
    if (result.isConfirmed) {
      await Swal.fire({ icon: 'success', title: 'Usuario creado', text: result.value.mensaje });
      await loadUsers();
    }
  }

  async function editUser(user) {
    const result = await Swal.fire({
      title: 'Editar usuario', html: userForm(user), width: 620,
      showCancelButton: true, confirmButtonText: 'Guardar', cancelButtonText: 'Cancelar',
      showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(),
      preConfirm: async () => {
        const data = readUserForm(false);
        if (!validateUser(data)) return false;
        try { return await send({ action: 'editar', id: Number(user.id), ...data }); }
        catch (error) { Swal.showValidationMessage(error.message); return false; }
      },
    });
    if (result.isConfirmed) {
      await Swal.fire({ icon: 'success', title: 'Cambios guardados', text: result.value.mensaje });
      await loadUsers();
    }
  }

  async function editPermissions(user) {
    try {
      const menus = await request(`${API}/menus_listar.php?id_usuario=${encodeURIComponent(user.id)}`);
      const result = await Swal.fire({
        title: `Permisos de ${fullName(user)}`,
        html: `<div class="permisos-grid" style="text-align:left">${menus.map(menu => `<label class="perm-item"><span class="perm-item-name">${escapeHtml(menu.nombre)}</span><input type="checkbox" name="permisos" value="${Number(menu.id_menu)}" ${Number(menu.permitido) === 1 ? 'checked' : ''}></label>`).join('')}</div>`,
        width: 720, showCancelButton: true, confirmButtonText: 'Guardar permisos', cancelButtonText: 'Cancelar',
        showLoaderOnConfirm: true, allowOutsideClick: () => !Swal.isLoading(),
        preConfirm: async () => {
          const selected = [...document.querySelectorAll('input[name="permisos"]:checked')].map(input => Number(input.value));
          try { return await send({ action: 'permisos', id_usuario: Number(user.id), menus: selected }); }
          catch (error) { Swal.showValidationMessage(error.message); return false; }
        },
      });
      if (result.isConfirmed) await Swal.fire({ icon: 'success', title: 'Permisos actualizados', text: result.value.mensaje });
    } catch (error) {
      await Swal.fire({ icon: 'error', title: 'Error', text: error.message });
    }
  }

  async function changeStatus(user) {
    const active = String(user.estado || '').toLowerCase() === 'activo';
    const action = active ? 'bloquear' : 'activar';
    const confirmation = await Swal.fire({
      icon: 'question', title: active ? '¿Bloquear usuario?' : '¿Activar usuario?',
      text: fullName(user), showCancelButton: true,
      confirmButtonText: active ? 'Sí, bloquear' : 'Sí, activar', cancelButtonText: 'Cancelar',
    });
    if (!confirmation.isConfirmed) return;
    try {
      const result = await send({ action, id: Number(user.id) });
      await Swal.fire({ icon: 'success', title: 'Estado actualizado', text: result.mensaje });
      await loadUsers();
    } catch (error) {
      await Swal.fire({ icon: 'error', title: 'Error', text: error.message });
    }
  }

  async function loadUsers() {
    tableBody.innerHTML = '<tr><td colspan="7" class="text-muted">Cargando usuarios…</td></tr>';
    try {
      state.usuarios = await request(`${API}/usuarios_listar.php`);
      renderTable();
    } catch (error) {
      total.textContent = '0';
      tableBody.innerHTML = `<tr><td colspan="7">${escapeHtml(error.message)}</td></tr>`;
    }
  }

  async function initialize() {
    try {
      [state.areas, state.colegios, state.menus] = await Promise.all([
        request(`${API}/areas_listar.php`), request(`${API}/colegios_listar.php`), request(`${API}/menus_listar.php`),
      ]);
      areaFilter.insertAdjacentHTML('beforeend', options(state.areas, 'id_area', 'nombre_area'));
      await loadUsers();
    } catch (error) {
      tableBody.innerHTML = `<tr><td colspan="7">${escapeHtml(error.message)}</td></tr>`;
    }
  }

  document.getElementById('btn-agregar-usuario').addEventListener('click', createUser);
  searchInput.addEventListener('input', renderTable);
  areaFilter.addEventListener('change', renderTable);
  statusFilter.addEventListener('change', renderTable);
  tableBody.addEventListener('click', event => {
    const button = event.target.closest('[data-action]');
    if (!button) return;
    const user = state.usuarios.find(item => Number(item.id) === Number(button.dataset.id));
    if (!user) return;
    if (button.dataset.action === 'editar') editUser(user);
    if (button.dataset.action === 'permisos') editPermissions(user);
    if (button.dataset.action === 'estado') changeStatus(user);
  });

  initialize();
})();

