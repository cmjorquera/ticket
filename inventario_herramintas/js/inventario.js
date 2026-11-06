(function ($) {
    'use strict';

    let tablaInventario = null;
    let cargaMasivaPayload = [];

    function buildAccionButtons(row) {
        const id = parseInt(row.id_herramienta, 10);
        return `
            <div class="inv-action-buttons d-flex flex-wrap gap-2">
                <a href="ver_herramienta.php?id_herramienta=${id}" class="btn btn-sm inv-btn-action inv-btn-view" title="Ver detalle" aria-label="Ver detalle"><i class="bi bi-eye"></i></a>
                <a href="editar_herramienta.php?id_herramienta=${id}" class="btn btn-sm inv-btn-action inv-btn-edit" title="Editar" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                <button type="button" class="btn btn-sm inv-btn-action inv-btn-photo btnVerFotos" data-id="${id}" title="Fotos" aria-label="Fotos"><i class="bi bi-images"></i></button>
                <button type="button" class="btn btn-sm inv-btn-action inv-btn-delete btnEliminarLogico" data-id="${id}" title="Cambiar a baja" aria-label="Cambiar a baja"><i class="bi bi-trash"></i></button>
            </div>`;
    }

    function collectFilters() {
        return {
            id_colegio: $('#filtroColegio').val() || '',
            id_estado: $('#filtroEstado').val() || '',
            categoria: $('#filtroCategoria').val() || '',
            id_usuario_asignado: $('#filtroUsuario').val() || '',
            busqueda: $('#filtroBusqueda').val() || ''
        };
    }

    function initTabla() {
        const $tabla = $('#tablaInventario');
        if (!$tabla.length) { return; }
        tablaInventario = $tabla.DataTable({
            data: [],
            responsive: true,
            pageLength: 10,
            paging: true,
            info: true,
            lengthChange: false,
            pagingType: 'simple_numbers',
            dom: "<'row align-items-center mb-3'<'col-md-6'i><'col-md-6 text-md-end'p>>rt<'row align-items-center mt-3'<'col-md-6'i><'col-md-6 text-md-end'p>>",
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ herramientas',
                infoEmpty: 'Sin herramientas para mostrar',
                paginate: { previous: 'Anterior', next: 'Siguiente' }
            },
            columns: [
                { data: 'id_herramienta' },
                { data: null, render: function (data) { return `<div class="fw-semibold">${escapeHtml(data.nombre_herramienta)}</div><small class="text-muted">${escapeHtml(data.marca || '')} ${escapeHtml(data.modelo || '')}<br>${escapeHtml(data.ubicacion || '')}</small>`; } },
                { data: 'nom_colegio', render: escapeHtml },
                { data: 'categoria', render: escapeHtml },
                { data: 'numero_serie', render: escapeHtml },
                { data: 'cantidad' },
                { data: 'usuario_asignado', render: function (data) { return escapeHtml(data || 'Sin asignar'); } },
                { data: 'badge_estado' },
                { data: null, orderable: false, searchable: false, render: function (row) { return buildAccionButtons(row); } }
            ]
        });
        loadInventario();
    }

    function loadInventario() {
        if (!window.INVENTARIO_CONFIG || !window.INVENTARIO_CONFIG.endpoints) { return; }
        $.getJSON(window.INVENTARIO_CONFIG.endpoints.listar, collectFilters()).done(function (response) {
            if (!response.ok) { return; }
            $('#contenedorResumen').html(response.resumen_html);
            if (tablaInventario) { tablaInventario.clear().rows.add(response.data || []).draw(); }
        }).fail(function () {
            Swal.fire('Error', 'No fue posible cargar el inventario.', 'error');
        });
    }

    function bindFilters() {
        $('#filtroColegio, #filtroEstado, #filtroCategoria, #filtroUsuario').on('change', loadInventario);
        $('#filtroBusqueda').on('keyup', debounce(loadInventario, 350));
    }

    function bindDelete() {
        $(document).on('click', '.btnEliminarLogico', function () {
            const idHerramienta = $(this).data('id');
            Swal.fire({
                title: 'Cambiar herramienta a baja',
                text: 'Esta accion realiza una baja logica cambiando el estado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, cambiar estado',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) { return; }
                $.post(window.INVENTARIO_CONFIG.endpoints.eliminar, { id_herramienta: idHerramienta, id_estado: 4 }, null, 'json')
                    .done(function (response) {
                        Swal.fire('Actualizado', response.mensaje, 'success');
                        loadInventario();
                    })
                    .fail(function (xhr) {
                        const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible actualizar la herramienta.';
                        Swal.fire('Error', mensaje, 'error');
                    });
            });
        });
    }

    function bindGaleriaModal() {
        $(document).on('click', '.btnVerFotos', function () {
            const idHerramienta = $(this).data('id');
            const $modal = $('#modalGaleriaEquipo');
            const $title = $('#modalGaleriaEquipoLabel');
            const $body = $('#modalGaleriaBody');
            if (!$modal.length || !idHerramienta) { return; }
            $title.text('Cargando imagenes...');
            $body.html('<div class="text-center py-5 text-muted">Cargando galeria...</div>');
            const modalInstance = new bootstrap.Modal($modal[0]);
            modalInstance.show();
            $.getJSON(window.INVENTARIO_CONFIG.endpoints.detalle, { id_herramienta: idHerramienta }).done(function (response) {
                if (!response.ok) {
                    $title.text('Galeria de la herramienta');
                    $body.html('<div class="alert alert-warning mb-0">No fue posible cargar las imagenes.</div>');
                    return;
                }
                $title.text(`Imagenes de ${response.equipo.nombre_herramienta || 'herramienta'}`);
                $body.html(response.galeria_html || '<div class="alert alert-light border mb-0">Sin imagenes registradas.</div>');
            }).fail(function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible cargar la galeria.';
                $title.text('Galeria de la herramienta');
                $body.html(`<div class="alert alert-danger mb-0">${escapeHtml(mensaje)}</div>`);
            });
        });
    }

    function bindModalCloseFallback() {
        $(document).on('click', '.modal .btn-close', function () {
            const modalElement = this.closest('.modal');
            if (!modalElement || !window.bootstrap || !bootstrap.Modal) { return; }
            const instance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
            instance.hide();
        });
    }

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
                Swal.fire({ icon: 'success', title: 'Guardado correctamente', text: response.mensaje }).then(() => {
                    if (response.redirect) { window.location.href = response.redirect; }
                });
            }).fail(function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible guardar la herramienta.';
                Swal.fire('Error', mensaje, 'error');
            });
        });
    }

    function bindRepeater() {
        $('#btnAgregarPrestamo').on('click', function () {
            const index = $('#contenedorPrestamos .prestamo-item').length;
            $('#contenedorPrestamos').append(`<div class="inv-repeat-card prestamo-item"><div class="row g-3"><div class="col-md-3"><label class="form-label">Entrega</label><input type="text" name="prestamo[${index}][responsable_entrega]" class="form-control"></div><div class="col-md-3"><label class="form-label">Recibe</label><input type="text" name="prestamo[${index}][responsable_recibe]" class="form-control"></div><div class="col-md-2"><label class="form-label">Salida</label><input type="date" name="prestamo[${index}][fecha_salida]" class="form-control"></div><div class="col-md-2"><label class="form-label">Devolucion prevista</label><input type="date" name="prestamo[${index}][fecha_devolucion_prevista]" class="form-control"></div><div class="col-md-2"><label class="form-label">Devolucion real</label><input type="date" name="prestamo[${index}][fecha_devolucion_real]" class="form-control"></div><div class="col-md-3"><label class="form-label">Estado prestamo</label><input type="text" name="prestamo[${index}][estado_prestamo]" class="form-control"></div><div class="col-md-9"><label class="form-label">Observacion</label><input type="text" name="prestamo[${index}][observacion]" class="form-control"></div></div><button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button></div>`);
        });
        $('#btnAgregarMantencion').on('click', function () {
            const index = $('#contenedorMantenciones .mantencion-item').length;
            $('#contenedorMantenciones').append(`<div class="inv-repeat-card mantencion-item"><div class="row g-3"><div class="col-md-2"><label class="form-label">Fecha</label><input type="date" name="mantencion[${index}][fecha_mantencion]" class="form-control"></div><div class="col-md-3"><label class="form-label">Tipo</label><input type="text" name="mantencion[${index}][tipo_mantencion]" class="form-control"></div><div class="col-md-3"><label class="form-label">Proveedor o tecnico</label><input type="text" name="mantencion[${index}][proveedor]" class="form-control"></div><div class="col-md-2"><label class="form-label">Costo</label><input type="number" step="0.01" name="mantencion[${index}][costo]" class="form-control"></div><div class="col-md-2"><label class="form-label">Proxima fecha</label><input type="date" name="mantencion[${index}][proxima_fecha]" class="form-control"></div><div class="col-md-12"><label class="form-label">Observacion</label><input type="text" name="mantencion[${index}][observacion]" class="form-control"></div></div><button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button></div>`);
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

    function bindCargaMasiva() {
        const $modal = $('#modalCargaMasivaHerramientas');
        const $form = $('#formCargaMasivaHerramientas');
        if (!$modal.length || !$form.length) { return; }

        $('#btnCargaMasivaHerramientas').on('click', function () {
            resetCargaMasiva();
            new bootstrap.Modal($modal[0]).show();
        });

        $('#btnLimpiarCargaMasiva').on('click', function () {
            resetCargaMasiva();
        });

        $form.on('submit', function (e) {
            e.preventDefault();
            const archivo = $('#archivoExcelHerramientas')[0];
            if (!archivo || !archivo.files || !archivo.files.length) {
                Swal.fire('Archivo requerido', 'Selecciona un Excel antes de continuar.', 'warning');
                return;
            }
            const formData = new FormData(this);
            const $btn = $('#btnAnalizarCargaMasiva');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Analizando...');
            $.ajax({
                url: window.INVENTARIO_CONFIG.endpoints.previewCargaMasiva,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json'
            }).done(function (response) {
                cargaMasivaPayload = response.filas_validas || [];
                renderCargaMasivaPreview(response);
                $('#btnGuardarCargaMasiva').prop('disabled', cargaMasivaPayload.length === 0);
            }).fail(function (xhr) {
                cargaMasivaPayload = [];
                $('#btnGuardarCargaMasiva').prop('disabled', true);
                const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible leer el archivo.';
                $('#resumenCargaMasiva').html(`<div class="alert alert-danger mb-0">${escapeHtml(mensaje)}</div>`);
                $('#tablaPreviewCargaMasiva').html('<div class="alert alert-light border mb-0">No hay previsualizacion disponible.</div>');
            }).always(function () {
                $btn.prop('disabled', false).html('<i class="bi bi-search me-1"></i>Previsualizar archivo');
            });
        });

        $('#btnGuardarCargaMasiva').on('click', function () {
            if (!cargaMasivaPayload.length) {
                Swal.fire('Sin registros validos', 'Primero revisa un archivo con filas validas.', 'info');
                return;
            }
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Cargando...');
            $.ajax({
                url: window.INVENTARIO_CONFIG.endpoints.guardarCargaMasiva,
                method: 'POST',
                data: { filas: JSON.stringify(cargaMasivaPayload) },
                dataType: 'json'
            }).done(function (response) {
                const mensaje = response.mensaje || 'Carga masiva completada.';
                Swal.fire('Carga completada', mensaje, 'success');
                resetCargaMasiva();
                const modalInstance = bootstrap.Modal.getInstance($modal[0]);
                if (modalInstance) { modalInstance.hide(); }
                loadInventario();
            }).fail(function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible guardar la carga masiva.';
                Swal.fire('Error', mensaje, 'error');
            }).always(function () {
                $btn.prop('disabled', cargaMasivaPayload.length === 0).html('<i class="bi bi-database-add me-1"></i>Cargar herramientas');
            });
        });
    }

    function renderCargaMasivaPreview(response) {
        const resumen = response.resumen || {};
        const filas = response.filas_preview || [];
        $('#resumenCargaMasiva').html(`
            <div class="row g-3">
                <div class="col-md-4"><div class="inv-massive-card"><span>Total leidas</span><strong>${parseInt(resumen.total || 0, 10)}</strong></div></div>
                <div class="col-md-4"><div class="inv-massive-card inv-massive-card-success"><span>Validas</span><strong>${parseInt(resumen.validas || 0, 10)}</strong></div></div>
                <div class="col-md-4"><div class="inv-massive-card inv-massive-card-danger"><span>Con observaciones</span><strong>${parseInt(resumen.invalidas || 0, 10)}</strong></div></div>
            </div>
        `);

        if (!filas.length) {
            $('#tablaPreviewCargaMasiva').html('<div class="alert alert-light border mb-0">El archivo no trajo filas con datos.</div>');
            return;
        }

        const rowsHtml = filas.map(function (fila) {
            const clase = fila.ok ? 'table-success' : 'table-warning';
            const estado = fila.ok ? '<span class="badge text-bg-success">Lista</span>' : '<span class="badge text-bg-warning">Revisar</span>';
            const errores = (fila.errores || []).length ? fila.errores.map(escapeHtml).join('<br>') : '<span class="text-muted">Sin observaciones</span>';
            return `
                <tr class="${clase}">
                    <td>${escapeHtml(fila.linea || '')}</td>
                    <td>${escapeHtml(fila.colegio || '')}</td>
                    <td>${escapeHtml(fila.nombre_herramienta || '')}</td>
                    <td>${escapeHtml(fila.numero_serie || '')}</td>
                    <td>${escapeHtml(fila.categoria || '')}</td>
                    <td>${escapeHtml(String(fila.cantidad || ''))}</td>
                    <td>${escapeHtml(fila.estado || '')}</td>
                    <td>${estado}</td>
                    <td class="small">${errores}</td>
                </tr>
            `;
        }).join('');

        $('#tablaPreviewCargaMasiva').html(`
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Fila</th>
                            <th>Colegio</th>
                            <th>Herramienta</th>
                            <th>Serie</th>
                            <th>Categoria</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                            <th>Resultado</th>
                            <th>Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>
            </div>
        `);
    }

    function resetCargaMasiva() {
        cargaMasivaPayload = [];
        $('#formCargaMasivaHerramientas')[0].reset();
        $('#btnGuardarCargaMasiva').prop('disabled', true).html('<i class="bi bi-database-add me-1"></i>Cargar herramientas');
        $('#resumenCargaMasiva').empty();
        $('#tablaPreviewCargaMasiva').html('<div class="alert alert-light border mb-0">Aun no se ha analizado ningun archivo.</div>');
    }

    function initExtras() {
        if (window.QRCode && window.INVENTARIO_CONFIG && window.INVENTARIO_CONFIG.qrText && document.getElementById('qrEquipo')) {
            new QRCode(document.getElementById('qrEquipo'), { text: window.INVENTARIO_CONFIG.qrText, width: 180, height: 180 });
        }
    }

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
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, delay);
        };
    }

    $(function () {
        initTabla();
        bindFilters();
        bindDelete();
        bindGaleriaModal();
        bindModalCloseFallback();
        bindFormAjax();
        bindRepeater();
        bindPreview();
        bindCargaMasiva();
        initExtras();
    });
})(jQuery);
