<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Modal Ticket Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- <link href="../css/sb-admin-2.css" rel="stylesheet"> -->

    <style>
    .cuerpo_modal_guardar {
        /* width: 100%; */
        font-family: 'Helvetica', sans-serif;
        /* Fuente sencilla y legible */
        background-color: #f0f0f0;
        /* Fondo suave */
        color: #333;
        /* Texto oscuro y neutro */
        border-radius: 15px;
        /* Bordes redondeados */
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        /* Sombra sutil */
        padding: 20px;
        /* Espacio en blanco alrededor del contenido */
        transition: all .3s ease-out;
        /* Transici贸n suave */
        border: 2px solid rgb(31, 30, 30);
    }

    .badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        font-size: 13px;
        border-radius: 20px;
        font-weight: 500;
        gap: 5px;
    }

    .timeline-horizontal {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 20px;
        gap: 10px;
    }

    .timeline-horizontal .estado {
        text-align: center;
        flex: 1;
        position: relative;
    }

    .timeline-horizontal .estado::after {
        content: '';
        position: absolute;
        top: 15px;
        left: 50%;
        width: 100%;
        height: 2px;
        background: #ccc;
        z-index: 0;
    }

    .timeline-horizontal .estado:last-child::after {
        content: none;
    }

    .timeline-horizontal .icono {
        background: #eee;
        border-radius: 50%;
        width: 30px;
        height: 30px;
        margin: 0 auto 5px;
        line-height: 30px;
        font-size: 16px;
        z-index: 1;
        position: relative;
    }

    .timeline-horizontal .estado.activo .icono {
        background: #3498db;
        color: white;
    }
    </style>
</head>

