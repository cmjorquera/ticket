(function ($) {
    'use strict';

    let tablaSoftware = null;
    let tablaSitios = null;
    let softwareActual = [];
    let sitiosActuales = [];

    function escapeHtml(text) {
        return $('<div>').text(text == null ? '' : String(text)).html();
    }

    function debounce(fn, wait) {
        let timer = null;
        return function () {
            const context = this;
            const args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function () {
                fn.apply(context, args);
            }, wait);
        };
    }

    function numeroSeguro(valor) {
        const numero = Number(valor);
        return Number.isFinite(numero) ? numero : 0;
    }

    function formatoEntero(valor) {
        return new Intl.NumberFormat('es-CL').format(numeroSeguro(valor));
    }

    function formatoMonedaUsd(valor) {
        return 'USD ' + new Intl.NumberFormat('es-CL', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0
        }).format(numeroSeguro(valor));
    }

    function crearTarjetaResumen(claseColor, icono, etiqueta, valor, descripcion) {
        return `<div class="col-12 col-md-6 col-xl-2">
            <div class="inv-stat-card ${claseColor}">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <span class="inv-stat-label">${escapeHtml(etiqueta)}</span>
                        <strong>${escapeHtml(valor)}</strong>
                        <small>${escapeHtml(descripcion)}</small>
                    </div>
                    <div class="inv-stat-icon">
                        <i class="bi ${escapeHtml(icono)}"></i>
                    </div>
                </div>
            </div>
        </div>`;
    }

    function renderResumenSoftware() {
        const $wrap = $('#resumenTabInventario');
        if (!$wrap.length) { return; }

        const totalRegistros = softwareActual.length;
        const totalLicencias = softwareActual.reduce((acc, item) => acc + numeroSeguro(item.cantidad_licencias), 0);
        const totalCosto = softwareActual.reduce((acc, item) => acc + numeroSeguro(item.costo), 0);
        const totalColegios = new Set(softwareActual.map(item => item.id_colegio).filter(Boolean)).size;
        const suscripciones = softwareActual.filter(item => String(item.tipo_licenciamiento || '').toLowerCase() === 'suscripcion').length;
        const gratuitas = softwareActual.filter(item => String(item.tipo_licenciamiento || '').toLowerCase() === 'gratuita').length;

        $wrap.html(`<div class="row g-3">
            ${crearTarjetaResumen('inv-stat-primary', 'bi-box-seam', 'Total programas', formatoEntero(totalRegistros), 'Registros visibles con filtros')}
            ${crearTarjetaResumen('inv-stat-success', 'bi-key', 'Licencias', formatoEntero(totalLicencias), 'Cantidad total visible')}
            ${crearTarjetaResumen('inv-stat-info', 'bi-building', 'Colegios', formatoEntero(totalColegios), 'Colegios con software visible')}
            ${crearTarjetaResumen('inv-stat-warning', 'bi-arrow-repeat', 'Suscripciones', formatoEntero(suscripciones), 'Registros por suscripcion')}
            ${crearTarjetaResumen('inv-stat-teal', 'bi-gift', 'Gratuitos', formatoEntero(gratuitas), 'Software sin costo')}
            ${crearTarjetaResumen('inv-stat-danger', 'bi-cash-stack', 'Gasto total', formatoMonedaUsd(totalCosto), 'Plata total declarada')}
        </div>
        <div class="row g-3 mt-1">
            <div class="col-12">
                <div class="inv-colegio-pill">
                    <div>
                        <strong class="d-block">${escapeHtml(formatoEntero(totalLicencias))} licencias en total</strong>
                        <span class="text-muted small">Resumen dinamico del software visible</span>
                    </div>
                    <span class="badge text-bg-light border">Actualiza con filtros y cambio de pestaña</span>
                </div>
            </div>
        </div>`);
    }

    function renderResumenSitios() {
        const $wrap = $('#resumenTabInventario');
        if (!$wrap.length) { return; }

        const totalSitios = sitiosActuales.length;
        const totalWeb = sitiosActuales.filter(item => String(item.tipo_sitio || '').toLowerCase() === 'web').length;
        const totalApp = sitiosActuales.filter(item => String(item.tipo_sitio || '').toLowerCase() === 'app').length;
        const totalCliente = sitiosActuales.filter(item => String(item.tipo_sitio || '').toLowerCase() === 'cliente').length;
        const totalColegios = new Set(sitiosActuales.map(item => item.id_colegio).filter(Boolean)).size;
        const totalResponsables = new Set(
            sitiosActuales
                .map(item => String(item.responsable || '').trim())
                .filter(item => item !== '' && item.toLowerCase() !== 'sin asignar')
        ).size;
        const totalUrls = sitiosActuales.filter(item => String(item.url_sitio || '').trim() !== '').length;
        const gastoTotal = softwareActual.reduce((acc, item) => acc + numeroSeguro(item.costo), 0);

        $wrap.html(`<div class="row g-3">
            ${crearTarjetaResumen('inv-stat-primary', 'bi-globe2', 'Total sitios', formatoEntero(totalSitios), 'Sitios visibles con filtros')}
            ${crearTarjetaResumen('inv-stat-success', 'bi-window', 'Web', formatoEntero(totalWeb), 'Portales y paginas')}
            ${crearTarjetaResumen('inv-stat-info', 'bi-phone', 'Apps', formatoEntero(totalApp), 'Aplicaciones registradas')}
            ${crearTarjetaResumen('inv-stat-warning', 'bi-pc-display', 'Clientes', formatoEntero(totalCliente), 'Clientes o accesos locales')}
            ${crearTarjetaResumen('inv-stat-teal', 'bi-person-badge', 'Responsables', formatoEntero(totalResponsables), 'Encargados visibles')}
            ${crearTarjetaResumen('inv-stat-danger', 'bi-cash-stack', 'Gasto total', formatoMonedaUsd(gastoTotal), 'Plata total declarada')}
        </div>
        <div class="row g-3 mt-1">
            <div class="col-12">
                <div class="inv-colegio-pill">
                    <div>
                        <strong class="d-block">${escapeHtml(formatoEntero(totalColegios))} colegios y ${escapeHtml(formatoEntero(totalUrls))} con URL</strong>
                        <span class="text-muted small">Presencia digital visible del modulo</span>
                    </div>
                    <span class="badge text-bg-light border">Resumen dinamico del tab actual</span>
                </div>
            </div>
        </div>`);
    }

    function renderResumenActivo() {
        if ($('#tab-sitios').hasClass('active')) {
            renderResumenSitios();
            return;
        }
        renderResumenSoftware();
    }

    function actualizarLinksPdf() {
        const $btnPdfSoftware = $('#btnPdfSoftware');
        if ($btnPdfSoftware.length) {
            const urlSoftware = new URL('descargar_pdf.php', window.location.href);
            urlSoftware.searchParams.set('tipo', 'software_listado');
            urlSoftware.searchParams.set('id_colegio', $('#filtroSoftwareColegio').val() || '');
            urlSoftware.searchParams.set('id_usuario_responsable', $('#filtroSoftwareUsuario').val() || '');
            urlSoftware.searchParams.set('tipo_licenciamiento', $('#filtroSoftwareLicencia').val() || '');
            urlSoftware.searchParams.set('busqueda', $('#filtroSoftwareBusqueda').val() || '');
            $btnPdfSoftware.attr('href', urlSoftware.pathname + urlSoftware.search);
        }

        const $btnPdfSitios = $('#btnPdfSitios');
        if ($btnPdfSitios.length) {
            const urlSitios = new URL('descargar_pdf.php', window.location.href);
            urlSitios.searchParams.set('tipo', 'sitios_listado');
            urlSitios.searchParams.set('id_colegio', $('#filtroSitioColegio').val() || '');
            urlSitios.searchParams.set('id_usuario_responsable', $('#filtroSitioUsuario').val() || '');
            urlSitios.searchParams.set('tipo_sitio', $('#filtroSitioTipo').val() || '');
            urlSitios.searchParams.set('busqueda', $('#filtroSitioBusqueda').val() || '');
            $btnPdfSitios.attr('href', urlSitios.pathname + urlSitios.search);
        }
    }

    function initTablaSoftware() {
        const $tabla = $('#tablaSoftware');
        if (!$tabla.length) { return; }
        tablaSoftware = $tabla.DataTable({
            data: [],
            responsive: true,
            pageLength: 10,
            paging: true,
            info: true,
            lengthChange: false,
            autoWidth: false,
            pagingType: 'simple_numbers',
            dom: "<'row align-items-center mb-3'<'col-md-6'i><'col-md-6 text-md-end'p>>rt<'row align-items-center mt-3'<'col-md-6'i><'col-md-6 text-md-end'p>>",
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' },
            columns: [
                { data: 'id_software' },
                { data: null, render: row => `<div class="fw-semibold">${escapeHtml(row.nombre_software)}</div><small class="text-muted">${escapeHtml(row.proveedor || '')}</small>` },
                { data: 'nom_colegio', render: escapeHtml },
                { data: 'version_software', render: data => escapeHtml(data || '-') },
                { data: 'cantidad_licencias' },
                { data: 'tipo_licenciamiento', render: escapeHtml },
                { data: 'pagado_por', render: escapeHtml },
                { data: 'responsable', render: data => escapeHtml(data || 'Sin asignar') },
                { data: null, orderable: false, searchable: false, render: row => `<div class="inv-action-buttons d-flex flex-wrap gap-2"><a href="ver_software.php?id_software=${parseInt(row.id_software, 10)}" class="btn btn-sm inv-btn-action inv-btn-view"><i class="bi bi-eye"></i></a><a href="editar_software.php?id_software=${parseInt(row.id_software, 10)}" class="btn btn-sm inv-btn-action inv-btn-edit"><i class="bi bi-pencil"></i></a><button type="button" class="btn btn-sm inv-btn-action inv-btn-delete btnEliminarSoftware" data-id="${parseInt(row.id_software, 10)}"><i class="bi bi-trash"></i></button></div>` }
            ]
        });
        loadSoftware();
    }

    function initTablaSitios() {
        const $tabla = $('#tablaSitios');
        if (!$tabla.length) { return; }
        tablaSitios = $tabla.DataTable({
            data: [],
            responsive: true,
            pageLength: 10,
            paging: true,
            info: true,
            lengthChange: false,
            autoWidth: false,
            pagingType: 'simple_numbers',
            dom: "<'row align-items-center mb-3'<'col-md-6'i><'col-md-6 text-md-end'p>>rt<'row align-items-center mt-3'<'col-md-6'i><'col-md-6 text-md-end'p>>",
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json' },
            columns: [
                { data: 'id_sitio' },
                { data: 'nombre_sitio', render: escapeHtml },
                { data: 'nom_colegio', render: escapeHtml },
                { data: 'tipo_sitio', render: escapeHtml },
                { data: 'url_sitio', render: data => `<span class="inv-url-cell">${escapeHtml(data || '-')}</span>` },
                { data: 'estado_sitio', render: escapeHtml },
                { data: 'responsable', render: data => escapeHtml(data || 'Sin asignar') },
                { data: null, orderable: false, searchable: false, render: row => `<div class="inv-action-buttons d-flex flex-wrap gap-2"><a href="ver_sitio_web.php?id_sitio=${parseInt(row.id_sitio, 10)}" class="btn btn-sm inv-btn-action inv-btn-view"><i class="bi bi-eye"></i></a><a href="editar_sitio_web.php?id_sitio=${parseInt(row.id_sitio, 10)}" class="btn btn-sm inv-btn-action inv-btn-edit"><i class="bi bi-pencil"></i></a><button type="button" class="btn btn-sm inv-btn-action inv-btn-delete btnEliminarSitio" data-id="${parseInt(row.id_sitio, 10)}"><i class="bi bi-trash"></i></button></div>` }
            ]
        });
        loadSitios();
    }

    function loadSoftware() {
        if (!tablaSoftware || !window.INVENTARIO_CONFIG) { return; }
        $.getJSON(window.INVENTARIO_CONFIG.endpoints.listarSoftware, {
            id_colegio: $('#filtroSoftwareColegio').val() || '',
            id_usuario_responsable: $('#filtroSoftwareUsuario').val() || '',
            tipo_licenciamiento: $('#filtroSoftwareLicencia').val() || '',
            busqueda: $('#filtroSoftwareBusqueda').val() || ''
        }).done(function (response) {
            softwareActual = response.data || [];
            tablaSoftware.clear().rows.add(softwareActual).draw();
            renderResumenActivo();
            actualizarLinksPdf();
        }).fail(function () {
            Swal.fire('Error', 'No fue posible cargar el software.', 'error');
        });
    }

    function loadSitios() {
        if (!tablaSitios || !window.INVENTARIO_CONFIG) { return; }
        $.getJSON(window.INVENTARIO_CONFIG.endpoints.listarSitios, {
            id_colegio: $('#filtroSitioColegio').val() || '',
            id_usuario_responsable: $('#filtroSitioUsuario').val() || '',
            tipo_sitio: $('#filtroSitioTipo').val() || '',
            busqueda: $('#filtroSitioBusqueda').val() || ''
        }).done(function (response) {
            sitiosActuales = response.data || [];
            tablaSitios.clear().rows.add(sitiosActuales).draw();
            renderResumenActivo();
            actualizarLinksPdf();
        }).fail(function () {
            Swal.fire('Error', 'No fue posible cargar los sitios.', 'error');
        });
    }

    function bindFiltros() {
        $('#filtroSoftwareColegio, #filtroSoftwareUsuario, #filtroSoftwareLicencia').on('change', loadSoftware);
        $('#filtroSoftwareBusqueda').on('keyup', debounce(loadSoftware, 300));
        $('#filtroSitioColegio, #filtroSitioUsuario, #filtroSitioTipo').on('change', loadSitios);
        $('#filtroSitioBusqueda').on('keyup', debounce(loadSitios, 300));
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', renderResumenActivo);
    }

    function bindFormAjax() {
        const $form = $('#formInventario');
        if (!$form.length) { return; }
        $form.on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: $form.serialize(),
                dataType: 'json'
            }).done(function (response) {
                Swal.fire({ icon: 'success', title: 'Guardado correctamente', text: response.mensaje }).then(function () {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                });
            }).fail(function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible guardar.';
                Swal.fire('Error', mensaje, 'error');
            });
        });
    }

    function bindDelete() {
        $(document).on('click', '.btnEliminarSoftware', function () {
            const id = $(this).data('id');
            Swal.fire({ title: 'Desactivar software', text: 'El registro quedara inactivo.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Si, desactivar', cancelButtonText: 'Cancelar' }).then(function (result) {
                if (!result.isConfirmed) { return; }
                $.post(window.INVENTARIO_CONFIG.endpoints.eliminarSoftware, { id_software: id }, null, 'json').done(loadSoftware).fail(function (xhr) {
                    const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible desactivar.';
                    Swal.fire('Error', mensaje, 'error');
                });
            });
        });

        $(document).on('click', '.btnEliminarSitio', function () {
            const id = $(this).data('id');
            Swal.fire({ title: 'Desactivar sitio', text: 'El registro quedara inactivo.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Si, desactivar', cancelButtonText: 'Cancelar' }).then(function (result) {
                if (!result.isConfirmed) { return; }
                $.post(window.INVENTARIO_CONFIG.endpoints.eliminarSitio, { id_sitio: id }, null, 'json').done(loadSitios).fail(function (xhr) {
                    const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible desactivar.';
                    Swal.fire('Error', mensaje, 'error');
                });
            });
        });
    }

    function bindRepeater() {
        $('#btnAgregarAlmacenamiento').on('click', function () {
            const index = $('#contenedorAlmacenamiento .almacenamiento-item').length;
            $('#contenedorAlmacenamiento').append(`<div class="inv-repeat-card almacenamiento-item"><div class="row g-3"><div class="col-md-3"><label class="form-label">Nombre</label><input type="text" name="almacenamiento[${index}][nombre_contacto]" class="form-control"></div><div class="col-md-3"><label class="form-label">RUT</label><input type="text" name="almacenamiento[${index}][rut_contacto]" class="form-control"></div><div class="col-md-3"><label class="form-label">Email</label><input type="email" name="almacenamiento[${index}][email_contacto]" class="form-control"></div><div class="col-md-3"><label class="form-label">Otros</label><input type="text" name="almacenamiento[${index}][otros_datos]" class="form-control"></div></div><button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button></div>`);
        });

        $(document).on('click', '.btnEliminarFila', function () {
            $(this).closest('.inv-repeat-card').remove();
        });
    }

    function crearCardDatoSensible(dato) {
        const descripcion = dato && dato.descripcion ? `<small class="text-muted d-block mt-2">${escapeHtml(dato.descripcion)}</small>` : '';
        return `<div class="col-md-6">
            <label class="inv-repeat-card py-3 h-100 d-block">
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" name="datos_sensibles[]" value="${parseInt(dato.id_dato_sensible, 10)}" checked>
                    <span class="form-check-label fw-semibold">${escapeHtml(dato.nombre || '')}</span>
                </div>
                ${descripcion}
            </label>
        </div>`;
    }

    function bindDatosSensibles() {
        $(document).on('click', '#btnAgregarDatoSensible', function () {
            const endpoint = $(this).data('endpoint');
            if (!endpoint) { return; }

            Swal.fire({
                title: 'Nuevo dato sensible',
                html: `
                    <input id="swalDatoSensibleNombre" class="swal2-input" placeholder="Nombre">
                    <textarea id="swalDatoSensibleDescripcion" class="swal2-textarea" placeholder="Descripcion opcional"></textarea>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                preConfirm: function () {
                    const nombre = $('#swalDatoSensibleNombre').val().trim();
                    const descripcion = $('#swalDatoSensibleDescripcion').val().trim();
                    if (!nombre) {
                        Swal.showValidationMessage('Debes ingresar el nombre del dato sensible.');
                        return false;
                    }

                    return $.ajax({
                        url: endpoint,
                        method: 'POST',
                        dataType: 'json',
                        data: { nombre: nombre, descripcion: descripcion }
                    }).then(function (response) {
                        return response;
                    }).catch(function (xhr) {
                        const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible guardar el dato sensible.';
                        Swal.showValidationMessage(mensaje);
                    });
                }
            }).then(function (result) {
                if (!result.isConfirmed || !result.value || !result.value.ok || !result.value.dato) {
                    return;
                }

                const dato = result.value.dato;
                const $contenedor = $('#contenedorDatosSensibles');
                if (!$contenedor.length) { return; }

                const selector = `input[name="datos_sensibles[]"][value="${parseInt(dato.id_dato_sensible, 10)}"]`;
                const $existente = $(selector);
                if ($existente.length) {
                    $existente.prop('checked', true);
                } else {
                    $contenedor.prepend(crearCardDatoSensible(dato));
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Dato sensible listo',
                    text: result.value.mensaje
                });
            });
        });
    }

    function initDashboard() {
        if (!window.INVENTARIO_DASHBOARD || typeof Chart === 'undefined') { return; }
        const resumenCanvas = document.getElementById('graficoDashboardResumen');
        const licenciasCanvas = document.getElementById('graficoDashboardLicencias');

        if (resumenCanvas) {
            const resumenGlobal = window.INVENTARIO_DASHBOARD.resumenGlobal || {};
            new Chart(resumenCanvas, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(resumenGlobal),
                    datasets: [{
                        data: Object.values(resumenGlobal),
                        backgroundColor: [
                            'rgba(27, 77, 151, 0.88)',
                            'rgba(8, 145, 178, 0.85)',
                            'rgba(22, 163, 74, 0.85)',
                            'rgba(217, 119, 6, 0.85)'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    cutout: '62%'
                }
            });
        }

        if (licenciasCanvas) {
            new Chart(licenciasCanvas, {
                type: 'bar',
                data: {
                    labels: window.INVENTARIO_DASHBOARD.labels || [],
                    datasets: [{
                        label: 'Licencias',
                        data: window.INVENTARIO_DASHBOARD.licencias || [],
                        backgroundColor: 'rgba(217, 119, 6, 0.82)',
                        borderRadius: 8,
                        barThickness: 18
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        },
                        y: {
                            ticks: {
                                autoSkip: false
                            }
                        }
                    }
                }
            });
        }
    }

    $(function () {
        initTablaSoftware();
        initTablaSitios();
        initDashboard();
        bindFiltros();
        bindFormAjax();
        bindDelete();
        bindRepeater();
        bindDatosSensibles();
        renderResumenActivo();
        actualizarLinksPdf();
    });
}(jQuery));
