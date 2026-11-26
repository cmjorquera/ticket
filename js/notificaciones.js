/*
 * Campanita de notificaciones (componentes/campanita.php).
 * Cuenta los avisos sin leer cada 30 s; al abrirla lista los últimos 20.
 * Lógica: ticket/logica/LOGICA_NOTIFICACIONES.txt
 */
(() => {
  'use strict';
  const raiz = document.getElementById('notif');
  if (!raiz || raiz.dataset.ready) return;
  raiz.dataset.ready = '1';
  const base = raiz.dataset.base || '';
  const csrf = raiz.dataset.csrf || '';
  const api = `${base}ajax/notificaciones.php`;
  const btn = document.getElementById('notif-btn');
  const badge = document.getElementById('notif-badge');
  const panel = document.getElementById('notif-panel');
  const lista = document.getElementById('notif-lista');
  const todas = document.getElementById('notif-todas');
  const ICONOS = {
    ticket_creado: 'bi-ticket-perforated', ticket_asignado: 'bi-person-check', ticket_comenzado: 'bi-play-circle',
    ticket_estado: 'bi-arrow-repeat', ticket_mensaje: 'bi-chat-dots', ticket_bitacora: 'bi-journal-text',
    evento_invitacion: 'bi-calendar-plus', evento_actualizado: 'bi-calendar-event', evento_cancelado: 'bi-calendar-x',
  };
  let anterior = null;

  function pintar(n) {
    badge.hidden = n <= 0;
    badge.textContent = n > 99 ? '99+' : String(n);
    btn.setAttribute('aria-label', n ? `Notificaciones: ${n} sin leer` : 'Notificaciones');
    if (anterior !== null && n > anterior) {
      btn.classList.remove('is-nueva');
      void btn.offsetWidth;
      btn.classList.add('is-nueva');
    }
    anterior = n;
  }

  /* Sobre de mensajes (barra superior) y números en el menú lateral. */
  const sobre = document.getElementById('notif-sobre');
  const sobreBadge = document.getElementById('notif-sobre-badge');
  const miniPanel = document.getElementById('mensajes-panel');
  const miniLista = document.getElementById('mensajes-panel-lista');
  let miniAbierto = false;
  async function cargarMini() {
    if (!miniLista) return;
    try {
      const r = await fetch(`${api}?accion=mensajes_recientes`, {credentials:'same-origin',cache:'no-store'});
      const d = await r.json();
      if (!d.ok) throw new Error();
      miniLista.replaceChildren();
      if (!d.mensajes.length) { miniLista.append(el('li','notif-vacio','No tienes conversaciones recibidas.')); return; }
      d.mensajes.forEach(m => {
        const li = el('li');
        const a = el('a', 'msg-mini-link' + (Number(m.no_leidos) ? ' is-nueva' : ''));
        a.href = `${base}mensajes/index.php?abrir=${encodeURIComponent(m.id)}`;
        a.append(el('strong','',m.otro + (Number(m.no_leidos) ? ' · ' + m.no_leidos + ' sin leer' : '')));
        a.append(el('small','',m.ultimo));
        li.append(a); miniLista.append(li);
      });
    } catch (_) { miniLista.replaceChildren(el('li','notif-vacio','No se pudieron cargar los mensajes.')); }
  }
  if (sobre && miniPanel) {
    sobre.addEventListener('click', e => {
      e.preventDefault(); e.stopPropagation();
      miniAbierto = !miniAbierto; miniPanel.hidden = !miniAbierto;
      sobre.setAttribute('aria-expanded', String(miniAbierto));
      if (miniAbierto) { cerrar(); cargarMini(); }
    });
    document.addEventListener('click', e => { if (miniAbierto && !miniPanel.contains(e.target) && !sobre.contains(e.target)) { miniAbierto=false; miniPanel.hidden=true; sobre.setAttribute('aria-expanded','false'); } });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && miniAbierto) { miniAbierto=false; miniPanel.hidden=true; sobre.setAttribute('aria-expanded','false'); sobre.focus(); } });
  }
  const txt = (n) => (n > 99 ? '99+' : String(n));

  function marca(enlace, n, titulo) {
    if (!enlace) return;
    let b = enlace.querySelector(':scope > .nav-contador');
    if (n <= 0) { if (b) b.remove(); return; }
    if (!b) {
      b = el('span', 'nav-contador');
      enlace.append(b);
    }
    b.textContent = txt(n);
    b.title = titulo;
    b.setAttribute('aria-label', titulo);
  }

  function enlaceA(fin) {
    return [...document.querySelectorAll('.sidebar a.nav-item, nav a.nav-item, a.nav-subitem')]
      .find((a) => (a.getAttribute('href') || '').split('?')[0].endsWith(fin)) || null;
  }

  function contadores(c) {
    const m = Number(c.mensajes) || 0;
    if (sobre && sobreBadge) {
      sobreBadge.hidden = m <= 0;
      sobreBadge.textContent = txt(m);
      sobre.setAttribute('aria-label', m ? `Mensajes: ${m} sin leer` : 'Mensajes');
    }
    const grupos = new Map();
    [
      ['ticket/ticket.php', Number(c.ticket_usuario) || 0, 'mensajes sin leer en tus tickets'],
      ['ticket/ticket_asignados.php', Number(c.ticket_tecnico) || 0, 'mensajes sin leer en tickets asignados'],
      ['mensajes/index.php', m, 'mensajes sin leer'],
    ].forEach(([fin, n, t]) => {
      const a = enlaceA(fin);
      if (!a) return;
      marca(a, n, `${n} ${t}`);
      const sub = a.closest('.nav-submenu');
      const padre = sub && sub.previousElementSibling && sub.previousElementSibling.matches('.nav-item-parent')
        ? sub.previousElementSibling
        : (sub && sub.parentElement ? sub.parentElement.querySelector(':scope > .nav-item-parent') : null);
      if (padre) grupos.set(padre, (grupos.get(padre) || 0) + n);
    });
    grupos.forEach((n, padre) => {
      let b = padre.querySelector(':scope > .nav-contador');
      if (n <= 0) { if (b) b.remove(); return; }
      if (!b) {
        b = el('span', 'nav-contador nav-contador--grupo');
        const chev = padre.querySelector(':scope > svg');
        chev ? padre.insertBefore(b, chev) : padre.append(b);
      }
      b.textContent = txt(n);
      b.title = `${n} sin leer`;
    });
  }

  async function contar() {
    if (document.hidden) return;
    try {
      const r = await fetch(`${api}?accion=contar`, {credentials: 'same-origin', cache: 'no-store'});
      const d = await r.json();
      if (d.ok) {
        pintar(Number(d.sin_leer) || 0);
        if (d.contadores) contadores(d.contadores);
        if (miniAbierto) cargarMini();
      }
    } catch (_) { /* sin red: se reintenta */ }
  }

  function hace(fecha) {
    const d = new Date(String(fecha).replace(' ', 'T'));
    const min = Math.round((Date.now() - d.getTime()) / 60000);
    if (Number.isNaN(min)) return '';
    if (min < 1) return 'ahora';
    if (min < 60) return `hace ${min} min`;
    const h = Math.round(min / 60);
    if (h < 24) return `hace ${h} h`;
    const dias = Math.round(h / 24);
    return dias === 1 ? 'ayer' : (dias < 7 ? `hace ${dias} días` : d.toLocaleDateString('es-CL', {day: '2-digit', month: '2-digit'}));
  }

  function el(tag, cls, txt) {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (txt !== undefined) n.textContent = txt;
    return n;
  }

  function render(d) {
    lista.replaceChildren();
    todas.hidden = !d.sin_leer;
    if (!d.avisos.length) {
      const v = el('li', 'notif-vacio');
      v.innerHTML = '<i class="bi bi-bell-slash"></i>';
      v.append(el('span', '', d.instalada ? 'No tienes notificaciones.' : 'Las notificaciones se activarán pronto.'));
      lista.append(v);
      return;
    }
    d.avisos.forEach((a) => {
      const li = el('li');
      const link = el('a', `notif-item${a.leida ? '' : ' is-nueva'}`);
      link.href = a.url ? base + a.url : '#';
      link.dataset.id = a.id;
      const ic = el('span', `notif-item__ic notif-item__ic--${a.tipo}`);
      ic.innerHTML = `<i class="bi ${ICONOS[a.tipo] || 'bi-bell'}"></i>`;
      const cuerpo = el('span', 'notif-item__cuerpo');
      const titulo = a.tipo === 'ticket_mensaje' && a.cantidad > 1 ? a.titulo.replace('Nuevo mensaje', `${a.cantidad} mensajes nuevos`) : a.titulo;
      cuerpo.append(el('strong', '', titulo));
      if (a.texto) cuerpo.append(el('span', '', a.texto));
      cuerpo.append(el('time', '', hace(a.fecha)));
      link.append(ic, cuerpo);
      li.append(link);
      lista.append(li);
    });
  }

  async function abrir() {
    if (miniAbierto && miniPanel) { miniAbierto=false; miniPanel.hidden=true; sobre.setAttribute('aria-expanded','false'); }
    panel.hidden = false;
    btn.setAttribute('aria-expanded', 'true');
    lista.innerHTML = '<li class="notif-vacio"><span>Cargando…</span></li>';
    try {
      const r = await fetch(`${api}?accion=listar`, {credentials: 'same-origin', cache: 'no-store'});
      const d = await r.json();
      if (!d.ok) throw new Error();
      pintar(Number(d.sin_leer) || 0);
      render(d);
    } catch (_) {
      lista.innerHTML = '<li class="notif-vacio"><span>No se pudieron cargar las notificaciones.</span></li>';
    }
  }

  function cerrar() {
    panel.hidden = true;
    btn.setAttribute('aria-expanded', 'false');
  }

  async function post(datos) {
    const body = new URLSearchParams({csrf, ...datos});
    const r = await fetch(api, {method: 'POST', credentials: 'same-origin', body});
    return r.json();
  }

  btn.addEventListener('click', (e) => {
    e.stopPropagation();
    if (panel.hidden) abrir(); else cerrar();
  });
  todas.addEventListener('click', async () => {
    const d = await post({accion: 'leer_todas'}).catch(() => null);
    if (d?.ok) { pintar(0); lista.querySelectorAll('.is-nueva').forEach((x) => x.classList.remove('is-nueva')); todas.hidden = true; }
  });
  lista.addEventListener('click', async (e) => {
    const a = e.target.closest('.notif-item');
    if (!a) return;
    e.preventDefault();
    if (a.classList.contains('is-nueva')) await post({accion: 'leer', id: a.dataset.id}).catch(() => null);
    if (a.getAttribute('href') !== '#') window.location.href = a.href;
  });
  document.addEventListener('click', (e) => { if (!panel.hidden && !raiz.contains(e.target)) cerrar(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !panel.hidden) { cerrar(); btn.focus(); } });
  document.addEventListener('visibilitychange', contar);

  contar();
  window.setInterval(contar, 30000);
  window.Notificaciones = {contar};
})();
