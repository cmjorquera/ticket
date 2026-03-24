<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';
$funciones = new Funciones();

$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$nombresession = htmlspecialchars($_SESSION['nombre']) . '--' . htmlspecialchars($_SESSION['apellido_paterno']);
$idPagActual = 4;
$versionModalesTicket = @filemtime(__DIR__ . '/css/modalesTicket.css') ?: time();
$versionTicketJs = @filemtime(__DIR__ . '/js/ticket.js') ?: time();
$versionPruebaCss = @filemtime(__DIR__ . '/css/ticket_admin_prueba.css') ?: time();
?>
<script>
var nombresession = "<?php echo $nombresession; ?>";
var idUsuarioSession = "<?php echo $idUsuarioSession; ?>";
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

    <link href="css/modalesTicket.css?v=<?php echo $versionModalesTicket; ?>" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/cronologiaTicket.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <link href="css/ticket_admin.css" rel="stylesheet">
    <link href="css/ticket_admin_prueba.css?v=<?php echo $versionPruebaCss; ?>" rel="stylesheet">
    <script type="text/javascript" src="js/chat_ticket.js"></script>
    <script type="text/javascript" src="js/funciones.js"></script>
    <script type="text/javascript" src="js/buscadores.js"></script>
    <script type="text/javascript" src="js/toast.js"></script>
    <script type="text/javascript" src="js/cronologiaTicket.js"></script>
    <script type="text/javascript" src="js/ticket.js?v=<?php echo $versionTicketJs; ?>"></script>
</head>

<body id="page-top" class="ticket-admin-prueba-body">
    <div id="wrapper">
        <?php $funciones->menuLateral3($idUsuarioSession, $idPagActual); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow">
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
                    <div class="offcanvas-body p-4" id="contenidoOffcanvasAsunto" style="background-color: #f5f7fa;"></div>
                </div>

                <div class="container-fluid ticket-admin-prueba-shell">
                    <div class="card tap-panel mb-4">
                        <div class="card-body p-4 p-lg-5">
                            <div class="tap-hero">
                                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                    <div>
                                        <span class="tap-kicker">Modulo institucional</span>
                                        <h1 class="tap-title">Gestion de Tickets Administrador</h1>
                                        <p class="tap-subtitle">Supervisa tickets, asigna tecnicos y revisa el estado operativo de cada requerimiento desde una vista mas limpia, cercana al estilo del inventario.</p>
                                    </div>
                                    <div class="tap-badge-pill">
                                        <i class="bi bi-ticket-detailed"></i>
                                        Vista de prueba
                                    </div>
                                </div>
                            </div>

                            <div class="tap-status-grid" id="idContenedoresEstadosTicket">
                                <?php
                                $funciones->contenedorTicketRecibidos($idUsuarioSession, $idPagActual);
                                $funciones->contenedorTicketAsignados($idUsuarioSession, $idPagActual);
                                $funciones->contenedorTicketEnProceso($idUsuarioSession, $idPagActual);
                                $funciones->contenedorTicketTerminados($idUsuarioSession, $idPagActual);
                                $funciones->contenedorTicketDemorados($idUsuarioSession, $idPagActual);
                                ?>
                            </div>

                            <div class="card tap-table-card">
                                <div class="card-body" id="contenedorTablaAdminPrueba">
                                    <div class="tap-table-toolbar">
                                        <div>
                                            <h2 class="tap-section-title">Tickets Administrador</h2>
                                            <p class="tap-section-subtitle">Misma informacion operativa actual, pero presentada con una interfaz mas clara y mas cercana al modulo de inventario.</p>
                                        </div>
                                        <div class="tap-badge-pill">
                                            <i class="bi bi-layout-text-window-reverse"></i>
                                            Propuesta visual
                                        </div>
                                    </div>
                                    <?php include("componentes/bloque_tabla_admin.php"); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php $funciones->footer(); ?>
                </div>
            </div>
        </div>

        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
    </div>

    <?php include("modal_salir.php") ?>
    <?php $funciones->script(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

