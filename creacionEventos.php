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
$eventos = $funciones->obtenerEventos();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <?php $funciones->header(); ?>

    <!-- ✅ jQuery (debe ir primero para que DataTables y otros lo usen) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- ✅ Bootstrap 5.3.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- ✅ Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <!-- ✅ DataTables CSS + JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- ✅ SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- ✅ Chart.js -->
    <!--<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>-->

    <!-- ✅ Intro.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"></script>

    <!-- ✅ FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- ✅ Popper.js y Bootstrap JS (solo versión 5.3.3 para mantener coherencia) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    <!-- ✅ FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales/es.js"></script>

    <!-- ✅ CSS Personalizados -->
    <link href="css/modalesTicket.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">

    <!-- ✅ JS Personalizados (van al final del head para que carguen con jQuery activo) -->
    <!--<script src="js/funciones.js"></script>-->
    <script src="js/mensajes.js"></script>
    <script src="js/ticket.js"></script>
    <script src="js/buscadores.js"></script>
    <script src="js/validacionTicket.js"></script>
    <script src="js/toast.js"></script>
    <link href="css/contenedor.css" rel="stylesheet">

</head>
<style>

    /* Estilo general del offcanvas */
    #offcanvasEvento {
      background-color: #f8f9fa;
      border-left: 1px solid #dee2e6;
    }
    
    /* Encabezado del offcanvas */
    #offcanvasEvento .offcanvas-header {
      background-color: #ffffff;
      padding: 1rem 1.5rem;
    }
    
    /* Título del offcanvas */
    #offcanvasEvento .offcanvas-title {
      font-size: 1.25rem;
      color: #333;
    }
    
    /* Cuerpo del offcanvas con scroll si hay mucho contenido */
    #offcanvasEvento .offcanvas-body {
      padding: 1.5rem;
      overflow-y: auto;
      max-height: 80vh;
    }
    
    /* Íconos informativos */
    #offcanvasEvento .bi {
      font-size: 1.2rem;
      vertical-align: middle;
    }
    
    /* Texto pequeño de metadata */
    #offcanvasEvento small.text-muted {
      display: block;
      margin-top: 1rem;
      font-size: 0.85rem;
    }
    
    /* Espaciado general de bloques */
    #offcanvasEvento h5,
    #offcanvasEvento .mb-2,
    #offcanvasEvento .mb-3,
    #offcanvasEvento p {
      margin-bottom: 1rem !important;
    }
    
    /* Botones de acción si agregas editar/eliminar */
    .botones-acciones-evento {
      position: absolute;
      bottom: 1rem;
      right: 1.5rem;
      display: flex;
      gap: 0.5rem;
    }
    
    
