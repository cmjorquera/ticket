$(function () {
    const $tablaUsuarios = $('#dataTableUsuarios');

    if ($tablaUsuarios.length && $.fn.DataTable) {
        $tablaUsuarios.DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                search: 'Buscar usuario:',
                searchPlaceholder: 'Nombre, correo, area o colegio',
                lengthMenu: 'Mostrar _MENU_ registros'
            },
            pageLength: 10,
            order: [[2, 'asc']],
            autoWidth: false,
            responsive: false
        });
    }
});

function mostrarDetalleEstadoUsuario(mensaje) {
    Swal.fire({
        icon: 'info',
        title: 'Detalle del estado',
        text: mensaje,
        confirmButtonText: 'Entendido'
    });
}

function obtenerIdUsuarioConfiguracion() {
    const campo = document.getElementById('idUsuario');
    return campo ? campo.value : '';
}

function iniciarActualizacionPanel(selectorDestino, url, intervalo, dataType) {
    const usuarioId = obtenerIdUsuarioConfiguracion();
    if (!usuarioId) {
        return;
    }

    const destino = document.querySelector(selectorDestino);
    if (!destino && selectorDestino !== '#numeroAlertas') {
        return;
    }

    function actualizar() {
        $.ajax({
            type: 'GET',
            url: url,
            dataType: dataType || undefined,
            data: { id_usuario: usuarioId },
            success: function (data) {
                if (selectorDestino === '#numeroAlertas') {
                    $('#numeroAlertas').text(data);
                    return;
                }

                $(selectorDestino).html(data);
            }
        });
    }

    actualizar();
    setInterval(actualizar, intervalo);
}

document.addEventListener('DOMContentLoaded', function () {
    iniciarActualizacionPanel('#numeroAlertas', 'http://127.0.0.1/ticket/api/obtenerCantidadAlertas', 3000, 'json');
    iniciarActualizacionPanel('#contendorMensajes', 'http://127.0.0.1/ticket/api/obtenerMensajes', 3000);
    iniciarActualizacionPanel('#contendorTicket', 'http://127.0.0.1/ticket/api/obtenerRecordatorios', 3000);
});

function toggleSubmenuColor(elemento) {
    const categoria = elemento.getAttribute('data-id_categoria');
    const esActivo = elemento.classList.contains('green');

    if (!esActivo) {
        const selector = '.submenu-item.green[data-id_categoria="' + categoria + '"]';
        const yaAsignada = document.querySelectorAll(selector);
        if (yaAsignada.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Ya asignada',
                text: 'Esta categoría ya fue asignada a otro técnico.',
                timer: 2000,
                showConfirmButton: false,
                customClass: {
                    popup: 'cuerpo_modal_guardar'
                }
            });
            return;
        }
    }

    elemento.classList.toggle('green');
    elemento.classList.toggle('red');
}
