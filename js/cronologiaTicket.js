function mostrarToastTicket(id_ticket) {
    // alert("-------wwww------");
    $.ajax({
        url: 'modelos/rescatar/ticket.php',
        type: 'POST',
        data: { id: id_ticket },
        dataType: 'json',
        success: function (response) {
            if (!response || response.error) {
                Swal.fire('Error', 'No se pudo cargar la cronología del ticket.', 'error');
                return;
            }

            let tecnicoHTML = '<span class="text-danger">Técnico no asignado</span>';

            if (response.id_tecnico) {
                $.ajax({
                    url: 'modelos/rescatar/rescatarTecnico.php',
                    type: 'POST',
                    data: { id_tecnico: response.id_tecnico },
                    dataType: 'json',
                    success: function (tecnico) {
                        tecnicoHTML = `<span class="text-primary">Técnico: ${tecnico.nombre} ${tecnico.apellido_paterno}</span>`;
                        mostrarCronologia(response, tecnicoHTML);
                    },
                    error: function () {
                        mostrarCronologia(response, tecnicoHTML); // Fallback si técnico no se encuentra
                    }
                });
            } else {
                mostrarCronologia(response, tecnicoHTML);
            }
        },
        error: function () {
            Swal.fire('Error', 'Ocurrió un error al conectar con el servidor.', 'error');
        }
    });
}

function mostrarCronologia(response, tecnicoHTML) {
    let contenido = `
        <h3 class="text-center">Cronología del Ticket</h3>
        <div class="text-center text-primary mb-2">${tecnicoHTML}</div>
        <div class="timeline-container">
            <div class="timeline-line"></div>
            <div class="timeline-items">
                ${generarBloqueEstado("Creado", response.fecha_creacion_inicio, response.hora_creacion_inicio)}
                ${generarBloqueEstado("Asignado", response.fecha_asignacion_tecnico, response.hora_asignacion_tecnico)}
                ${generarBloqueEstado("En proceso", response.fecha_comienzo_ticket, response.hora_comienzo_ticket)}
                ${generarBloqueEstado("Finalizado", response.fecha_termino_ticket, response.hora_termino_ticket)}
            </div>
        </div>
    `;

    Swal.fire({
        html: contenido,
        showConfirmButton: false,
        showCloseButton: true,
        width: 600,
        customClass: {
            popup: 'cuerpo_modal_guardar'
        }
    });
}

function generarBloqueEstado(nombre, fecha, hora) {
    const inactivo = (!fecha || fecha === '0000-00-00') ? 'inactive' : '';
    return `
        <div class="timeline-item">
            <div class="timeline-dot ${inactivo}"></div>
            <div class="timeline-content">
                <span class="timeline-label">${nombre}</span><br>
                <span class="timeline-date">${fecha || '--/--/----'}</span><br>
                <span class="timeline-time">${hora || '--:--'}</span>
            </div>
        </div>
    `;
}