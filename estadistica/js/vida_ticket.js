/* Vida del ticket: tabla con filtros y detalle expandible por fila. */
(function ($) {
    'use strict';

    const $tabla = $('#tablaVida');
    if (!$tabla.length) { return; }

    const tabla = $tabla.DataTable({
        order: [[1, 'desc']],
        pageLength: 25,
        lengthChange: false,
        searching: true,
        columnDefs: [{ targets: [7], orderable: false, searchable: false }],
        dom: "<'row align-items-center'<'col-md-12'i>>rt<'row align-items-center'<'col-md-6'i><'col-md-6 text-md-end'p>>",
        language: {
            emptyTable: 'No hay tickets para mostrar',
            zeroRecords: 'No hay tickets que coincidan con los filtros',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ tickets',
            infoEmpty: 'Sin tickets para mostrar',
            infoFiltered: '(de _MAX_ en total)',
            paginate: { previous: 'Anterior', next: 'Siguiente' }
        }
    });

    // Filtros por atributos de la fila
    $.fn.dataTable.ext.search.push(function (settings, data, index) {
        if (settings.nTable !== $tabla[0]) { return true; }
        const fila = tabla.row(index).node();
        const colegio = $('#filtroColegio').val();
        const categoria = $('#filtroCategoria').val();
        const estado = $('#filtroEstado').val();
        if (colegio && fila.dataset.colegio !== colegio) { return false; }
        if (categoria && fila.dataset.categoria !== categoria) { return false; }
        if (estado === '__abiertos' && fila.dataset.abierto !== '1') { return false; }
        if (estado && estado !== '__abiertos' && fila.dataset.estado !== estado) { return false; }
        return true;
    });

    $('#filtroColegio, #filtroCategoria, #filtroEstado').on('change', () => tabla.draw());
    let espera;
    $('#filtroBusqueda').on('input', function () {
        clearTimeout(espera);
        const texto = this.value;
        espera = setTimeout(() => tabla.search(texto).draw(), 250);
    });
    tabla.draw();

    // Detalle expandible
    $tabla.on('click', '.btnVerVida', function () {
        const $btn = $(this);
        const tr = $btn.closest('tr');
        const fila = tabla.row(tr);
        if (fila.child.isShown()) {
            fila.child.hide();
            tr.removeClass('shown');
            $btn.html('<i class="bi bi-diagram-3"></i>Ver vida');
            return;
        }
        $btn.prop('disabled', true);
        fila.child('<div class="est-empty">Cargando…</div>', 'est-detalle-celda').show();
        tr.addClass('shown');
        $.get('ajax/vida_ticket_detalle.php', { id_ticket: $btn.data('id') })
            .done(html => fila.child(html, 'est-detalle-celda').show())
            .fail(xhr => fila.child(xhr.responseText || '<div class="est-empty">No fue posible cargar el detalle.</div>', 'est-detalle-celda').show())
            .always(() => {
                $btn.prop('disabled', false).html('<i class="bi bi-chevron-up"></i>Ocultar');
            });
    });
}(jQuery));