.resaltar-hoy {
  background: linear-gradient(135deg, #fff0f0, #ffe3e3);
  border: 2px solid #dc3545;
  border-radius: 0.5rem;
  box-shadow: 0 0 10px rgba(220, 53, 69, 0.3);
  animation: resaltarHoyBrillo 1.5s ease-in-out infinite;
  font-weight: bold;
  color: black;
}
@keyframes resaltarHoyBrillo {
  0%, 100% { box-shadow: 0 0 10px rgba(220, 53, 69, 0.3); }
  50%      { box-shadow: 0 0 20px rgba(220, 53, 69, 0.6); }
}

    .tarjeta-ticket-estado {
        background: linear-gradient(135deg, #f2f2f2, #e0e0e0);
    }

    /* Parpadeo suave opcional si lo necesitas en otro elemento */
    @keyframes parpadeo-suave {
        0%, 100% {
            box-shadow: 0 0 8px rgba(13, 110, 253, 0.5); /* azul */
        }
        50% {
            box-shadow: 0 0 14px rgba(13, 110, 253, 0.9);
        }
    }
</style>

<?php
    $diaActual = date('N'); // 1 = Lunes, ..., 5 = Viernes
    $fechaHoy = date('Y-m-d');

?>
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

                <div class="container-fluid px-4">
                    
                    
                <div class="container-fluid px-4 mt-3">
                    <div class="row d-flex flex-wrap justify-content-between">
                     <?php  $funciones->renderizarResumenEventosPorDia($eventos); ?>

                    </div>
                </div>

                    <div class="row gx-3">
                        
                        <!-- TABLA DE EVENTOS -->
                        <div class="col-lg-8 col-sm-12">
                            
                            <div class="card shadow mb-4">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="tablaEventos" class="table table-bordered table-hover table-striped">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Título</th>
                                                    <th>Descripción</th>
                                                    <th>Fecha</th>
                                                    <th>Música</th>
                                                    <th>Presentacion</th>
                                                    <th>Audio</th>
                                                    <th>Responsable</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $fechaHoy = date('Y-m-d');
                                                $contador  =1;

                                                foreach ($eventos as $evento):
                                                    $fechaEvento = date('Y-m-d', strtotime($evento['fecha_inicio']));
                                                    $esHoy = ($fechaEvento === $fechaHoy);
                                                    $esHoy = (date('Y-m-d') === date('Y-m-d', strtotime($evento['fecha_inicio']))) ? 'resaltar-hoy' : '';
                                                
                                                    $horaEvento = strtotime($evento['fecha_inicio'] . ' ' . $evento['hora_evento']);
                                                    $diferenciaHoras = ($horaEvento - time()) / 3600;
                                                    $claseHora = ($esHoy && $diferenciaHoras <= 2 && $diferenciaHoras >= 0) ? 'text-warning fw-bold' : '';
                                                ?>
                                            <tr class="<?= $esHoy ?>">
                                                        <td><?= $contador ++ ?></td>
                                                        <td><?= htmlspecialchars($evento['titulo']) ?></td>
                                                        <td><?= htmlspecialchars($evento['descripcion']) ?></td>
                                                   <td>
                                                      <?php 
                                                        $fechaFormateada = date('d-m-Y', strtotime($evento['fecha_inicio']));
                                                        echo $esHoy 
                                                          ? '<span class="estado-badge estado-rojo" data-bs-toggle="tooltip" title="' . $evento['fecha_inicio'] . '"><i class="fas fa-calendar-day me-1"></i>HOY</span>' 
                                                          : '<span class="estado-badge estado-azul"><i class="fas fa-calendar-alt me-1"></i>' . $evento['fecha_inicio'] . '</span>';
                                                      ?>
                                                    </td>

                                            <td><?= $evento['solo_presentacion'] == 1? '<i class="bi bi-check-circle-fill text-success" title="Solo Presentación"></i>': '<i class="bi bi-x-circle-fill text-danger" title="No es Solo Presentación"></i>' ?></td>
                                            <td><?= $evento['con_audio'] == 1 ? '<i class="bi bi-check-circle-fill text-success" title="Con Audio"></i>': '<i class="bi bi-x-circle-fill text-danger" title="Sin Audio"></i>' ?></td>
                                            <td><?= $evento['musica_ambiental'] == 1 ? '<i class="bi bi-check-circle-fill text-success" title="Con Música Ambiental"></i>': '<i class="bi bi-x-circle-fill text-danger" title="Sin Música Ambiental"></i>' ?></td>

                                                        <td><?= $evento['nombre']."-".$evento['apellido_paterno'] ?></td>
                                                        <td class="text-center">
                                                              <div class="d-flex justify-content-center gap-1">
                                                           <button class="btn btn-sm btn-primary" onclick="confirmarEditar(<?= $evento['id'] ?>)">
                                                                  <i class="bi bi-pencil-square"></i>
                                                                </button>

                                                            
                                                                <button class="btn btn-sm btn-secondary" onclick="verEvento(<?= $evento['id'] ?>)">
                                                                  <i class="bi bi-eye-fill"></i>
                                                                </button>
                                                              </div>
                                                            </td>

                                                    </tr>
                                                <?php endforeach; ?>
                                                </tbody>
                                        </table>
                                    </div>
                                </div>
                
                            </div>
                        </div>
                
                        <!-- CALENDARIO -->
                        <div class="col-lg-4 col-sm-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Calendario de Eventos</h6>
                                </div>
                                <div class="card-body">
                                    <div id="calendario" style="min-height: 500px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    <?php $funciones->footer(); ?>
            </div>
        </div>
    </div>



<div class="offcanvas offcanvas-end shadow" tabindex="-1" id="offcanvasEvento" aria-labelledby="offcanvasEventoLabel" style="linear-gradient(135deg, #f2f2f2, #e0e0e0)">
  <div class="offcanvas-header border-bottom" style="linear-gradient(135deg, #f2f2f2, #e0e0e0)">
    <h5 class="offcanvas-title fw-bold" id="offcanvasEventoLabel">
      <i class="bi bi-info-circle-fill me-2 text-primary"></i> Detalle del Evento
    </h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
  </div>
  
  <div class="offcanvas-body p-4" id="offcanvasContenidoEvento" style="max-height: 90vh; overflow-y: auto;">
    <!-- Se cargará el contenido dinámico desde verEvento(id) -->
  </div>
</div>



</script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>


    <?php $funciones->script(); ?>
    
<script>
$(document).ready(function () {
    $('#tablaEventos').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        responsive: true,
        pageLength: 10
    });
});
</script>
<script>
const idUsuarioSession = <?php echo json_encode($idUsuarioSession); ?>;
const eventos = <?= json_encode($eventos) ?>;

