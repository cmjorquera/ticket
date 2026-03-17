(function ($) {
    'use strict';

    let tablaInventario = null;

    function buildAccionButtons(row) {
        const id = parseInt(row.id_producto, 10);
        return `
            <div class="inv-action-buttons d-flex flex-wrap gap-2">
                <a href="ver_producto.php?id_producto=${id}" class="btn btn-sm inv-btn-action inv-btn-view" title="Ver detalle" aria-label="Ver detalle"><i class="bi bi-eye"></i></a>
                <a href="editar_producto.php?id_producto=${id}" class="btn btn-sm inv-btn-action inv-btn-edit" title="Editar" aria-label="Editar"><i class="bi bi-pencil"></i></a>
                <button type="button" class="btn btn-sm inv-btn-action inv-btn-delete btnEliminarLogico" data-id="${id}" title="Desactivar" aria-label="Desactivar"><i class="bi bi-trash"></i></button>
            </div>
        `;
    }

    function collectFilters() {
        return {
            id_colegio: $('#filtroColegio').val() || '',
            categoria: $('#filtroCategoria').val() || '',
            activo: $('#filtroActivo').val(),
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
            responsive: true,
            pageLength: 10,
            paging: true,
            info: true,
            lengthChange: false,
            pagingType: 'simple_numbers',
            dom: "<'row align-items-center mb-3'<'col-md-6'i><'col-md-6 text-md-end'p>>rt<'row align-items-center mt-3'<'col-md-6'i><'col-md-6 text-md-end'p>>",
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.8/i18n/es-ES.json',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ productos',
                infoEmpty: 'Sin productos para mostrar',
                paginate: { previous: 'Anterior', next: 'Siguiente' }
            },
            columns: [
                { data: 'id_producto' },
                {
                    data: null,
                    render: function (data) {
                        return `<div class="fw-semibold">${escapeHtml(data.nombre_producto)}</div><small class="text-muted">${escapeHtml(data.descripcion || '')}</small>`;
                    }
                },
                { data: 'nom_colegio', render: escapeHtml },
                { data: 'categoria', render: escapeHtml },
                { data: 'unidad_medida', render: escapeHtml },
                {
                    data: null,
                    render: function (data) {
                        return `<div class="fw-semibold">${escapeHtml(data.stock_actual)}</div>${data.badge_stock || ''}`;
                    }
                },
                { data: 'stock_minimo' },
                {
                    data: 'ultimo_movimiento',
                    render: function (data) {
                        return escapeHtml(data || 'Sin movimientos');
                    }
                },
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
    }

    function loadInventario() {
        if (!window.INVENTARIO_CONFIG || !window.INVENTARIO_CONFIG.endpoints) {
            return;
        }

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
                Swal.fire('Error', 'No fue posible cargar el inventario de aseo.', 'error');
            });
    }

    function bindFilters() {
        $('#filtroColegio, #filtroCategoria, #filtroActivo').on('change', loadInventario);
        $('#filtroBusqueda').on('keyup', debounce(loadInventario, 350));
    }

    function bindDelete() {
        $(document).on('click', '.btnEliminarLogico', function () {
            const idProducto = $(this).data('id');
            Swal.fire({
                title: 'Desactivar producto',
                text: 'El producto quedara inactivo, pero se mantendra su historial.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si, desactivar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                $.post(window.INVENTARIO_CONFIG.endpoints.eliminar, { id_producto: idProducto, activo: 0 }, null, 'json')
                    .done(function (response) {
                        Swal.fire('Actualizado', response.mensaje, 'success');
                        loadInventario();
                    })
                    .fail(function (xhr) {
                        const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible actualizar el producto.';
                        Swal.fire('Error', mensaje, 'error');
                    });
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
                Swal.fire({ icon: 'success', title: 'Guardado correctamente', text: response.mensaje }).then(() => {
                    if (response.redirect) {
                        window.location.href = response.redirect;
                    }
                });
            }).fail(function (xhr) {
                const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'No fue posible guardar el producto.';
                Swal.fire('Error', mensaje, 'error');
            });
        });
    }

    function bindRepeater() {
        $('#btnAgregarMovimiento').on('click', function () {
            const index = $('#contenedorMovimientos .movimiento-item').length;
            $('#contenedorMovimientos').append(`
                <div class="inv-repeat-card movimiento-item">
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label class="form-label">Tipo</label>
                            <select name="movimiento[${index}][tipo_movimiento]" class="form-select">
                                <option value="">Seleccione</option>
                                <option value="ingreso">Ingreso</option>
                                <option value="salida">Salida</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Cantidad</label>
                            <input type="number" step="0.01" min="0" name="movimiento[${index}][cantidad]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha y hora</label>
                            <input type="datetime-local" name="movimiento[${index}][fecha_movimiento]" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Responsable</label>
                            <input type="text" name="movimiento[${index}][responsable]" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Observacion</label>
                            <input type="text" name="movimiento[${index}][observacion]" class="form-control">
                        </div>
                    </div>
                    <button type="button" class="btn btn-link text-danger px-0 mt-2 btnEliminarFila">Quitar fila</button>
                </div>
            `);
        });

        $(document).on('click', '.btnEliminarFila', function () {
            $(this).closest('.inv-repeat-card').remove();
        });
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

    $(function () {
        initTabla();
        bindFilters();
        bindDelete();
        bindFormAjax();
        bindRepeater();
    });
})(jQuery);
