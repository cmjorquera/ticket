/*
 * Bitácora del ticket (detalle): avances del técnico + conversación interna con administración.
 * Lógica: ticket/logica/LOGICA_BITACORA.txt
 */
(() => {
  'use strict';

  const app = document.getElementById('ticket-detail-app');
  if (!app) return;
  const ticketId = app.dataset.ticketId;
  const csrf = app.dataset.csrf;

  /* --- botones rápidos del ciclo: Comenzar / Terminar ticket --- */
  document.querySelectorAll('.tb-accion').forEach((b) => b.addEventListener('click', async () => {
    if (b.dataset.confirmar && !window.confirm(b.dataset.confirmar)) return;
    b.disabled = true;
    try {
      const r = await fetch('../ajax/guardar_ticket.php', {
        method: 'POST', credentials: 'same-origin',
        body: new URLSearchParams({accion: 'actualizar_estado', ticket_id: ticketId, estado: b.dataset.estado, csrf}),
      });
      const d = await r.json();
      if (!r.ok || !d.ok) throw new Error(d.error || 'No fue posible cambiar el estado.');
      window.location.reload();
    } catch (e) {
      window.alert(e.message);
      b.disabled = false;
    }
  }));

  /* --- QR de seguimiento (ticket/seguimiento.php) --- */
  const qr = document.getElementById('tb-qr');
  if (qr && typeof window.QRCode === 'function') {
    new window.QRCode(qr, {text: qr.dataset.enlace, width: 168, height: 168, correctLevel: window.QRCode.CorrectLevel.M});
    qr.removeAttribute('title');
  } else if (qr) {
    qr.textContent = 'No se pudo dibujar el código QR. Usa "Copiar enlace".';
  }
  document.getElementById('tb-qr-copiar')?.addEventListener('click', async (e) => {
    const b = e.currentTarget;
    try { await navigator.clipboard.writeText(qr.dataset.enlace); } catch (_) { window.prompt('Copia el enlace:', qr.dataset.enlace); return; }
    const antes = b.innerHTML;
    b.innerHTML = '<i class="bi bi-check2"></i> Copiado';
    window.setTimeout(() => { b.innerHTML = antes; }, 1600);
  });

  const card = document.getElementById('bitacora');
  const lista = document.getElementById('tb-list');
  if (!card || !lista) return;
  const form = document.getElementById('tb-form');
  const texto = document.getElementById('tb-texto');
  const aviso = document.getElementById('tb-mensaje');
  const total = document.getElementById('tb-total');
  const ROL = {tecnico: 'Técnico', admin: 'Administración'};
  let firma = '';

  function el(tag, cls, txt) {
    const n = document.createElement(tag);
    if (cls) n.className = cls;
    if (txt !== undefined) n.textContent = txt;
    return n;
  }
  function fecha(f) {
    const d = f ? new Date(String(f).replace(' ', 'T')) : null;
    if (!d || Number.isNaN(d.getTime())) return '';
    return d.toLocaleString('es-CL', {day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'});
  }

  function render(entradas) {
    const f = JSON.stringify(entradas.map((e) => [e.id, e.visto]));
    if (f === firma) return;
    firma = f;
    lista.replaceChildren();
    total.textContent = `${entradas.length} registro${entradas.length === 1 ? '' : 's'}`;
    if (!entradas.length) {
      const v = el('li', 'tb-vacio');
      v.innerHTML = '<i class="bi bi-pin-angle"></i>';
      v.append(el('span', '', 'No hay avances registrados.'));
      lista.append(v);
      return;
    }
    entradas.forEach((e) => {
      if (e.lado === 'sistema') {
        const li = el('li', 'tb-sistema');
        li.append(el('i', 'bi bi-arrow-repeat'), el('span', '', e.texto), el('time', '', fecha(e.fecha)));
        lista.append(li);
        return;
      }
      const li = el('li', `tb-item tb-item--${e.lado}${e.propio ? ' is-own' : ''}`);
      const punto = el('span', 'tb-punto');
      punto.innerHTML = e.lado === 'admin' ? '<i class="bi bi-shield-check"></i>' : '<i class="bi bi-tools"></i>';
      const cuerpo = el('div', 'tb-cuerpo');
      const cab = el('div', 'tb-cab');
      cab.append(el('strong', '', e.autor), el('span', `tb-rol tb-rol--${e.lado}`, ROL[e.lado] || ''), el('time', '', fecha(e.fecha)));
      if (e.propio) {
        const c = el('span', `tc-check${e.visto ? ' is-seen' : ''}`);
        c.innerHTML = e.visto ? '<i class="bi bi-check2-all"></i>' : '<i class="bi bi-check2"></i>';
        c.title = e.visto ? 'Visto' : 'Enviado';
        cab.append(c);
      }
      cuerpo.append(cab, el('p', '', e.texto));
      li.append(punto, cuerpo);
      lista.append(li);
    });
  }

  async function post(datos) {
    const r = await fetch('../ajax/ticket_bitacora.php', {
      method: 'POST', credentials: 'same-origin',
      body: new URLSearchParams({csrf, ticket_id: ticketId, vista: card.dataset.rol, ...datos}),
    });
    const d = await r.json().catch(() => ({ok: false, error: 'Respuesta no válida del servidor.'}));
    if (!r.ok || !d.ok) throw new Error(d.error || 'No fue posible cargar la bitácora.');
    return d;
  }

  async function refrescar() {
    if (document.hidden) return;
    try {
      const d = await post({accion: 'listar'});
      render(d.entradas || []);
    } catch (_) { /* se reintenta en el próximo ciclo */ }
  }

  try { render(JSON.parse(document.getElementById('tb-datos').textContent || '[]')); } catch (_) { render([]); }
  window.setInterval(refrescar, 5000);
  document.addEventListener('visibilitychange', refrescar);

  texto?.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); form.requestSubmit(); }
  });
  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const valor = texto.value.trim();
    if (!valor) return;
    const b = form.querySelector('button[type="submit"]');
    b.disabled = true;
    aviso.className = 'ticket-message';
    aviso.textContent = '';
    try {
      await post({accion: 'guardar', texto: valor});
      texto.value = '';
      await refrescar();
      lista.lastElementChild?.scrollIntoView({block: 'nearest', behavior: 'smooth'});
    } catch (err) {
      aviso.className = 'ticket-message error';
      aviso.textContent = err.message;
    } finally {
      b.disabled = false;
      texto.focus();
    }
  });
})();
