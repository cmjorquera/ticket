/* Catálogo de categorías: crear, editar, activar/desactivar y ordenar.
 * Datos: window.CAT_DATA · Guardar: ajax/categorias_ticket.php */
(() => {
  'use strict';
  const D = window.CAT_DATA;
  const tbody = document.getElementById('ccFilas');
  if (!D || !tbody) return;
  const dlg = document.getElementById('dlgCategoria');
  const form = document.getElementById('formCategoria');
  const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const ICONOS = ['bi-tag', 'bi-pc-display', 'bi-laptop', 'bi-printer', 'bi-wifi', 'bi-router', 'bi-telephone', 'bi-envelope', 'bi-globe',
    'bi-server', 'bi-database', 'bi-shield-lock', 'bi-key', 'bi-person-badge', 'bi-people', 'bi-building', 'bi-book', 'bi-box',
    'bi-camera-video', 'bi-projector', 'bi-mic', 'bi-headset', 'bi-tools', 'bi-gear', 'bi-cloud', 'bi-phone', 'bi-display',
    'bi-file-earmark-text', 'bi-calendar-event', 'bi-cash-coin', 'bi-mortarboard', 'bi-lightning', 'bi-puzzle', 'bi-question-circle'];
  const tecnicos = new Map(D.tecnicos.map(t => [t.id, t]));
  let verInactivas = false;
  try { verInactivas = localStorage.getItem('cc_inactivas') === '1'; } catch (_) { /* sin almacenamiento */ }
  document.getElementById('ccVerInactivas').checked = verInactivas;

  function pintar(resaltar = 0) {
    const lista = D.catalogo.filter(c => verInactivas || c.estado === 1);
    const activas = D.catalogo;
    tbody.innerHTML = lista.map(c => {
      const pos = activas.indexOf(c);
      const avatares = c.otros ? '<span class="cc-ayuda">No se asigna</span>'
        : c.tecnicos.length ? `<div class="cc-avatares">${c.tecnicos.map(id => tecnicos.get(id)).filter(Boolean)
          .map(t => `<span class="cc-avatar" title="${esc(t.nombre)}">${esc(t.iniciales)}</span>`).join('')}</div>`
        : '<span class="cc-sin"><i class="bi bi-exclamation-circle"></i> Sin técnico</span>';
      return `<tr class="${c.estado ? '' : 'is-inactiva'}${c.id === resaltar ? ' is-nueva' : ''}" data-id="${c.id}">
        <td><div class="cc-orden">${D.puedeEditar ? `<button type="button" class="btn-icon" data-mover="arriba" aria-label="Subir ${esc(c.nombre)}" ${pos === 0 ? 'disabled' : ''}><i class="bi bi-chevron-up"></i></button>` : ''}
          <span>${pos + 1}</span>${D.puedeEditar ? `<button type="button" class="btn-icon" data-mover="abajo" aria-label="Bajar ${esc(c.nombre)}" ${pos === activas.length - 1 ? 'disabled' : ''}><i class="bi bi-chevron-down"></i></button>` : ''}</div></td>
        <td><div class="cc-nombre"><span class="cc-ico"><i class="${esc(c.icono)}"></i></span><div>${esc(c.nombre)}${c.otros ? '<small>Categoría por defecto</small>' : ''}</div></div></td>
        <td>${esc(c.abreviacion)}</td>
        <td>${avatares}</td>
        <td class="cc-num">${c.tickets}</td>
        <td><span class="cc-estado ${c.estado ? 'cc-estado--on' : 'cc-estado--off'}">${c.estado ? 'Activa' : 'Inactiva'}</span></td>
        ${D.puedeEditar ? `<td class="cc-acc">
          <button type="button" class="btn-icon" data-editar aria-label="Editar ${esc(c.nombre)}" title="Editar"><i class="bi bi-pencil"></i></button>
          ${c.otros ? '' : `<button type="button" class="${c.estado ? 'btn-danger-ghost' : 'btn-icon'}" data-estado title="${c.estado ? 'Desactivar' : 'Activar'}" aria-label="${c.estado ? 'Desactivar' : 'Activar'} ${esc(c.nombre)}"><i class="bi ${c.estado ? 'bi-toggle-on' : 'bi-toggle-off'}"></i></button>`}
        </td>` : ''}
      </tr>`;
    }).join('') || '<tr><td colspan="7" class="cc-vacio">No hay categorías.</td></tr>';
  }

  async function enviar(datos) {
    const r = await fetch('ajax/categorias_ticket.php', {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ csrf: D.csrf, ...datos }),
    });
    const j = await r.json().catch(() => ({}));
    if (!r.ok || !j.ok) throw new Error(j.mensaje || 'No fue posible guardar los cambios.');
    return j;
  }
  // Cambios del catálogo afectan la grilla de asignación: se recarga (avisando si hay chips sin guardar)
  function recargar(mensaje, idNuevo) {
    const pendientes = document.querySelectorAll('.ac-card.is-dirty').length;
    if (pendientes && !confirm('Tienes cambios de asignación sin guardar. ¿Recargar de todas formas?')) return;
    try { sessionStorage.setItem('cc_aviso', JSON.stringify({ mensaje, id: idNuevo || 0 })); } catch (_) { /* nada */ }
    location.reload();
  }

  // ---------- diálogo crear / editar
  let editando = null;
  const $ = s => form.querySelector(s);
  function vista() {
    $('#ccVistaNombre').textContent = form.nombre.value.trim() || 'Nueva categoría';
    $('#ccVistaIcono').className = 'bi ' + (form.icono.value.trim().match(/bi-[a-z0-9-]+/)?.[0] || 'bi-tag');
    $('#ccIconos').querySelectorAll('button').forEach(b => b.setAttribute('aria-checked', String(b.dataset.icono === form.icono.value.trim())));
  }
  function abrir(cat) {
    editando = cat || null;
    form.reset();
    $('#ccError').hidden = true;
    $('#ccTitulo').textContent = cat ? `Editar "${cat.nombre}"` : 'Nueva categoría';
    form.nombre.value = cat ? cat.nombre : '';
    form.abreviacion.value = cat ? cat.abreviacion : '';
    const icono = cat ? (cat.icono.match(/bi-[a-z0-9-]+/)?.[0] || 'bi-tag') : 'bi-tag';
    form.icono.value = icono;
    $('#ccIconos').innerHTML = [...new Set([icono, ...ICONOS])].map(i =>
      `<button type="button" role="radio" data-icono="${i}" aria-label="${i}" title="${i}"><i class="bi ${i}"></i></button>`).join('');
    const otros = cat && cat.otros;
    $('#ccTecnicos').closest('.cc-campo').hidden = otros;
    $('#ccTecnicos').innerHTML = D.tecnicos.map(t => `<label class="cc-tec"><input type="checkbox" value="${t.id}" ${cat && cat.tecnicos.includes(t.id) ? 'checked' : ''}>
      <span class="cc-avatar">${esc(t.iniciales)}</span>${esc(t.nombre)}</label>`).join('') || '<span class="cc-ayuda">No hay técnicos en Informática de Seduc.</span>';
    vista();
    dlg.showModal();
    form.nombre.focus();
  }
  form.addEventListener('input', vista);
  $('#ccIconos').addEventListener('click', e => {
    const b = e.target.closest('[data-icono]');
    if (!b) return;
    form.icono.value = b.dataset.icono;
    vista();
  });
  dlg.addEventListener('click', e => { if (e.target === dlg || e.target.closest('[data-cc-cerrar]')) dlg.close(); });
  form.addEventListener('submit', async e => {
    e.preventDefault();
    const err = $('#ccError');
    err.hidden = true;
    if (!form.nombre.value.trim()) { err.textContent = 'Escribe el nombre de la categoría.'; err.hidden = false; form.nombre.focus(); return; }
    const boton = $('#ccGuardar');
    boton.disabled = true;
    try {
      const j = await enviar({
        action: editando ? 'editar' : 'crear', id: editando ? editando.id : 0,
        nombre: form.nombre.value.trim(), abreviacion: form.abreviacion.value.trim(), icono: form.icono.value.trim(),
        tecnicos: [...$('#ccTecnicos').querySelectorAll('input:checked')].map(i => Number(i.value)),
      });
      dlg.close();
      recargar(j.mensaje, j.id);
    } catch (ex) {
      err.textContent = ex.message; err.hidden = false;
    } finally {
      boton.disabled = false;
    }
  });

  // ---------- acciones de la tabla
  tbody.addEventListener('click', async e => {
    const fila = e.target.closest('tr[data-id]');
    if (!fila) return;
    const cat = D.catalogo.find(c => c.id === Number(fila.dataset.id));
    if (e.target.closest('[data-editar]')) { abrir(cat); return; }
    const btnEstado = e.target.closest('[data-estado]');
    const btnMover = e.target.closest('[data-mover]');
    try {
      if (btnEstado) {
        if (cat.estado && !confirm(`¿Desactivar "${cat.nombre}"? Ya no aparecerá al crear tickets (los tickets existentes no cambian).`)) return;
        const j = await enviar({ action: 'estado', id: cat.id });
        recargar(j.mensaje, cat.id);
      } else if (btnMover) {
        btnMover.disabled = true;
        await enviar({ action: 'mover', id: cat.id, direccion: btnMover.dataset.mover });
        recargar('', cat.id);
      }
    } catch (ex) {
      alert(ex.message);
    }
  });
  document.getElementById('ccVerInactivas').addEventListener('change', e => {
    verInactivas = e.target.checked;
    try { localStorage.setItem('cc_inactivas', verInactivas ? '1' : '0'); } catch (_) { /* nada */ }
    pintar();
  });
  document.getElementById('btnNuevaCategoria')?.addEventListener('click', () => abrir(null));

  // Aviso después de recargar
  let aviso = null;
  try { aviso = JSON.parse(sessionStorage.getItem('cc_aviso') || 'null'); sessionStorage.removeItem('cc_aviso'); } catch (_) { aviso = null; }
  pintar(aviso ? aviso.id : 0);
  if (aviso && aviso.id) {
    document.querySelector(`#ccFilas tr[data-id="${aviso.id}"]`)?.scrollIntoView({ block: 'center' });
  }
  if (aviso && aviso.mensaje) {
    const p = document.createElement('p');
    p.className = 'cc-aviso';
    p.setAttribute('role', 'status');
    p.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${esc(aviso.mensaje)}`;
    document.querySelector('#ccPanel .card-header').after(p);
    setTimeout(() => p.remove(), 6000);
  }
})();
