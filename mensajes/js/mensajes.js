/* Mensajes: bandeja, conversación (panel lateral) y mensaje nuevo. */
(function ($) {
    'use strict';
    const CFG = window.MSG_CONFIG || {};
    const URL = 'ajax/mensajes.php';
    const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const aviso = (icon, title, text) => (window.Swal ? Swal.fire({ icon, title, text, confirmButtonColor: '#003B6F', customClass: { popup: 'rounded-3', confirmButton: 'btn btn-primary' }, buttonsStyling: false }) : alert(title + (text ? '\n' + text : '')));
    const errorDe = xhr => (xhr && xhr.responseJSON && xhr.responseJSON.mensaje) || 'No fue posible completar la acción.';
    const post = datos => $.ajax({ url: URL, method: 'POST', data: Object.assign({ csrf: CFG.csrf }, datos), dataType: 'json', traditional: true });

    function fechaCorta(f) {
        const d = new Date(String(f).replace(' ', 'T'));
        if (isNaN(d)) { return f; }
        const hoy = new Date(), dos = n => String(n).padStart(2, '0'), hora = dos(d.getHours()) + ':' + dos(d.getMinutes());
        if (d.toDateString() === hoy.toDateString()) { return 'Hoy ' + hora; }
        const ayer = new Date(hoy); ayer.setDate(hoy.getDate() - 1);
        if (d.toDateString() === ayer.toDateString()) { return 'Ayer ' + hora; }
        return dos(d.getDate()) + '-' + dos(d.getMonth() + 1) + '-' + d.getFullYear() + ' ' + hora;
    }

    // ---------- Tabla
    const $tabla = $('#tablaMensajes');
    const tabla = $tabla.DataTable({
        order: [[2, 'desc']],
        pageLength: 20,
        lengthChange: false,
        columnDefs: [{ targets: 3, orderable: false, searchable: false }],
        dom: "rt<'row align-items-center'<'col-md-6'i><'col-md-6 text-md-end'p>>",
        language: {
            emptyTable: CFG.carpeta === 'archivados' ? 'No tienes conversaciones archivadas' : 'No hay mensajes en esta bandeja',
            zeroRecords: 'Ninguna conversación coincide con la búsqueda',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ conversaciones',
            infoEmpty: 'Sin conversaciones', infoFiltered: '(de _MAX_)',
            paginate: { previous: 'Anterior', next: 'Siguiente' }
        }
    });
    $.fn.dataTable.ext.search.push(function (settings, data, i) {
        if (settings.nTable !== $tabla[0]) { return true; }
        const f = $('#filtroLectura').val(), fila = tabla.row(i).node();
        if (f === 'no_leidas') { return fila.dataset.noLeida === '1'; }
        if (f === 'urgentes') { return fila.dataset.urgente === '1'; }
        return true;
    });
    let espera;
    $('#filtroBusqueda').on('input', function () { clearTimeout(espera); const t = this.value; espera = setTimeout(() => tabla.search(t).draw(), 200); });
    $('#filtroLectura').on('change', () => tabla.draw());

    // ---------- Conversación
    const panelEl = document.getElementById('panelConversacion');
    const panel = panelEl ? new bootstrap.Offcanvas(panelEl) : null;
    let conversacionActual = 0;
    let huboCambios = false;
    let hiloFirma = '';
    let listaFirma = null;

    function pintarHilo(c) {
        hiloFirma = c.mensajes.map(m => m.id).join(',');
        $('#panelTitulo').text(c.otro);
        $('#panelSub').text(c.mensajes.length + (c.mensajes.length === 1 ? ' mensaje' : ' mensajes') + (c.archivada ? ' · archivada' : ''));
        const html = c.mensajes.map(m => `
            <div class="msg-burbuja ${m.mio ? 'mia' : 'otra'}">
                ${m.urgente ? '<span class="msg-urgente">Urgente</span>' : ''}
                <p>${esc(m.texto)}</p>
                <small>${esc(m.mio ? 'Tú' : m.autor)} · ${esc(fechaCorta(m.fecha))}</small>
            </div>`).join('') || '<div class="text-muted p-3">Sin mensajes.</div>';
        const $hilo = $('#panelHilo').html(html);
        $hilo.scrollTop($hilo[0].scrollHeight);
    }

    function abrir(id) {
        conversacionActual = id;
        $('#panelTitulo').text('Cargando…'); $('#panelSub').text(''); $('#panelHilo').html('<div class="text-muted p-3">Cargando conversación…</div>');
        panel && panel.show();
        $.getJSON(URL, { accion: 'conversacion', id })
            .done(r => {
                pintarHilo(r.data);
                const $fila = $tabla.find('tr[data-id="' + id + '"]');
                if ($fila.hasClass('msg-no-leida')) { huboCambios = true; $fila.removeClass('msg-no-leida').attr('data-no-leida', '0').find('.msg-badge').remove(); }
                setTimeout(() => $('#txtRespuesta').trigger('focus'), 300);
            })
            .fail(xhr => $('#panelHilo').html('<div class="alert alert-warning m-3">' + esc(errorDe(xhr)) + '</div>'));
    }
    $tabla.on('click', '.btnAbrir', function () { abrir($(this).data('id')); });
    $tabla.on('dblclick', 'tr.msg-fila', function () { abrir($(this).data('id')); });
    panelEl && panelEl.addEventListener('hidden.bs.offcanvas', () => { conversacionActual = 0; hiloFirma = ''; if (huboCambios) { location.reload(); } });
    // Actualización periódica sin recargar: solo sustituye el hilo si llegaron mensajes nuevos.
    async function actualizarMensajes() {
        if (document.hidden) return;
        if (conversacionActual && panelEl?.classList.contains('show')) {
            try {
                const r = await $.getJSON(URL, {accion:'conversacion',id:conversacionActual});
                const firma = r.data.mensajes.map(m => m.id).join(',');
                if (firma !== hiloFirma) { pintarHilo(r.data); huboCambios = true; window.Notificaciones?.contar?.(); }
            } catch (_) { /* Reintentar en próximo ciclo */ }
        }
        // Mantener la bandeja visible sincronizada, preservando filtros y paginación.
        try {
            const r = await $.getJSON(URL, {accion:'listar',carpeta:CFG.carpeta});
            if (!r.ok) return;
            const actual = JSON.stringify(r.data.map(m => [m.id,m.total,m.no_leidos,m.fecha,m.ultimo]));
            if (actual !== listaFirma) {
                listaFirma = actual;
                const pagina = tabla.page();
                tabla.clear();
                r.data.forEach(m => {
                    const id = Number(m.id), nombre = esc(m.otro), mensaje = esc(m.ultimo), fecha = esc(fechaCorta(m.fecha));
                    const urgente = m.urgente ? ' · Urgente' : '';
                    const badge = m.no_leidos ? '<span class="msg-badge" title="Sin leer">'+Number(m.no_leidos)+'</span>' : '';
                    const accion = CFG.carpeta === 'archivados' ? 'desarchivar' : 'archivar';
                    const icono = accion === 'archivar' ? 'bi-archive' : 'bi-inbox';
                    const node = tabla.row.add([
                        '<div class="msg-persona"><span class="msg-avatar">'+nombre.charAt(0)+'</span><div><strong>'+nombre+'</strong><span class="msg-sub">'+Number(m.total)+' mensajes'+urgente+'</span></div>'+badge+'</div>',
                        (m.ultimo_mio ? '<span class="msg-sub d-inline">Tú: </span>' : '')+mensaje,
                        fecha,
                        '<button type="button" class="module-btn module-btn--primary btnAbrir" data-id="'+id+'"><i class="bi bi-chat-dots"></i>Abrir</button> '+
                        '<button type="button" class="module-btn module-btn--secondary btnAccion" data-accion="'+accion+'" data-id="'+id+'"><i class="bi '+icono+'"></i></button> '+
                        '<button type="button" class="module-btn module-btn--secondary btnAccion" data-accion="eliminar" data-id="'+id+'"><i class="bi bi-trash3"></i></button>'
                    ]).node();
                    node.className = 'msg-fila'+(m.no_leidos ? ' msg-no-leida' : '');
                    node.dataset.id = id; node.dataset.noLeida = m.no_leidos ? '1' : '0'; node.dataset.urgente = m.urgente ? '1' : '0';
                    node.cells[2].setAttribute('data-order',m.fecha);
                });
                tabla.draw(false);
                if (pagina < tabla.page.info().pages) tabla.page(pagina).draw('page');
            }
            const stats = document.querySelectorAll('.msg-stat-link .inv-stat-card strong');
            ['no_leidos','recibidos','enviados','archivados'].forEach((k,i) => {if(stats[i]) stats[i].textContent = Number(r.resumen[k] || 0).toLocaleString('es-CL');});
            window.Notificaciones?.contar?.();
        } catch (_) { /* Reintentar en siguiente ciclo */ }
    }
    window.setInterval(actualizarMensajes, 10000);

    $('#formResponder').on('submit', function (e) {
        e.preventDefault();
        const texto = $('#txtRespuesta').val().trim();
        if (!texto || !conversacionActual) { return; }
        const $btn = $(this).find('button').prop('disabled', true);
        post({ accion: 'responder', id: conversacionActual, mensaje: texto })
            .done(() => { $('#txtRespuesta').val(''); huboCambios = true; abrir(conversacionActual); })
            .fail(xhr => aviso('error', 'No se envió', errorDe(xhr)))
            .always(() => $btn.prop('disabled', false));
    });
    $('#txtRespuesta').on('keydown', function (e) { if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { $('#formResponder').trigger('submit'); } });

    // ---------- Archivar / desarchivar / eliminar
    $tabla.on('click', '.btnAccion', function () {
        const $b = $(this), accion = $b.data('accion'), id = $b.data('id');
        const ejecutar = () => post({ accion, id })
            .done(r => { tabla.row($b.closest('tr')).remove().draw(false); if (window.Swal) { Swal.fire({ icon: 'success', title: r.mensaje, timer: 1300, showConfirmButton: false }); } setTimeout(() => location.reload(), 1300); })
            .fail(xhr => aviso('error', 'No fue posible', errorDe(xhr)));
        if (accion !== 'eliminar' || !window.Swal) { ejecutar(); return; }
        Swal.fire({ icon: 'warning', title: '¿Eliminar la conversación?', text: 'Desaparece de tu bandeja. La otra persona la sigue viendo.', showCancelButton: true,
            confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar', confirmButtonColor: '#003B6F', customClass: { popup: 'rounded-3', confirmButton: 'btn btn-danger', cancelButton: 'btn btn-outline-secondary' }, buttonsStyling: false })
            .then(r => { if (r.isConfirmed) { ejecutar(); } });
    });

    // ---------- Mensaje nuevo
    const modalEl = document.getElementById('modalNuevo');
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
    let destinos = null;
    const elegidos = new Set();
    function pintarDestinos() {
        const filtro = $('#buscarDestino').val().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
        const lista = (destinos || []).filter(d => (d.nombre + ' ' + d.cargo).toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '').includes(filtro));
        $('#listaDestinos').html(lista.slice(0, 200).map(d => `
            <label class="msg-destino"><input type="checkbox" class="form-check-input" value="${d.id}" ${elegidos.has(d.id) ? 'checked' : ''}>
            <span><strong>${esc(d.nombre)}</strong>${d.cargo ? '<small>' + esc(d.cargo) + '</small>' : ''}</span></label>`).join('')
            || '<div class="text-muted small p-2">Sin coincidencias.</div>');
        $('#contadorDestinos').text(elegidos.size ? elegidos.size + (elegidos.size === 1 ? ' destinatario seleccionado.' : ' destinatarios seleccionados.') : 'Ningún destinatario seleccionado.');
    }
    $('#btnNuevoMensaje').on('click', () => {
        modal && modal.show();
        if (destinos) { pintarDestinos(); return; }
        $.getJSON(URL, { accion: 'destinatarios' })
            .done(r => { destinos = r.data; pintarDestinos(); })
            .fail(xhr => $('#listaDestinos').html('<div class="text-danger small p-2">' + esc(errorDe(xhr)) + '</div>'));
    });
    $('#buscarDestino').on('input', pintarDestinos);
    $('#listaDestinos').on('change', 'input', function () {
        const id = +this.value; this.checked ? elegidos.add(id) : elegidos.delete(id); pintarDestinos();
    });
    $('#formNuevo').on('submit', function (e) {
        e.preventDefault();
        if (!elegidos.size) { aviso('info', 'Falta el destinatario', 'Elige al menos un colaborador.'); return; }
        const texto = $('#txtNuevo').val().trim();
        if (!texto) { return; }
        const $btn = $(this).find('button[type=submit]').prop('disabled', true);
        post({ accion: 'nuevo', mensaje: texto, urgente: $('#chkUrgente').is(':checked') ? '1' : '0', 'destinatarios[]': Array.from(elegidos) })
            .done(r => { modal.hide(); aviso('success', r.mensaje); setTimeout(() => { location.href = 'enviados.php'; }, 1200); })
            .fail(xhr => aviso('error', 'No se envió', errorDe(xhr)))
            .always(() => $btn.prop('disabled', false));
    });

    // Abrir directo una conversación (?abrir=ID), útil para enlaces desde otras páginas
    const abrirId = new URLSearchParams(location.search).get('abrir');
    if (abrirId) { abrir(+abrirId); }
}(jQuery));
