<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';
$funciones = new Funciones();
$idUsuarioSession   = htmlspecialchars($_SESSION['id']);
$nombre             = htmlspecialchars($_SESSION['nombre']) . ' ' . htmlspecialchars($_SESSION['apellido_paterno']);
$AreaTrabajo        = htmlspecialchars($_SESSION['id_area_trabajo']);
$idPagActual        = "13";
?>

<script>
var idUsuarioSession = <?php echo json_encode($idUsuarioSession); ?>;
var eventos = <?php echo $eventos; ?>;
</script>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- CSS de Intro.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css">
    <!-- JS de Intro.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


    <link href="css/modalesTicket.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <script type="text/javascript" src="js/funciones.js"></script>
    <script type="text/javascript" src="js/mensajes.js"></script>
    <script type="text/javascript" src="js/ticket.js"></script>
    <script type="text/javascript" src="js/buscadores.js"></script>
    <script type="text/javascript" src="js/validacionTicket.js"></script>
    <script type="text/javascript" src="js/toast.js"></script>

    <!-- FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales/es.js"></script>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery y SweetAlert -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body id="page-top">
    <input type="hidden" id="idUsuario" value="<?php echo $idUsuarioSession; ?>" />
    <div id="wrapper">
        <?php $funciones->menuLateral($idUsuarioSession, $idPagActual); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>

                <div class="d-sm-flex align-items-center justify-content-between mb-4"></div>

                <div class="row mx-4">
                    <!-- CONTENDOR DE MENSAJES -->

                    <!--Contenedor para usuario y admin  Tecnico-->
                    <div class="col-12">
                        <div class="card shadow mb-4 px-0"
                            data-intro="En esta sección puedes ver los tickets asignados." data-step=12>
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Calendario de Eventos</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <div id="calendario"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendario');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: eventos,
            selectable: true,
            select: function(info) {
                Swal.fire({
                    title: '<div class="alert alert-dark" role="alert">Agregar Evento</div>',
                    html: `
                        <div class="container-fluid">
                            <!-- Lógica de badges eliminada -->
                            <div class="row g-3">
                            <div class="col-md-6">
                                <label for="cantidadInvitados" class="form-label">Cantidad de Invitados</label>
                                <input type="number" class="form-control" id="cantidadInvitados" value="0" min="0">

                                <label for="fechaInicio" class="form-label mt-3">Fecha de Inicio</label>
                                <input type="date" class="form-control" id="fechaInicio" value="${info.startStr}" required>

                                <label for="horaEvento" class="form-label mt-3">Hora del Evento</label>
                                <input type="time" class="form-control" id="horaEvento" required>
                            </div>

                            <div class="col-md-6">
                                <label for="personaResponsable" class="form-label">Persona Responsable</label>
                                <select class="form-select" id="personaResponsable" required>
                                <option value="">Seleccione...</option>
                                </select>

                                <label class="form-label mt-3">Opciones:</label>
                                <div class="d-flex flex-column gap-1 ps-1">
                                <div class="form-check">
                                    <input class="form-check-input check-evento" type="checkbox" id="conAudio" value="Audio">
                                    <label class="form-check-label" for="conAudio">Con Audio</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input check-evento" type="checkbox" id="soloPresentacion" value="Solo Presentación">
                                    <label class="form-check-label" for="soloPresentacion">Solo Presentación</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input check-evento" type="checkbox" id="musicaAmbiental" value="Música Ambiental">
                                    <label class="form-check-label" for="musicaAmbiental">Música Ambiental</label>
                                </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <label for="titulo" class="form-label">Título</label>
                                <input type="text" class="form-control" id="titulo" placeholder="Ej: Reunión mensual" required>
                            </div>

                            <div class="col-12">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" rows="3" placeholder="Agrega detalles importantes del evento..." required></textarea>
                            </div>
                            </div>
                        </div>
                        `,
                    showCloseButton: true,
                    confirmButtonText: 'Guardar',
                    width: "750px",
                    padding: "30px",
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        confirmButton: 'bt_crear'
                    },
                    didOpen: () => {
                        // Cargar responsables
                        $.ajax({
                            url: 'modelos/rescatar/usuarios.php',
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    const select = Swal.getPopup()
                                        .querySelector(
                                            '#personaResponsable');
                                    response.usuarios.forEach(usuario => {
                                        const option = document
                                            .createElement(
                                            'option');
                                        option.value = usuario.id;
                                        option.textContent = usuario
                                            .nombre_completo_usuario;
                                        select.appendChild(option);
                                    });
                                }
                            },
                            error: function() {
                                console.error(
                                    'No se pudo cargar la lista de responsables.'
                                    );
                            }
                        });
                    },
                    preConfirm: () => {
                        const getVal = id => document.getElementById(id).value;
                        const required = ["titulo", "descripcion", "fechaInicio", "horaEvento", "personaResponsable", "cantidadInvitados"];
                        for (let campo of required) {
                            if (!getVal(campo)) {
                                Swal.showValidationMessage(
                                    'Por favor, completa todos los campos obligatorios'
                                );
                                return false;
                            }
                        }

                        return {
                            titulo: getVal('titulo'),
                            descripcion: getVal('descripcion'),
                            fechaInicio: getVal('fechaInicio'),
                            horaEvento: getVal('horaEvento'),
                            personaResponsable: getVal('personaResponsable'),
                            cantidadInvitados: getVal('cantidadInvitados'),
                            conAudio: document.getElementById('conAudio').checked,
                            soloPresentacion: document.getElementById('soloPresentacion').checked,
                            musicaAmbiental: document.getElementById('musicaAmbiental').checked
                        };
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const datos = result.value;
                        $.post('modelos/guardar/evento_calendario.php', datos, function() {
                           Swal.fire({
                        title: 'Guardado',
                        text: 'Reunión agendada con éxito',
                        icon: 'success',
                        showConfirmButton: true, // <-- mostramos el botón
                        confirmButtonText: 'Aceptar', // texto del botón
                        customClass: {
                            popup: 'cuerpo_modal_guardar',
                            confirmButton: 'bt_crear'
                        }
                    }).then(() => location.reload());
                        }).fail(() => {
                            Swal.fire('Error', 'No se pudo guardar el evento',
                                'error');
                        });
                    }
                });
            },

            eventClick: function(info) {
                Swal.fire({
                    title: '<div class="alert alert-dark" role="alert">Detalle del Evento</div>',
                    html: `
    <div class="container-fluid">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="cantidadInvitados" class="form-label">Cantidad de Invitados</label>
                <input type="number" class="form-control" id="cantidadInvitados" value="${info.event.extendedProps.cantidadInvitados || 0}" min="0" readonly>

                <label for="fechaInicio" class="form-label mt-3">Fecha de Inicio</label>
                <input type="date" class="form-control" id="fechaInicio" value="${info.event.startStr}" readonly>

                <label for="horaEvento" class="form-label mt-3">Hora del Evento</label>
                <input type="time" class="form-control" id="horaEvento" value="${info.event.extendedProps.horaEvento || ''}" readonly>
            </div>
            <div class="col-md-6">
                <label for="personaResponsable" class="form-label">Persona Responsable</label>
                <select class="form-select" id="personaResponsable" disabled>
                    <option value="">${info.event.extendedProps.personaResponsable || ''}</option>
                </select>

                <label class="form-label mt-3">Opciones:</label>
                <div class="d-flex flex-column gap-1 ps-1">
                    <div class="form-check">
                        <input class="form-check-input check-evento" type="checkbox" id="conAudio" ${info.event.extendedProps.con_audio ? 'checked' : ''} disabled>
                        <label class="form-check-label" for="conAudio">Con Audio</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input check-evento" type="checkbox" id="soloPresentacion" ${info.event.extendedProps.solo_presentacion ? 'checked' : ''} disabled>
                        <label class="form-check-label" for="soloPresentacion">Solo Presentación</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input check-evento" type="checkbox" id="musicaAmbiental" ${info.event.extendedProps.musica_ambiental ? 'checked' : ''} disabled>
                        <label class="form-check-label" for="musicaAmbiental">Música Ambiental</label>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <label for="titulo" class="form-label">Título</label>
                <input type="text" class="form-control" id="titulo" value="${info.event.title}" readonly>
            </div>
            <div class="col-12">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea class="form-control" id="descripcion" rows="3" readonly>${info.event.extendedProps.descripcion}</textarea>
            </div>
        </div>
    </div>
`,
                    showCloseButton: true,
                    showConfirmButton: false, // Ocultamos el botón OK
                    showCancelButton: true,   // Mostramos el botón de cancelación
                    cancelButtonText: 'ELIMINAR', // El botón de cancelación será 'ELIMINAR'
                    width: "750px",
                    padding: "30px",
                    customClass: {
                        popup: 'cuerpo_modal_guardar',
                        cancelButton: 'bt_eliminar'
                    }



                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        // Modal de confirmación de eliminación
                        Swal.fire({
                            title: '¿Está seguro?',
                            text: "Esta acción eliminará el evento",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar',
                            customClass: {
                                popup: 'cuerpo_modal_guardar',
                                confirmButton: 'bt_crear',
                                cancelButton: 'bt_eliminar'
                            }
                        }).then((confirmResult) => {
                            if (confirmResult.isConfirmed) {
                                // Llamada AJAX para eliminar el evento
                                $.post('modelos/eliminar/eliminar_evento_calendario.php', { id: info.event.id }, function() {
                                    Swal.fire({
                                        title: 'Eliminado',
                                        text: 'El evento ha sido eliminado con éxito',
                                        icon: 'success',
                                        showConfirmButton: false,
                                        timer: 2000,
                                        customClass: {
                                            popup: 'cuerpo_modal_guardar',
                                            confirmButton: 'bt_crear'
                                        }
                                    }).then(() => location.reload());
                                }).fail(() => {
                                    Swal.fire('Error', 'No se pudo eliminar el evento', 'error');
                                });
                            }
                        });
                    }
                });
            }
        });

        calendar.render();
    });
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>

    <?php $funciones->script(); ?>
</body>

</html>