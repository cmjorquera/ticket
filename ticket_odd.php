<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';
$funciones = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$nombresession = htmlspecialchars($_SESSION['nombre']) . '--' . htmlspecialchars($_SESSION['apellido_paterno']);
$AreaTrabajo = htmlspecialchars($_SESSION['id_area_trabajo']);
$idPagActual = 3;
$versionModalesTicket = @filemtime(__DIR__ . '/css/modalesTicket.css') ?: time();
$versionTicketAdminCss = @filemtime(__DIR__ . '/css/ticket_admin.css') ?: time();
$versionTicketJs = @filemtime(__DIR__ . '/js/ticket.js') ?: time();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <?php $funciones->header(); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <link href="css/contenedor_estados.css" rel="stylesheet">
    <link href="css/modalesTicket.css?v=<?php echo $versionModalesTicket; ?>" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/cronologiaTicket.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <link href="css/ticket_admin.css?v=<?php echo $versionTicketAdminCss; ?>" rel="stylesheet">
    <script src="js/funciones.js"></script>
    <script src="js/mensajes.js"></script>
    <script src="js/ticket.js?v=<?php echo $versionTicketJs; ?>"></script>
    <script src="js/buscadores.js"></script>
    <script src="js/validacionTicket.js"></script>
    <script src="js/chat_ticket.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/intro.js/minified/introjs.min.css">
    <script src="https://unpkg.com/intro.js/minified/intro.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <script src="js/comunes.js"></script>
    <script src="js/equipos.js"></script>
    <script src="js/dispositivos.js"></script>
</head>

<script>
    function iniciarTour() {
      introJs().start();
    }
</script>



<body id="page-top">
    <div id="wrapper">
        <?php $funciones->menuLateral2($idUsuarioSession, $idPagActual); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>

                <!-- <button id="bi" class="btn btn-light border rounded-circle" onclick="iniciarTour()" title="Guia rapida">
                    <i class="bi bi-info-circle-fill text-primary fs-3"></i>
                </button> -->

                <div class="container-fluid">
                    <div class="contenedor-estados-ticket" id="idContenedoresEstadosTicket">
                        <?php
                            // $funciones->contenedorTicketRecibidos($idUsuarioSession, $idPagActual);
                            $funciones->contenedorTicketAsignados($idUsuarioSession, $idPagActual);
                            $funciones->contenedorTicketEnProceso($idUsuarioSession, $idPagActual);
                            $funciones->contenedorTicketTerminados($idUsuarioSession, $idPagActual);
                            $funciones->contenedorTicketDemorados($idUsuarioSession, $idPagActual);
                        ?>
                    </div><br>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary" data-intro="Aca puedes ver la tabla de tickets creados y agregar uno nuevo.">
                                <a href="#" class="btn btn-primary btn-icon-split" id="buttonAgregarTicket" onclick="crearTicketGuadalupe(<?php echo (int) $idUsuarioSession; ?>)">
                                    <span class="text">Agregar ticket</span>
                                </a>
                            </h6>
                        </div>
                        <div class="card-body" id="contenedorTablaUsuario">
                            <?php include('componentes/bloque_tabla_usuario.php'); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div id="offcanvasContainerTicket"></div>

            <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
            </a>

            <?php $funciones->footer(); ?>
        </div>
    </div>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAsunto" aria-labelledby="offcanvasAsuntoLabel">
      <div class="offcanvas-header bg-light border-bottom shadow-sm" style="background-color: #f5f7fa;">
        <h5 class="offcanvas-title text-primary fw-bold" id="offcanvasAsuntoLabel">
          <i class="bi bi-chat-left-text me-2"></i>Detalle del Asunto
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
      </div>

      <div class="offcanvas-body p-4" id="contenidoOffcanvasAsunto" style="background-color: #f5f7fa;">
      </div>
    </div>

    <?php $funciones->script(); ?>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.forEach(function (popoverTriggerEl) {
          new bootstrap.Popover(popoverTriggerEl);
        });
      });
    </script>

<script>
$(document).ready(function () {
    let tablaUsuario = null;
    let filtroActivo = false;
    let estadoFiltrado = null;
    let ultimaHuellaTabla = null;

    function inicializarTablaUsuario() {
        if ($.fn.DataTable.isDataTable('#tablaUsuario')) {
            tablaUsuario = $('#tablaUsuario').DataTable();
            return;
        }

        tablaUsuario = $('#tablaUsuario').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            responsive: true,
            pageLength: 10
        });
    }

    function actualizarTablaUsuario() {
        const datos = { idUsuarioSession: <?= (int) $_SESSION['id']; ?> };

        if (filtroActivo && estadoFiltrado !== null) {
            datos.estado = estadoFiltrado;
        }

        $.post('componentes/ajax/bloque_tabla_usuario.php', datos, function (data) {
            const nuevoTbody = $('<div>').html(data).find('#tablaUsuario tbody').html();
            if (typeof nuevoTbody === 'undefined') {
                return;
            }

            inicializarTablaUsuario();

            const huellaNueva = nuevoTbody.replace(/\s+/g, ' ').trim();
            if (huellaNueva === ultimaHuellaTabla) {
                return;
            }

            ultimaHuellaTabla = huellaNueva;

            const filasNuevas = $('<table><tbody>' + nuevoTbody + '</tbody></table>').find('tbody tr').toArray();
            tablaUsuario.clear();
            tablaUsuario.rows.add(filasNuevas);
            tablaUsuario.draw(false);
        });
    }

    inicializarTablaUsuario();
    ultimaHuellaTabla = $('#tablaUsuario tbody').html()?.replace(/\s+/g, ' ').trim() || '';
    setInterval(actualizarTablaUsuario, 3000);

    window.filtrarTickets = function (estado) {
        if (filtroActivo && estadoFiltrado === estado) {
            filtroActivo = false;
            estadoFiltrado = null;
        } else {
            filtroActivo = true;
            estadoFiltrado = estado;
        }

        actualizarTablaUsuario();
    };

    window.mostrarTodosLosTickets = function () {
        filtroActivo = false;
        estadoFiltrado = null;
        actualizarTablaUsuario();
    };
});
</script>

<script>
function mostrarOffcanvasAsunto(idTicket) {
  const myOffcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvasAsunto'));
  myOffcanvas.show();

  $.ajax({
    url: 'modelos/rescatar/asunto_del_ticket.php',
    type: 'POST',
    data: { id_ticket: idTicket },
    beforeSend: function () {
      $('#contenidoOffcanvasAsunto').html('<div class="text-center text-muted">Cargando...</div>');
    },
    success: function (data) {
      $('#contenidoOffcanvasAsunto').html(data);
    },
    error: function () {
      $('#contenidoOffcanvasAsunto').html('<div class="text-danger">Error al cargar la informacion</div>');
    }
  });
}
</script>
</body>
</html>