<body class="p-5">

    <button class="btn btn-primary" onclick="modalTicketUsuario()">Ver Detalle del Ticket Usuario</button>
    <button class="btn btn-primary" onclick="modalTicketTecnico()">Ver Detalle del Ticket tecnico</button>
    <button class="btn btn-primary" onclick="modalTickeAdministrador()">Ver Detalle del Ticket administrador</button>


    <script>
    function modalTicketUsuario() {
        Swal.fire({
            title: '<strong>Detalle del Ticket</strong>',
            html: `
          <div class="d-flex flex-wrap gap-2 justify-content-start mb-3">
            <span class="badge bg-danger" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Prioridad Alta">
              <i class="fas fa-exclamation-circle"></i> Alta
            </span>
            <span class="badge bg-info" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Ticket en proceso">
              <i class="fas fa-spinner"></i> En proceso
            </span>
            <span class="badge bg-secondary" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Correo Electrónico enviado">
              <i class="fas fa-envelope"></i> Correo Electrónico
            </span>
            <span class="badge bg-light text-dark" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Código QR del ticket">
              <i class="fas fa-qrcode"></i> QR Code
            </span>
            <span class="badge bg-warning" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Días estimados para la resolución">
              <i class="fas fa-clock"></i> 3 días
            </span>
          </div>

          <div class="row text-start">
            <div class="col-6 mb-2">
              <label><strong>ID Ticket:</strong></label>
              <input type="text" class="form-control" value="#1025" readonly>
            </div>
            <div class="col-6 mb-2">
              <label><strong>Usuario:</strong></label>
              <input type="text" class="form-control" value="Cristian Jorquera" readonly>
            </div>
            <div class="col-6 mb-2">
              <label><strong>Técnico:</strong></label>
              <input type="text" class="form-control" value="Juan Pérez" readonly>
            </div>
            <div class="col-6 mb-2">
              <label><strong>Asunto:</strong></label>
              <input type="text" class="form-control" value="Problemas con el correo" readonly>
            </div>
            <div class="col-12 mb-3">
              <label><strong>Descripción:</strong></label>
              <textarea class="form-control" rows="2" readonly>No puedo acceder a mi correo desde ayer.</textarea>
            </div>
          </div>

          <div class="timeline-horizontal">
            <div class="estado activo">
              <div class="icono"><i class="fas fa-plus"></i></div>
              <div class="texto">
                Creado<br>
                <small>01/04/2025<br>09:15 AM</small>
              </div>
            </div>
            <div class="estado activo">
              <div class="icono"><i class="fas fa-check"></i></div>
              <div class="texto">
                Asignado<br>
                <small>01/04/2025<br>09:30 AM</small>
              </div>
            </div>
            <div class="estado activo">
              <div class="icono"><i class="fas fa-spinner"></i></div>
              <div class="texto">
                En proceso<br>
                <small>02/04/2025<br>10:00 AM</small>
              </div>
            </div>
            <div class="estado">
              <div class="icono"><i class="fas fa-flag-checkered"></i></div>
              <div class="texto">
                Cerrado<br>
                <small>--<br>--</small>
              </div>
            </div>
          </div>
         `,
            width: '800px',
            confirmButtonText: 'Cerrar',
            showCloseButton: true,
            customClass: {
                popup: 'cuerpo_modal_guardar',
                confirmButton: 'bt_crear'
            },
            didOpen: () => {
                // Inicializa los popovers de Bootstrap en el contenido del SweetAlert2
                const popoverTriggerList = [].slice.call(document.querySelectorAll(
                    '[data-bs-toggle="popover"]'));
                popoverTriggerList.map(triggerEl => new bootstrap.Popover(triggerEl));
            }
        });
    }

    function modalTicketTecnico() {
        Swal.fire({
            title: '<strong>Detalle del Ticket Técnico</strong>',
            html: `
      <div class="d-flex flex-wrap gap-2 justify-content-start mb-3">
        <span class="badge bg-secondary" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Correo Electrónico enviado">
          <i class="fas fa-envelope"></i> Correo Electrónico
        </span>
        <span class="badge bg-light text-dark" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Código QR del ticket">
          <i class="fas fa-qrcode"></i> QR Code
        </span>
        <!-- vacíos para los dinámicos -->
        <span id="badgePrioridad"></span>
        <span id="badgeDias"></span>
      </div>

      <div class="row text-start">
        <div class="col-6 mb-2">
          <label><strong>ID Ticket:</strong></label>
          <input type="text" class="form-control" value="#1026" readonly>
        </div>
        <div class="col-6 mb-2">
          <label><strong>Usuario:</strong></label>
          <input type="text" class="form-control" value="Usuario Técnico" readonly>
        </div>
        <div class="col-6 mb-2">
          <label><strong>Técnico:</strong></label>
          <input type="text" class="form-control" value="Técnico Asignado" readonly>
        </div>
        <div class="col-6 mb-2">
          <label><strong>Asunto:</strong></label>
          <input type="text" class="form-control" value="Ticket Técnico" readonly>
        </div>
        <div class="col-12 mb-3">
          <label><strong>Descripción:</strong></label>
          <textarea class="form-control" rows="2" readonly>Descripción del ticket técnico.</textarea>
        </div>
      </div>

      <div class="row text-start">
        <div class="col-6 mb-2">
          <label><strong>Prioridad:</strong></label>
          <select class="form-select" id="selectPrioridad">
            <option value="">Seleccione...</option>
            <option value="Alta">Alta</option>
            <option value="Media">Media</option>
            <option value="Baja">Baja</option>
          </select>
        </div>
        <div class="col-6 mb-2">
          <label><strong>Días estimados:</strong></label>
          <input type="number" class="form-control" id="diasEstimados" placeholder="0">
        </div>
        <div class="col-12 mb-2">
          <label><strong>Comentario:</strong></label>
          <textarea class="form-control" id="comentarioTecnico" rows="2" placeholder="Agregar comentario"></textarea>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-3 mt-3">
        <button type="button" class="btn btn-success" id="btnComenzarTicket">Comenzar Ticket</button>
        <button type="button" class="btn btn-danger" id="btnCerrarTicket">Cerrar Ticket</button>
      </div>
    `,
            width: '800px',
            showCloseButton: true,
            showConfirmButton: false,
            customClass: {
                popup: 'cuerpo_modal_guardar'
            },
            didOpen: () => {
                const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
popoverTriggerList.map(triggerEl => new bootstrap.Popover(triggerEl));


                const prioridadSelect = document.getElementById('selectPrioridad');
                const diasInput = document.getElementById('diasEstimados');
                const badgePrioridad = document.getElementById('badgePrioridad');
                const badgeDias = document.getElementById('badgeDias');

                const refreshPopovers = () => {
                    const newPopovers = [].slice.call(document.querySelectorAll(
                        '[data-bs-toggle="popover"]'));
                    newPopovers.map(triggerEl => new bootstrap.Popover(triggerEl));
                };

                // PRIORIDAD
                prioridadSelect.addEventListener('change', () => {
                    const prioridad = prioridadSelect.value;
                    let clase = '',
                        icono = '',
                        contenido = '';

                    switch (prioridad) {
                        case 'Alta':
                            clase = 'bg-danger';
                            icono = 'fa-exclamation-circle';
                            contenido = 'Prioridad Alta';
                            break;
                        case 'Media':
                            clase = 'bg-warning text-dark';
                            icono = 'fa-exclamation-triangle';
                            contenido = 'Prioridad Media';
                            break;
                        case 'Baja':
                            clase = 'bg-success';
                            icono = 'fa-check-circle';
                            contenido = 'Prioridad Baja';
                            break;
                        default:
                            badgePrioridad.innerHTML = '';
                            return;
                    }

                    badgePrioridad.innerHTML = `
      <span class="badge ${clase}" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="${contenido}">
        <i class="fas ${icono}"></i> ${prioridad}
      </span>
    `;
                    refreshPopovers();
                });

                // DÍAS ESTIMADOS
                diasInput.addEventListener('blur', () => {
                    const dias = diasInput.value;
                    if (dias && dias > 0) {
                        badgeDias.innerHTML = `
        <span class="badge bg-warning text-dark" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="Días estimados para la resolución">
          <i class="fas fa-clock"></i> ${dias} día(s)
        </span>
      `;
                        refreshPopovers();
                    } else {
                        badgeDias.innerHTML = '';
                    }
                });

                // BOTONES
                document.getElementById('btnComenzarTicket').addEventListener('click', () => {
                    const prioridad = prioridadSelect.value;
                    const dias = diasInput.value;
                    const comentario = document.getElementById('comentarioTecnico').value;
                    Swal.fire(
                        `Ticket comenzado<br>Prioridad: ${prioridad}<br>Días estimados: ${dias}<br>Comentario: ${comentario}`
                        );
                });

                document.getElementById('btnCerrarTicket').addEventListener('click', () => {
                    Swal.fire('Ticket cerrado');
                });
            }

        });
    }
    </script>
</body>

</html>