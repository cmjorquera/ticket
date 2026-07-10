(function ($) {
    'use strict';

    let tablaInventario = null;
    let tablaMonitores  = null;

    // =========================================================================
    // HELPERS
    // =========================================================================

    function escapeHtml(value) {
        if (value === null || value === undefined) { return ''; }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function debounce(fn, delay) {
        let timer = null;
        return function () { clearTimeout(timer); timer = setTimeout(fn, delay); };
    }

    function normalizarSerieEquipo(value) {
        return String(value || '').trim().toUpperCase().replace(/\s+/g, ' ').replace(/[^A-Z0-9\-_.\/]/g, '');
    }

    function nombreEquipoDesdeSerie(value) {
        const serie = normalizarSerieEquipo(value);
        return serie ? 'PC-' + serie : '';
    }

    function badgePendiente(label) {
        return '<span class="inv-table-chip inv-table-chip-warning"><span></span>' + escapeHtml(label || 'Pendiente') + '</span>';
    }

    function buildQrFichaUrl(idEquipo) {
        const cfg = window.INVENTARIO_CONFIG || {};
        const base = cfg.qrEquipoBaseUrl || 'equipoQRinformacion.php';
        return base + (base.indexOf('?') === -1 ? '?' : '&') + 'id=' + encodeURIComponent(idEquipo);
    }

    // =========================================================================
    // ESTADO SELECT (inline en tabla, reemplaza modal)
    // =========================================================================

    function buildEstadoSelectEquipo(row) {
        const estados  = (window.INVENTARIO_CONFIG && window.INVENTARIO_CONFIG.estadosEquipo) || [];
        const idEquipo = parseInt(row.id_equipo, 10);
        const idEstado = parseInt(row.id_estado, 10);
        const options  = estados.map(function (e) {
            const sel = e.id_estado === idEstado ? ' selected' : '';
            return '<option value="' + e.id_estado + '"' + sel + '>' + escapeHtml(e.nombre_estado) + '</option>';
        }).join('');
        return '<select class="form-select form-select-sm inv-estado-select"' +
               ' data-id="' + idEquipo + '"' +
               ' onchange="InventarioFunciones.cambiarEstadoEquipo(this)">' +
               options + '</select>';
    }

    // =========================================================================
    // BOTONES DE ACCIÓN — PC
    // =========================================================================

    function buildAccionButtons(row) {
        const id = parseInt(row.id_equipo, 10);
        const tieneAsignado = row.id_usuario_asignado && parseInt(row.id_usuario_asignado, 10) > 0;
        const btnLiberar = tieneAsignado
            ? '<button type="button" class="btn btn-sm inv-btn-action inv-btn-release"' +
              ' onclick="InventarioFunciones.liberarEquipo(' + id + ')"' +
              ' title="Liberar equipo" aria-label="Liberar equipo"><i class="bi bi-person-x"></i></button>'
            : '';
        return '<div class="inv-action-buttons d-flex flex-wrap gap-2">' +
            '<button type="button" class="btn btn-sm inv-btn-action inv-btn-view"' +
            ' onclick="InventarioFunciones.verDetalle(' + id + ')"' +
            ' title="Ver detalle" aria-label="Ver detalle"><i class="bi bi-eye"></i></button>' +
            '<a href="editar_equipo.php?id_equipo=' + id + '" class="btn btn-sm inv-btn-action inv-btn-edit" title="Editar" aria-label="Editar"><i class="bi bi-pencil"></i></a>' +
            '<button type="button" class="btn btn-sm inv-btn-action inv-btn-qr"' +
            ' onclick="InventarioFunciones.verQrEquipo(' + id + ')"' +
            ' title="Ver código QR" aria-label="Ver código QR"><i class="bi bi-qr-code"></i></button>' +
            '<button type="button" class="btn btn-sm inv-btn-action inv-btn-photo"' +
            ' onclick="InventarioFunciones.verFotosEquipo(' + id + ')"' +
            ' title="Fotos" aria-label="Fotos"><i class="bi bi-images"></i></button>' +
            btnLiberar +
            '<button type="button" class="btn btn-sm inv-btn-action inv-btn-delete"' +
            ' onclick="InventarioFunciones.eliminarEquipo(' + id + ')"' +
            ' title="Eliminar" aria-label="Eliminar"><i class="bi bi-trash"></i></button>' +
            '</div>';
    }

    // =========================================================================
    // BOTONES DE ACCIÓN — MONITORES
    // =========================================================================

    function buildMonitorAccionButtons(row) {
        const id = parseInt(row.id_monitor, 10);
        const tieneAsignado = row.id_usuario_asignado && parseInt(row.id_usuario_asignado, 10) > 0;
        const btnLiberar = tieneAsignado
            ? '<button type="button" class="btn btn-sm inv-btn-action inv-btn-release"' +
              ' onclick="InventarioFunciones.liberarMonitor(' + id + ')"' +
              ' title="Liberar monitor" aria-label="Liberar monitor"><i class="bi bi-person-x"></i></button>'
            : '';
        return '<div class="inv-action-buttons d-flex flex-wrap gap-2">' +
            '<button type="button" class="btn btn-sm inv-btn-action inv-btn-view"' +
            ' onclick="InventarioFunciones.verDetalleMonitor(' + id + ')"' +
            ' title="Ver detalle" aria-label="Ver detalle"><i class="bi bi-eye"></i></button>' +
            '<a href="editar_monitor.php?id_monitor=' + id + '" class="btn btn-sm inv-btn-action inv-btn-edit" title="Editar" aria-label="Editar"><i class="bi bi-pencil"></i></a>' +
            '<button type="button" class="btn btn-sm inv-btn-action inv-btn-photo"' +
            ' onclick="InventarioFunciones.verFotosMonitor(' + id + ')"' +
            ' title="Fotos" aria-label="Fotos"><i class="bi bi-images"></i></button>' +
            btnLiberar +
            '<button type="button" class="btn btn-sm inv-btn-action inv-btn-delete"' +
            ' onclick="InventarioFunciones.eliminarMonitor(' + id + ')"' +
            ' title="Eliminar" aria-label="Eliminar"><i class="bi bi-trash"></i></button>' +
            '</div>';
    }

    // =========================================================================
    // FILTROS
    // =========================================================================

    function collectFilters() {
        return {
            id_colegio:          $('#filtroColegio').val()    || '',
            id_estado:           $('#filtroEstado').val()     || '',
            tipo_pc:             '',
            id_ubicacion:        $('#filtroUbicacion').val()  || '',
            id_usuario_asignado: $('#filtroUsuario').val()    || '',
            busqueda:            $('#filtroBusqueda').val()   || ''
        };
    }

    function updateUbicacionSelect(idColegio) {
        const $sel = $('#filtroUbicacion');
        if (!$sel.length) { return; }
        const ubicaciones = (window.INVENTARIO_CONFIG || {}).ubicaciones || {};
        const lista = idColegio ? (ubicaciones[String(idColegio)] || []) : [];
        let html = '<option value="">Todas</option>';
        lista.forEach(function (ub) {
            html += '<option value="' + ub.id_ubicacion + '">' + escapeHtml(ub.nombre_ubicacion) + '</option>';
        });
        $sel.html(html).val('');
    }

    function updateUsuarioSelect(idColegio, selector) {
        const $sel = $(selector);
        if (!$sel.length) { return; }
        const cfg = window.INVENTARIO_CONFIG || {};
        const mapa = cfg.usuariosPorColegio || {};
        const lista = idColegio ? (mapa[String(idColegio)] || []) : (cfg.usuariosPermitidos || []);
        let html = '<option value="">Todos</option>';
        lista.forEach(function (usuario) {
            html += '<option value="' + usuario.id + '">' + escapeHtml(usuario.nombre_completo) + '</option>';
        });
        $sel.html(html).val('');
    }

    function collectMonitorFilters() {
        return {
            id_colegio:          $('#filtroColegioMon').val()  || '',
            id_estado:           $('#filtroEstadoMon').val()   || '',
            id_usuario_asignado: $('#filtroUsuarioMon').val()  || '',
            busqueda:            $('#filtroBusquedaMon').val() || ''
        };
    }

    // =========================================================================
    // CARGA DE DATOS
    // =========================================================================

    function loadInventario() {
        if (!window.INVENTARIO_CONFIG || !window.INVENTARIO_CONFIG.endpoints) { return; }
        $.getJSON(window.INVENTARIO_CONFIG.endpoints.listar, collectFilters())
            .done(function (response) {
                if (!response.ok) { return; }
                $('#contenedorResumen').html(response.resumen_html);
                if (tablaInventario) { tablaInventario.clear().rows.add(response.data || []).draw(); }
            })
            .fail(function () { Swal.fire('Error', 'No fue posible cargar el inventario.', 'error'); });
    }

    function loadMonitores() {
        if (!window.INVENTARIO_CONFIG || !window.INVENTARIO_CONFIG.endpoints) { return; }
        $.getJSON(window.INVENTARIO_CONFIG.endpoints.listarMonitores, collectMonitorFilters())
            .done(function (response) {
                if (!response.ok) { return; }
                $('#contenedorResumenMonitores').html(response.resumen_html);
                if (tablaMonitores) { tablaMonitores.clear().rows.add(response.data || []).draw(); }
            })
            .fail(function () { Swal.fire('Error', 'No fue posible cargar los monitores.', 'error'); });
    }

    // =========================================================================
    // DATATABLES
    // =========================================================================

    function initTabla() {
        const $tabla = $('#tablaInventario');
        if (!$tabla.length) { return; }

        const mostrarColegio = !!((window.INVENTARIO_CONFIG || {}).mostrarColumnaColegio);
        const columnas = [
            {
                data: null, orderable: false, searchable: false,
                render: function (data, type, row, meta) {
                    const pageInfo = new $.fn.dataTable.Api(meta.settings).page.info();
                    return pageInfo.start + meta.row + 1;
                }
            },
            {
                data: null,
                render: function (data) {
                    const nombrePersonalizado = (data.nombre_personalizado || '').trim();
                    const lineaNombre = nombrePersonalizado
                        ? '<div class="fw-semibold">' + escapeHtml(nombrePersonalizado) + '</div>' +
                          '<small class="text-muted">(' + escapeHtml(data.nombre_equipo) + ')</small><br>'
                        : '<div class="fw-semibold">' + escapeHtml(data.nombre_equipo) + '</div>';
                    return lineaNombre +
                           '<small class="text-muted">' + escapeHtml(data.fabricante || '') + ' ' + escapeHtml(data.producto || '') + '</small>';
                }
            }
        ];
        if (mostrarColegio) {
            columnas.push({ data: 'nom_colegio', render: escapeHtml });
        }
        columnas.push(
            { data: 'tipo_pc', render: escapeHtml },
            { data: 'numero_serie', render: escapeHtml },
            {
                data: 'nombre_ubicacion',
                render: function (data) {
                    return data ? escapeHtml(data) : badgePendiente('Pendiente');
                }
            },
            {
                data: 'usuario_asignado',
                render: function (data) { return data ? escapeHtml(data) : badgePendiente('Sin asignar'); }
            },
            {
                data: 'id_estado',
                render: function (data, type, row) {
                    if (type === 'sort' || type === 'type') { return data; }
                    return buildEstadoSelectEquipo(row);
                }
            },
            {
                data: null, orderable: false, searchable: false,
                render: function (row) { return buildAccionButtons(row); }
            }
        );

        tablaInventario = $tabla.DataTable({
            data: [],
            responsive: false,
            autoWidth: false,
            pageLength: 10,
            paging: true,
            info: true,
            lengthChange: false,
            pagingType: 'simple_numbers',
            dom: "<'row align-items-center mb-3'<'col-md-12'i>>" +
                 "rt" +
                 "<'row align-items-center mt-3'<'col-md-6'i><'col-md-6 text-md-end'p>>",
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ equipos',
                infoEmpty: 'Sin equipos para mostrar',
                paginate: { previous: 'Anterior', next: 'Siguiente' }
            },
            columns: columnas
        });

        loadInventario();

        if (window.ResizeObserver) {
            const _adj = debounce(function () { if (tablaInventario) { tablaInventario.columns.adjust(); } }, 130);
            const _wr = document.getElementById('tablaInventario_wrapper');
            if (_wr) { new ResizeObserver(_adj).observe(_wr); }
        }
    }

    function initTablaMonitores() {
        const $tabla = $('#tablaMonitores');
        if (!$tabla.length) { return; }

        const mostrarColegio = !!((window.INVENTARIO_CONFIG || {}).mostrarColumnaColegio);
        const columnas = [
            {
                data: null, orderable: false, searchable: false,
                render: function (data, type, row, meta) {
                    const pageInfo = new $.fn.dataTable.Api(meta.settings).page.info();
                    return pageInfo.start + meta.row + 1;
                }
            },
            {
                data: null,
                render: function (data) {
                    const nombre = escapeHtml(data.nombre_monitor || '');
                    const sub    = [data.marca, data.modelo, data.tamano_monitor ? data.tamano_monitor + '"' : '']
                                    .filter(Boolean).map(escapeHtml).join(' ');
                    return '<div class="fw-semibold">' + nombre + '</div><small class="text-muted">' + sub + '</small>';
                }
            }
        ];
        if (mostrarColegio) {
            columnas.push({ data: 'nom_colegio', render: escapeHtml });
        }
        columnas.push(
            {
                data: 'nombre_ubicacion',
                render: function (data, type, row) {
                    if (!data) { return '<span class="text-muted">Sin ubicación</span>'; }
                    const tip = row.tipo_ubicacion ? escapeHtml(row.tipo_ubicacion) + ' — ' : '';
                    return tip + escapeHtml(data);
                }
            },
            { data: 'numero_serie', render: escapeHtml },
            {
                data: 'usuario_asignado',
                render: function (data) { return escapeHtml(data || 'Sin asignar'); }
            },
            { data: 'badge_estado' },
            {
                data: null, orderable: false, searchable: false,
                render: function (row) { return buildMonitorAccionButtons(row); }
            }
        );

        tablaMonitores = $tabla.DataTable({
            data: [],
            responsive: false,
            autoWidth: false,
            pageLength: 10,
            paging: true,
            info: true,
            lengthChange: false,
            pagingType: 'simple_numbers',
            dom: "<'row align-items-center mb-3'<'col-md-12'i>>" +
                 "rt" +
                 "<'row align-items-center mt-3'<'col-md-6'i><'col-md-6 text-md-end'p>>",
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ monitores',
                infoEmpty: 'Sin monitores para mostrar',
                paginate: { previous: 'Anterior', next: 'Siguiente' }
            },
            columns: columnas
        });

        loadMonitores();

        if (window.ResizeObserver) {
            const _adj = debounce(function () { if (tablaMonitores) { tablaMonitores.columns.adjust(); } }, 130);
            const _wr = document.getElementById('tablaMonitores_wrapper');
            if (_wr) { new ResizeObserver(_adj).observe(_wr); }
        }
    }

    // =========================================================================
    // OFFCANVAS DETALLE
    // =========================================================================

    function setDetalleOffcanvasLoading(title) {
        const $offcanvas = $('#offcanvasDetalleInventario');
        const $title     = $('#offcanvasDetalleInventarioLabel');
        const $body      = $('#offcanvasDetalleInventarioBody');
        if (!$offcanvas.length || !$body.length) { return null; }
        $title.text(title || 'Detalle');
        $body.html('<div class="text-center py-5 text-muted"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Cargando detalle...</div>');
        showDetalleOffcanvas($offcanvas[0]);
        return { $title: $title, $body: $body };
    }

    function showDetalleOffcanvas(offcanvasEl) {
        if (!offcanvasEl) { return; }
        if (window.bootstrap && bootstrap.Offcanvas && bootstrap.Offcanvas.getOrCreateInstance) {
            bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl).show();
            return;
        }
        offcanvasEl.classList.add('show');
        offcanvasEl.style.visibility = 'visible';
        offcanvasEl.setAttribute('aria-modal', 'true');
        offcanvasEl.setAttribute('role', 'dialog');
        document.body.classList.add('modal-open');
        if (!document.querySelector('.inv-offcanvas-backdrop')) {
            const backdrop = document.createElement('div');
            backdrop.className = 'offcanvas-backdrop fade show inv-offcanvas-backdrop';
            document.body.appendChild(backdrop);
        }
    }

    function hideDetalleOffcanvas() {
        const offcanvasEl = document.getElementById('offcanvasDetalleInventario');
        if (!offcanvasEl) { return; }
        if (window.bootstrap && bootstrap.Offcanvas && bootstrap.Offcanvas.getInstance) {
            const instance = bootstrap.Offcanvas.getInstance(offcanvasEl);
            if (instance) { instance.hide(); return; }
        }
        offcanvasEl.classList.remove('show');
        offcanvasEl.style.visibility = 'hidden';
        offcanvasEl.removeAttribute('aria-modal');
        offcanvasEl.removeAttribute('role');
        document.querySelectorAll('.inv-offcanvas-backdrop').forEach(function (el) { el.remove(); });
        document.body.classList.remove('modal-open');
    }

    function renderDetalleOffcanvas($title, $body, title, html) {
        $title.text(title || 'Detalle');
        $body.html(html || '<div class="alert alert-light border mb-0">Sin detalle disponible.</div>');
        initQrBlocks($body[0]);
    }

    function bindOffcanvasClose() {
        $(document).on('click', '#offcanvasDetalleInventario [data-bs-dismiss="offcanvas"], .inv-offcanvas-backdrop', function () {
            hideDetalleOffcanvas();
        });
    }

    // =========================================================================
    // MODAL GALERÍA CLOSE FALLBACK
    // =========================================================================

    function bindModalCloseFallback() {
        function cerrarModalSeguro(modalEl) {
            if (!modalEl) { return; }
            if (window.bootstrap && bootstrap.Modal && bootstrap.Modal.getOrCreateInstance) {
                try { bootstrap.Modal.getOrCreateInstance(modalEl).hide(); } catch (e) { /* fallback manual */ }
            } else if ($.fn.modal) {
                try { $(modalEl).modal('hide'); } catch (e) { /* fallback manual */ }
            }

            modalEl.classList.remove('show');
            modalEl.style.display = 'none';
            modalEl.setAttribute('aria-hidden', 'true');
            modalEl.removeAttribute('aria-modal');
            modalEl.removeAttribute('role');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });
        }

        $(document).on('click', '#modalQrEquipo [data-bs-dismiss="modal"], #modalGaleriaEquipo [data-bs-dismiss="modal"], #modalGaleriaMonitor [data-bs-dismiss="modal"]', function () {
            const modalEl = this.closest('.modal');
            cerrarModalSeguro(modalEl);
        });
        $('#modalQrEquipo, #modalGaleriaEquipo, #modalGaleriaMonitor').on('hidden.bs.modal', function () {
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });
        });
    }

    // =========================================================================
    // FILTROS — BIND
    // =========================================================================

    function bindFilters() {
        $('#filtroColegio').on('change', function () {
            const idColegio = parseInt($(this).val(), 10) || 0;
            updateUbicacionSelect(idColegio);
            updateUsuarioSelect(idColegio, '#filtroUsuario');
            loadInventario();
        });
        $('#filtroEstado, #filtroUbicacion, #filtroUsuario').on('change', loadInventario);
        $('#filtroBusqueda').on('keyup', debounce(loadInventario, 350));
    }

    function bindMonitorFilters() {
        $('#filtroColegioMon').on('change', function () {
            updateUsuarioSelect(parseInt($(this).val(), 10) || 0, '#filtroUsuarioMon');
            loadMonitores();
        });
        $('#filtroEstadoMon, #filtroUsuarioMon').on('change', loadMonitores);
        $('#filtroBusquedaMon').on('keyup', debounce(loadMonitores, 350));
    }

    // =========================================================================
    // FORMULARIOS Y REPEATERS (páginas de registro/edición)
    // =========================================================================

    function bindFormAjax() {
        const $form = $('#formInventario');
        if (!$form.length) { return; }
        $form.on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            $.ajax({
                url: $form.attr('action'),
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(function (response) {
                Swal.fire({ icon: 'success', title: 'Guardado correctamente', text: response.mensaje })
                    .then(function () { if (response.redirect) { window.location.href = response.redirect; } });
            }).fail(function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible guardar el equipo.';
                Swal.fire('Error', mensaje, 'error');
            });
        });
    }

    function bindNombreEquipoAuto() {
        const $serie = $('#numeroSerieEquipo');
        const $preview = $('#nombreEquipoPreview');
        const $hidden = $('#nombreEquipoAuto');
        if (!$serie.length || !$preview.length) { return; }

        function refresh() {
            const nombre = nombreEquipoDesdeSerie($serie.val());
            $serie.val(normalizarSerieEquipo($serie.val()));
            $preview.text(nombre || 'Se genera desde la serie');
            if ($hidden.length) { $hidden.val(nombre); }
        }

        $serie.on('input blur change', refresh);
        refresh();
    }

    function bindRepeater() {
        $('#btnAgregarMemoria').on('click', function () {
            const index = $('#contenedorMemorias .memoria-item').length;
            $('#contenedorMemorias').append(
                '<div class="inv-repeat-card memoria-item"><div class="row g-3">' +
                '<div class="col-md-2"><label class="form-label">Slot</label><input type="text" name="memoria[' + index + '][designacion_memoria]" class="form-control"></div>' +
                '<div class="col-md-2"><label class="form-label">Formato</label><input type="text" name="memoria[' + index + '][formato_memoria]" class="form-control"></div>' +
                '<div class="col-md-2"><label class="form-label">Tipo</label><input type="text" name="memoria[' + index + '][tipo_memoria]" class="form-control"></div>' +
                '<div class="col-md-2"><label class="form-label">Tamano</label><input type="text" name="memoria[' + index + '][tamano_memoria]" class="form-control"></div>' +
                '<div class="col-md-2"><label class="form-label">Frecuencia</label><input type="text" name="memoria[' + index + '][frecuencia_memoria]" class="form-control"></div>' +
                '<div class="col-md-1"><label class="form-label">Marca</label><input type="text" name="memoria[' + index + '][marca_memoria]" class="form-control"></div>' +
                '<div class="col-md-1"><label class="form-label">Orden</label><input type="number" name="memoria[' + index + '][orden_memoria]" class="form-control" value="' + (index + 1) + '"></div>' +
                '</div><button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button></div>'
            );
        });
        $('#btnAgregarMonitorPC').on('click', function () {
            const index = $('#contenedorMonitores .monitor-item').length;
            $('#contenedorMonitores').append(
                '<div class="inv-repeat-card monitor-item"><div class="row g-3">' +
                '<div class="col-md-3"><label class="form-label">Modelo</label><input type="text" name="monitor[' + index + '][modelo_monitor]" class="form-control"></div>' +
                '<div class="col-md-2"><label class="form-label">Codigo</label><input type="text" name="monitor[' + index + '][codigo_monitor]" class="form-control"></div>' +
                '<div class="col-md-2"><label class="form-label">Serie</label><input type="text" name="monitor[' + index + '][serie_monitor]" class="form-control"></div>' +
                '<div class="col-md-2"><label class="form-label">Tamano</label><input type="text" name="monitor[' + index + '][tamano_monitor]" class="form-control"></div>' +
                '<div class="col-md-2"><label class="form-label">Resolucion</label><input type="text" name="monitor[' + index + '][resolucion_monitor]" class="form-control"></div>' +
                '<div class="col-md-1"><label class="form-label">Orden</label><input type="number" name="monitor[' + index + '][orden_monitor]" class="form-control" value="' + (index + 1) + '"></div>' +
                '</div><button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button></div>'
            );
        });
        $(document).on('click', '.btnEliminarFila', function () {
            $(this).closest('.inv-repeat-card').remove();
        });
    }

    function bindPreview() {
        $('#fotos_equipo').on('change', function () {
            const container = $('#previewFotos');
            container.empty();
            Array.from(this.files || []).forEach(function (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    container.append('<div class="inv-preview-item"><img src="' + e.target.result + '" alt="' + escapeHtml(file.name) + '"><span>' + escapeHtml(file.name) + '</span></div>');
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // =========================================================================
    // QR
    // =========================================================================

    function initQrBlocks(root) {
        if (!window.QRCode) { return; }
        const scope = root || document;
        $(scope).find('.js-qr-equipo').each(function () {
            const el   = this;
            const text = el.getAttribute('data-qr-text') || '';
            if (!text || el.getAttribute('data-qr-ready') === '1') { return; }
            el.innerHTML = '';
            new QRCode(el, { text: text, width: 180, height: 180 });
            el.setAttribute('data-qr-ready', '1');
            setTimeout(function () { prepareQrForPrint(el); }, 0);
        });
    }

    function prepareQrForPrint(root) {
        const scope = root || document;
        $(scope).find('.js-qr-equipo').addBack('.js-qr-equipo').each(function () {
            const box    = this;
            const canvas = box.querySelector('canvas');
            if (!canvas || box.querySelector('.inv-qr-print-image')) { return; }
            try {
                const img   = document.createElement('img');
                img.src     = canvas.toDataURL('image/png');
                img.alt     = 'QR del equipo';
                img.className = 'inv-qr-print-image';
                box.appendChild(img);
            } catch (e) { /* canvas CORS fallback */ }
        });
    }

    function renderQrEnContenedor(container, url, size) {
        if (!container) { return; }
        container.innerHTML = '';
        if (!window.QRCode) {
            container.innerHTML = '<div class="alert alert-warning mb-0">No fue posible cargar el generador QR.</div>';
            return;
        }
        new QRCode(container, { text: url, width: size || 220, height: size || 220 });
    }

    function obtenerFilaEquipo(idEquipo) {
        if (!tablaInventario) { return null; }
        const rows = tablaInventario.rows().data().toArray();
        for (let i = 0; i < rows.length; i++) {
            if (parseInt(rows[i].id_equipo, 10) === parseInt(idEquipo, 10)) {
                return rows[i];
            }
        }
        return null;
    }

    function bindImprimirQrMasivo() {
        $('#btnImprimirQrEquipos').on('click', function () {
            const params = new URLSearchParams(collectFilters());
            window.open('imprimir_qr_equipos.php?' + params.toString(), '_blank');
        });
    }

    // =========================================================================
    // TABS
    // =========================================================================

    function initTabBehavior() {
        const cfg       = window.INVENTARIO_CONFIG || {};
        const $btn      = $('#btnAgregarPrincipal');
        const $label    = $('#btnAgregarLabel');
        const $cargaPc  = $('.inv-btn-carga-masiva');
        const $cargaMon = $('.inv-btn-carga-masiva-mon');

        function applyTab(tab) {
            if (!$btn.length) { return; }
            if (tab === 'monitores') {
                if ($btn.length) { $btn.removeClass('d-none'); }
                $btn.attr('href', $btn.data('href-mon') || 'registrar_monitor.php');
                if ($label.length)  { $label.text($btn.data('label-mon') || 'Agregar monitor'); }
                if ($cargaPc.length)  { $cargaPc.addClass('d-none'); }
                if ($cargaMon.length) { $cargaMon.removeClass('d-none'); }
            } else if (tab === 'tablet' || tab === 'impresoras') {
                if ($btn.length) { $btn.addClass('d-none'); }
                if ($cargaPc.length)  { $cargaPc.addClass('d-none'); }
                if ($cargaMon.length) { $cargaMon.addClass('d-none'); }
            } else {
                if ($btn.length) { $btn.removeClass('d-none'); }
                $btn.attr('href', $btn.data('href-pc') || 'registrar_equipo.php');
                if ($label.length)  { $label.text($btn.data('label-pc') || 'Agregar PC'); }
                if ($cargaPc.length)  { $cargaPc.removeClass('d-none'); }
                if ($cargaMon.length) { $cargaMon.addClass('d-none'); }
            }
        }

        applyTab(cfg.tabActiva || 'pc');

        $('#inventarioTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
            const tab = $(this).data('tab');
            applyTab(tab);
            if (tab === 'monitores' && !tablaMonitores) {
                initTablaMonitores();
                bindMonitorFilters();
            }
        });
    }

    // =========================================================================
    // CLASE InventarioFunciones — todos los handlers de onclick van aquí
    // =========================================================================

    class InventarioFunciones {

        // --- VER DETALLE EQUIPO (ojo) ---
        static verDetalle(idEquipo) {
            const href = 'ver_equipo.php?id_equipo=' + idEquipo;
            const ui = setDetalleOffcanvasLoading('Detalle del equipo');
            if (!ui || !idEquipo) { window.location.href = href; return; }
            $.getJSON(window.INVENTARIO_CONFIG.endpoints.detalle, { id_equipo: idEquipo })
                .done(function (response) {
                    if (!response.ok) {
                        ui.$body.html('<div class="alert alert-warning mb-0">No fue posible cargar el detalle.</div>');
                        return;
                    }
                    renderDetalleOffcanvas(ui.$title, ui.$body,
                        response.equipo.nombre_equipo || 'Detalle del equipo',
                        response.detalle_html || response.html);
                })
                .fail(function (xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible cargar el detalle.';
                    ui.$body.html('<div class="alert alert-danger mb-3">' + escapeHtml(msg) + '</div><a href="' + escapeHtml(href) + '" class="btn btn-outline-primary">Abrir ficha completa</a>');
                });
        }

        // --- VER QR EQUIPO ---
        static verQrEquipo(idEquipo) {
            const row = obtenerFilaEquipo(idEquipo) || {};
            const url = buildQrFichaUrl(idEquipo);
            const nombre = row.nombre_equipo || ('Equipo #' + idEquipo);
            const serie = row.numero_serie ? ('Serie ' + row.numero_serie) : '';
            const modalEl = document.getElementById('modalQrEquipo');
            if (!modalEl) {
                window.open(url, '_blank');
                return;
            }

            $('#modalQrEquipoLabel').text('Código QR del equipo');
            $('#qrEquipoNombre').text(nombre);
            $('#qrEquipoSerie').text(serie);
            $('#btnAbrirFichaQr').attr('href', url);
            $('#btnImprimirQrIndividual').data('qr-url', url).data('qr-nombre', nombre).data('qr-serie', serie);
            renderQrEnContenedor(document.getElementById('qrEquipoCanvas'), url, 220);

            if (window.bootstrap && bootstrap.Modal && bootstrap.Modal.getOrCreateInstance) {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            } else {
                modalEl.classList.add('show');
                modalEl.style.display = 'block';
            }
        }

        static imprimirQrIndividual(btn) {
            const $btn = $(btn);
            const url = $btn.data('qr-url') || '';
            const nombre = $btn.data('qr-nombre') || 'Equipo';
            const serie = $btn.data('qr-serie') || '';
            const canvas = document.querySelector('#qrEquipoCanvas canvas');
            if (!url || !canvas) { return; }
            const img = canvas.toDataURL('image/png');
            const win = window.open('', '_blank', 'width=520,height=680');
            if (!win) { return; }
            win.document.write(
                '<!doctype html><html><head><meta charset="utf-8"><title>QR ' + escapeHtml(nombre) + '</title>' +
                '<style>body{font-family:Arial,sans-serif;margin:24px;color:#102a43}.label{width:260px;border:1px solid #d9e2ec;border-radius:10px;padding:16px;text-align:center}.label img{width:180px;height:180px}.name{font-weight:700;margin-top:10px}.meta{font-size:12px;color:#64748b;margin-top:4px;word-break:break-all}@media print{body{margin:0}.label{border:0}}</style>' +
                '</head><body><div class="label"><img src="' + img + '" alt="QR"><div class="name">' + escapeHtml(nombre) + '</div><div class="meta">' + escapeHtml(serie) + '</div></div><script>window.onload=function(){window.print();};<\/script></body></html>'
            );
            win.document.close();
        }

        // --- VER DETALLE MONITOR (ojo) ---
        static verDetalleMonitor(idMonitor) {
            const href = 'ver_monitor.php?id_monitor=' + idMonitor;
            const ui = setDetalleOffcanvasLoading('Detalle del monitor');
            if (!ui || !idMonitor) { window.location.href = href; return; }
            $.getJSON(window.INVENTARIO_CONFIG.endpoints.detalleMonitor, { id_monitor: idMonitor })
                .done(function (response) {
                    if (!response.ok) {
                        ui.$body.html('<div class="alert alert-warning mb-0">No fue posible cargar el detalle.</div>');
                        return;
                    }
                    const nombre = response.monitor && response.monitor.nombre_monitor ? response.monitor.nombre_monitor : 'Detalle del monitor';
                    renderDetalleOffcanvas(ui.$title, ui.$body, nombre, response.detalle_html || response.html);
                })
                .fail(function (xhr) {
                    const msg = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible cargar el detalle.';
                    ui.$body.html('<div class="alert alert-danger mb-3">' + escapeHtml(msg) + '</div><a href="' + escapeHtml(href) + '" class="btn btn-outline-primary">Abrir ficha completa</a>');
                });
        }

        // --- CAMBIAR ESTADO EQUIPO (select inline) ---
        static cambiarEstadoEquipo(selectEl) {
            const idEquipo = parseInt(selectEl.getAttribute('data-id'), 10);
            const idEstado = parseInt(selectEl.value, 10);

            selectEl.disabled = true;
            $.post(window.INVENTARIO_CONFIG.endpoints.cambiarEstado, { id_equipo: idEquipo, id_estado: idEstado }, null, 'json')
                .done(function (response) {
                    selectEl.disabled = false;
                    if (!response.ok) {
                        Swal.fire('Error', response.mensaje || 'No fue posible actualizar el estado.', 'error');
                        loadInventario();
                    }
                })
                .fail(function () {
                    selectEl.disabled = false;
                    Swal.fire('Error', 'No fue posible actualizar el estado.', 'error');
                    loadInventario();
                });
        }

        // --- ELIMINAR EQUIPO ---
        static eliminarEquipo(idEquipo) {
            Swal.fire({
                title: '¿Eliminar equipo?',
                text: 'El equipo dejará de aparecer en el inventario, pero no se borrará de la base de datos.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                customClass: {
                    popup: 'inv-swal-popup',
                    icon: 'inv-swal-icon',
                    title: 'inv-swal-title',
                    htmlContainer: 'inv-swal-text',
                    actions: 'inv-swal-actions',
                    confirmButton: 'btn btn-danger inv-swal-confirm',
                    cancelButton: 'btn btn-light border inv-swal-cancel'
                }
            }).then(function (result) {
                if (!result.isConfirmed) { return; }
                $.post(window.INVENTARIO_CONFIG.endpoints.eliminar, { id_equipo: idEquipo }, null, 'json')
                    .done(function (response) {
                        Swal.fire('Eliminado', response.mensaje, 'success');
                        loadInventario();
                    })
                    .fail(function (xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible eliminar el equipo.';
                        Swal.fire('Error', msg, 'error');
                    });
            });
        }

        // --- LIBERAR EQUIPO ---
        static liberarEquipo(idEquipo) {
            Swal.fire({
                title: '¿Liberar este equipo?',
                text: 'Se quitará el usuario asignado y quedará disponible.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, liberar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.isConfirmed) { return; }
                $.post(window.INVENTARIO_CONFIG.endpoints.liberarEquipo, { id_equipo: idEquipo }, null, 'json')
                    .done(function (response) {
                        Swal.fire('Liberado', response.mensaje, 'success');
                        loadInventario();
                    })
                    .fail(function (xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible liberar el equipo.';
                        Swal.fire('Error', msg, 'error');
                    });
            });
        }

        // --- VER FOTOS EQUIPO ---
        static verFotosEquipo(idEquipo) {
            const $modal = $('#modalGaleriaEquipo');
            const $title = $('#modalGaleriaEquipoLabel');
            const $body  = $('#modalGaleriaBody');
            if (!$modal.length || !idEquipo) { return; }
            $title.text('Cargando imágenes...');
            $body.html('<div class="text-center py-5 text-muted">Cargando galería...</div>');
            new bootstrap.Modal($modal[0]).show();
            $.getJSON(window.INVENTARIO_CONFIG.endpoints.detalle, { id_equipo: idEquipo })
                .done(function (response) {
                    $title.text(response.ok ? ('Imágenes de ' + (response.equipo.nombre_equipo || 'equipo')) : 'Galería del equipo');
                    $body.html(response.ok
                        ? (response.galeria_html || '<div class="alert alert-light border mb-0">Sin imágenes registradas.</div>')
                        : '<div class="alert alert-warning mb-0">No fue posible cargar las imágenes.</div>');
                })
                .fail(function () {
                    $title.text('Galería del equipo');
                    $body.html('<div class="alert alert-danger mb-0">No fue posible cargar la galería.</div>');
                });
        }

        // --- ELIMINAR MONITOR ---
        static eliminarMonitor(idMonitor) {
            Swal.fire({
                title: 'Eliminar monitor',
                text: 'Esta acción realiza una eliminación lógica del monitor.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.isConfirmed) { return; }
                $.post(window.INVENTARIO_CONFIG.endpoints.eliminarMonitor, { id_monitor: idMonitor }, null, 'json')
                    .done(function (response) {
                        Swal.fire('Eliminado', response.mensaje, 'success');
                        loadMonitores();
                    })
                    .fail(function (xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible eliminar el monitor.';
                        Swal.fire('Error', msg, 'error');
                    });
            });
        }

        // --- LIBERAR MONITOR ---
        static liberarMonitor(idMonitor) {
            Swal.fire({
                title: '¿Liberar este monitor?',
                text: 'Se quitará el usuario asignado y quedará disponible.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, liberar',
                cancelButtonText: 'Cancelar'
            }).then(function (result) {
                if (!result.isConfirmed) { return; }
                $.post(window.INVENTARIO_CONFIG.endpoints.liberarMonitor, { id_monitor: idMonitor }, null, 'json')
                    .done(function (response) {
                        Swal.fire('Liberado', response.mensaje, 'success');
                        loadMonitores();
                    })
                    .fail(function (xhr) {
                        const msg = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible liberar el monitor.';
                        Swal.fire('Error', msg, 'error');
                    });
            });
        }

        // --- VER FOTOS MONITOR ---
        static verFotosMonitor(idMonitor) {
            const $modal = $('#modalGaleriaMonitor');
            const $title = $('#modalGaleriaMonitorLabel');
            const $body  = $('#modalGaleriaMonitorBody');
            if (!$modal.length || !idMonitor) { return; }
            $title.text('Cargando imágenes...');
            $body.html('<div class="text-center py-5 text-muted">Cargando galería...</div>');
            new bootstrap.Modal($modal[0]).show();
            const endpoint = window.INVENTARIO_CONFIG.endpoints.fotosMonitor || window.INVENTARIO_CONFIG.endpoints.detalleMonitor;
            $.getJSON(endpoint, { id_monitor: idMonitor })
                .done(function (response) {
                    $title.text(response.ok ? ('Imágenes de ' + (response.monitor.nombre_monitor || 'monitor')) : 'Galería');
                    $body.html(response.ok
                        ? (response.galeria_html || '<div class="alert alert-light border mb-0">Sin imágenes registradas.</div>')
                        : '<div class="alert alert-warning mb-0">No fue posible cargar las imágenes.</div>');
                })
                .fail(function () {
                    $title.text('Galería del monitor');
                    $body.html('<div class="alert alert-danger mb-0">No fue posible cargar la galería.</div>');
                });
        }
    }

    // Exponer globalmente para los onclick del HTML
    window.InventarioFunciones = InventarioFunciones;

    // =========================================================================
    // INICIALIZACIÓN
    // =========================================================================

    $(function () {
        window.addEventListener('beforeprint', function () { prepareQrForPrint(document); });

        const _cfg = window.INVENTARIO_CONFIG || {};
        if (_cfg.idColegioRestringido > 0) {
            updateUbicacionSelect(_cfg.idColegioRestringido);
        }

        initTabla();
        bindFilters();
        bindOffcanvasClose();
        bindModalCloseFallback();
        bindFormAjax();
        bindNombreEquipoAuto();
        bindRepeater();
        bindPreview();
        bindImprimirQrMasivo();
        initQrBlocks(document);
        initTabBehavior();

        if ((window.INVENTARIO_CONFIG || {}).tabActiva === 'monitores') {
            initTablaMonitores();
            bindMonitorFilters();
        }
    });

})(jQuery);
