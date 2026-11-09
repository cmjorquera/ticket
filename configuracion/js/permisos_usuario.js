/* Permisos de usuario: marca/desmarca menús y submenús y guarda.
 * Lo usan usuarios_permisos.php (página) y el panel lateral del listado.
 *   PermisosUsuario.iniciar(form, { alGuardar(data) })
 */
window.PermisosUsuario = (() => {
  'use strict';
  function iniciar(form, opciones = {}) {
  if (!form || form.dataset.permisosListo === '1') return;
  form.dataset.permisosListo = '1';
  const message = form.querySelector('#up-mensaje');
  const saveButton = form.querySelector('#up-guardar');
  const permissionGroups = new Map(
    [...form.querySelectorAll('[data-permission-group-id]')].map(group => {
      const menuId = String(group.dataset.permissionGroupId);
      return [menuId, {
        group,
        parent: group.querySelector(`[data-permission-menu-id="${menuId}"]`),
        children: [...group.querySelectorAll(`[data-parent-menu-id="${menuId}"]`)],
      }];
    })
  );

  function syncGroup(groupState) {
    const { group, parent, children } = groupState;
    if (!parent) return;
    const checked = children.filter(child => child.checked).length;
    if (children.length) {
      parent.checked = checked > 0;
      parent.indeterminate = checked > 0 && checked < children.length;
    }
    group.querySelector('.up-menu__count span').textContent = String(checked);
  }

  function syncSummary() {
    const menus = form.querySelectorAll('[data-permission-menu-id]:checked').length;
    const submenus = form.querySelectorAll('[data-permission-submenu-id]:checked').length;
    form.querySelector('#up-resumen').textContent = `${menus} menús · ${submenus} submenús`;
  }

  permissionGroups.forEach(groupState => {
    const { parent, children } = groupState;
    if (!parent) return;

    parent.addEventListener('change', event => {
      const checked = event.currentTarget.checked;
      children.forEach(child => {
        child.checked = checked;
      });
      parent.indeterminate = false;
      syncGroup(groupState);
      syncSummary();
    });

    children.forEach(child => {
      child.addEventListener('change', event => {
        if (event.currentTarget.dataset.parentMenuId !== parent.dataset.permissionMenuId) return;
        syncGroup(groupState);
        syncSummary();
      });
    });

    syncGroup(groupState);
  });
  syncSummary();

  function showOrders(data = null) {
    form.querySelectorAll('[data-menu-order-id]').forEach(label => {
      const item = data?.menus?.[label.dataset.menuOrderId];
      const order = item?.orden ?? label.dataset.orderFallback ?? '';
      label.textContent = order === '' ? '' : `(${order})`;
    });
    form.querySelectorAll('[data-submenu-order-id]').forEach(label => {
      const item = data?.submenus?.[label.dataset.submenuOrderId];
      const order = item?.orden ?? label.dataset.orderFallback ?? '';
      label.textContent = order === '' ? '' : `(${order})`;
    });
  }

  async function loadOrders() {
    try {
      const response = await fetch(form.dataset.base + 'ajax/obtener_orden_menus.php', {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      });
      const data = await response.json();
      if (!response.ok || !data.menus || !data.submenus) {
        throw new Error(data.error || 'Respuesta inválida al consultar los órdenes.');
      }
      showOrders(data);
    } catch (error) {
      showOrders();
      console.error('No fue posible cargar el orden de los menús.', error);
    }
  }

  loadOrders();

  form.addEventListener('submit', async event => {
    event.preventDefault(); saveButton.disabled = true; saveButton.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando…'; message.className = ''; message.textContent = 'Aplicando cambios…';
    try {
      const response = await fetch(form.dataset.base + 'ajax/guardar_permisos_usuario.php', {method:'POST', credentials:'same-origin', body:new FormData(form)});
      const data = await response.json();
      if (!response.ok || !data.ok) throw new Error(data.error || 'No fue posible guardar los permisos.');
      message.className = 'is-success'; message.textContent = data.mensaje; saveButton.innerHTML = '<i class="bi bi-check-circle"></i> Guardado';
      if (typeof opciones.alGuardar === 'function') opciones.alGuardar(data);
      setTimeout(() => { saveButton.disabled = false; saveButton.innerHTML = '<i class="bi bi-floppy-fill"></i> Guardar permisos'; }, 1200);
    } catch (error) {
      message.className = 'is-error'; message.textContent = error.message || 'No fue posible guardar los permisos.'; saveButton.disabled = false; saveButton.innerHTML = '<i class="bi bi-floppy-fill"></i> Guardar permisos';
    }
  });
  }
  return { iniciar };
})();

// Página completa: iniciar el formulario si existe
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('form-permisos');
  if (form && !form.closest('.oc-perm')) window.PermisosUsuario.iniciar(form);
});
