/* Nuevo usuario con el modelo de roles.
 * Colegio → SOLO sus departamentos → cargo permitido (según quién está conectado).
 * Opciones: ajax/usuarios_alta_opciones.php · Guardar: ajax/usuarios_guardar.php (action=crear).
 * Uso: window.AltaUsuario.abrir([idColegio])
 */
(function () {
  'use strict';

  const API = '../ajax';
  const dlg = document.getElementById('dlgAlta');
  if (!dlg) { return; }
  const form = document.getElementById('formAlta');
  const $ = sel => dlg.querySelector(sel);
  const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const titulo = t => String(t || '').toLowerCase().replace(/(^|\s)\S/g, l => l.toUpperCase());
  const csrf = () => document.getElementById('modal-usuario')?.dataset.csrf || '';
  const ROL_INFO = {
    trabajador: { icono: 'bi-person', ayuda: 'Pertenece a un departamento. No administra.' },
    jefe_departamento: { icono: 'bi-person-badge', ayuda: 'Jefe de un departamento. Puede crear trabajadores de su departamento.' },
    admin_colegio: { icono: 'bi-building-gear', ayuda: 'Administra todo el colegio: departamentos, jefes y trabajadores.' },
  };
  let opciones = null;
  let cargando = null;

  async function cargarOpciones() {
    if (opciones) { return opciones; }
    if (!cargando) {
      cargando = fetch(`${API}/usuarios_alta_opciones.php`, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then(async r => {
          const j = await r.json().catch(() => ({}));
          if (!r.ok || !j.ok) { throw new Error(j.mensaje || 'No fue posible cargar las opciones.'); }
          opciones = j.data;
          return opciones;
        })
        .finally(() => { cargando = null; });
    }
    return cargando;
  }

  const colegioActual = () => (opciones?.colegios || []).find(c => c.id === Number($('#altaColegio').value)) || null;
  const rolActual = () => (form.querySelector('input[name="rol"]:checked') || {}).value || '';

  function pintarEscudo(id) {
    const caja = $('#altaEscudo');
    caja.innerHTML = '<i class="bi bi-building"></i>';
    if (!id) { return; }
    fetch(`../pages/modelos/rescatar/obtener_icono_colegio.php?id_colegio=${encodeURIComponent(id)}`, { credentials: 'same-origin' })
      .then(r => r.json()).then(d => {
        if (d && d.existe && d.icono && Number($('#altaColegio').value) === id) {
          const img = new Image();
          img.alt = '';
          img.src = /^(https?:)?\/\//.test(d.icono) || d.icono.startsWith('/') ? d.icono : '../' + d.icono;
          caja.replaceChildren(img);
        }
      }).catch(() => {});
  }

  function pintarRoles() {
    const c = colegioActual();
    const previo = rolActual();
    const roles = c ? c.roles : [];
    $('#altaRoles').innerHTML = roles.map(r => `
      <label class="alta__rol">
        <input type="radio" name="rol" value="${r}" ${r === previo || (roles.length === 1) ? 'checked' : ''}>
        <strong><i class="bi ${ROL_INFO[r].icono}"></i> ${esc(opciones.roles[r])}</strong>
        <small>${esc(ROL_INFO[r].ayuda)}</small>
      </label>`).join('') || '<p class="alta__ayuda">No puedes crear usuarios en este colegio.</p>';
    if (!rolActual() && roles.includes('trabajador')) {
      form.querySelector('input[name="rol"][value="trabajador"]').checked = true;
    }
  }

  function pintarDepartamentos() {
    const c = colegioActual();
    const sel = $('#altaDep');
    const previo = sel.value;
    const deps = c ? c.departamentos : [];
    const esAdmin = rolActual() === 'admin_colegio';
    $('#altaDepWrap').hidden = esAdmin;
    sel.innerHTML = deps.length
      ? (deps.length > 1 ? '<option value="">Selecciona un departamento</option>' : '') +
        deps.map(d => `<option value="${d.id}" ${String(d.id) === previo ? 'selected' : ''}>${esc(titulo(d.nombre))}</option>`).join('')
      : '<option value="">Este colegio no tiene departamentos</option>';
    sel.disabled = !deps.length;
    $('#altaDepAyuda').textContent = deps.length
      ? `${deps.length} ${deps.length === 1 ? 'departamento' : 'departamentos'} de ${c.nombre}.`
      : (c ? 'Primero crea un departamento en este colegio (menú ⋮ del colegio → Agregar departamento).' : '');
  }

  function pintarResumen() {
    const c = colegioActual();
    const rol = rolActual();
    const nombre = [form.nombre.value.trim(), form.apellido_paterno.value.trim()].filter(Boolean).join(' ') || 'La persona';
    if (!c || !rol) { $('#altaResumen').innerHTML = '<span class="alta__ayuda">Completa los datos para ver el resumen.</span>'; return; }
    const dep = $('#altaDep').selectedOptions[0];
    const donde = rol === 'admin_colegio' ? `<strong>${esc(c.nombre)}</strong>` : `<strong>${esc(dep && dep.value ? dep.textContent : '(departamento)')}</strong> de <strong>${esc(c.nombre)}</strong>`;
    const puede = ['Crear y ver sus tickets, almuerzos y su cuenta'];
    if (rol === 'admin_colegio') { puede.push('Crear departamentos, jefes y trabajadores de su colegio'); }
    if (rol === 'jefe_departamento') { puede.push('Crear trabajadores de su departamento'); }
    if ($('#altaTecnico').checked) { puede.push('Atender tickets'); }
    if ($('#altaSuper').checked) { puede.push('Todo, en todos los colegios (super administrador)'); }
    $('#altaResumen').innerHTML = `<strong>${esc(nombre)}</strong> será <strong>${esc(opciones.roles[rol].toLowerCase())}</strong> en ${donde}.<ul>${puede.map(p => `<li>${esc(p)}</li>`).join('')}</ul>`;
  }

  function error(msg, campo) {
    form.querySelectorAll('.is-error').forEach(e => e.classList.remove('is-error'));
    $('#altaError').textContent = msg || '';
    $('#altaError').hidden = !msg;
    if (campo) { campo.classList.add('is-error'); campo.focus(); }
  }

  function modoFormulario() {
    $('#altaFormulario').hidden = false;
    $('#altaPie').hidden = false;
    $('#altaExito').hidden = true;
    $('#altaPieExito').hidden = true;
  }

  async function abrir(idColegio) {
    form.reset();
    error('');
    modoFormulario();
    $('#altaCrear').disabled = true;
    if (!dlg.open) { dlg.showModal(); }
    opciones = null;   // siempre fresco: incluye departamentos recién creados
    try {
      await cargarOpciones();
    } catch (e) {
      error(e.message);
      return;
    }
    const cols = opciones.colegios;
    if (!cols.length) { error('No tienes permiso para crear usuarios.'); return; }
    $('#altaColegio').innerHTML = (cols.length > 1 ? '<option value="">Selecciona un colegio</option>' : '') +
      cols.map(c => `<option value="${c.id}">${esc(c.nombre)}</option>`).join('');
    const pedido = cols.find(c => c.id === Number(idColegio || 0));
    if (idColegio && !pedido) { error('No tienes permiso para crear usuarios en ese colegio.'); }
    $('#altaColegio').value = pedido ? String(pedido.id) : (cols.length === 1 ? String(cols[0].id) : '');
    $('#altaColegio').disabled = cols.length === 1;
    $('#altaSuperWrap').hidden = !opciones.super_admin;
    pintarEscudo(Number($('#altaColegio').value || 0));
    pintarRoles();
    pintarDepartamentos();
    pintarResumen();
    $('#altaCrear').disabled = false;
    form.nombre.focus();
  }

  function cerrar() { dlg.close(); }

  /** Pantalla final: correo enviado o enlace para copiar. */
  function mostrarExito(j, tituloTexto) {
    $('#altaFormulario').hidden = true;
    $('#altaPie').hidden = true;
    $('#altaExito').hidden = false;
    $('#altaPieExito').hidden = false;
    $('#altaOtro').hidden = !!j.reenvio;
    $('#altaExitoTitulo').textContent = tituloTexto;
    $('#altaExitoTexto').textContent = `Su usuario es ${j.email}.`;
    const c = $('#altaCorreo');
    c.className = 'alta__correo ' + (j.correo_enviado ? 'is-ok' : 'is-aviso');
    c.innerHTML = j.correo_enviado
      ? `<i class="bi bi-envelope-check"></i> Le enviamos el enlace a <strong>${esc(j.email)}</strong> para que elija su contraseña.`
      : `<i class="bi bi-exclamation-triangle"></i> ${esc(j.motivo_correo || 'No se pudo enviar el correo.')} Copia el enlace y envíaselo por otro medio.`;
    $('#altaEnlace').value = j.enlace || '';
  }

  /** "Reenviar activación" desde el listado (usuarios en estado Pendiente). */
  async function reenviar(idUsuario) {
    error('');
    if (!dlg.open) { dlg.showModal(); }
    $('#altaFormulario').hidden = true;
    $('#altaPie').hidden = true;
    $('#altaExito').hidden = true;
    $('#altaPieExito').hidden = true;
    try {
      const r = await fetch(`${API}/usuarios_guardar.php`, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ action: 'reenviar_activacion', id: Number(idUsuario), csrf: csrf() }),
      });
      const j = await r.json().catch(() => ({}));
      if (!r.ok || !j.ok) { throw new Error(j.mensaje || 'No fue posible reenviar la activación.'); }
      mostrarExito({ ...j, reenvio: true }, `Activación de ${j.nombre}`);
    } catch (err) {
      error(err.message);
      $('#altaPieExito').hidden = false;
      $('#altaOtro').hidden = true;
    }
  }

  // ---------- eventos
  $('#altaColegio').addEventListener('change', () => {
    pintarEscudo(Number($('#altaColegio').value || 0));
    pintarRoles();
    pintarDepartamentos();
    pintarResumen();
  });
  $('#altaRoles').addEventListener('change', () => { pintarDepartamentos(); pintarResumen(); });
  form.addEventListener('input', pintarResumen);
  form.addEventListener('change', pintarResumen);
  dlg.addEventListener('click', e => {
    if (e.target.closest('[data-alta-cerrar]') || e.target === dlg) { cerrar(); }
  });
  $('#altaOtro').addEventListener('click', () => abrir(Number($('#altaColegio').value || 0)));
  $('#altaCopiar').addEventListener('click', async () => {
    const campo = $('#altaEnlace');
    try { await navigator.clipboard.writeText(campo.value); } catch (_) { campo.select(); document.execCommand('copy'); }
    $('#altaCopiar').innerHTML = '<i class="bi bi-check2"></i> Copiada';
    setTimeout(() => { $('#altaCopiar').innerHTML = '<i class="bi bi-clipboard"></i> Copiar'; }, 1800);
  });

  form.addEventListener('submit', async e => {
    e.preventDefault();
    error('');
    const rol = rolActual();
    if (!form.nombre.value.trim()) { return error('Escribe el nombre.', form.nombre); }
    if (!form.apellido_paterno.value.trim()) { return error('Escribe el apellido paterno.', form.apellido_paterno); }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email.value.trim())) { return error('Escribe un email válido.', form.email); }
    if (!$('#altaColegio').value) { return error('Elige el colegio.', $('#altaColegio')); }
    if (!rol) { return error('Elige el cargo en el colegio.'); }
    if (rol !== 'admin_colegio' && !$('#altaDep').value) { return error('Elige el departamento.', $('#altaDep')); }

    const datos = {
      action: 'crear',
      csrf: csrf(),
      nombre: form.nombre.value.trim(),
      apellido_paterno: form.apellido_paterno.value.trim(),
      apellido_materno: form.apellido_materno.value.trim(),
      email: form.email.value.trim(),
      telefono: form.telefono.value.trim(),
      anexo: form.anexo.value.trim(),
      cargo: form.cargo.value.trim(),
      sexo: form.sexo.value,
      id_colegio: Number($('#altaColegio').value),
      rol,
      id_departamento_colegio: rol === 'admin_colegio' ? 0 : Number($('#altaDep').value),
      tecnico: $('#altaTecnico').checked,
      super_admin: $('#altaSuper').checked,
    };
    const boton = $('#altaCrear');
    boton.disabled = true;
    boton.innerHTML = '<i class="bi bi-hourglass-split"></i> Creando…';
    try {
      const r = await fetch(`${API}/usuarios_guardar.php`, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(datos),
      });
      const j = await r.json().catch(() => ({}));
      if (!r.ok || !j.ok) { throw new Error(j.mensaje || 'No fue posible crear el usuario.'); }
      mostrarExito(j, `${j.nombre} ya tiene cuenta`);
      document.dispatchEvent(new CustomEvent('usuarios:recargar'));
    } catch (err) {
      error(err.message);
    } finally {
      boton.disabled = false;
      boton.innerHTML = '<i class="bi bi-person-check"></i> Crear usuario';
    }
  });

  window.AltaUsuario = { abrir, reenviar, cargarOpciones };
})();