</script>

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


<script>

    function confirmarEditar(id) {
      fetch('modelos/rescatar/EventosCalendario.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'id=' + encodeURIComponent(id)
      })
        .then(res => res.json())
        .then(data => {
          if (data.error) {
            Swal.fire('Error', data.error, 'error');
            return;
          }
    
          Swal.fire({
            title:'<div class="alert alert-dark" role="alert">Editar Evento</div>',
            html: `
              <input type="text" id="titulo" class="form-control mb-2" placeholder="Título" value="${data.titulo}">
              <textarea id="descripcion" class="form-control mb-2" rows="3" placeholder="Descripción">${data.descripcion}</textarea>
              <input type="date" id="fecha" class="form-control mb-2" value="${data.fecha_inicio}">
              <input type="time" id="hora" class="form-control mb-2" value="${data.hora_evento}">
              
              <label class="form-label">Opciones:</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="conAudio" ${data.con_audio ? 'checked' : ''}>
                <label class="form-check-label" for="conAudio">Con Audio</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="soloPresentacion" ${data.solo_presentacion ? 'checked' : ''}>
                <label class="form-check-label" for="soloPresentacion">Solo Presentación</label>
              </div>
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="musicaAmbiental" ${data.musica_ambiental ? 'checked' : ''}>
                <label class="form-check-label" for="musicaAmbiental">Música Ambiental</label>
              </div>
    
              <hr>
              <button type="button" class="btn btn-outline-danger w-100" onclick="eliminarEvento(${id})">
                <i class="bi bi-trash3 me-1"></i> Eliminar Evento
              </button>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar cambios',
            cancelButtonText: 'Cancelar',
              showCloseButton: true,
              allowOutsideClick: false,
            focusConfirm: false,
             customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_finalizar_alumno',
        },
            preConfirm: () => {
              const titulo = document.getElementById('titulo').value;
              const descripcion = document.getElementById('descripcion').value;
              const fecha = document.getElementById('fecha').value;
              const hora = document.getElementById('hora').value;
              const conAudio = document.getElementById('conAudio').checked;
              const soloPresentacion = document.getElementById('soloPresentacion').checked;
              const musicaAmbiental = document.getElementById('musicaAmbiental').checked;
    
              if (!titulo || !descripcion || !fecha || !hora) {
                Swal.showValidationMessage('Por favor completa todos los campos obligatorios');
                return false;
              }
    
              return {
                id,
                titulo,
                descripcion,
                fecha,
                hora,
                con_audio: conAudio,
                solo_presentacion: soloPresentacion,
                musica_ambiental: musicaAmbiental
              };
            }
          }).then(result => {
            if (result.isConfirmed) {
              const datos = result.value;
    
              fetch('modelos/guardar/actualizar_evento_calendario.php', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(datos)
              })
              .then(res => res.json())
             .then(resp => {
  if (resp.success) {
    Swal.fire({
      title: 'Actualizado',
      text: 'El evento fue modificado con éxito',
      icon: 'success',
      timer: 5000,
      timerProgressBar: true,
      showConfirmButton: false,
      customClass: {
        popup: 'cuerpo_modal_guardar'
      }
    }).then(() => location.reload());
  } else {
    Swal.fire({
      title: 'Error',
      text: resp.mensaje || 'No se pudo actualizar el evento',
      icon: 'error',
      customClass: {
        popup: 'cuerpo_modal_guardar'
      }
    });
  }
})

              .catch(() => {
                Swal.fire('Error', 'Error de conexión al actualizar', 'error');
              });
            }
          });
        });
    }
    
    function eliminarEvento(id) {
      Swal.fire({
        title: '¿Eliminar evento?',
        text: 'Esta acción no se puede deshacer.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
         customClass: {
            popup: 'cuerpo_modal_guardar',
            confirmButton: 'bt_activar_alumno',
            cancelButton: 'bt_activar_alumno'
        },
      }).then(result => {
        if (result.isConfirmed) {
          fetch('modelos/eliminar/eliminarEnvento.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'id=' + encodeURIComponent(id)
          })
          .then(res => res.json())
      .then(data => {
  if (data.success) {
    Swal.fire({
      title: 'Eliminado',
      text: 'El evento fue eliminado con éxito.',
      icon: 'success',
      timer: 5000,
      timerProgressBar: true,
      showConfirmButton: false,
      customClass: {
        popup: 'cuerpo_modal_guardar'
      }
    }).then(() => location.reload());
  } else {
    Swal.fire({
      title: 'Error',
      text: data.error || 'No se pudo eliminar el evento',
      icon: 'error',
      customClass: {
        popup: 'cuerpo_modal_guardar'
      }
    });
  }
})

          .catch(() => {
            Swal.fire('Error', 'Error al intentar eliminar el evento', 'error');
          });
        }
      });
    }
    
    function verEvento(id) {
      fetch('modelos/rescatar/EventosCalendario.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + encodeURIComponent(id)
      })
        .then(res => res.json())
        .then(data => {
          if (data.error) {
            Swal.fire('Error', data.error, 'error');
            return;
          }
    
          // Iconos visuales
          const iconos = [];
          if (data.con_audio) iconos.push('<i class="bi bi-volume-up-fill text-success fs-5 me-2" title="Con Audio"></i>');
          if (data.solo_presentacion) iconos.push('<i class="bi bi-display text-primary fs-5 me-2" title="Solo Presentación"></i>');
          if (data.musica_ambiental) iconos.push('<i class="bi bi-music-note-beamed text-warning fs-5" title="Música Ambiental"></i>');
    
          // Construir HTML del contenido del offcanvas
          const contenido = `
            <div class="mb-3">
              <h5 class="fw-bold mb-2">${data.titulo}</h5>
              <div class="text-muted mb-2">
                <i class="bi bi-calendar-event me-1"></i> ${data.fecha_inicio}
                <i class="bi bi-clock ms-3 me-1"></i> ${data.hora_evento}
              </div>
              <div class="mb-3">${iconos.join('')}</div>
              <p class="mb-3">${data.descripcion}</p>
              <hr class="mb-3">
              <div class="d-flex align-items-center gap-2 mb-3">
                <div class="avatar rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                  <i class="bi bi-person-fill"></i>
                </div>
                <div>
                  <div class="fw-semibold">Responsable</div>
                <small class="text-muted">
                  ${data.nombre ? `${data.nombre} - ${data.apellido_paterno}` : 'No asignado'}
                </small>
                </div>
              </div>
              <div class="text-end mt-4">
                <button class="btn btn-outline-danger btn-sm" onclick="eliminarEventoConfirmado(${id})">
                  <i class="bi bi-trash3 me-1"></i> Eliminar
                </button>
              </div>
              <hr>
              <div class="text-muted small">Creado en: ${data.creado_en}</div>
            </div>
          `;
    
          document.getElementById('offcanvasContenidoEvento').innerHTML = contenido;
          const myOffcanvas = new bootstrap.Offcanvas('#offcanvasEvento');
          myOffcanvas.show();
        })
        .catch(err => {
          console.error(err);
          Swal.fire('Error', 'No se pudo obtener el evento', 'error');
        });
    }

</script>


</body>

</html>