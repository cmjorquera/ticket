(() => {
  'use strict';

  const API = 'ajax/colaboradores.php';
  const COLORES = ['#16365c', '#1d4675', '#3C8D2F', '#b7791f', '#7c3a5a', '#2b6cb0', '#0f766e', '#9a3412'];

  const $ = id => document.getElementById(id);
  const buscar = $('colab-buscar'), selArea = $('colab-area'), selColegio = $('colab-colegio');
  const grid = $('colab-grid'), tablaWrap = $('colab-tabla-wrap'), tbody = $('colab-tbody');
  const resumen = $('colab-resumen'), vacio = $('colab-vacio');
  const barra = $('barra-sel'), modal = $('modal-mensaje'), form = $('form-mensaje');
  const texto = $('m-texto'), largo = $('m-largo'), errorBox = $('m-error'), btnEnviar = $('m-enviar');

  let todos = [];
  const seleccion = new Set();
  let destinatarios = [];
  let vista = 'tarjetas';
  try { vista = localStorage.getItem('colab_vista') === 'lista' ? 'lista' : 'tarjetas'; } catch (e) { /* sin storage */ }

  const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
  const iniciales = n => String(n || '').trim().split(/\s+/).slice(0, 2).map(p => p.charAt(0).toUpperCase()).join('') || 'U';
  const color = id => COLORES[Math.abs(Number(id) || 0) % COLORES.length];
  const porId = id => todos.find(c => c.id === id);
  const normal = s => String(s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

  function filtrados() {
    const q = normal(buscar.value.trim());
    const area = selArea.value, colegio = selColegio.value;
    return todos.filter(c =>
      (!q || normal(`${c.nombre} ${c.cargo} ${c.email} ${c.anexo} ${c.area} ${c.colegio}`).includes(q)) &&
      (!area || String(c.id_area) === area) &&
      (!colegio || String(c.id_colegio) === colegio));
  }

  function check(c) {
    return `<label class="colab-check" title="Seleccionar"><input type="checkbox" data-sel="${c.id}" ${seleccion.has(c.id) ? 'checked' : ''} aria-label="Seleccionar a ${esc(c.nombre)}"><span><i class="bi bi-check-lg"></i></span></label>`;
  }
  function anexoHtml(c) {
    return c.anexo
      ? `<span class="colab-dato"><i class="bi bi-telephone"></i><span>Anexo ${esc(c.anexo)}</span></span>`
      : `<span class="colab-dato colab-dato--vacio"><i class="bi bi-telephone"></i><span>Sin anexo</span></span>`;
  }
  function correoHtml(c) {
    return c.email
      ? `<a class="colab-dato" href="mailto:${esc(c.email)}"><i class="bi bi-envelope"></i><span>${esc(c.email)}</span></a>`
      : `<span class="colab-dato colab-dato--vacio"><i class="bi bi-envelope"></i><span>Sin correo</span></span>`;
  }

  function tarjeta(c) {
    const tags = [c.area && `<span class="colab-tag colab-tag--area">${esc(c.area)}</span>`, c.colegio && `<span class="colab-tag">${esc(c.colegio)}</span>`].filter(Boolean).join('');
    return `<article class="colab-card${seleccion.has(c.id) ? ' is-selected' : ''}" data-id="${c.id}">
      ${check(c)}
      <div class="colab-top"><div class="colab-avatar" style="background:${color(c.id)}">${esc(iniciales(c.nombre))}</div>
        <div class="colab-id"><h3>${esc(c.nombre)}</h3><p>${esc(c.cargo || 'Sin cargo registrado')}</p></div></div>
      ${tags ? `<div class="colab-tags">${tags}</div>` : ''}
      <div class="colab-datos">${anexoHtml(c)}${correoHtml(c)}</div>
      <div class="colab-acciones">
        <button type="button" class="btn-msg" data-msg="${c.id}"><i class="bi bi-chat-dots"></i> Mensaje</button>
        ${c.email ? `<button type="button" class="btn-ico" data-copiar="${esc(c.email)}" title="Copiar correo" aria-label="Copiar correo"><i class="bi bi-copy"></i></button>` : ''}
      </div>
    </article>`;
  }

  function fila(c) {
    return `<tr class="${seleccion.has(c.id) ? 'is-selected' : ''}" data-id="${c.id}">
      <td class="col-check">${check(c)}</td>
      <td><div class="persona"><span class="colab-avatar" style="background:${color(c.id)}">${esc(iniciales(c.nombre))}</span>${esc(c.nombre)}</div></td>
      <td>${esc(c.cargo || '—')}</td><td>${esc(c.area || '—')}</td><td>${esc(c.colegio || '—')}</td>
      <td>${esc(c.anexo || '—')}</td>
      <td>${c.email ? `<a href="mailto:${esc(c.email)}">${esc(c.email)}</a>` : '—'}</td>
      <td><button type="button" class="btn-ico" style="height:32px" data-msg="${c.id}" title="Enviar mensaje" aria-label="Enviar mensaje a ${esc(c.nombre)}"><i class="bi bi-chat-dots"></i></button></td>
    </tr>`;
  }

  function render() {
    const lista = filtrados();
    const colegios = new Set(lista.map(c => c.id_colegio).filter(Boolean)).size;
    resumen.innerHTML = `<strong>${lista.length}</strong> colaborador${lista.length === 1 ? '' : 'es'}${colegios ? ` · ${colegios} colegio${colegios === 1 ? '' : 's'}` : ''} · ordenados por nombre`;
    vacio.hidden = lista.length > 0;
    grid.hidden = vista !== 'tarjetas' || !lista.length;
    tablaWrap.hidden = vista !== 'lista' || !lista.length;
    if (vista === 'tarjetas') grid.innerHTML = lista.map(tarjeta).join('');
    else tbody.innerHTML = lista.map(fila).join('');
    document.querySelectorAll('.colab-vista button').forEach(b => {
      const on = b.dataset.vista === vista; b.classList.toggle('on', on); b.setAttribute('aria-pressed', String(on));
    });
    renderBarra();
  }

  function renderBarra() {
    const n = seleccion.size;
    barra.hidden = n === 0;
    $('sel-num').textContent = String(n);
    $('sel-texto').textContent = n === 1 ? 'seleccionado' : 'seleccionados';
    $('sel-avs').innerHTML = [...seleccion].slice(0, 5).map(id => {
      const c = porId(id); return c ? `<span style="background:${color(id)}" title="${esc(c.nombre)}">${esc(iniciales(c.nombre))}</span>` : '';
    }).join('');
  }

  function llenarFiltros() {
    const opciones = (sel, pares, primero) => {
      const unicos = [...new Map(pares.filter(([id, n]) => id && n).map(p => [String(p[0]), p[1]])).entries()]
        .sort((a, b) => a[1].localeCompare(b[1], 'es'));
      sel.innerHTML = `<option value="">${primero}</option>` + unicos.map(([id, n]) => `<option value="${esc(id)}">${esc(n)}</option>`).join('');
    };
    opciones(selArea, todos.map(c => [c.id_area, c.area]), 'Todas las áreas');
    opciones(selColegio, todos.map(c => [c.id_colegio, c.colegio]), 'Todos los colegios');
  }

  // ---- Modal
  function abrirModal(ids) {
    destinatarios = ids.filter(id => porId(id));
    if (!destinatarios.length) return;
    errorBox.hidden = true;
    pintarChips();
    modal.hidden = false;
    setTimeout(() => texto.focus(), 30);
  }
  function cerrarModal() { modal.hidden = true; }
  function pintarChips() {
    const n = destinatarios.length;
    $('m-chips').innerHTML = destinatarios.map(id => {
      const c = porId(id);
      return `<span class="chip"><b style="background:${color(id)}">${esc(iniciales(c.nombre))}</b>${esc(c.nombre)}<button type="button" data-quitar="${id}" aria-label="Quitar a ${esc(c.nombre)}">×</button></span>`;
    }).join('');
    $('m-cuantos').textContent = `${n} destinatario${n === 1 ? '' : 's'}`;
    btnEnviar.querySelector('span').textContent = n === 1 ? 'Enviar' : `Enviar a ${n}`;
    if (!n) cerrarModal();
  }

  function toast(msg) {
    const t = $('colab-toast'); t.textContent = msg; t.hidden = false;
    clearTimeout(toast._t); toast._t = setTimeout(() => { t.hidden = true; }, 3500);
  }

  // ---- Eventos
  document.addEventListener('change', e => {
    const id = Number(e.target.dataset?.sel || 0);
    if (!id) return;
    e.target.checked ? seleccion.add(id) : seleccion.delete(id);
    e.target.closest('.colab-card, tr')?.classList.toggle('is-selected', e.target.checked);
    renderBarra();
  });
  document.addEventListener('click', e => {
    const msg = e.target.closest('[data-msg]');
    if (msg) { abrirModal([Number(msg.dataset.msg)]); return; }
    const cp = e.target.closest('[data-copiar]');
    if (cp) { navigator.clipboard?.writeText(cp.dataset.copiar).then(() => toast('Correo copiado')); return; }
    const q = e.target.closest('[data-quitar]');
    if (q) { destinatarios = destinatarios.filter(id => id !== Number(q.dataset.quitar)); pintarChips(); }
  });
  document.querySelectorAll('.colab-vista button').forEach(b => b.addEventListener('click', () => {
    vista = b.dataset.vista; try { localStorage.setItem('colab_vista', vista); } catch (e) { /* sin storage */ }
    render();
  }));
  [buscar, selArea, selColegio].forEach(el => el.addEventListener('input', render));
  $('sel-limpiar').addEventListener('click', () => { seleccion.clear(); render(); });
  $('sel-enviar').addEventListener('click', () => abrirModal([...seleccion]));
  $('m-cerrar').addEventListener('click', cerrarModal);
  $('m-cancelar').addEventListener('click', cerrarModal);
  modal.addEventListener('click', e => { if (e.target === modal) cerrarModal(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.hidden) cerrarModal(); });
  texto.addEventListener('input', () => { largo.textContent = String(texto.value.length); });

  form.addEventListener('submit', async e => {
    e.preventDefault();
    const mensaje = texto.value.trim();
    if (!mensaje) { errorBox.textContent = 'Escribe un mensaje.'; errorBox.hidden = false; return; }
    const datos = new FormData();
    datos.append('accion', 'enviar_mensaje');
    datos.append('csrf', window.COLAB_CSRF || '');
    datos.append('mensaje', mensaje);
    datos.append('urgente', $('m-urgente').checked ? '1' : '0');
    destinatarios.forEach(id => datos.append('destinatarios[]', String(id)));
    btnEnviar.disabled = true; errorBox.hidden = true;
    try {
      const r = await fetch(API, { method: 'POST', credentials: 'same-origin', body: datos });
      const j = await r.json().catch(() => ({}));
      if (!r.ok || !j.ok) throw new Error(j.mensaje || 'No fue posible enviar el mensaje.');
      cerrarModal();
      texto.value = ''; largo.textContent = '0'; $('m-urgente').checked = false;
      destinatarios.forEach(id => seleccion.delete(id));
      render();
      toast(j.mensaje || 'Mensaje enviado.');
    } catch (err) {
      errorBox.textContent = err.message; errorBox.hidden = false;
    } finally {
      btnEnviar.disabled = false;
    }
  });

  // ---- Carga inicial
  fetch(API, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
    .then(r => r.json())
    .then(j => {
      if (!j.ok) throw new Error(j.mensaje || 'Error');
      todos = j.data || [];
      llenarFiltros();
      render();
    })
    .catch(() => { resumen.textContent = 'No fue posible cargar los colaboradores. Recarga la página.'; });
})();
