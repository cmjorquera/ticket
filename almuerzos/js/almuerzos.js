/* Almuerzos: pedido del día, menú del mes (modal + PDF) y carga mensual del menú.
 * Todo se guarda en la base de datos a través de ajax/almuerzos.php.
 * El horario (almuerzo_configuracion) lo valida el servidor; aquí solo se muestra.
 */
(function () {
    'use strict';

    const CFG = window.ALM_CONFIG || {};
    const API = 'ajax/almuerzos.php';
    const OPCIONES = {
        menu:   { nombre: 'Menú del día', icono: 'bi-egg-fried', clase: 'menu' },
        hipo:   { nombre: 'Hipocalórico', icono: 'bi-heart-pulse', clase: 'hipo' },
        picnic: { nombre: 'Picnic', icono: 'bi-basket', clase: 'picnic' },
    };
    const DIAS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    const MESES = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

    // ------------------------------------------------------------ utilidades
    const $ = sel => document.querySelector(sel);
    const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const dos = n => String(n).padStart(2, '0');
    const clave = d => d.getFullYear() + '-' + dos(d.getMonth() + 1) + '-' + dos(d.getDate());
    const desdeClave = k => { const [y, m, d] = k.split('-').map(Number); return new Date(y, m - 1, d); };
    const claveMes = d => d.getFullYear() + '-' + dos(d.getMonth() + 1);
    const habil = d => d.getDay() >= 1 && d.getDay() <= 5;
    const titulo = t => t.charAt(0).toUpperCase() + t.slice(1);
    const fechaLarga = d => titulo(DIAS[d.getDay()]) + ' ' + d.getDate() + ' de ' + MESES[d.getMonth()];
    const platos = texto => String(texto).split('+').map(t => t.trim()).filter(Boolean);
    const platoPrincipal = (id, texto) => { const p = platos(texto); return (id === 'picnic' ? p[0] : (p[1] || p[0])) || ''; };
    // Foto del menú (almuerzo_menu.imagen) o, si no hay, la imagen referencial del tipo
    const imagenMenu = (dia, id) => (dia.img && dia.img[id]) || ('img/ref_' + id + '.svg');
    const platosHtml = texto => '<ul class="alm-platos">' + platos(texto).map(p => '<li>' + esc(p) + '</li>').join('') + '</ul>';

    async function api(params, metodo) {
        const opciones = { credentials: 'same-origin', headers: { Accept: 'application/json' } };
        let url = API;
        if (metodo === 'POST') {
            opciones.method = 'POST';
            opciones.body = new URLSearchParams(Object.assign({ csrf: CFG.csrf }, params));
        } else {
            url += '?' + new URLSearchParams(params);
        }
        const r = await fetch(url, opciones);
        let datos = {};
        try { datos = await r.json(); } catch (e) { datos = { ok: false, mensaje: 'Respuesta no válida del servidor.' }; }
        if (!r.ok || !datos.ok) { throw new Error(datos.mensaje || 'No fue posible completar la acción.'); }
        return datos;
    }

    let toastTimer;
    function toast(texto, tipo) {
        const t = $('#almToast');
        t.textContent = texto;
        t.className = 'alm-toast' + (tipo ? ' alm-toast--' + tipo : '');
        t.hidden = false;
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => { t.hidden = true; }, 3000);
    }
    function aviso(texto) {
        const a = $('#almAviso');
        a.hidden = !texto;
        a.textContent = texto || '';
    }

    // ------------------------------------------------------------ estado de hoy
    let estado = null;
    let relojBase = null; // diferencia entre la hora del servidor (Chile) y la del navegador

    function horaServidor() {
        return relojBase === null ? new Date() : new Date(Date.now() + relojBase);
    }
    const textoRestante = min => (Math.floor(min / 60) ? Math.floor(min / 60) + ' h ' : '') + (min % 60) + ' min';
    const aMin = hm => { const [h, m] = hm.split(':').map(Number); return h * 60 + m; };

    function pintarEstado() {
        if (!estado) { return; }
        const h = estado.horario;
        const ahora = horaServidor();
        const min = ahora.getHours() * 60 + ahora.getMinutes();
        $('#almReloj').textContent = dos(ahora.getHours()) + ':' + dos(ahora.getMinutes());
        // Si cambia de tramo (abre o cierra) se vuelve a consultar al servidor
        const tramo = h.simulado ? 'abierto' : (!habil(ahora) ? 'cerrado' : (min < aMin(h.inicio) ? 'antes' : (min < aMin(h.fin) ? 'abierto' : 'cerrado')));
        if (clave(ahora) !== h.hoy || tramo !== h.estado) { cargarEstado(); return; } // cambió el día o el tramo

        const e = $('#almEstado');
        e.classList.toggle('is-abierto', h.estado === 'abierto');
        e.classList.toggle('is-cerrado', h.estado !== 'abierto');
        if (h.estado === 'abierto') {
            $('#almEstadoTitulo').textContent = 'Pedidos abiertos';
            $('#almEstadoDetalle').textContent = h.simulado
                ? 'Horario simulado (solo para pruebas).'
                : 'Puedes elegir o cambiar tu almuerzo hasta las ' + h.fin + ' (quedan ' + textoRestante(Math.max(0, aMin(h.fin) - min)) + ').';
        } else if (h.estado === 'antes') {
            $('#almEstadoTitulo').textContent = 'Aún no abre';
            $('#almEstadoDetalle').textContent = 'Los pedidos de hoy se abren a las ' + h.inicio + ' (en ' + textoRestante(Math.max(0, aMin(h.inicio) - min)) + ').';
        } else if (estado.semana && estado.semana.abierta) {
            e.classList.add('is-abierto'); e.classList.remove('is-cerrado');
            $('#almEstadoTitulo').textContent = 'Planificación semanal abierta';
            $('#almEstadoDetalle').textContent = 'Deja elegidos tus almuerzos ' + rangoSemana(estado.semana) + '.';
        } else {
            $('#almEstadoTitulo').textContent = 'Pedidos cerrados';
            $('#almEstadoDetalle').textContent = 'Vuelve a abrir el ' + fechaLarga(desdeClave(h.siguiente_habil)).toLowerCase() + ' a las ' + h.inicio + '.';
        }
    }

    // ------------------------------------------------------------ planificación semanal
    const OPCION_NO = { nombre: 'No almuerzo', icono: 'bi-x-circle', clase: 'no' };
    function rangoSemana(s) {
        const a = desdeClave(s.desde), b = desdeClave(s.hasta);
        return 'del lunes ' + a.getDate() + (a.getMonth() !== b.getMonth() ? ' de ' + MESES[a.getMonth()] : '')
            + ' al viernes ' + b.getDate() + ' de ' + MESES[b.getMonth()];
    }

    function pintarSemana() {
        const s = estado.semana;
        const sec = $('#almSemana');
        if (!s || !s.abierta) { sec.hidden = true; return; }
        sec.hidden = false;
        const conMenu = s.dias.filter(d => d.menu);
        const listos = conMenu.filter(d => d.pedido).length;
        $('#almSemanaSub').textContent = 'Elige tu almuerzo ' + rangoSemana(s) + '. Puedes cambiarlo hasta el domingo'
            + ' y, durante la semana, cada día entre las ' + estado.horario.inicio + ' y las ' + estado.horario.fin + '.'
            + (s.simulada ? ' (Simulado: solo para pruebas.)' : '');
        const prog = $('#almSemanaProgreso');
        prog.textContent = conMenu.length ? listos + ' de ' + conMenu.length + ' días elegidos' : 'Sin menús publicados';
        prog.className = 'alm-pill' + (conMenu.length && listos === conMenu.length ? ' alm-pill--hipo' : '');

        const ops = Object.entries(OPCIONES).concat([['no', OPCION_NO]]);
        $('#almSemanaDias').innerHTML = s.dias.map(dia => {
            const d = desdeClave(dia.fecha);
            const fecha = `<span class="alm-sdia__dow">${esc(titulo(DIAS[d.getDay()]))}</span><strong>${d.getDate()}</strong><small>${esc(MESES[d.getMonth()].slice(0, 3))}</small>`;
            if (!dia.menu) {
                return `<div class="alm-sdia is-sin"><div class="alm-sdia__fecha">${fecha}</div><p class="alm-sdia__sin">Todavía no se publica el menú de este día.</p></div>`;
            }
            return `<div class="alm-sdia${dia.pedido ? ' is-listo' : ''}">
                <button type="button" class="alm-sdia__fecha" data-dia="${dia.fecha}" title="Ver el detalle del día">${fecha}</button>
                <div class="alm-sdia__ops" role="group" aria-label="${esc(fechaLarga(d))}">
                    ${ops.map(([id, o]) => {
                        const on = dia.pedido === id;
                        const plato = id === 'no' ? 'Ese día no almuerzo' : platoPrincipal(id, dia.menu[id]);
                        return `<button type="button" class="alm-chip alm-chip--${o.clase}${on ? ' is-on' : ''}" data-opcion="${id}" data-fecha="${dia.fecha}" aria-pressed="${on}" ${on ? 'disabled' : ''}>
                            <span class="alm-chip__nombre"><i class="bi ${on ? 'bi-check-circle-fill' : o.icono}"></i> ${esc(o.nombre)}</span>
                            <span class="alm-chip__plato">${esc(plato)}</span>
                        </button>`;
                    }).join('')}
                </div>
            </div>`;
        }).join('');
    }

    function pintarHoy() {
        const h = estado.horario;
        const hoy = desdeClave(h.hoy);
        const abierto = h.estado === 'abierto';
        const menu = estado.menu_hoy;
        const elegido = estado.mi_pedido;
        $('#almHoyTitulo').textContent = 'Almuerzo de hoy · ' + fechaLarga(hoy);

        const pill = $('#almMiPedido');
        pill.className = 'alm-pill' + (elegido && OPCIONES[elegido] ? ' alm-pill--' + OPCIONES[elegido].clase : '');
        pill.textContent = elegido === 'no' ? 'No almorzarás hoy' : (elegido ? 'Tu pedido: ' + OPCIONES[elegido].nombre : 'Sin elegir');

        const btnNo = $('#btnNoAlmuerzo');
        if (!menu) {
            $('#almOpciones').innerHTML = '<div class="alm-vacio"><i class="bi bi-calendar-x"></i><p>'
                + (habil(hoy) ? 'Todavía no se publica el menú de hoy.' : 'Hoy no hay servicio de almuerzo.') + '</p></div>';
            $('#almHoySub').textContent = 'Revisa el menú del mes.';
            btnNo.hidden = true;
            $('#almNota').textContent = '';
            return;
        }
        btnNo.hidden = false;
        btnNo.disabled = !abierto || elegido === 'no';
        $('#almHoySub').textContent = abierto
            ? 'Elige una opción. Puedes cambiarla hasta las ' + h.fin + '.'
            : 'El horario de pedidos está cerrado: solo puedes ver el menú.';
        $('#almOpciones').innerHTML = Object.entries(OPCIONES).map(([id, o]) => `
            <article class="alm-opcion alm-opcion--${o.clase}${elegido === id ? ' is-elegida' : ''}">
                <div class="alm-opcion__top">
                    <span class="alm-opcion__icono"><i class="bi ${o.icono}"></i></span>
                    <h3>${esc(o.nombre)}</h3>
                    ${elegido === id ? '<i class="bi bi-check-circle-fill alm-opcion__check" aria-label="Elegido"></i>' : ''}
                </div>
                ${platosHtml(menu[id])}
                <button type="button" class="btn ${elegido === id ? 'btn-outline' : 'btn-primary'} btn-sm alm-opcion__btn" data-opcion="${id}" ${abierto && elegido !== id ? '' : 'disabled'}>
                    ${elegido === id ? '<i class="bi bi-check2"></i> Elegido' : 'Elegir'}
                </button>
            </article>`).join('');
        $('#almNota').textContent = !elegido && abierto ? 'Si no eliges antes de las ' + h.fin + ' quedas sin almuerzo.' : '';
    }

    function pintarResumen() {
        const hoy = desdeClave(estado.horario.hoy);
        const conteo = { menu: 0, hipo: 0, picnic: 0, no: 0 };
        Object.values(estado.mis_pedidos_mes || {}).forEach(v => { if (v in conteo) { conteo[v]++; } });
        $('#almMesNombre').textContent = titulo(MESES[hoy.getMonth()]) + ' ' + hoy.getFullYear();
        $('#almConteo').innerHTML = Object.entries(OPCIONES).map(([id, o]) =>
            `<li><span><i class="alm-ico alm-ico--${o.clase}"></i>${esc(o.nombre)}</span><strong>${conteo[id]}</strong></li>`).join('')
            + `<li class="is-muted"><span><i class="alm-ico"></i>Sin almuerzo</span><strong>${conteo.no}</strong></li>`;
        $('#almProximos').innerHTML = (estado.proximos || []).map(m => {
            const d = desdeClave(m.fecha);
            return `<li><button type="button" data-dia="${m.fecha}"><span>${esc(titulo(DIAS[d.getDay()]).slice(0, 3))} ${d.getDate()}</span><em>${esc(platoPrincipal('menu', m.menu))}</em></button></li>`;
        }).join('') || '<li class="is-muted alm-nota">Aún no hay menús publicados para los próximos días.</li>';

        if ($('#almCocina')) {
            const c = estado.cocina;
            $('#almCocina').innerHTML = !c ? '<li class="is-muted">Hoy no hay servicio de almuerzo.</li>'
                : Object.entries(OPCIONES).map(([id, o]) =>
                    `<li><span><i class="alm-ico alm-ico--${o.clase}"></i>${esc(o.nombre)}</span><strong>${c[id]}</strong></li>`).join('')
                + `<li class="is-muted"><span><i class="alm-ico"></i>No almuerzan</span><strong>${c.no}</strong></li>`
                + `<li class="is-muted"><span><i class="alm-ico"></i>Sin respuesta</span><strong>${c.sin}</strong></li>`
                + `<li class="is-total"><span>Total a preparar</span><strong>${c.menu + c.hipo + c.picnic}</strong></li>`;
        }
    }

    async function cargarEstado() {
        try {
            estado = await api({ accion: 'estado' });
            const [f, hm] = estado.horario.ahora.split(' ');
            relojBase = desdeClave(f).getTime() + aMin(hm.slice(0, 5)) * 60000 + (+hm.slice(6, 8) * 1000) - Date.now();
            aviso('');
            pintarEstado(); pintarHoy(); pintarSemana(); pintarResumen();
        } catch (e) {
            aviso(e.message);
            $('#almEstadoTitulo').textContent = 'Sin conexión con el servidor';
            $('#almEstadoDetalle').textContent = '';
        }
    }

    // ------------------------------------------------------------ menú del mes (modal)
    let mesVista = new Date(); mesVista.setDate(1);
    let datosMes = null;

    async function cargarMes() {
        const k = claveMes(mesVista);
        $('#almMesTitulo').textContent = titulo(MESES[mesVista.getMonth()]) + ' ' + mesVista.getFullYear();
        $('#btnPdfMes').href = 'pdf_menu_mes.php?mes=' + k;
        $('#almCalendario').innerHTML = '<div class="alm-vacio"><p>Cargando…</p></div>';
        try {
            datosMes = await api({ accion: 'mes', mes: k });
            pintarCalendario();
        } catch (e) {
            $('#almCalendario').innerHTML = '<div class="alm-vacio"><p>' + esc(e.message) + '</p></div>';
        }
    }

    function pintarCalendario() {
        const año = mesVista.getFullYear(), mes = mesVista.getMonth();
        const menus = datosMes.menu.dias || {};
        const p = datosMes.pedidos || {};
        const hoy = estado ? estado.horario.hoy : clave(new Date());
        const publicado = Object.keys(menus).length > 0;
        $('#almMesInfo').textContent = publicado && datosMes.menu.publicado_por ? 'Publicado por ' + datosMes.menu.publicado_por : '';
        $('#btnPdfMes').classList.toggle('is-off', !publicado);
        if (!publicado) {
            $('#almCalendario').innerHTML = '<div class="alm-vacio"><i class="bi bi-calendar-x"></i><p>Todavía no se publica el menú de ' + esc(MESES[mes]) + '.</p></div>';
            return;
        }
        let html = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie'].map(d => `<div class="alm-cal__dow">${d}</div>`).join('');
        const primero = new Date(año, mes, 1);
        let desfase = (primero.getDay() + 6) % 7;
        if (desfase > 4) { desfase = 0; }
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

    async function abrirDia(k) {
        const d = desdeClave(k);
        // El día puede ser de otro mes que el cargado en el modal
        if (!datosMes || claveMes(d) !== claveMes(mesVista)) {
            mesVista = new Date(d.getFullYear(), d.getMonth(), 1);
            await cargarMes();
        }
        const m = (datosMes.menu.dias || {})[k];
        if (!m) { return; }
        const elegido = (datosMes.pedidos || {})[k];
        $('#dlgDiaTitulo').textContent = fechaLarga(d);
        $('#dlgDiaCuerpo').innerHTML = Object.entries(OPCIONES).map(([id, o]) => `
            <div class="alm-detalle alm-detalle--${o.clase}${elegido === id ? ' is-elegida' : ''}">
                <figure class="alm-detalle__foto">
                    <img src="${esc(imagenMenu(m, id))}" alt="${esc(o.nombre + ': ' + platoPrincipal(id, m[id]))}" loading="lazy" onerror="this.onerror=null;this.src='img/ref_${id}.svg'">
                    ${m.img && m.img[id] ? '' : '<figcaption>Imagen referencial</figcaption>'}
                </figure>
                <div class="alm-detalle__texto">
                    <h3><i class="bi ${o.icono}"></i> ${esc(o.nombre)} ${elegido === id ? '<span class="alm-pill alm-pill--' + o.clase + '">Tu pedido</span>' : ''}</h3>
                    ${platosHtml(m[id])}
                </div>
            </div>`).join('')
            + (estado && estado.semana && estado.semana.abierta && estado.semana.fechas.includes(k)
                ? '<p class="alm-nota mt">Puedes dejarlo elegido ahora en «Planifica tu semana».</p>'
                : estado && k > estado.horario.hoy ? '<p class="alm-nota mt">Podrás elegir este almuerzo ese día entre las ' + esc(CFG.inicio) + ' y las ' + esc(CFG.fin) + '.</p>' : '')
            + (elegido === 'no' ? '<p class="alm-nota mt">Ese día indicaste que no almorzabas.</p>' : '');
        $('#dlgDia').showModal();
    }

    // ------------------------------------------------------------ eventos generales
    document.addEventListener('click', async e => {
        const cerrar = e.target.closest('[data-cerrar]');
        if (cerrar) { cerrar.closest('dialog').close(); return; }
        if (e.target.tagName === 'DIALOG') { e.target.close(); return; } // clic en el fondo

        const elegir = e.target.closest('[data-opcion]');
        if (elegir && !elegir.disabled) {
            const id = elegir.dataset.opcion;
            const fecha = elegir.dataset.fecha || '';
            elegir.disabled = true;
            try {
                await api(Object.assign({ accion: 'pedir', opcion: id }, fecha ? { fecha } : {}), 'POST');
                const cuando = fecha ? 'el ' + fechaLarga(desdeClave(fecha)).toLowerCase() : 'hoy';
                toast(id === 'no' ? 'Anotado: ' + cuando + ' no almorzarás.'
                    : 'Listo: ' + OPCIONES[id].nombre + ' para ' + cuando + '.', 'ok');
                await cargarEstado();
                if ($('#dlgMes').open) { cargarMes(); }
            } catch (err) {
                toast(err.message, 'error');
                cargarEstado();
            }
            return;
        }
        const dia = e.target.closest('[data-dia]');
        if (dia) { abrirDia(dia.dataset.dia); }
    });

    $('#btnVerMes').addEventListener('click', () => {
        const hoy = estado ? desdeClave(estado.horario.hoy) : new Date();
        mesVista = new Date(hoy.getFullYear(), hoy.getMonth(), 1); // siempre abre en el mes actual
        if (!$('#dlgMes').open) { $('#dlgMes').showModal(); }
        cargarMes();
    });
    $('#btnMesAnterior').addEventListener('click', () => { mesVista.setMonth(mesVista.getMonth() - 1); cargarMes(); });
    $('#btnMesSiguiente').addEventListener('click', () => { mesVista.setMonth(mesVista.getMonth() + 1); cargarMes(); });
    $('#btnPdfMes').addEventListener('click', e => {
        if (e.currentTarget.classList.contains('is-off')) { e.preventDefault(); toast('Este mes todavía no tiene menú publicado.', 'error'); }
    });

    const chkSimular = $('#chkSimular');
    if (chkSimular) {
        chkSimular.addEventListener('change', async () => {
            try { await api({ accion: 'simular', valor: chkSimular.checked ? '1' : '0' }, 'POST'); } catch (err) { toast(err.message, 'error'); }
            cargarEstado();
        });
    }

    // ------------------------------------------------------------ cargar menú del mes (encargado)
    const dlgCargar = $('#dlgCargar');
    if (dlgCargar) {
        let filasCargadas = null;
        const mesSiguiente = () => { const d = new Date(); d.setMonth(d.getMonth() + 1, 1); return claveMes(d); };
        const mesElegido = () => ($('#cargarMes').value || mesSiguiente()).split('-').map(Number);

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
            picnic: [  // el picnic es siempre un sándwich
                ['Sándwich de ave pimiento'],
                ['Sándwich de pollo y verduras'],
                ['Sándwich de jamón queso'],
                ['Sándwich de pollo César'],
                ['Sándwich de queso y tomate'],
            ],        };
        const semilla = (k, n) => { let h = 7; for (const c of k) { h = (h * 31 + c.charCodeAt(0)) % 100003; } return h % n; };
        function menuEjemplo(año, mes) {
            const dias = {};
            for (let d = new Date(año, mes, 1); d.getMonth() === mes; d.setDate(d.getDate() + 1)) {
                if (!habil(d)) { continue; }
                const k = clave(d);
                dias[k] = {};
                Object.keys(PLATOS).forEach(op => { dias[k][op] = PLATOS[op][semilla(k + op, PLATOS[op].length)].join(' + '); });
            }
            return dias;
        }

        async function revisarMesElegido() {
            const [y, m] = mesElegido();
            $('#cargarMesEstado').textContent = '';
            try {
                const r = await api({ accion: 'mes', mes: y + '-' + dos(m) });
                const n = Object.keys(r.menu.dias || {}).length;
                $('#cargarMesEstado').textContent = n ? 'Este mes ya tiene ' + n + ' días publicados: al publicar se reemplazan.' : 'Este mes aún no tiene menú.';
            } catch (e) { /* solo informativo */ }
        }

        $('#btnCargarMes').addEventListener('click', () => {
            $('#cargarMes').value = $('#cargarMes').value || mesSiguiente();
            revisarMesElegido();
            dlgCargar.showModal();
        });

        // Plantilla: una fila por día hábil, separada por ; (Excel en español)
        $('#btnPlantilla').addEventListener('click', () => {
            const [y, m] = mesElegido();
            const ej = menuEjemplo(y, m - 1);
            const lineas = ['fecha;menu_del_dia;hipocalorico;picnic'];
            Object.keys(ej).forEach(k => lineas.push([k.split('-').reverse().join('-'), ej[k].menu, ej[k].hipo, ej[k].picnic]
                .map(v => '"' + v.replace(/"/g, '""') + '"').join(';')));
            const blob = new Blob(['﻿' + lineas.join('\r\n')], { type: 'text/csv;charset=utf-8' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'almuerzos_' + y + '-' + dos(m) + '.csv';
            document.body.appendChild(a); a.click(); a.remove();
            setTimeout(() => URL.revokeObjectURL(a.href), 1000);
        });

        function parsearCsv(texto) {
            texto = texto.replace(/^﻿/, '');
            const primera = texto.split('\n')[0];
            const sep = (primera.match(/;/g) || []).length >= (primera.match(/,/g) || []).length ? ';' : ',';
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
                const n = i + 1;
                if (!d || isNaN(d)) { errores.push('Fila ' + n + ': fecha no válida'); return; }
                if (d.getFullYear() !== y || d.getMonth() !== m - 1) { errores.push('Fila ' + n + ': la fecha no es del mes elegido'); return; }
                if (!habil(d)) { errores.push('Fila ' + n + ': ' + d.getDate() + '/' + (d.getMonth() + 1) + ' es fin de semana'); return; }
                const [menu, hipo, picnic] = [f[1], f[2], f[3]].map(v => String(v || '').trim());
                if (!menu || !hipo || !picnic) { errores.push('Fila ' + n + ': faltan menús'); return; }
                if ([menu, hipo, picnic].some(v => v.length > 255)) { errores.push('Fila ' + n + ': un menú supera 255 caracteres'); return; }
                validas[clave(d)] = { menu, hipo, picnic };
            });
            filasCargadas = errores.length ? null : validas;
            const claves = Object.keys(validas).sort();
            $('#almPreviewTitulo').textContent = 'Vista previa · ' + titulo(MESES[m - 1]) + ' ' + y + ' (' + claves.length + ' días)' + (origen ? ' · ' + origen : '');
            $('#almPreviewAviso').innerHTML = errores.length
                ? '<span class="alm-error">Corrige ' + errores.length + ' fila(s) antes de publicar: ' + esc(errores.slice(0, 3).join('; ')) + (errores.length > 3 ? '…' : '') + '</span>'
                : '<span class="alm-ok"><i class="bi bi-check-circle"></i> Todo correcto</span>';
            $('#almPreviewFilas').innerHTML = claves.map(k => {
                const v = validas[k], d = desdeClave(k);
                return `<tr><td class="nowrap">${esc(titulo(DIAS[d.getDay()]).slice(0, 3))} ${d.getDate()}</td><td>${esc(v.menu)}</td><td>${esc(v.hipo)}</td><td>${esc(v.picnic)}</td></tr>`;
            }).join('') || '<tr><td colspan="4" class="alm-error">No se encontraron días válidos.</td></tr>';
            $('#almPreview').hidden = false;
            $('#btnPublicar').disabled = !filasCargadas || claves.length === 0;
        }

        function leerArchivo(archivo) {
            if (!archivo) { return; }
            if (!/\.csv$/i.test(archivo.name)) { toast('Por ahora solo se aceptan archivos .csv', 'error'); return; }
            const r = new FileReader();
            r.onload = () => mostrarPreview(parsearCsv(String(r.result)), archivo.name);
            r.readAsText(archivo, 'utf-8');
        }
        $('#cargarArchivo').addEventListener('change', e => { leerArchivo(e.target.files[0]); e.target.value = ''; });
        const drop = $('#almDrop');
        ['dragenter', 'dragover'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.add('is-sobre'); }));
        ['dragleave', 'drop'].forEach(ev => drop.addEventListener(ev, e => { e.preventDefault(); drop.classList.remove('is-sobre'); }));
        drop.addEventListener('drop', e => leerArchivo(e.dataTransfer.files[0]));
        $('#cargarMes').addEventListener('change', () => { $('#almPreview').hidden = true; $('#btnPublicar').disabled = true; filasCargadas = null; revisarMesElegido(); });

        $('#btnEjemplo').addEventListener('click', () => {
            const [y, m] = mesElegido();
            const ej = menuEjemplo(y, m - 1);
            mostrarPreview([['fecha']].concat(Object.keys(ej).map(k => [k, ej[k].menu, ej[k].hipo, ej[k].picnic])), 'ejemplo');
        });

        $('#btnPublicar').addEventListener('click', async () => {
            if (!filasCargadas) { return; }
            const [y, m] = mesElegido();
            const btn = $('#btnPublicar');
            btn.disabled = true;
            try {
                const r = await api({ accion: 'publicar', mes: y + '-' + dos(m), dias: JSON.stringify(filasCargadas) }, 'POST');
                dlgCargar.close();
                toast(r.mensaje, 'ok');
                await cargarEstado();
                mesVista = new Date(y, m - 1, 1);
                $('#dlgMes').showModal();
                cargarMes();
            } catch (err) {
                toast(err.message, 'error');
                btn.disabled = false;
            }
        });
    }

    cargarEstado();
    setInterval(pintarEstado, 20000);
}());
