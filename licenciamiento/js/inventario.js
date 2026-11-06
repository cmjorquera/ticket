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

    function getSwalBaseConfig() {
        return {
            customClass: {
                popup: 'inv-swal-popup',
                title: 'inv-swal-title',
                htmlContainer: 'inv-swal-html',
                confirmButton: 'inv-swal-confirm',
                cancelButton: 'inv-swal-cancel',
                input: 'inv-swal-input',
                textarea: 'inv-swal-textarea',
                validationMessage: 'inv-swal-validation'
            },
            buttonsStyling: false
        };
    }

    function showInventarioSwal(options) {
        return Swal.fire($.extend(true, {}, getSwalBaseConfig(), options));
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

    function crearTarjetaResumenEstado(opciones) {
        const variante = opciones.variante === 'teal' ? 'secondary' : (opciones.variante || 'primary');
        const icono = opciones.icono ? `<div class="inv-stat-icon"><i class="bi ${escapeHtml(opciones.icono)}"></i></div>` : '';
        return `<div class="col-12 col-md-6 col-xl-2">
            <div class="inv-stat-card inv-stat-${escapeHtml(variante)}">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="inv-stat-label">${escapeHtml(opciones.titulo)}</span>
                        <strong${opciones.compacto ? ' class="inv-stat-compacto"' : ''}>${escapeHtml(opciones.valor).replace(/ · /g, '<br>')}</strong>
                        <small>${escapeHtml(opciones.descripcion)}</small>
                    </div>
                    ${icono}
                </div>
            </div>
        </div>`;
    }

    // Suma costos SEPARADOS por moneda (antes se sumaban CLP y USD juntos).
    function gastoPorMoneda(items) {
        const totales = {};
        items.forEach(item => {
            const costo = numeroSeguro(item.costo);
            if (costo <= 0) { return; }
            const moneda = String(item.moneda || 'USD').toUpperCase();
            totales[moneda] = (totales[moneda] || 0) + costo;
        });
        const partes = Object.keys(totales).sort().map(m => m + ' ' + new Intl.NumberFormat('es-CL', {
            minimumFractionDigits: m === 'CLP' ? 0 : 2, maximumFractionDigits: m === 'CLP' ? 0 : 2
        }).format(totales[m]));
        return partes.length ? partes.join(' · ') : '0';
    }

    // Solo http/https se convierten en enlace (evita enlaces javascript:)
    function enlaceSeguro(url) {
        const texto = String(url || '').trim();
        if (!texto) { return '<span class="text-muted">—</span>'; }
        if (/^https?:\/\//i.test(texto)) {
            return `<a class="inv-url-cell" href="${escapeHtml(texto)}" target="_blank" rel="noopener noreferrer">${escapeHtml(texto)}</a>`;
        }
        return `<span class="inv-url-cell">${escapeHtml(texto)}</span>`;
    }

    function renderResumenSoftware() {
        const $wrap = $('#resumenTabInventario');
        if (!$wrap.length) { return; }

        const totalRegistros = softwareActual.length;
        const totalLicencias = softwareActual.reduce((acc, item) => acc + numeroSeguro(item.cantidad_licencias), 0);
        const totalColegios = new Set(softwareActual.map(item => item.id_colegio).filter(Boolean)).size;
        const suscripciones = softwareActual.filter(item => String(item.tipo_licenciamiento || '').toLowerCase() === 'suscripcion').length;
        const gratuitas = softwareActual.filter(item => String(item.tipo_licenciamiento || '').toLowerCase() === 'gratuita').length;

        $wrap.html(`<div class="row g-3">
            ${crearTarjetaResumenEstado({ titulo: 'Total programas', valor: formatoEntero(totalRegistros), descripcion: 'Registros visibles con filtros', variante: 'primary', icono: 'bi-window-stack' })}
            ${crearTarjetaResumenEstado({ titulo: 'Licencias', valor: formatoEntero(totalLicencias), descripcion: 'Cantidad total visible', variante: 'success', icono: 'bi-key' })}
            ${crearTarjetaResumenEstado({ titulo: 'Colegios', valor: formatoEntero(totalColegios), descripcion: 'Colegios con software visible', variante: 'info', icono: 'bi-buildings' })}
            ${crearTarjetaResumenEstado({ titulo: 'Suscripciones', valor: formatoEntero(suscripciones), descripcion: 'Registros por suscripción', variante: 'warning', icono: 'bi-arrow-repeat' })}
            ${crearTarjetaResumenEstado({ titulo: 'Gratuitos', valor: formatoEntero(gratuitas), descripcion: 'Software sin costo', variante: 'teal', icono: 'bi-gift' })}
            ${crearTarjetaResumenEstado({ titulo: 'Gasto declarado', valor: gastoPorMoneda(softwareActual), compacto: true, descripcion: 'Sumado por moneda', variante: 'danger', icono: 'bi-cash-coin' })}
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

        $wrap.html(`<div class="row g-3">
            ${crearTarjetaResumenEstado({ titulo: 'Total sitios', valor: formatoEntero(totalSitios), descripcion: 'Sitios visibles con filtros', variante: 'primary', icono: 'bi-globe2' })}
            ${crearTarjetaResumenEstado({ titulo: 'Web', valor: formatoEntero(totalWeb), descripcion: 'Portales y páginas', variante: 'success', icono: 'bi-browser-chrome' })}
            ${crearTarjetaResumenEstado({ titulo: 'Apps', valor: formatoEntero(totalApp), descripcion: 'Aplicaciones registradas', variante: 'info', icono: 'bi-phone' })}
            ${crearTarjetaResumenEstado({ titulo: 'Clientes', valor: formatoEntero(totalCliente), descripcion: 'Clientes o accesos locales', variante: 'warning', icono: 'bi-pc-display' })}
            ${crearTarjetaResumenEstado({ titulo: 'Responsables', valor: formatoEntero(totalResponsables), descripcion: 'Encargados visibles', variante: 'teal', icono: 'bi-person-badge' })}
            ${crearTarjetaResumenEstado({ titulo: 'Con URL', valor: formatoEntero(totalUrls) + ' / ' + formatoEntero(totalColegios), descripcion: 'URL registradas / colegios', variante: 'danger', icono: 'bi-link-45deg' })}
        </div>`);
    }

    function renderResumenActivo() {
        const pagina = (window.INVENTARIO_CONFIG && window.INVENTARIO_CONFIG.pagina) ? window.INVENTARIO_CONFIG.pagina : 'software';
        if (pagina === 'sitios') {
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
            dom: "<'row align-items-center'<'col-md-12'i>>rt<'row align-items-center'<'col-md-6'i><'col-md-6 text-md-end'p>>",
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
                emptyTable: 'No hay registros para mostrar',
                zeroRecords: 'No hay registros que coincidan con los filtros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Sin registros para mostrar',
                paginate: { previous: 'Anterior', next: 'Siguiente' }
            },
            createdRow: function (row) {
                $(row).addClass('fila-ticket-admin-compacta');
            },
            columns: [
                { data: 'id_software', className: 'celda-id' },
                { data: null, className: 'celda-asunto', render: row => `<div class="fw-semibold">${escapeHtml(row.nombre_software)}</div><small class="text-muted">${escapeHtml(row.proveedor || '')}</small>` },
                { data: 'nom_colegio', className: 'celda-de', render: escapeHtml },
                { data: 'version_software', className: 'celda-fecha-hora', render: data => escapeHtml(data || '-') },
                { data: 'cantidad_licencias', className: 'celda-estado' },
                { data: 'tipo_licenciamiento', className: 'celda-fecha-respuesta', render: escapeHtml },
                { data: 'pagado_por', className: 'celda-dias-restantes', render: escapeHtml },
                { data: 'responsable', className: 'celda-tecnico', render: data => escapeHtml(data || 'Sin asignar') },
                { data: null, className: 'celda-opciones p-2', orderable: false, searchable: false, render: row => {
                    const id = parseInt(row.id_software, 10);
                    return `<div class="dropdown lic-actions-dropdown">
                        <button class="btn btn-light border" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más opciones" aria-label="Más opciones">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="ver_software.php?id_software=${id}"><i class="bi bi-eye me-2"></i>Ver</a></li>
                            <li><a class="dropdown-item" href="editar_software.php?id_software=${id}"><i class="bi bi-pencil me-2"></i>Editar</a></li>
                            <li><a class="dropdown-item text-danger btnEliminarSoftware" href="#" data-id="${id}"><i class="bi bi-trash me-2"></i>Eliminar</a></li>
                        </ul>
                    </div>`;
                } }
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
            dom: "<'row align-items-center'<'col-md-12'i>>rt<'row align-items-center'<'col-md-6'i><'col-md-6 text-md-end'p>>",
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
                emptyTable: 'No hay registros para mostrar',
                zeroRecords: 'No hay registros que coincidan con los filtros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Sin registros para mostrar',
                paginate: { previous: 'Anterior', next: 'Siguiente' }
            },
            createdRow: function (row) {
                $(row).addClass('fila-ticket-admin-compacta');
            },
            columns: [
                { data: 'id_sitio', className: 'celda-id' },
                { data: 'nombre_sitio', className: 'celda-asunto', render: escapeHtml },
                { data: 'nom_colegio', className: 'celda-de', render: escapeHtml },
                { data: 'tipo_sitio', className: 'celda-fecha-hora', render: escapeHtml },
                { data: 'url_sitio', className: 'celda-dias-restantes', render: data => enlaceSeguro(data) },
                { data: 'estado_sitio', className: 'celda-estado', render: escapeHtml },
                { data: 'responsable', className: 'celda-tecnico', render: data => escapeHtml(data || 'Sin asignar') },
                { data: null, className: 'celda-opciones p-2', orderable: false, searchable: false, render: row => {
                    const id = parseInt(row.id_sitio, 10);
                    return `<div class="dropdown lic-actions-dropdown">
                        <button class="btn btn-light border" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Más opciones" aria-label="Más opciones">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="ver_sitio_web.php?id_sitio=${id}"><i class="bi bi-eye me-2"></i>Ver</a></li>
                            <li><a class="dropdown-item" href="editar_sitio_web.php?id_sitio=${id}"><i class="bi bi-pencil me-2"></i>Editar</a></li>
                            <li><a class="dropdown-item text-danger btnEliminarSitio" href="#" data-id="${id}"><i class="bi bi-trash me-2"></i>Eliminar</a></li>
                        </ul>
                    </div>`;
                } }
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
            showInventarioSwal({
                icon: 'error',
                title: 'Error',
                text: 'No fue posible cargar el software.'
            });
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
            showInventarioSwal({
                icon: 'error',
                title: 'Error',
                text: 'No fue posible cargar los sitios.'
            });
        });
    }

    function bindActionsDropdownOverflowFix() {
        $(document).on('show.bs.dropdown', '.lic-actions-dropdown', function () {
            $(this).closest('.table-responsive').addClass('lic-table-responsive-overflow-visible');
        });
        $(document).on('hide.bs.dropdown', '.lic-actions-dropdown', function () {
            $(this).closest('.table-responsive').removeClass('lic-table-responsive-overflow-visible');
        });
    }

    function bindFiltros() {
        $('#filtroSoftwareColegio, #filtroSoftwareUsuario, #filtroSoftwareLicencia').on('change', loadSoftware);
        $('#filtroSoftwareBusqueda').on('input', debounce(loadSoftware, 300));
        $('#filtroSitioColegio, #filtroSitioUsuario, #filtroSitioTipo').on('change', loadSitios);
        $('#filtroSitioBusqueda').on('input', debounce(loadSitios, 300));
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
                showInventarioSwal({ icon: 'success', title: 'Guardado correctamente', text: response.mensaje }).then(function () {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                });
            }).fail(function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible guardar.';
                showInventarioSwal({ icon: 'error', title: 'Error', text: mensaje });
            });
        });
    }

    function bindDelete() {
        $(document).on('click', '.btnEliminarSoftware', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            showInventarioSwal({
                title: 'Desactivar software',
                text: 'El registro quedará inactivo.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar',
                customClass: { confirmButton: 'inv-swal-confirm inv-swal-danger' }
            }).then(function (result) {
                if (!result.isConfirmed) { return; }
                $.post(window.INVENTARIO_CONFIG.endpoints.eliminarSoftware, { id_software: id }, null, 'json').done(loadSoftware).fail(function (xhr) {
                    const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible desactivar.';
                    showInventarioSwal({ icon: 'error', title: 'Error', text: mensaje });
                });
            });
        });

        $(document).on('click', '.btnEliminarSitio', function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            showInventarioSwal({
                title: 'Desactivar sitio',
                text: 'El registro quedará inactivo.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar',
                customClass: { confirmButton: 'inv-swal-confirm inv-swal-danger' }
            }).then(function (result) {
                if (!result.isConfirmed) { return; }
                $.post(window.INVENTARIO_CONFIG.endpoints.eliminarSitio, { id_sitio: id }, null, 'json').done(loadSitios).fail(function (xhr) {
                    const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible desactivar.';
                    showInventarioSwal({ icon: 'error', title: 'Error', text: mensaje });
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

            Swal.fire($.extend(true, {}, getSwalBaseConfig(), {
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
            })).then(function (result) {
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

                Swal.fire($.extend(true, {}, getSwalBaseConfig(), {
                    icon: 'success',
                    title: 'Dato sensible listo',
                    text: result.value.mensaje
                }));
            });
        });
    }

    function crearCardTipoUsuario(tipoUsuario) {
        const descripcion = tipoUsuario && tipoUsuario.descripcion ? `<small class="text-muted d-block mt-2">${escapeHtml(tipoUsuario.descripcion)}</small>` : '';
        return `<div class="inv-user-type-item">
            <label class="inv-repeat-card inv-user-type-card py-3 h-100 d-block">
                <div class="form-check m-0">
                    <input class="form-check-input" type="checkbox" name="tipos_usuario[]" value="${parseInt(tipoUsuario.id_tipo_usuario, 10)}" checked>
                    <span class="form-check-label fw-semibold">${escapeHtml(tipoUsuario.nombre || '')}</span>
                </div>
                ${descripcion}
            </label>
        </div>`;
    }

    function bindTiposUsuario() {
        $(document).on('click', '#btnAgregarTipoUsuario', function () {
            const endpoint = $(this).data('endpoint');
            if (!endpoint) { return; }

            Swal.fire($.extend(true, {}, getSwalBaseConfig(), {
                title: 'Nuevo tipo de usuario',
                html: `
                    <input id="swalTipoUsuarioNombre" class="swal2-input" placeholder="Nombre">
                    <textarea id="swalTipoUsuarioDescripcion" class="swal2-textarea" placeholder="Descripcion opcional"></textarea>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                preConfirm: function () {
                    const nombre = $('#swalTipoUsuarioNombre').val().trim();
                    const descripcion = $('#swalTipoUsuarioDescripcion').val().trim();
                    if (!nombre) {
                        Swal.showValidationMessage('Debes ingresar el nombre del tipo de usuario.');
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
                        const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible guardar el tipo de usuario.';
                        Swal.showValidationMessage(mensaje);
                    });
                }
            })).then(function (result) {
                if (!result.isConfirmed || !result.value || !result.value.ok || !result.value.tipo_usuario) {
                    return;
                }

                const tipoUsuario = result.value.tipo_usuario;
                const $contenedor = $('#contenedorTiposUsuario');
                if (!$contenedor.length) { return; }

                const selector = `input[name="tipos_usuario[]"][value="${parseInt(tipoUsuario.id_tipo_usuario, 10)}"]`;
                const $existente = $(selector);
                if ($existente.length) {
                    $existente.prop('checked', true);
                } else {
                    $contenedor.append(crearCardTipoUsuario(tipoUsuario));
                }

                Swal.fire($.extend(true, {}, getSwalBaseConfig(), {
                    icon: 'success',
                    title: 'Tipo de usuario listo',
                    text: result.value.mensaje
                }));
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

    // QR generado en el navegador (qrcodejs); apunta a la ficha_qr.php absoluta.
    function initQr() {
        document.querySelectorAll('.inv-qr-box[data-qr-url]').forEach(box => {
            if (typeof QRCode === 'undefined') { box.textContent = 'No se pudo cargar el generador de QR.'; return; }
            const ruta = box.getAttribute('data-qr-url') || '';
            const url = /^https?:\/\//i.test(ruta) ? ruta : window.location.origin + ruta;
            box.innerHTML = '';
            new QRCode(box, { text: url, width: 200, height: 200, correctLevel: QRCode.CorrectLevel.M });
            box.setAttribute('title', url);
        });
        $(document).on('click', '.btnDescargarQr', function () {
            const box = document.querySelector('.inv-qr-box');
            if (!box) { return; }
            const canvas = box.querySelector('canvas');
            const img = box.querySelector('img');
            const dataUrl = canvas ? canvas.toDataURL('image/png') : (img ? img.src : '');
            if (!dataUrl) { return; }
            const a = document.createElement('a');
            a.href = dataUrl;
            a.download = ($(this).data('nombre') || 'codigo_qr') + '.png';
            document.body.appendChild(a); a.click(); a.remove();
        });
    }

    $(function () {
        initQr();
        initTablaSoftware();
        initTablaSitios();
        initDashboard();
        bindFiltros();
        bindActionsDropdownOverflowFix();
        bindFormAjax();
        bindDelete();
        bindRepeater();
        bindDatosSensibles();
        bindTiposUsuario();
        renderResumenActivo();
        actualizarLinksPdf();
    });
}(jQuery));
