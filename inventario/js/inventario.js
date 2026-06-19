(function ($) {
    'use strict';

    let tablaInventario = null;
    let tablaMonitores  = null;

    function buildAccionButtons(row) {
        const id = parseInt(row.id_equipo, 10);
        const tieneAsignado = row.id_usuario_asignado && parseInt(row.id_usuario_asignado, 10) > 0;
        const btnLiberar = tieneAsignado
            ? `<button type="button" class="btn btn-sm inv-btn-action inv-btn-release btnLiberarEquipo" data-id="${id}" title="Liberar equipo" aria-label="Liberar equipo">
                   <i class="bi bi-person-x"></i>
               </button>`
            : '';
        return `
            <div class="inv-action-buttons d-flex flex-wrap gap-2">
                <a href="ver_equipo.php?id_equipo=${id}" class="btn btn-sm inv-btn-action inv-btn-view" title="Ver detalle" aria-label="Ver detalle">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="editar_equipo.php?id_equipo=${id}" class="btn btn-sm inv-btn-action inv-btn-edit" title="Editar" aria-label="Editar">
                    <i class="bi bi-pencil"></i>
                </a>
                <button type="button" class="btn btn-sm inv-btn-action inv-btn-photo btnVerFotos" data-id="${id}" title="Fotos" aria-label="Fotos">
                    <i class="bi bi-images"></i>
                </button>
                ${btnLiberar}
                <button type="button" class="btn btn-sm inv-btn-action inv-btn-delete btnEliminarLogico" data-id="${id}" title="Cambiar a baja" aria-label="Cambiar a baja">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
    }

    function collectFilters() {
        return {
            id_colegio: $('#filtroColegio').val() || '',
            id_estado: $('#filtroEstado').val() || '',
            tipo_pc: $('#filtroTipo').val() || '',
            id_usuario_asignado: $('#filtroUsuario').val() || '',
            busqueda: $('#filtroBusqueda').val() || ''
        };
    }

    function initTabla() {
        const $tabla = $('#tablaInventario');
        if (!$tabla.length) {
            return;
        }

        tablaInventario = $tabla.DataTable({
            data: [],
            responsive: false,
            autoWidth: false,
            pageLength: 10,
            paging: true,
            info: true,
            lengthChange: false,
            pagingType: 'simple_numbers',
            dom: "<'row align-items-center mb-3'<'col-md-6'i><'col-md-6 text-md-end'p>>" +
                 "rt" +
                 "<'row align-items-center mt-3'<'col-md-6'i><'col-md-6 text-md-end'p>>",
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ equipos',
                infoEmpty: 'Sin equipos para mostrar',
                paginate: {
                    previous: 'Anterior',
                    next: 'Siguiente'
                }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        const pageInfo = new $.fn.dataTable.Api(meta.settings).page.info();
                        return pageInfo.start + meta.row + 1;
                    }
                },
                {
                    data: null,
                    render: function (data) {
                        return `<div class="fw-semibold">${escapeHtml(data.nombre_equipo)}</div><small class="text-muted">${escapeHtml(data.fabricante || '')} ${escapeHtml(data.producto || '')}</small>`;
                    }
                },
                { data: 'nom_colegio', render: escapeHtml },
                { data: 'tipo_pc', render: escapeHtml },
                { data: 'numero_serie', render: escapeHtml },
                {
                    data: 'usuario_asignado',
                    render: function (data) {
                        return escapeHtml(data || 'Sin asignar');
                    }
                },
                { data: 'badge_estado' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (row) {
                        return buildAccionButtons(row);
                    }
                }
            ]
        });

        loadInventario();

        // Recalcular anchos cuando el contenedor cambia de tamaño (toggle sidebar, resize)
        if (window.ResizeObserver) {
            const _adjustCols = debounce(function () {
                if (tablaInventario) { tablaInventario.columns.adjust(); }
            }, 130);
            const _wrapper = document.getElementById('tablaInventario_wrapper');
            if (_wrapper) { new ResizeObserver(_adjustCols).observe(_wrapper); }
        }
    }

    function loadInventario() {
        if (!window.INVENTARIO_CONFIG || !window.INVENTARIO_CONFIG.endpoints) {
            return;
        }

        // Refresca resumen y tabla con la misma respuesta AJAX para evitar consultas duplicadas desde la vista.
        $.getJSON(window.INVENTARIO_CONFIG.endpoints.listar, collectFilters())
            .done(function (response) {
                if (!response.ok) {
                    return;
                }
                $('#contenedorResumen').html(response.resumen_html);
                if (tablaInventario) {
                    tablaInventario.clear().rows.add(response.data || []).draw();
                }
            })
            .fail(function () {
                Swal.fire('Error', 'No fue posible cargar el inventario.', 'error');
            });
    }

    function bindFilters() {
        $('#filtroColegio, #filtroEstado, #filtroTipo, #filtroUsuario').on('change', loadInventario);
        $('#filtroBusqueda').on('keyup', debounce(loadInventario, 350));
    }

    function bindLiberarEquipo() {
        $(document).on('click', '.btnLiberarEquipo', function () {
            const idEquipo = $(this).data('id');
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
                        const msg = xhr.responseJSON && xhr.responseJSON.mensaje
                            ? xhr.responseJSON.mensaje : 'No fue posible liberar el equipo.';
                        Swal.fire('Error', msg, 'error');
                    });
            });
        });
    }

    function bindDelete() {
        $(document).on('click', '.btnEliminarLogico', function () {
            const idEquipo = $(this).data('id');
            Swal.fire({
                title: 'Cambiar equipo a baja',
                text: 'Esta accion realiza una eliminacion logica cambiando el estado del equipo.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, cambiar estado',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.post(window.INVENTARIO_CONFIG.endpoints.eliminar, { id_equipo: idEquipo, id_estado: 4 }, null, 'json')
                    .done(function (response) {
                        Swal.fire('Actualizado', response.mensaje, 'success');
                        loadInventario();
                    })
                    .fail(function (xhr) {
                        const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible actualizar el equipo.';
                        Swal.fire('Error', mensaje, 'error');
                    });
            });
        });
    }

    function bindGaleriaModal() {
        $(document).on('click', '.btnVerFotos', function () {
            const idEquipo = $(this).data('id');
            const $modal = $('#modalGaleriaEquipo');
            const $title = $('#modalGaleriaEquipoLabel');
            const $body = $('#modalGaleriaBody');

            if (!$modal.length || !idEquipo) {
                return;
            }

            $title.text('Cargando imagenes...');
            $body.html('<div class="text-center py-5 text-muted">Cargando galeria...</div>');

            const modalInstance = new bootstrap.Modal($modal[0]);
            modalInstance.show();

            $.getJSON(window.INVENTARIO_CONFIG.endpoints.detalle, { id_equipo: idEquipo })
                .done(function (response) {
                    if (!response.ok) {
                        $title.text('Galeria del equipo');
                        $body.html('<div class="alert alert-warning mb-0">No fue posible cargar las imagenes.</div>');
                        return;
                    }

                    $title.text(`Imagenes de ${response.equipo.nombre_equipo || 'equipo'}`);
                    $body.html(response.galeria_html || '<div class="alert alert-light border mb-0">Sin imagenes registradas.</div>');
                })
                .fail(function (xhr) {
                    const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible cargar la galeria.';
                    $title.text('Galeria del equipo');
                    $body.html(`<div class="alert alert-danger mb-0">${escapeHtml(mensaje)}</div>`);
                });
        });
    }

    function bindFormAjax() {
        const $form = $('#formInventario');
        if (!$form.length) {
            return;
        }

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
                Swal.fire({
                    icon: 'success',
                    title: 'Guardado correctamente',
                    text: response.mensaje
                }).then(() => {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                });
            }).fail(function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible guardar el equipo.';
                Swal.fire('Error', mensaje, 'error');
            });
        });
    }

    function bindRepeater() {
        $('#btnAgregarMemoria').on('click', function () {
            const index = $('#contenedorMemorias .memoria-item').length;
            $('#contenedorMemorias').append(`
                <div class="inv-repeat-card memoria-item">
                    <div class="row g-3">
                        <div class="col-md-2"><label class="form-label">Slot</label><input type="text" name="memoria[${index}][designacion_memoria]" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Formato</label><input type="text" name="memoria[${index}][formato_memoria]" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Tipo</label><input type="text" name="memoria[${index}][tipo_memoria]" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Tamano</label><input type="text" name="memoria[${index}][tamano_memoria]" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Frecuencia</label><input type="text" name="memoria[${index}][frecuencia_memoria]" class="form-control"></div>
                        <div class="col-md-1"><label class="form-label">Marca</label><input type="text" name="memoria[${index}][marca_memoria]" class="form-control"></div>
                        <div class="col-md-1"><label class="form-label">Orden</label><input type="number" name="memoria[${index}][orden_memoria]" class="form-control" value="${index + 1}"></div>
                    </div>
                    <button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button>
                </div>
            `);
        });

        $('#btnAgregarMonitorPC').on('click', function () {
            const index = $('#contenedorMonitores .monitor-item').length;
            $('#contenedorMonitores').append(`
                <div class="inv-repeat-card monitor-item">
                    <div class="row g-3">
                        <div class="col-md-3"><label class="form-label">Modelo</label><input type="text" name="monitor[${index}][modelo_monitor]" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Codigo</label><input type="text" name="monitor[${index}][codigo_monitor]" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Serie</label><input type="text" name="monitor[${index}][serie_monitor]" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Tamano</label><input type="text" name="monitor[${index}][tamano_monitor]" class="form-control"></div>
                        <div class="col-md-2"><label class="form-label">Resolucion</label><input type="text" name="monitor[${index}][resolucion_monitor]" class="form-control"></div>
                        <div class="col-md-1"><label class="form-label">Orden</label><input type="number" name="monitor[${index}][orden_monitor]" class="form-control" value="${index + 1}"></div>
                    </div>
                    <button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button>
                </div>
            `);
        });

        $(document).on('click', '.btnEliminarFila', function () {
            $(this).closest('.inv-repeat-card').remove();
        });
    }

    function bindPreview() {
        $('#fotos_equipo').on('change', function () {
            const container = $('#previewFotos');
            container.empty();
            Array.from(this.files || []).forEach(file => {
                const reader = new FileReader();
                reader.onload = function (e) {
                    container.append(`<div class="inv-preview-item"><img src="${e.target.result}" alt="${escapeHtml(file.name)}"><span>${escapeHtml(file.name)}</span></div>`);
                };
                reader.readAsDataURL(file);
            });
        });

    }

    function initExtras() {
        if (window.QRCode && window.INVENTARIO_CONFIG && window.INVENTARIO_CONFIG.qrText && document.getElementById('qrEquipo')) {
            new QRCode(document.getElementById('qrEquipo'), {
                text: window.INVENTARIO_CONFIG.qrText,
                width: 180,
                height: 180
            });
        }
    }

    function escapeHtml(value) {
        if (value === null || value === undefined) {
            return '';
        }
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function debounce(fn, delay) {
        let timer = null;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, delay);
        };
    }

    // =========================================================================
    // MONITORES
    // =========================================================================

    function buildMonitorAccionButtons(row) {
        const id = parseInt(row.id_monitor, 10);
        const tieneAsignado = row.id_usuario_asignado && parseInt(row.id_usuario_asignado, 10) > 0;
        const btnLiberar = tieneAsignado
            ? `<button type="button" class="btn btn-sm inv-btn-action inv-btn-release btnLiberarMonitor" data-id="${id}" title="Liberar monitor" aria-label="Liberar monitor">
                   <i class="bi bi-person-x"></i>
               </button>`
            : '';
        return `
            <div class="inv-action-buttons d-flex flex-wrap gap-2">
                <a href="ver_monitor.php?id_monitor=${id}" class="btn btn-sm inv-btn-action inv-btn-view" title="Ver detalle" aria-label="Ver detalle">
                    <i class="bi bi-eye"></i>
                </a>
                <a href="editar_monitor.php?id_monitor=${id}" class="btn btn-sm inv-btn-action inv-btn-edit" title="Editar" aria-label="Editar">
                    <i class="bi bi-pencil"></i>
                </a>
                <button type="button" class="btn btn-sm inv-btn-action inv-btn-photo btnVerFotosMonitor" data-id="${id}" title="Fotos" aria-label="Fotos">
                    <i class="bi bi-images"></i>
                </button>
                ${btnLiberar}
                <button type="button" class="btn btn-sm inv-btn-action inv-btn-delete btnEliminarMonitor" data-id="${id}" title="Eliminar" aria-label="Eliminar">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
    }

    function collectMonitorFilters() {
        return {
            id_colegio:          $('#filtroColegioMon').val()  || '',
            id_estado:           $('#filtroEstadoMon').val()   || '',
            id_usuario_asignado: $('#filtroUsuarioMon').val()  || '',
            busqueda:            $('#filtroBusquedaMon').val() || ''
        };
    }

    function initTablaMonitores() {
        const $tabla = $('#tablaMonitores');
        if (!$tabla.length) {
            return;
        }
        tablaMonitores = $tabla.DataTable({
            data: [],
            responsive: false,
            autoWidth: false,
            pageLength: 10,
            paging: true,
            info: true,
            lengthChange: false,
            pagingType: 'simple_numbers',
            dom: "<'row align-items-center mb-3'<'col-md-6'i><'col-md-6 text-md-end'p>>" +
                 "rt" +
                 "<'row align-items-center mt-3'<'col-md-6'i><'col-md-6 text-md-end'p>>",
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ monitores',
                infoEmpty: 'Sin monitores para mostrar',
                paginate: { previous: 'Anterior', next: 'Siguiente' }
            },
            columns: [
                {
                    data: null,
                    orderable: false,
                    searchable: false,
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
                        return `<div class="fw-semibold">${nombre}</div><small class="text-muted">${sub}</small>`;
                    }
                },
                { data: 'nom_colegio',   render: escapeHtml },
                {
                    data: 'nombre_ubicacion',
                    render: function (data, type, row) {
                        if (!data) return '<span class="text-muted">Sin ubicación</span>';
                        const tip = row.tipo_ubicacion ? escapeHtml(row.tipo_ubicacion) + ' — ' : '';
                        return tip + escapeHtml(data);
                    }
                },
                { data: 'numero_serie',  render: escapeHtml },
                {
                    data: 'usuario_asignado',
                    render: function (data) { return escapeHtml(data || 'Sin asignar'); }
                },
                { data: 'badge_estado' },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function (row) { return buildMonitorAccionButtons(row); }
                }
            ]
        });

        loadMonitores();

        if (window.ResizeObserver) {
            const _adj = debounce(function () {
                if (tablaMonitores) { tablaMonitores.columns.adjust(); }
            }, 130);
            const _wr = document.getElementById('tablaMonitores_wrapper');
            if (_wr) { new ResizeObserver(_adj).observe(_wr); }
        }
    }

    function loadMonitores() {
        if (!window.INVENTARIO_CONFIG || !window.INVENTARIO_CONFIG.endpoints) {
            return;
        }
        $.getJSON(window.INVENTARIO_CONFIG.endpoints.listarMonitores, collectMonitorFilters())
            .done(function (response) {
                if (!response.ok) { return; }
                $('#contenedorResumenMonitores').html(response.resumen_html);
                if (tablaMonitores) {
                    tablaMonitores.clear().rows.add(response.data || []).draw();
                }
            })
            .fail(function () {
                Swal.fire('Error', 'No fue posible cargar los monitores.', 'error');
            });
    }

    function bindMonitorFilters() {
        $('#filtroColegioMon, #filtroEstadoMon, #filtroUsuarioMon').on('change', loadMonitores);
        $('#filtroBusquedaMon').on('keyup', debounce(loadMonitores, 350));
    }

    function bindLiberarMonitor() {
        $(document).on('click', '.btnLiberarMonitor', function () {
            const idMonitor = $(this).data('id');
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
                        const msg = xhr.responseJSON && xhr.responseJSON.mensaje
                            ? xhr.responseJSON.mensaje : 'No fue posible liberar el monitor.';
                        Swal.fire('Error', msg, 'error');
                    });
            });
        });
    }

    function bindMonitorDelete() {
        $(document).on('click', '.btnEliminarMonitor', function () {
            const idMonitor = $(this).data('id');
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
                        const msg = xhr.responseJSON && xhr.responseJSON.mensaje
                            ? xhr.responseJSON.mensaje : 'No fue posible eliminar el monitor.';
                        Swal.fire('Error', msg, 'error');
                    });
            });
        });
    }

    function bindMonitorGaleriaModal() {
        $(document).on('click', '.btnVerFotosMonitor', function () {
            const idMonitor = $(this).data('id');
            const $modal    = $('#modalGaleriaMonitor');
            const $title    = $('#modalGaleriaMonitorLabel');
            const $body     = $('#modalGaleriaMonitorBody');

            if (!$modal.length || !idMonitor) { return; }

            $title.text('Cargando imágenes...');
            $body.html('<div class="text-center py-5 text-muted">Cargando galería...</div>');

            new bootstrap.Modal($modal[0]).show();

            $.getJSON(window.INVENTARIO_CONFIG.endpoints.detalleMonitor, { id_monitor: idMonitor })
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
        });
    }

    function initTabBehavior() {
        const cfg = window.INVENTARIO_CONFIG || {};
        const $btn      = $('#btnAgregarPrincipal');
        const $label    = $('#btnAgregarLabel');
        const $cargaPc  = $('.inv-btn-carga-masiva');
        const $cargaMon = $('.inv-btn-carga-masiva-mon');

        function applyTab(tab) {
            if (!$btn.length) { return; }
            if (tab === 'monitores') {
                $btn.attr('href', $btn.data('href-mon') || 'registrar_monitor.php');
                if ($label.length) { $label.text($btn.data('label-mon') || 'Agregar monitor'); }
                if ($cargaPc.length)  { $cargaPc.addClass('d-none'); }
                if ($cargaMon.length) { $cargaMon.removeClass('d-none'); }
            } else {
                $btn.attr('href', $btn.data('href-pc') || 'registrar_equipo.php');
                if ($label.length) { $label.text($btn.data('label-pc') || 'Agregar PC'); }
                if ($cargaPc.length)  { $cargaPc.removeClass('d-none'); }
                if ($cargaMon.length) { $cargaMon.addClass('d-none'); }
            }
        }

        // Set initial state from server-rendered tab
        applyTab(cfg.tabActiva || 'pc');

        // React to tab changes
        $('#inventarioTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function () {
            const tab = $(this).data('tab');
            applyTab(tab);
            // Lazy-init monitor table on first show
            if (tab === 'monitores' && !tablaMonitores) {
                initTablaMonitores();
                bindMonitorFilters();
                bindLiberarMonitor();
                bindMonitorDelete();
                bindMonitorGaleriaModal();
            }
        });
    }

    $(function () {
        initTabla();
        bindFilters();
        bindLiberarEquipo();
        bindDelete();
        bindGaleriaModal();
        bindFormAjax();
        bindRepeater();
        initExtras();
        initTabBehavior();

        // Si la tab activa al cargar la pagina es monitores, inicializar tabla inmediatamente
        if ((window.INVENTARIO_CONFIG || {}).tabActiva === 'monitores') {
            initTablaMonitores();
            bindMonitorFilters();
            bindLiberarMonitor();
            bindMonitorDelete();
            bindMonitorGaleriaModal();
        }
    });
})(jQuery);
