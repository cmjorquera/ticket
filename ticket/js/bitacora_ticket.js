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

  /* Confirmación visual coherente con el diseño del sistema, sin confirm() nativo. */
  function confirmarCambioEstado(mensaje) {
    return new Promise(resolve => {
      const overlay = document.createElement('div');
      overlay.className = 'ticket-confirm-overlay';
      overlay.innerHTML = `
        <section class="ticket-confirm-box" role="dialog" aria-modal="true" aria-labelledby="ticket-confirm-title" aria-describedby="ticket-confirm-desc">
          <div class="ticket-confirm-heading">
            <span class="ticket-confirm-icon"><i class="bi bi-flag-fill" aria-hidden="true"></i></span>
            <div><small>GESTIÓN DE TICKETS</small><h2 id="ticket-confirm-title">Terminar ticket</h2></div>
          </div>
          <div class="ticket-confirm-body">
            <p id="ticket-confirm-desc"></p>
            <p class="ticket-confirm-note"><i class="bi bi-info-circle" aria-hidden="true"></i> La conversación y la bitácora permanecerán disponibles como registro.</p>
          </div>
          <div class="ticket-confirm-actions">
            <button type="button" class="ticket-confirm-cancel">Cancelar</button>
            <button type="button" class="ticket-confirm-accept"><i class="bi bi-check2" aria-hidden="true"></i> Terminar ticket</button>
          </div>
        </section>`;
      overlay.querySelector('#ticket-confirm-desc').textContent = mensaje.replace(/La conversación y la bitácora quedarán como registro\.?/i, '').trim();
      const styleId = 'ticket-confirm-style';
      if (!document.getElementById(styleId)) {
        const style = document.createElement('style');
        style.id = styleId;
        style.textContent = `
          .ticket-confirm-overlay{position:fixed!important;inset:0!important;z-index:2147483646!important;background:rgba(16,32,52,.55)!important;display:flex!important;align-items:center!important;justify-content:center!important;padding:16px!important;box-sizing:border-box!important}
          .ticket-confirm-box{width:min(470px,100%)!important;max-height:calc(100dvh - 32px)!important;overflow:auto!important;border-radius:14px!important;background:#fff!important;box-shadow:0 20px 65px rgba(9,28,52,.28)!important;color:#26364c!important}
          .ticket-confirm-heading{display:flex;align-items:center;gap:14px;padding:20px 22px;background:linear-gradient(110deg,#17365b,#234e79);color:white}
          .ticket-confirm-icon{display:grid;place-items:center;width:44px;height:44px;flex:none;background:rgba(255,255,255,.15);border-radius:12px;font-size:20px}
          .ticket-confirm-heading small{display:block;font-size:10px;letter-spacing:1px;font-weight:700;opacity:.8}
          .ticket-confirm-heading h2{margin:3px 0 0!important;font-size:20px!important;color:#fff!important;font-weight:700!important}
          .ticket-confirm-body{padding:22px 22px 8px;font-size:14px}
          .ticket-confirm-body>p:first-child{margin:0 0 14px;font-weight:600}
          .ticket-confirm-note{display:flex;gap:9px;align-items:flex-start;padding:12px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:9px;font-size:12px;color:#53657a;line-height:1.5}
          .ticket-confirm-actions{display:flex;justify-content:flex-end;gap:10px;padding:12px 22px 22px}
          .ticket-confirm-actions button{border-radius:8px;padding:10px 16px;font-weight:600;font-size:13px;cursor:pointer}
          .ticket-confirm-cancel{background:#fff;color:#334155;border:1px solid #cbd5e1}
          .ticket-confirm-accept{background:#17365b;color:#fff;border:1px solid #17365b}
          .ticket-confirm-actions button:focus-visible{outline:3px solid #94b8df;outline-offset:2px}
        `;
        document.head.appendChild(style);
      }
      document.body.appendChild(overlay);
      const cancel = overlay.querySelector('.ticket-confirm-cancel');
      const accept = overlay.querySelector('.ticket-confirm-accept');
      const previousFocus = document.activeElement;
      function finish(value) {
        document.removeEventListener('keydown', onKey);
        overlay.remove();
        previousFocus?.focus?.();
        resolve(value);
      }
      function onKey(event) {
        if (event.key === 'Escape') { event.preventDefault(); finish(false); }
        if (event.key === 'Tab') {
          if (event.shiftKey && document.activeElement === cancel) { event.preventDefault(); accept.focus(); }
          else if (!event.shiftKey && document.activeElement === accept) { event.preventDefault(); cancel.focus(); }
        }
      }
      cancel.addEventListener('click', () => finish(false));
      accept.addEventListener('click', () => finish(true));
      overlay.addEventListener('click', event => { if (event.target === overlay) finish(false); });
      document.addEventListener('keydown', onKey);
      cancel.focus();
    });
  }

  /* --- botones rápidos del ciclo: Comenzar / Terminar ticket --- */
  document.querySelectorAll('.tb-accion[data-estado]').forEach((b) => b.addEventListener('click', async () => {
    if (b.dataset.confirmar && !(await confirmarCambioEstado(b.dataset.confirmar))) return;
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
