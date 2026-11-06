/* Almuerzos – interfaz con datos de ejemplo (todavía sin base de datos).
 * - Pedido del día entre 09:00 y 12:00: Menú del día, Hipocalórico o Picnic.
 * - Calendario del mes con los tres menús por día hábil.
 * - Administración: plantilla CSV, carga y vista previa del menú del mes.
 * La elección y el menú cargado se guardan solo en este navegador (localStorage).
 */
(function () {
    'use strict';

    const CFG = window.ALM_CONFIG || {};
    const HORA_INICIO = 9 * 60;   // 09:00
    const HORA_FIN = 12 * 60;     // 12:00
    const OPCIONES = {
        menu:   { nombre: 'Menú del día', icono: 'bi-egg-fried', clase: 'menu' },
        hipo:   { nombre: 'Hipocalórico', icono: 'bi-heart-pulse', clase: 'hipo' },
        picnic: { nombre: 'Picnic', icono: 'bi-basket', clase: 'picnic' },
    };
    const DIAS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    const MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    // ---------------------------------------------------------- utilidades
    const $ = sel => document.querySelector(sel);
    const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const dos = n => String(n).padStart(2, '0');
    const clave = d => d.getFullYear() + '-' + dos(d.getMonth() + 1) + '-' + dos(d.getDate());
    const desdeClave = k => { const [y, m, d] = k.split('-').map(Number); return new Date(y, m - 1, d); };
    const claveMes = d => d.getFullYear() + '-' + dos(d.getMonth() + 1);
    const habil = d => d.getDay() >= 1 && d.getDay() <= 5;
    const titulo = t => t.charAt(0).toUpperCase() + t.slice(1);
    const fechaLarga = d => titulo(DIAS[d.getDay()]) + ' ' + d.getDate() + ' de ' + MESES[d.getMonth()];
    const store = {
        get(k, def) { try { const v = localStorage.getItem('alm_' + CFG.usuario + '_' + k); return v ? JSON.parse(v) : def; } catch (e) { return def; } },
        set(k, v) { try { localStorage.setItem('alm_' + CFG.usuario + '_' + k, JSON.stringify(v)); } catch (e) { /* sin almacenamiento */ } },
    };

    // ---------------------------------------------------------- datos de ejemplo
    const PLATOS = {
        menu: [
            ['Crema de zapallo', 'Pollo arvejado con arroz', 'Fruta de la estación'],
            ['Ensalada chilena', 'Porotos granados', 'Mote con huesillo'],
            ['Sopa de verduras', 'Carne mechada con puré', 'Leche asada'],
            ['Ensalada de repollo', 'Pastel de choclo', 'Fruta picada'],
            ['Consomé', 'Lentejas con arroz', 'Sémola con leche'],
            ['Ensalada surtida', 'Merluza frita con papas cocidas', 'Gelatina'],
            ['Crema de espárragos', 'Charquicán con huevo', 'Manzana asada'],
            ['Ensalada de betarraga', 'Tallarines con salsa bolognesa', 'Fruta de la estación'],
            ['Sopa de pollo', 'Cazuela de vacuno', 'Arroz con leche'],
            ['Ensalada mixta', 'Pollo al jugo con papas doradas', 'Flan'],
        ],
        hipo: [
            ['Ensalada verde', 'Pechuga a la plancha con quínoa', 'Yogur natural'],
            ['Crema de zapallo italiano', 'Reineta al vapor con verduras', 'Fruta'],
            ['Ensalada de tomate', 'Omelette de espinaca con ensalada', 'Gelatina light'],
            ['Caldo de verduras', 'Pavo al horno con puré de coliflor', 'Fruta picada'],
            ['Ensalada de pepino', 'Salteado de verduras con tofu', 'Manzana'],
            ['Ensalada de lechuga', 'Atún con garbanzos y verduras', 'Fruta'],
        ],
        picnic: [
            ['Sándwich de ave pimiento', 'Jugo natural', 'Barra de cereal'],
            ['Wrap de pollo y verduras', 'Agua saborizada', 'Fruta'],
            ['Sándwich de jamón queso', 'Yogur', 'Galletas de avena'],
            ['Ensalada César para llevar', 'Jugo natural', 'Fruta'],
            ['Empanada de horno', 'Agua mineral', 'Manzana'],
        ],
    };
    // Número pseudoaleatorio estable por fecha (mismo menú cada vez que se abre)
    const semilla = (k, n) => { let h = 7; for (const c of k) { h = (h * 31 + c.charCodeAt(0)) % 100003; } return h % n; };
    const unir = p => p.join(' + ');
    // Plato que se muestra en el calendario: el fondo (o lo primero, en picnic)
    const platoPrincipal = (id, texto) => { const p = String(texto).split('+').map(t => t.trim()); return (id === 'picnic' ? p[0] : (p[1] || p[0])) || ''; };

    function menuEjemplo(año, mes) {
        const dias = {};
        for (let d = new Date(año, mes, 1); d.getMonth() === mes; d.setDate(d.getDate() + 1)) {
            if (!habil(d)) { continue; }
            const k = clave(d);
            dias[k] = {
                menu: unir(PLATOS.menu[semilla(k + 'm', PLATOS.menu.length)]),
                hipo: unir(PLATOS.hipo[semilla(k + 'h', PLATOS.hipo.length)]),
                picnic: unir(PLATOS.picnic[semilla(k + 'p', PLATOS.picnic.length)]),
            };
        }
        return dias;
    }

    // Menú publicado (cargado por administración) o de ejemplo
    function menuDelMes(año, mes) {
        const cargados = store.get('menus', {});
        const k = año + '-' + dos(mes + 1);
        return cargados[k] || menuEjemplo(año, mes);
    }
    const menuDe = d => menuDelMes(d.getFullYear(), d.getMonth())[clave(d)] || null;

    // Pedidos: los de hoy los elige la persona; los días pasados se inventan
    function pedidos() {
        const guardados = store.get('pedidos', {});
        const hoy = clave(new Date());
        const res = Object.assign({}, guardados);
        const inicio = new Date(); inicio.setDate(1);
        for (let d = new Date(inicio); clave(d) < hoy; d.setDate(d.getDate() + 1)) {
            const k = clave(d);
            if (!habil(d) || k in res) { continue; }
            res[k] = ['menu', 'menu', 'hipo', 'picnic', 'menu', 'no'][semilla(k + CFG.usuario, 6)];
        }
        return res;
    }
    function guardarPedido(k, opcion) {
        const g = store.get('pedidos', {});
        if (opcion === null) { delete g[k]; } else { g[k] = opcion; }
        store.set('pedidos', g);
    }

    // ---------------------------------------------------------- horario
    function horarioAbierto(ahora) {
        if ($('#chkSimular') && $('#chkSimular').checked) { return true; }
        if (!habil(ahora)) { return false; }
        const min = ahora.getHours() * 60 + ahora.getMinutes();
        return min >= HORA_INICIO && min < HORA_FIN;
    }
    function textoRestante(min) {
        const h = Math.floor(min / 60), m = min % 60;
        return (h ? h + ' h ' : '') + m + ' min';
    }
    function pintarEstado() {
        const ahora = new Date();
        const min = ahora.getHours() * 60 + ahora.getMinutes();
        const abierto = horarioAbierto(ahora);
        const $e = $('#almEstado');
        $e.classList.toggle('is-abierto', abierto);
        $e.classList.toggle('is-cerrado', !abierto);
        $('#almReloj').textContent = dos(ahora.getHours()) + ':' + dos(ahora.getMinutes());
        if (abierto) {
            $('#almEstadoTitulo').textContent = 'Pedidos abiertos';
            $('#almEstadoDetalle').textContent = $('#chkSimular').checked && !(habil(ahora) && min >= HORA_INICIO && min < HORA_FIN)
                ? 'Horario simulado para probar la pantalla.'
                : 'Puedes elegir o cambiar tu almuerzo. Cierra a las 12:00 (quedan ' + textoRestante(HORA_FIN - min) + ').';
        } else if (habil(ahora) && min < HORA_INICIO) {
            $('#almEstadoTitulo').textContent = 'Aún no abre';
            $('#almEstadoDetalle').textContent = 'Los pedidos de hoy se abren a las 09:00 (en ' + textoRestante(HORA_INICIO - min) + ').';
        } else {
            const sig = new Date(ahora); do { sig.setDate(sig.getDate() + 1); } while (!habil(sig));
            $('#almEstadoTitulo').textContent = 'Pedidos cerrados';
            $('#almEstadoDetalle').textContent = 'Vuelve a abrir el ' + fechaLarga(sig).toLowerCase() + ' a las 09:00.';
        }
        return abierto;
    }

    // ---------------------------------------------------------- hoy
    function platosHtml(texto) {
        return '<ul class="alm-platos">' + String(texto).split('+').map(p => '<li>' + esc(p.trim()) + '</li>').join('') + '</ul>';
    }
    function pintarHoy() {
        const hoy = new Date();
        const k = clave(hoy);
        const menu = menuDe(hoy);
        const abierto = pintarEstado();
        const elegido = (store.get('pedidos', {}))[k];
        $('#almHoyTitulo').textContent = 'Almuerzo de hoy · ' + fechaLarga(hoy);

        const $pill = $('#almMiPedido');
        $pill.className = 'alm-pill' + (elegido && elegido !== 'no' ? ' alm-pill--' + OPCIONES[elegido].clase : '');
        $pill.textContent = elegido === 'no' ? 'No almorzarás hoy' : (elegido ? 'Tu pedido: ' + OPCIONES[elegido].nombre : 'Sin elegir');

        if (!menu) {
            $('#almOpciones').innerHTML = '<div class="alm-vacio"><i class="bi bi-calendar-x"></i><p>Hoy no hay servicio de almuerzo.</p></div>';
            $('#almHoySub').textContent = 'Revisa el menú del mes más abajo.';
            $('#btnNoAlmuerzo').hidden = true;
            return;
        }
        $('#btnNoAlmuerzo').hidden = false;
        $('#almHoySub').textContent = abierto ? 'Elige una opción. Puedes cambiarla hasta las 12:00.' : 'El horario de pedidos está cerrado: solo puedes ver el menú.';
        $('#almOpciones').innerHTML = Object.entries(OPCIONES).map(([id, o]) => `
            <article class="alm-opcion alm-opcion--${o.clase}${elegido === id ? ' is-elegida' : ''}">
                <div class="alm-opcion__top">
                    <span class="alm-opcion__icono"><i class="bi ${o.icono}"></i></span>
                    <h3>${esc(o.nombre)}</h3>
                    ${elegido === id ? '<i class="bi bi-check-circle-fill alm-opcion__check" aria-label="Elegido"></i>' : ''}
                </div>
                ${platosHtml(menu[id])}
                <button type="button" class="btn ${elegido === id ? 'btn-outline' : 'btn-primary'} btn-sm alm-opcion__btn" data-opcion="${id}" ${abierto ? '' : 'disabled'}>
                    ${elegido === id ? '<i class="bi bi-check2"></i> Elegido' : 'Elegir'}
                </button>
            </article>`).join('');
        $('#btnNoAlmuerzo').disabled = !abierto;
        $('#almNota').textContent = elegido ? '' : (abierto ? 'Si no eliges antes de las 12:00 quedas sin almuerzo.' : '');
    }

    // ---------------------------------------------------------- resumen y calendario
    let mesVista = new Date(); mesVista.setDate(1);

    function pintarResumen() {
        const hoy = new Date();
        const p = pedidos();
        const prefijo = claveMes(hoy);
        const conteo = { menu: 0, hipo: 0, picnic: 0, no: 0 };
        Object.entries(p).forEach(([k, v]) => { if (k.startsWith(prefijo) && v in conteo) { conteo[v]++; } });
        $('#almMesNombre').textContent = titulo(MESES[hoy.getMonth()]) + ' ' + hoy.getFullYear();
        $('#almConteo').innerHTML = Object.entries(OPCIONES).map(([id, o]) =>
            `<li><span><i class="alm-ico alm-ico--${o.clase}"></i>${esc(o.nombre)}</span><strong>${conteo[id]}</strong></li>`).join('')
            + `<li class="is-muted"><span><i class="alm-ico"></i>Sin almuerzo</span><strong>${conteo.no}</strong></li>`;

        const proximos = [];
        for (let d = new Date(hoy); proximos.length < 4;) {
            d.setDate(d.getDate() + 1);
            if (habil(d) && menuDe(d)) { proximos.push(new Date(d)); }
            if (d - hoy > 1000 * 60 * 60 * 24 * 40) { break; }
        }
        $('#almProximos').innerHTML = proximos.map(d => {
            const m = menuDe(d);
            return `<li><button type="button" data-dia="${clave(d)}"><span>${esc(titulo(DIAS[d.getDay()]).slice(0, 3))} ${d.getDate()}</span><em>${esc(platoPrincipal('menu', m.menu))}</em></button></li>`;
        }).join('') || '<li class="is-muted">Sin menús publicados.</li>';

        if ($('#almCocina')) {
            // Mismos números que el PDF (calculados en el servidor)
            const base = CFG.cocina;
            if (!base) {
                $('#almCocina').innerHTML = '<li class="is-muted">Hoy no hay servicio de almuerzo.</li>';
            } else {
                const total = base.menu + base.hipo + base.picnic;
                $('#almCocina').innerHTML = Object.entries(OPCIONES).map(([id, o]) =>
                    `<li><span><i class="alm-ico alm-ico--${o.clase}"></i>${esc(o.nombre)}</span><strong>${base[id]}</strong></li>`).join('')
                    + `<li class="is-muted"><span><i class="alm-ico"></i>No almuerzan / sin pedido</span><strong>${base.no + base['']}</strong></li>`
                    + `<li class="is-total"><span>Total a preparar</span><strong>${total}</strong></li>`;
            }
        }
    }

    function pintarCalendario() {
        const año = mesVista.getFullYear(), mes = mesVista.getMonth();
        const menus = menuDelMes(año, mes);
        const p = pedidos();
        const hoy = clave(new Date());
        $('#almMesTitulo').textContent = titulo(MESES[mes]) + ' ' + año;

        let html = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie'].map(d => `<div class="alm-cal__dow">${d}</div>`).join('');
        const primero = new Date(año, mes, 1);
        let desfase = (primero.getDay() + 6) % 7; // lunes = 0
        if (desfase > 4) { desfase = 0; }        // si el mes parte en fin de semana
        html += '<div class="alm-cal__vacio"></div>'.repeat(desfase);
        for (let d = new Date(primero); d.getMonth() === mes; d.setDate(d.getDate() + 1)) {
            if (!habil(d)) { continue; }
            const k = clave(d), m = menus[k], elegido = p[k];
            const clases = ['alm-dia'];
            if (k === hoy) { clases.push('is-hoy'); }
            if (k < hoy) { clases.push('is-pasado'); }
            if (!m) { clases.push('is-sin'); }
            html += `<button type="button" class="${clases.join(' ')}" data-dia="${k}" ${m ? '' : 'disabled'}>
                <span class="alm-dia__num"><span class="alm-dia__dow">${esc(titulo(DIAS[d.getDay()]))} </span>${d.getDate()}${k === hoy ? ' <small>Hoy</small>' : ''}</span>
                ${m ? Object.entries(OPCIONES).map(([id, o]) =>
                    `<span class="alm-dia__plato alm-dia__plato--${o.clase}${elegido === id ? ' is-elegido' : ''}" title="${esc(o.nombre)}">${esc(platoPrincipal(id, m[id]))}</span>`).join('')
                    : '<span class="alm-dia__sin">Sin servicio</span>'}
                ${elegido === 'no' ? '<span class="alm-dia__no">Sin almuerzo</span>' : ''}
            </button>`;
        }
        $('#almCalendario').innerHTML = html;
    }

    function abrirDia(k) {
        const d = desdeClave(k), m = menuDe(d);
        if (!m) { return; }
        const elegido = pedidos()[k];
        $('#dlgDiaTitulo').textContent = fechaLarga(d);
        $('#dlgDiaCuerpo').innerHTML = Object.entries(OPCIONES).map(([id, o]) => `
            <div class="alm-detalle alm-detalle--${o.clase}${elegido === id ? ' is-elegida' : ''}">
                <h3><i class="bi ${o.icono}"></i> ${esc(o.nombre)} ${elegido === id ? '<span class="alm-pill alm-pill--' + o.clase + '">Tu pedido</span>' : ''}</h3>
                ${platosHtml(m[id])}
            </div>`).join('')
            + (k > clave(new Date()) ? '<p class="alm-nota mt">Podrás elegir este almuerzo ese día entre las 09:00 y las 12:00.</p>' : '')
            + (elegido === 'no' ? '<p class="alm-nota mt">Ese día indicaste que no almorzabas.</p>' : '');
        $('#dlgDia').showModal();
    }

    // ---------------------------------------------------------- avisos
    let toastTimer;
    function toast(texto, tipo) {
        const $t = $('#almToast');
        $t.textContent = texto;
        $t.className = 'alm-toast' + (tipo ? ' alm-toast--' + tipo : '');
        $t.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { $t.hidden = true; }, 2600);
    }

    function refrescar() { pintarHoy(); pintarResumen(); pintarCalendario(); }

    // ---------------------------------------------------------- eventos
    document.addEventListener('click', e => {
        const elegir = e.target.closest('[data-opcion]');
        if (elegir && !elegir.disabled) {
            if (!horarioAbierto(new Date())) { toast('El horario de pedidos está cerrado.', 'error'); return; }
            const id = elegir.dataset.opcion;
            guardarPedido(clave(new Date()), id);
            toast('Listo: pediste ' + OPCIONES[id].nombre + ' para hoy.', 'ok');
            refrescar();
            return;
        }
        const dia = e.target.closest('[data-dia]');
        if (dia) { abrirDia(dia.dataset.dia); }
    });
    $('#btnNoAlmuerzo').addEventListener('click', () => {
        if (!horarioAbierto(new Date())) { return; }
        guardarPedido(clave(new Date()), 'no');
        toast('Anotado: hoy no almorzarás.', 'ok');
        refrescar();
    });
    $('#chkSimular').addEventListener('change', pintarHoy);
    $('#btnMesAnterior').addEventListener('click', () => { mesVista.setMonth(mesVista.getMonth() - 1); pintarCalendario(); });
    $('#btnMesSiguiente').addEventListener('click', () => { mesVista.setMonth(mesVista.getMonth() + 1); pintarCalendario(); });
    $('#dlgDia').addEventListener('click', e => { if (e.target === $('#dlgDia')) { $('#dlgDia').close(); } });

    // ---------------------------------------------------------- cargar menú del mes (administración)
    const dlgCargar = $('#dlgCargar');
    if (dlgCargar) {
        let filasCargadas = null;
        const mesSiguiente = () => { const d = new Date(); d.setMonth(d.getMonth() + 1, 1); return claveMes(d); };
        const mesElegido = () => ($('#cargarMes').value || mesSiguiente()).split('-').map(Number);

        $('#btnCargarMes').addEventListener('click', () => {
            $('#cargarMes').value = $('#cargarMes').value || mesSiguiente();
            dlgCargar.showModal();
        });
        dlgCargar.querySelectorAll('[data-cerrar]').forEach(b => b.addEventListener('click', () => dlgCargar.close()));

        // Plantilla: una fila por día hábil del mes, separada por ; (Excel en español)
        $('#btnPlantilla').addEventListener('click', () => {
            const [y, m] = mesElegido();
            const ej = menuEjemplo(y, m - 1);
            const lineas = ['fecha;menu_del_dia;hipocalorico;picnic'];
            Object.keys(ej).forEach(k => lineas.push([k.split('-').reverse().join('-'), ej[k].menu, ej[k].hipo, ej[k].picnic].map(v => '"' + v.replace(/"/g, '""') + '"').join(';')));
            const blob = new Blob(['﻿' + lineas.join('\r\n')], { type: 'text/csv;charset=utf-8' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'almuerzos_' + y + '-' + dos(m) + '.csv';
            document.body.appendChild(a); a.click(); a.remove();
            setTimeout(() => URL.revokeObjectURL(a.href), 1000);
        });

        function parsearCsv(texto) {
            texto = texto.replace(/^﻿/, '');
            const sep = (texto.split('\n')[0].match(/;/g) || []).length >= (texto.split('\n')[0].match(/,/g) || []).length ? ';' : ',';
            const filas = []; let fila = [], campo = '', comillas = false;
            for (let i = 0; i < texto.length; i++) {
                const c = texto[i];
                if (comillas) {
                    if (c === '"' && texto[i + 1] === '"') { campo += '"'; i++; }
                    else if (c === '"') { comillas = false; }
                    else { campo += c; }
                } else if (c === '"') { comillas = true; }
                else if (c === sep) { fila.push(campo); campo = ''; }
                else if (c === '\n' || c === '\r') {
                    if (c === '\r' && texto[i + 1] === '\n') { i++; }
                    fila.push(campo); campo = '';
                    if (fila.some(v => v.trim() !== '')) { filas.push(fila); }
                    fila = [];
                } else { campo += c; }
            }
            fila.push(campo);
            if (fila.some(v => v.trim() !== '')) { filas.push(fila); }
            return filas;
        }

        function leerFecha(t) {
            t = String(t).trim();
            let m = t.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{4})$/);
            if (m) { return new Date(+m[3], +m[2] - 1, +m[1]); }
            m = t.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
            if (m) { return new Date(+m[1], +m[2] - 1, +m[3]); }
            return null;
        }

        function mostrarPreview(filas, origen) {
            const [y, m] = mesElegido();
            const errores = [];
            const validas = {};
            filas.forEach((f, i) => {
                if (i === 0 && /fecha/i.test(f[0])) { return; }
                const d = leerFecha(f[0] || '');
                const fila = i + 1;
                if (!d || isNaN(d)) { errores.push('Fila ' + fila + ': fecha no válida'); return; }
                if (d.getFullYear() !== y || d.getMonth() !== m - 1) { errores.push('Fila ' + fila + ': la fecha no es del mes elegido'); return; }
                if (!habil(d)) { errores.push('Fila ' + fila + ': ' + clave(d) + ' es fin de semana'); return; }
                const [menu, hipo, picnic] = [f[1], f[2], f[3]].map(v => String(v || '').trim());
                if (!menu || !hipo || !picnic) { errores.push('Fila ' + fila + ': faltan menús'); return; }
                validas[clave(d)] = { menu, hipo, picnic };
            });
            filasCargadas = validas;
            const claves = Object.keys(validas).sort();
            $('#almPreviewTitulo').textContent = 'Vista previa · ' + titulo(MESES[m - 1]) + ' ' + y + ' (' + claves.length + ' días)' + (origen ? ' · ' + origen : '');
            $('#almPreviewAviso').innerHTML = errores.length
                ? '<span class="alm-error">' + errores.length + ' fila(s) con problemas: ' + esc(errores.slice(0, 3).join('; ')) + (errores.length > 3 ? '…' : '') + '</span>'
                : '<span class="alm-ok"><i class="bi bi-check-circle"></i> Todo correcto</span>';
            $('#almPreviewFilas').innerHTML = claves.map(k => {
                const v = validas[k], d = desdeClave(k);
                return `<tr><td class="nowrap">${esc(titulo(DIAS[d.getDay()]).slice(0, 3))} ${d.getDate()}</td><td>${esc(v.menu)}</td><td>${esc(v.hipo)}</td><td>${esc(v.picnic)}</td></tr>`;
            }).join('') || '<tr><td colspan="4" class="alm-error">No se encontraron días válidos.</td></tr>';
            $('#almPreview').hidden = false;
            $('#btnPublicar').disabled = claves.length === 0;
        }

        function leerArchivo(archivo) {
            if (!archivo) { return; }
            if (!/\.csv$/i.test(archivo.name)) { toast('Por ahora solo se aceptan archivos .csv', 'error'); return; }
            const r = new FileReader();
            r.onload = () => mostrarPreview(parsearCsv(String(r.result)), archivo.name);
            r.readAsText(archivo, 'utf-8');
        }
        $('#cargarArchivo').addEventListener('change', e => leerArchivo(e.target.files[0]));
        const drop = $('#almDrop');
        ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('is-sobre'); }));
        ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('is-sobre'); }));
        drop.addEventListener('drop', e => leerArchivo(e.dataTransfer.files[0]));
        $('#cargarMes').addEventListener('change', () => { $('#almPreview').hidden = true; $('#btnPublicar').disabled = true; filasCargadas = null; });

        $('#btnEjemplo').addEventListener('click', () => {
            const [y, m] = mesElegido();
            const ej = menuEjemplo(y, m - 1);
            mostrarPreview([['fecha']].concat(Object.keys(ej).map(k => [k, ej[k].menu, ej[k].hipo, ej[k].picnic])), 'ejemplo');
        });

        $('#btnPublicar').addEventListener('click', () => {
            if (!filasCargadas) { return; }
            const [y, m] = mesElegido();
            const menus = store.get('menus', {});
            menus[y + '-' + dos(m)] = filasCargadas;
            store.set('menus', menus);
            dlgCargar.close();
            mesVista = new Date(y, m - 1, 1);
            refrescar();
            document.getElementById('almMes').scrollIntoView({ behavior: 'smooth' });
            toast('Menú de ' + MESES[m - 1] + ' publicado (solo en este navegador por ahora).', 'ok');
        });
    }

    refrescar();
    setInterval(pintarHoy, 30000); // el estado del horario se actualiza solo
}());
