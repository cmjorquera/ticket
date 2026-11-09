/*
 * Calendario de eventos (eventos/index.php) con FullCalendar 6.
 * Mes / semana / día / agenda · crear con clic o arrastrando · mover arrastrando ·
 * invitados con correo .ics · detalle lateral con Sí / No / Quizás.
 * Lógica: eventos/logica/LOGICA.txt
 */
(() => {
  'use strict';
  const C = window.EV || {};
  if (!C.instalado) return;
  const API = 'ajax/eventos.php';
  const $ = (id) => document.getElementById(id);
  const form = $('evForm');
  const msg = $('evFormMsg');
  const tiposOcultos = new Set();
  let invitados = [];   // [{id_usuario?, email, nombre}]
  let calendario;

  /* ---------- utilidades ---------- */
  const esc = (v) => { const d = document.createElement('div'); d.textContent = v ?? ''; return d.innerHTML; };
  const pad = (n) => String(n).padStart(2, '0');
  const ymd = (d) => `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
  const hm = (d) => `${pad(d.getHours())}:${pad(d.getMinutes())}`;

  async function get(params) {
    const r = await fetch(`${API}?${new URLSearchParams(params)}`, {credentials: 'same-origin', cache: 'no-store'});
    const d = await r.json().catch(() => ({ok: false, error: 'Respuesta no válida del servidor.'}));
    if (!r.ok || !d.ok) throw new Error(d.error || 'No fue posible cargar.');
    return d;
  }
  async function post(datos) {
    const r = await fetch(API, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({csrf: C.csrf, ...datos})});
    const d = await r.json().catch(() => ({ok: false, error: 'Respuesta no válida del servidor.'}));
    if (!r.ok || !d.ok) throw new Error(d.error || 'No fue posible guardar.');
    return d;
  }
  function aviso(texto, tipo = 'ok') {
    const t = document.createElement('div');
    t.className = `ev-toast ev-toast--${tipo}`;
    t.setAttribute('role', 'status');
    t.innerHTML = `<i class="bi ${tipo === 'ok' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'}"></i> <span>${esc(texto)}</span>`;
    document.body.append(t);
    requestAnimationFrame(() => t.classList.add('is-on'));
    setTimeout(() => { t.classList.remove('is-on'); setTimeout(() => t.remove(), 300); }, tipo === 'ok' ? 5000 : 7000);
  }
  const filtros = () => ({
    colegio: $('evFiltroColegio')?.value || '',
    mios: $('evMios').checked ? '1' : '',
    cancelados: $('evCancelados').checked ? '1' : '',
  });

  /* ---------- calendario ---------- */
  calendario = new FullCalendar.Calendar($('evCalendario'), {
    locale: 'es',
    firstDay: 1,
    initialView: window.matchMedia('(max-width: 700px)').matches ? 'listWeek' : 'dayGridMonth',
    headerToolbar: {left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'},
    buttonText: {today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día', list: 'Agenda'},
    height: 'auto',
    nowIndicator: true,
    selectable: true,
    selectMirror: true,
    dayMaxEvents: 3,
    slotMinTime: '07:00:00',
    slotMaxTime: '22:00:00',
    scrollTime: '08:00:00',
    businessHours: {daysOfWeek: [1, 2, 3, 4, 5], startTime: '08:00', endTime: '18:00'},
    eventTimeFormat: {hour: '2-digit', minute: '2-digit', hour12: false},
    events: async (info, ok, mal) => {
      try {
        const d = await get({accion: 'listar', inicio: info.startStr.slice(0, 10), fin: info.endStr.slice(0, 10), ...filtros()});
        ok(d.eventos.filter((e) => !tiposOcultos.has(e.extendedProps.tipo)));
      } catch (e) { mal(e); aviso(e.message, 'error'); }
    },
    select: (s) => {
      calendario.unselect();
      const fin = new Date(s.end);
      if (s.allDay) fin.setDate(fin.getDate() - 1);   // fin exclusivo
      abrirFormulario(null, {
        fecha_inicio: ymd(s.start), fecha_fin: ymd(fin), todo_el_dia: s.allDay && !(ymd(s.start) === ymd(fin) && calendario.view.type === 'dayGridMonth'),
        hora_inicio: s.allDay ? '09:00' : hm(s.start), hora_fin: s.allDay ? '10:00' : hm(s.end),
      });
    },
    eventClick: (i) => { i.jsEvent.preventDefault(); abrirDetalle(Number(i.event.id)); },
    eventDrop: (i) => mover(i),
    eventResize: (i) => mover(i),
    eventDidMount: (i) => {
      const p = i.event.extendedProps;
      i.el.title = [i.event.title, p.ubicacion, p.colegio].filter(Boolean).join(' · ');
      if (p.mi_respuesta === 'pendiente') i.el.classList.add('ev-pendiente');
    },
  });
  calendario.render();

  async function mover(i) {
    try {
      const d = await post({accion: 'mover', id: Number(i.event.id), inicio: i.event.startStr, fin: i.event.endStr || '', todo_el_dia: i.event.allDay ? 1 : 0});
      aviso(d.mensaje);
      cargarProximos();
    } catch (e) { i.revert(); aviso(e.message, 'error'); }
  }

  function refrescar() { calendario.refetchEvents(); cargarProximos(); }
  document.querySelectorAll('.ev-chip').forEach((b) => b.addEventListener('click', () => {
    const t = b.dataset.tipo;
    if (tiposOcultos.has(t)) tiposOcultos.delete(t); else tiposOcultos.add(t);
    b.classList.toggle('is-on', !tiposOcultos.has(t));
    b.setAttribute('aria-pressed', String(!tiposOcultos.has(t)));
    calendario.refetchEvents();
  }));
  ['evFiltroColegio', 'evMios', 'evCancelados'].forEach((id) => $(id)?.addEventListener('change', refrescar));

  /* ---------- próximos 14 días ---------- */
  async function cargarProximos() {
    const lista = $('evProximos');
    const hoy = new Date();
    const fin = new Date(); fin.setDate(fin.getDate() + 14);
    try {
      const d = await get({accion: 'listar', inicio: ymd(hoy), fin: ymd(fin), ...filtros()});
      lista.replaceChildren();
      if (!d.eventos.length) { lista.innerHTML = '<li class="ev-vacio"><i class="bi bi-calendar2-check"></i> Sin eventos próximos.</li>'; return; }
      d.eventos.slice(0, 12).forEach((e) => {
        const ini = new Date(e.start.length > 10 ? e.start : `${e.start}T00:00`);
        const li = document.createElement('li');
        li.innerHTML = `<button type="button" class="ev-prox" style="--c:${esc(e.color)}">
            <span class="ev-prox__dia"><strong>${ini.getDate()}</strong>${ini.toLocaleDateString('es-CL', {month: 'short'}).replace('.', '')}</span>
            <span class="ev-prox__txt"><strong>${esc(e.title)}</strong><small>${e.allDay ? 'Todo el día' : hm(ini)}${e.extendedProps.ubicacion ? ' · ' + esc(e.extendedProps.ubicacion) : ''}</small>
            ${e.extendedProps.mi_respuesta === 'pendiente' ? '<em>Responder invitación</em>' : ''}</span></button>`;
        li.querySelector('button').addEventListener('click', () => abrirDetalle(e.id));
        lista.append(li);
      });
    } catch (_) { lista.innerHTML = '<li class="ev-vacio">No se pudieron cargar.</li>'; }
  }
  cargarProximos();

  /* ---------- formulario ---------- */
  function pintarInvitados() {
    const cont = $('evInvitados');
    cont.replaceChildren();
    invitados.forEach((p, i) => {
      const c = document.createElement('span');
      c.className = 'ev-chip-persona';
      c.innerHTML = `<i class="bi ${p.id_usuario ? 'bi-person-fill' : 'bi-envelope'}"></i> ${esc(p.nombre || p.email)}
        <button type="button" aria-label="Quitar ${esc(p.nombre || p.email)}"><i class="bi bi-x"></i></button>`;
      c.title = p.email;
      c.querySelector('button').addEventListener('click', () => { invitados.splice(i, 1); pintarInvitados(); });
      cont.append(c);
    });
    const n = invitados.length;
    $('evNotaCorreo').textContent = n ? `Se enviará la invitación a ${n} ${n === 1 ? 'persona' : 'personas'} (y al responsable).` : 'Se avisará por correo al responsable.';
  }

  function sincronizarTodoElDia() {
    const todo = form.todo_el_dia.checked;
    form.hora_inicio.hidden = form.hora_fin.hidden = todo;
    form.querySelector('.ev-guion').hidden = todo;
  }
  form.todo_el_dia.addEventListener('change', sincronizarTodoElDia);
  form.fecha_inicio.addEventListener('change', () => { if (!form.fecha_fin.value || form.fecha_fin.value < form.fecha_inicio.value) form.fecha_fin.value = form.fecha_inicio.value; });
  form.hora_inicio.addEventListener('change', () => {   // como Google: mantiene 1 h de duración
    if (!form.hora_inicio.value) return;
    const [h, m] = form.hora_inicio.value.split(':').map(Number);
    if (!form.hora_fin.value || form.hora_fin.value <= form.hora_inicio.value) form.hora_fin.value = `${pad(Math.min(23, h + 1))}:${pad(m)}`;
  });

  async function abrirFormulario(evento = null, base = {}) {
    form.reset();
    msg.className = 'ticket-message';
    msg.textContent = '';
    const e = evento || {};
    $('evModalTitulo').textContent = evento ? 'Editar evento' : 'Nuevo evento';
    form.id.value = e.id || '';
    form.titulo.value = e.titulo || '';
    form.fecha_inicio.value = e.fecha_inicio || base.fecha_inicio || ymd(new Date());
    form.fecha_fin.value = e.fecha_fin || base.fecha_fin || form.fecha_inicio.value;
    form.todo_el_dia.checked = evento ? Number(e.todo_el_dia) === 1 || !e.hora_inicio : Boolean(base.todo_el_dia);
    form.hora_inicio.value = (e.hora_inicio || base.hora_inicio || '09:00').slice(0, 5);
    form.hora_fin.value = (e.hora_fin || base.hora_fin || '10:00').slice(0, 5);
    form.ubicacion.value = e.ubicacion || '';
    form.tipo_evento.value = e.tipo_evento || 'reunion';
    if (e.id_colegio && form.id_colegio.querySelector(`option[value="${e.id_colegio}"]`)) form.id_colegio.value = e.id_colegio;
    else if ($('evFiltroColegio')?.value) form.id_colegio.value = $('evFiltroColegio').value;
    form.responsable_id.value = e.responsable_id || C.yo.id;
    $('evResponsable').value = evento ? (e.responsable || '') : C.yo.nombre;
    form.descripcion.value = e.descripcion || '';
    ['con_audio', 'solo_presentacion', 'musica_ambiental'].forEach((k) => { form[k].checked = Number(e[k]) === 1; });
    form.cantidad_personas.value = e.cantidad_personas || 0;
    form.observaciones_logisticas.value = e.observaciones_logisticas || '';
    form.querySelector('.ev-logistica').open = Boolean(Number(e.con_audio) || Number(e.solo_presentacion) || Number(e.musica_ambiental) || Number(e.cantidad_personas) || e.observaciones_logisticas);
    invitados = (e.invitados || []).map((i) => ({id_usuario: i.id_usuario ? Number(i.id_usuario) : null, email: i.email, nombre: i.nombre}));
    pintarInvitados();
    sincronizarTodoElDia();
    $('evGuardar').disabled = false;
    openModal('evModal');
    setTimeout(() => form.titulo.focus(), 60);
  }
  $('evNuevo').addEventListener('click', () => abrirFormulario());

  /* buscador de personas (responsable e invitados) */
  function buscador(input, lista, alElegir, permitirCorreo) {
    let t = 0;
    let items = [];
    let activo = -1;
    const cerrar = () => { lista.hidden = true; activo = -1; };
    const pintar = () => {
      lista.replaceChildren();
      items.forEach((u, i) => {
        const li = document.createElement('li');
        li.className = i === activo ? 'is-activo' : '';
        li.innerHTML = `<strong>${esc(u.nombre)}</strong><small>${esc(u.email)}${u.colegio ? ' · ' + esc(u.colegio) : ''}</small>`;
        li.addEventListener('mousedown', (ev) => { ev.preventDefault(); alElegir(u); input.value = ''; cerrar(); });
        lista.append(li);
      });
      lista.hidden = !items.length;
    };
    input.addEventListener('input', () => {
      clearTimeout(t);
      const q = input.value.trim();
      if (q.length < 2) { items = []; cerrar(); return; }
      t = setTimeout(async () => {
        try { items = (await get({accion: 'usuarios', q})).usuarios; activo = items.length ? 0 : -1; pintar(); } catch (_) { cerrar(); }
      }, 220);
    });
    input.addEventListener('keydown', (ev) => {
      if (ev.key === 'ArrowDown' && items.length) { ev.preventDefault(); activo = (activo + 1) % items.length; pintar(); }
      else if (ev.key === 'ArrowUp' && items.length) { ev.preventDefault(); activo = (activo - 1 + items.length) % items.length; pintar(); }
      else if (ev.key === 'Escape') { cerrar(); }
      else if (ev.key === 'Enter') {
        ev.preventDefault();
        if (!lista.hidden && items[activo]) { alElegir(items[activo]); input.value = ''; cerrar(); }
        else if (permitirCorreo && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim())) { alElegir({email: input.value.trim().toLowerCase(), nombre: ''}); input.value = ''; cerrar(); }
      }
    });
    input.addEventListener('blur', () => setTimeout(cerrar, 150));
  }
  buscador($('evResponsable'), $('evRespSug'), (u) => { form.responsable_id.value = u.id; setTimeout(() => { $('evResponsable').value = u.nombre; }); }, false);
  buscador($('evInvitar'), $('evInvSug'), (u) => {
    const email = (u.email || '').toLowerCase();
    if (!email || invitados.some((i) => i.email === email)) return;
    invitados.push({id_usuario: u.id ? Number(u.id) : null, email, nombre: u.nombre || ''});
    pintarInvitados();
  }, true);

  form.addEventListener('submit', async (ev) => {
    ev.preventDefault();
    const datos = Object.fromEntries(new FormData(form).entries());
    ['todo_el_dia', 'con_audio', 'solo_presentacion', 'musica_ambiental'].forEach((k) => { datos[k] = form[k].checked ? 1 : 0; });
    datos.invitados = invitados.map((i) => (i.id_usuario ? {id_usuario: i.id_usuario} : {email: i.email, nombre: i.nombre}));
    datos.accion = 'guardar';
    datos.id = Number(datos.id) || 0;
    $('evGuardar').disabled = true;
    msg.className = 'ticket-message';
    msg.textContent = invitados.length ? 'Guardando y enviando invitaciones…' : 'Guardando…';
    try {
      const d = await post(datos);
      closeModal('evModal');
      aviso(d.mensaje);
      refrescar();
      if (datos.id) abrirDetalle(datos.id);
    } catch (e) {
      msg.className = 'ticket-message error';
      msg.textContent = e.message;
      $('evGuardar').disabled = false;
    }
  });

  /* ---------- detalle ---------- */
  const panel = $('evDetalle');
  const RESP = {si: ['Sí', 'bi-check-circle-fill', 'ok'], quizas: ['Quizás', 'bi-question-circle-fill', 'tal'], no: ['No', 'bi-x-circle-fill', 'no'], pendiente: ['Pendiente', 'bi-circle', 'pend']};

  function cerrarDetalle() {
    panel.classList.remove('is-open');
    setTimeout(() => { panel.hidden = true; }, 200);
    if (new URLSearchParams(location.search).has('evento')) history.replaceState(null, '', location.pathname);
  }
  panel.addEventListener('click', (e) => { if (e.target.closest('[data-ev-cerrar]')) cerrarDetalle(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !panel.hidden && !document.querySelector('.modal-overlay.open')) cerrarDetalle(); });

  async function abrirDetalle(id) {
    panel.hidden = false;
    requestAnimationFrame(() => panel.classList.add('is-open'));
    const cuerpo = $('evDetCuerpo');
    const acciones = $('evDetAcciones');
    cuerpo.innerHTML = '<p class="ev-vacio">Cargando…</p>';
    acciones.replaceChildren();
    let d;
    try { d = await get({accion: 'detalle', id}); } catch (e) { cuerpo.innerHTML = `<p class="ev-vacio">${esc(e.message)}</p>`; return; }
    const e = d.evento;
    const tipo = C.tipos[e.tipo_evento] || {nombre: e.tipo_evento, color: '#0f766e'};
    const cancelado = e.estado === 'cancelado';
    if (e.puede_editar && !cancelado) {
      acciones.append(boton('bi-pencil', 'Editar', () => { cerrarDetalle(); abrirFormulario(e); }));
      acciones.append(boton('bi-x-octagon', 'Cancelar evento', async () => {
        const motivo = prompt('¿Cancelar este evento? Se avisará a los invitados.\nMotivo (opcional):');
        if (motivo === null) return;
        try { const r = await post({accion: 'cancelar', id, motivo}); aviso(r.mensaje); cerrarDetalle(); refrescar(); } catch (er) { aviso(er.message, 'error'); }
      }));
    }
    if (e.puede_eliminar) {
      acciones.append(boton('bi-trash', 'Eliminar', async () => {
        if (!confirm('¿Eliminar el evento? Desaparece del calendario (no avisa a los invitados; para avisar, usa "Cancelar evento").')) return;
        try { const r = await post({accion: 'eliminar', id}); aviso(r.mensaje); cerrarDetalle(); refrescar(); } catch (er) { aviso(er.message, 'error'); }
      }));
    }
    const conteo = {si: 0, quizas: 0, no: 0, pendiente: 0};
    e.invitados.forEach((i) => { conteo[i.respuesta] += 1; });
    const logistica = [Number(e.con_audio) && 'Audio / micrófono', Number(e.solo_presentacion) && 'Presentación (proyector)',
      Number(e.musica_ambiental) && 'Música ambiental', Number(e.cantidad_personas) && `${e.cantidad_personas} asistentes`].filter(Boolean);
    cuerpo.innerHTML = `
      <div class="ev-det-titulo"><span class="ev-punto" style="background:${esc(e.color_evento || tipo.color)}"></span>
        <div><h2 id="evDetTitulo" class="${cancelado ? 'is-cancelado' : ''}">${esc(e.titulo)}</h2>
        <p>${esc(d.cuando)}</p>${cancelado ? '<span class="ev-badge ev-badge--no">Cancelado</span>' : ''}${e.estado === 'finalizado' ? '<span class="ev-badge">Finalizado</span>' : ''}</div></div>
      ${e.mi_respuesta && !cancelado ? `<div class="ev-rsvp"><span>¿Asistirás?</span>${['si', 'quizas', 'no'].map((r) => `<button type="button" data-rsvp="${r}" class="${e.mi_respuesta === r ? 'is-on' : ''}"><i class="bi ${RESP[r][1]}"></i> ${RESP[r][0]}</button>`).join('')}</div>` : ''}
      <ul class="ev-det-datos">
        ${e.ubicacion ? `<li><i class="bi bi-geo-alt"></i><span>${esc(e.ubicacion)}</span></li>` : ''}
        <li><i class="bi bi-tag"></i><span>${esc(tipo.nombre)}${e.nom_colegio ? ' · ' + esc(e.nom_colegio) : ''}</span></li>
        <li><i class="bi bi-person-badge"></i><span>Responsable: <strong>${esc(e.responsable)}</strong></span></li>
        ${e.creador && e.creador !== e.responsable ? `<li><i class="bi bi-pencil-square"></i><span>Creado por ${esc(e.creador)}</span></li>` : ''}
        ${e.descripcion ? `<li class="ev-det-desc"><i class="bi bi-text-left"></i><span>${esc(e.descripcion)}</span></li>` : ''}
        ${logistica.length || e.observaciones_logisticas ? `<li><i class="bi bi-speaker"></i><span>${esc(logistica.join(' · '))}${e.observaciones_logisticas ? `<br><small>${esc(e.observaciones_logisticas)}</small>` : ''}</span></li>` : ''}
      </ul>
      <section class="ev-det-inv">
        <h3><i class="bi bi-people"></i> ${e.invitados.length} invitado${e.invitados.length === 1 ? '' : 's'}
          <small>${conteo.si} sí · ${conteo.quizas} quizás · ${conteo.no} no · ${conteo.pendiente} pendiente${conteo.pendiente === 1 ? '' : 's'}</small></h3>
        <ul>${e.invitados.map((i) => `<li><span class="ev-resp ev-resp--${RESP[i.respuesta][2]}" title="${RESP[i.respuesta][0]}"><i class="bi ${RESP[i.respuesta][1]}"></i></span>
          <span><strong>${esc(i.nombre)}</strong><small>${esc(i.email)}</small></span></li>`).join('') || '<li class="ev-vacio">Sin invitados.</li>'}</ul>
      </section>
      ${e.historial.length ? `<details class="ev-det-hist"><summary>Historial</summary><ol>${e.historial.map((h) => `<li><strong>${esc(h.usuario || 'Invitado')}</strong> ${esc(h.descripcion)}<time>${esc(new Date(h.creado_en.replace(' ', 'T')).toLocaleString('es-CL', {dateStyle: 'short', timeStyle: 'short'}))}</time></li>`).join('')}</ol></details>` : ''}`;
    cuerpo.querySelectorAll('[data-rsvp]').forEach((b) => b.addEventListener('click', async () => {
      try { await post({accion: 'responder', id, respuesta: b.dataset.rsvp}); aviso('Respuesta guardada.'); abrirDetalle(id); calendario.refetchEvents(); cargarProximos(); } catch (er) { aviso(er.message, 'error'); }
    }));
    panel.querySelector('.ev-detalle__caja').focus({preventScroll: true});
  }
  function boton(icono, texto, alClic) {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'ev-accion';
    b.title = texto;
    b.setAttribute('aria-label', texto);
    b.innerHTML = `<i class="bi ${icono}"></i><span>${texto}</span>`;
    b.addEventListener('click', alClic);
    return b;
  }

  // ?evento=ID (desde la campanita o el correo): abre el detalle y va a esa fecha
  if (C.abrir) {
    abrirDetalle(C.abrir);
    get({accion: 'detalle', id: C.abrir}).then((d) => calendario.gotoDate(d.evento.fecha_inicio)).catch(() => {});
  }
  window.EventosCalendario = {calendario, abrirDetalle, abrirFormulario};
})();
