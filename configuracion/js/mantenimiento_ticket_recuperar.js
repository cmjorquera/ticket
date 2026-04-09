function recuperarTicket(idTicket, asunto) {
    Swal.fire({
        icon: 'question',
        title: 'Recuperar ticket',
        html: `¿Deseas recuperar el ticket <strong>#${idTicket}</strong>?<br><small class="text-muted">${asunto}</small><br><br>El ticket volvera al estado activo (id_estado = 1).`,
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-arrow-counterclockwise me-1"></i>Si, recuperar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        customClass: { popup: 'cuerpo_modal_guardar' }
    }).then((result) => {
        if (!result.isConfirmed) {
            return;
        }

        $.ajax({
            url: '../modelos/guardar/mantenimiento_ticket_recuperar.php',
            type: 'POST',
            dataType: 'json',
            data: { ticket_id: idTicket },
            success: function (response) {
                if (!response || !response.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response?.message || 'No se pudo recuperar el ticket.',
                        customClass: { popup: 'cuerpo_modal_guardar' }
                    });
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Ticket recuperado',
                    text: `El ticket #${idTicket} fue recuperado correctamente.`,
                    customClass: { popup: 'cuerpo_modal_guardar' }
                }).then(() => {
                    window.location.reload();
                });
            },
            error: function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrio un error al intentar recuperar el ticket.',
                    customClass: { popup: 'cuerpo_modal_guardar' }
                });
            }
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    const tabla = document.getElementById('tablaMantenimientoRecuperar');

    if (tabla && window.jQuery && $.fn.DataTable) {
        $('#tablaMantenimientoRecuperar').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 10,
            order: [[0, 'desc']]
        });
    }
});
