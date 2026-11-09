/* Mi cuenta: utilidades comunes (API, formato, modo privado, diálogos). */
window.MC = (function () {
    'use strict';
    const app = document.getElementById('mcApp');
    const CSRF = app ? app.dataset.csrf : '';
    const MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const pesos = n => '$ ' + Math.round(Number(n) || 0).toLocaleString('es-CL');
    const periodo = p => { const [a, m] = String(p).split('-').map(Number); return MESES[m - 1] + ' ' + a; };
    const Periodo = p => { const t = periodo(p); return t.charAt(0).toUpperCase() + t.slice(1); };
    const fecha = f => { if (!f) { return '—'; } const [a, m, d] = f.split('-'); return d + '-' + m + '-' + a; };
    const tamano = b => !b ? '—' : b < 1024 * 1024 ? Math.max(1, Math.round(b / 1024)) + ' KB' : (b / 1024 / 1024).toFixed(1).replace('.', ',') + ' MB';

    function toast(tipo, titulo, mensaje) {
        if (typeof window.showToast === 'function') { window.showToast(tipo, titulo, mensaje); return; }
        alert(titulo + (mensaje ? '\n' + mensaje : ''));
    }

    async function post(datos) {
        const fd = datos instanceof FormData ? datos : new FormData();
        if (!(datos instanceof FormData)) { Object.entries(datos).forEach(([k, v]) => fd.append(k, v)); }
        fd.append('csrf', CSRF);
        const r = await fetch('ajax/cuenta.php', { method: 'POST', body: fd, credentials: 'same-origin', headers: { Accept: 'application/json' } });
        let j = {};
        try { j = await r.json(); } catch (e) { j = { ok: false, mensaje: 'Respuesta no válida del servidor.' }; }
        if (!r.ok || !j.ok) { throw new Error(j.mensaje || 'No fue posible completar la acción.'); }
        return j;
    }

    async function get(params) {
        const r = await fetch('ajax/rrhh.php?' + new URLSearchParams(params), { credentials: 'same-origin', headers: { Accept: 'application/json' } });
        let j = {};
        try { j = await r.json(); } catch (e) { j = { ok: false, mensaje: 'Respuesta no válida del servidor.' }; }
        if (!r.ok || !j.ok) { throw new Error(j.mensaje || 'No fue posible cargar la información.'); }
        return j;
    }

    // Modo privado: difumina los montos (se recuerda en este navegador)
    const CLAVE_PRIVADO = 'mc_privado';
    function privado(activo) {
        if (activo === undefined) {
            try { return localStorage.getItem(CLAVE_PRIVADO) === '1'; } catch (e) { return false; }
        }
        try { localStorage.setItem(CLAVE_PRIVADO, activo ? '1' : '0'); } catch (e) { /* sin almacenamiento */ }
        document.documentElement.classList.toggle('mc-privado', !!activo);
        const b = document.getElementById('btnPrivado');
        if (b) {
            b.setAttribute('aria-pressed', String(!!activo));
            b.querySelector('i').className = 'bi ' + (activo ? 'bi-eye' : 'bi-eye-slash');
            b.querySelector('span').textContent = activo ? 'Mostrar montos' : 'Ocultar montos';
        }
        return !!activo;
    }

    // Diálogos: cerrar con [data-cerrar] o clic en el fondo
    document.addEventListener('click', e => {
        const c = e.target.closest('.mc-dialog [data-cerrar]');
        if (c) { c.closest('dialog').close(); return; }
        if (e.target.classList && e.target.classList.contains('mc-dialog')) { e.target.close(); }
    });

    return { esc, pesos, periodo, Periodo, fecha, tamano, toast, post, get, privado, MESES };
})();
