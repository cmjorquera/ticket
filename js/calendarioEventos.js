








function cerrarEvento(eventoId) {
    Swal.fire({
        title: '<div class="alert alert-dark mb-2 text-center">Confirmar cierre</div>',
        text: "¿Está seguro que desea cerrar este evento?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, cerrar',
        cancelButtonText: 'Cancelar',
        customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_crear',
            cancelButton: 'bt_eliminar'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'modelos/eliminar/eliminar_evento_calendario.php',
                type: 'POST',
                data: {
                    id: eventoId
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Evento cerrado',
                            icon: 'success',
                            showConfirmButton: false,
                            timer: 3000,
       
           
                            customClass: {
                                popup: 'cuerpo_modal_guardar',
                       
                            }
                        }).then(() => location.reload());
                    } else {
                        Swal.fire('Error', response.error, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
                }
            });
        }
    });
}
function mostrarCronologia(eventoId) {
$.ajax({
    url: 'modelos/rescatar/EventosCalendario.php',
    type: 'POST',
    data: { id: eventoId },
    dataType: 'json',
    success: function(response) {
        const audio         = response.con_audio == 1;
        const presentacion  = response.solo_presentacion == 1;
        const musica        = response.musica_ambiental == 1;

        const titulo        = response.titulo       || "";
        const descripcion   = response.descripcion  || "";
        const fechaInicio   = response.fecha_inicio || "";
        const horaEvento    = response.hora_evento  || "";
        const creado        = response.creado_en    || "";
        const eliminado     = response.eliminado == "si" ? "Sí" : "no";

        const caracteristicas = `
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="audio" ${audio ? 'checked' : ''} disabled>
                <label class="form-check-label" for="audio"><i class="fas fa-volume-up text-primary me-2"></i> Con Audio</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="presentacion" ${presentacion ? 'checked' : ''} disabled>
                <label class="form-check-label" for="presentacion"><i class="fas fa-chalkboard-teacher text-success me-2"></i> Solo Presentación</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="musica" ${musica ? 'checked' : ''} disabled>
                <label class="form-check-label" for="musica"><i class="bi bi-music-note-beamed text-warning me-2"></i> Música Ambiental</label>
            </div>
        `;

        Swal.fire({
            title: '<div class="alert alert-dark mb-2 text-center">Detalles del Evento</div>',
            html: `
                <div class="container text-start">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="tituloTicket"><strong>Título:</strong></label>
                            <input type="text" class="form-control mb-2" id="tituloTicket" value="${titulo}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="encargadoTicket"><strong>Encargado:</strong></label>
                            <input type="text" class="form-control mb-2" id="encargadoTicket" value="Cristian Jorquera" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="fechaInicioTicket"><strong>Fecha de Inicio:</strong></label>
                            <input type="date" class="form-control mb-2" id="fechaInicioTicket" value="${fechaInicio}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="horaEventoTicket"><strong>Hora del Evento:</strong></label>
                            <input type="time" class="form-control mb-2" id="horaEventoTicket" value="${horaEvento}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label for="creadoTicket"><strong>Creado el:</strong></label>
                            <input type="text" class="form-control mb-2" id="creadoTicket" value="${creado}" disabled>
                        </div>
                 
                        <div class="col-md-12">
                            <label for="descripcionTicket"><strong>Descripción:</strong></label>
                            <textarea class="form-control mb-2" id="descripcionTicket" rows="4" disabled>${descripcion}</textarea>
                        </div>
                        <div class="col-md-12">
                            <label><strong>Características:</strong></label>
                            ${caracteristicas}
                        </div>
                    </div>
                </div>`,
            width: '750px',
            confirmButtonText: 'Cerrar',
            customClass: {
                popup: 'cuerpo_modal_guardar',
                confirmButton: 'bt_crear'
            }
        });
    },
    error: function() {
        Swal.fire('Error', 'No se pudo obtener la cronología del evento', 'error');
    }
});
}