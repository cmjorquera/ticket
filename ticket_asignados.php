<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';
$funciones = new Funciones();

$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$nombresession = htmlspecialchars($_SESSION['nombre']) . '--' . htmlspecialchars($_SESSION['apellido_paterno']);
$idPagActual = 5;
$versionModalesTicket = @filemtime(__DIR__ . '/css/modalesTicket.css') ?: time();
$versionTicketAdminCss = @filemtime(__DIR__ . '/css/ticket_admin.css') ?: time();
$versionTicketJs = @filemtime(__DIR__ . '/js/ticket.js') ?: time();
?>
<script>
    var nombresession = <?php echo json_encode($nombresession); ?>;
    var idUsuarioSession = <?php echo json_encode($idUsuarioSession); ?>;
</script>

<!DOCTYPE html>
<html lang="en">
<head>
<?php $funciones->header(); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <link href="css/contenedor_estados.css" rel="stylesheet">
    <link href="css/modalesTicket.css?v=<?php echo $versionModalesTicket; ?>" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/cronologiaTicket.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <link href="css/ticket_admin.css?v=<?php echo $versionTicketAdminCss; ?>" rel="stylesheet">
    <script type="text/javascript" src="js/chat_ticket.js"></script>
    <script type="text/javascript" src="js/funciones.js"></script>
    <script type="text/javascript" src="js/buscadores.js"></script>
    <script type="text/javascript" src="js/toast.js"></script>
    <script type="text/javascript" src="js/cronologiaTicket.js"></script>
    <script type="text/javascript" src="js/ticket.js?v=<?php echo $versionTicketJs; ?>"></script>
</head>
<style>
.dropdown-menu .dropdown-item:hover {
  background-color: #f8f9fa;
  font-weight: 500;
  color: #212529;
}

#contenedorTablaTecnico #tablaTecnicoTicketAsignados {
  font-size: 0.84rem !important;
}

#contenedorTablaTecnico #tablaTecnicoTicketAsignados thead th {
  font-size: 0.78rem !important;
}

#contenedorTablaTecnico #tablaTecnicoTicketAsignados .ticket-fecha-hora__fecha {
  font-size: 0.79rem !important;
}

#contenedorTablaTecnico #tablaTecnicoTicketAsignados .ticket-fecha-hora__hora {
  font-size: 0.71rem !important;
}

#contenedorTablaTecnico #tablaTecnicoTicketAsignados .ticket-resumen-estado__titulo {
  font-size: 0.78rem !important;
}

#contenedorTablaTecnico #tablaTecnicoTicketAsignados .ticket-resumen-estado__detalle {
  font-size: 0.73rem !important;
}
</style>
<body id="page-top">
    <div id="wrapper">
        <?php $funciones->menuLateral3($idUsuarioSession, $idPagActual); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>

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

                <div class="container-fluid">
                    <div class="contenedor-estados-ticket" id="idContenedoresEstadosTicket">
                        <?php
                            $funciones->contenedorTicketRecibidos($idUsuarioSession, $idPagActual);
                            $funciones->contenedorTicketAsignados($idUsuarioSession, $idPagActual);
                            $funciones->contenedorTicketEnProceso($idUsuarioSession, $idPagActual);
                            $funciones->contenedorTicketTerminados($idUsuarioSession, $idPagActual);
                            $funciones->contenedorTicketDemorados($idUsuarioSession, $idPagActual);
                        ?>
                    </div><br>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">Tickets Tecnico</h6>
                        </div>
                        <div class="card-body" id="contenedorTablaTecnico">
                            <?php include('componentes/bloque_tabla_tecnico.php'); ?>
                        </div>
                    </div>
                </div>
                <?php $funciones->footer(); ?>
            </div>
        </div>

        <div id="offcanvasContainerTicket"></div>
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
        <?php include('modal_salir.php'); ?>
        <?php $funciones->script(); ?>
    </div>

    <script>
    $(document).ready(function () {
        let tablaTecnico = null;
        let filtroActivo = false;
        let estadoFiltrado = null;
        let ultimaHuellaTabla = null;

        function inicializarTablaTecnico() {
            if ($.fn.DataTable.isDataTable('#tablaTecnicoTicketAsignados')) {
                tablaTecnico = $('#tablaTecnicoTicketAsignados').DataTable();
                return;
            }

            tablaTecnico = $('#tablaTecnicoTicketAsignados').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                responsive: true,
                pageLength: 10
            });
        }

        function actualizarTablaTecnico() {
            const datos = { idUsuarioSession: <?= (int) $_SESSION['id']; ?> };

            if (filtroActivo && estadoFiltrado !== null) {
                datos.estado = estadoFiltrado;
            }

            $.post('componentes/ajax/bloque_tabla_tecnico.php', datos, function (data) {
                const nuevoTbody = $('<div>').html(data).find('#tablaTecnicoTicketAsignados tbody').html();
                if (typeof nuevoTbody === 'undefined') {
                    return;
                }

                inicializarTablaTecnico();

                const huellaNueva = nuevoTbody.replace(/\s+/g, ' ').trim();
                if (huellaNueva === ultimaHuellaTabla) {
                    return;
                }

                ultimaHuellaTabla = huellaNueva;

                const filasNuevas = $('<table><tbody>' + nuevoTbody + '</tbody></table>').find('tbody tr').toArray();

                tablaTecnico.clear();
                tablaTecnico.rows.add(filasNuevas);
                tablaTecnico.draw(false);
            });
        }

        inicializarTablaTecnico();
        ultimaHuellaTabla = $('#tablaTecnicoTicketAsignados tbody').html()?.replace(/\s+/g, ' ').trim() || '';
        setInterval(actualizarTablaTecnico, 3000);

        window.filtrarTickets = function (estado) {
            if (filtroActivo && estadoFiltrado === estado) {
                filtroActivo = false;
                estadoFiltrado = null;
            } else {
                filtroActivo = true;
                estadoFiltrado = estado;
            }

            actualizarTablaTecnico();
        };

        window.mostrarTodosLosTickets = function () {
            filtroActivo = false;
            estadoFiltrado = null;
            actualizarTablaTecnico();
        };
    });
    </script>
</body>
</html>

