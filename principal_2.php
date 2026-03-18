<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

    session_start();
    require_once 'class/conexion.php';
    require_once 'class/funciones.php';
    require_once 'componentes/offcanvas.php';
    
    
    $funciones = new Funciones();
    $idUsuarioSession = $_SESSION['id'];
    $nombre             = htmlspecialchars($_SESSION['nombre']) . ' ' . htmlspecialchars($_SESSION['apellido_paterno']);
    $AreaTrabajo        = htmlspecialchars($_SESSION['id_area_trabajo']);
    $idPagActual        = "8";
    $contadorPasos = 10;
    
    // Obtener alertas de cumpleaños y tickets
    // $alertas    = $funciones->alertaModal($idUsuarioSession);
    // $mensajes   = $funciones->mensajesModal($idUsuarioSession);
    // $estados    = $funciones->cargarEstadosTicket($idUsuarioSession);
            
        if ($_SESSION['primera_vez'] == 0) {
            $_SESSION['primera_vez'] = 1;
        }
$versionModalesTicket = @filemtime(__DIR__ . '/css/modalesTicket.css') ?: time();
$versionTicketAdminCss = @filemtime(__DIR__ . '/css/ticket_admin.css') ?: time();
$versionTicketJs = @filemtime(__DIR__ . '/js/ticket.js') ?: time();

    ?>

<script>
var idUsuarioSession = <?php echo json_encode($idUsuarioSession); ?>;
</script>

<!DOCTYPE html>
<html lang="en">


<head>
    <?php $funciones->header(); ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Incluir DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Incluir Bootstrap Icons -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Incluir jQuery -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <!-- Incluir DataTableswww JS -->
    <link href="css/modalesTicket.css" rel="stylesheet">
    <!-- Incluir ESTILOS -->

    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/cronologiaTicket.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <link href="css/ticket_admin.css?v=<?php echo $versionTicketAdminCss; ?>" rel="stylesheet">

    <script type="text/javascript" src="js/mensajes.js"></script>
    <script>
        window.ID_USUARIO_SESSION = <?= json_encode($_SESSION['id']) ?>;
    </script>
    <script src="js/funciones.js"></script>

    <script type="text/javascript" src="js/funciones.js"></script>
    <!--PARA LAS CONVERSACIONES DE WHASAP -->
    <script type="text/javascript" src="js/ticket.js"></script>
    <script type="text/javascript" src="js/buscadores.js"></script>
    <script type="text/javascript" src="js/validacionTicket.js"></script>
    <script type="text/javascript" src="js/tour.js"></script>
    <script type="text/javascript" src="js/cronologiaTicket.js"></script> <!-- PARA MOSTRAR EL MODAL DE CRONOLOGIA-->
    <script type="text/javascript" src="js/chat_ticket.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="css/bitacora.css"> <!-- PROPIO DE ESTA PAGINA -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="js/comunes.js"></script> <!-- FUNCIONES COMUNES -->
    <script src="js/equipos.js"></script> <!-- FUNCIONES DE COMPUTADORES -->
    <script src="js/dispositivos.js"></script> <!-- FUNCIONES DE DISPOSITIVOS -->
    <style>
        .swal-perfil-popup {
            border-radius: 28px;
            border: 1px solid rgba(109, 170, 214, 0.25);
            box-shadow: 0 30px 60px rgba(30, 73, 112, 0.22);
            padding: 0 0 1.35rem;
            overflow: hidden;
        }
        .swal-perfil-card {
            text-align: center;
            padding: 1.8rem 2rem 0.4rem;
        }
        .swal-perfil-badge {
            width: 88px;
            height: 88px;
            border-radius: 28px;
            margin: 0 auto 1.1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #edf7ff 0%, #dbeefe 100%);
            color: #1f80e8;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.7), 0 16px 30px rgba(44, 126, 196, 0.16);
        }
        .swal-perfil-badge i {
            font-size: 2.25rem;
        }
        .swal-perfil-title {
            margin: 0 0 .55rem;
            color: #17324d;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.1;
        }
        .swal-perfil-text {
            margin: 0;
            color: #56708b;
            font-size: 1rem;
            line-height: 1.45;
        }
        .swal2-actions .swal-perfil-confirm,
        .swal2-actions .swal-perfil-cancel {
            border-radius: 16px !important;
            padding: 0.85rem 1.35rem !important;
            font-weight: 700 !important;
            box-shadow: none !important;
        }
        .swal2-actions .swal-perfil-confirm {
            background: linear-gradient(135deg, #1f80e8, #3ca3ff) !important;
        }
        .swal2-actions .swal-perfil-cancel {
            background: #edf3f8 !important;
            color: #3a5876 !important;
        }
        .perfil-side-tabs-wrap {
            position: fixed;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1040;
            display: flex;
            justify-content: flex-end;
        }
        .perfil-side-tabs {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.9rem;
        }
        .perfil-side-tab {
            width: 72px;
            padding: 0.0rem;
            border-radius: 22px 0 0 22px;
            border: 1px solid #dfe8f4;
            background: #ffffff;
            color: #4f6783;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            box-shadow: 0 10px 24px rgba(33, 69, 110, 0.10);
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease, background .2s ease;
        }
        .perfil-side-tab:hover {
            color: #17324d;
            transform: translateX(-8px);
            box-shadow: 0 14px 28px rgba(33, 69, 110, 0.14);
        }
        .perfil-side-tab.is-active {
            width: 150px;
            transform: translateX(-18px);
            border-color: #3b82f6;
            background: #ffffff;
            color: #1f3b5b;
            box-shadow: 0 16px 32px rgba(59, 130, 246, 0.16);
            justify-content: flex-start;
            gap: 0.8rem;
        }
        .perfil-side-tab-icon {
            width: 44px;
            height: 35px;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #fafdff 0%, #eef5ff 100%);
            color: #3b82f6;
            flex: 0 0 44px;
            box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.16);
        }
        .perfil-side-tab.is-active .perfil-side-tab-icon {
            background: linear-gradient(180deg, #f5faff 0%, #e4efff 100%);
            box-shadow: inset 0 0 0 1px rgba(59, 130, 246, 0.20);
        }
        .perfil-side-tab-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.1;
            display: none;
        }
        .perfil-side-tab.is-active .perfil-side-tab-title {
            display: inline;
        }
        .dashboard-loading-card {
            position: relative;
            overflow: hidden;
        }
        .dashboard-loading-overlay {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 0.65rem;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(2px);
            z-index: 15;
        }
        .dashboard-loading-overlay.is-visible {
            display: flex;
        }
        .dashboard-loading-spinner {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            border: 4px solid #dbeafe;
            border-top-color: #3b82f6;
            animation: dashboardSpin .9s linear infinite;
        }
        .dashboard-loading-text {
            margin: 0;
            color: #36506d;
            font-size: 0.98rem;
            font-weight: 700;
        }
        @keyframes dashboardSpin {
            to {
                transform: rotate(360deg);
            }
        }
        @media (max-width: 768px) {
            .perfil-side-tabs-wrap {
                position: static;
                transform: none;
                margin-bottom: 1rem;
                justify-content: stretch;
            }
            .perfil-side-tabs {
                width: 100%;
                align-items: stretch;
            }
            .perfil-side-tab,
            .perfil-side-tab.is-active {
                width: 50%;
                transform: none;
                border-radius: 16px;
                justify-content: flex-start;
                gap: 0.8rem;
            }
            .perfil-side-tab .perfil-side-tab-title {
                display: inline;
            }
        }
    </style>

</head>
<script>
    function iniciarTour() {
  introJs().start();
}

</script>
<?php
    mostrarOffcanvasActividad();
    mostrarOffcanvasConversacion();
?>
<!-- container para ver los el char de conversacion -->
<div id="offcanvasContainerTicket"></div>
<div id="offcanvasContainer"></div>
<style>
/*#bi {*/
/*  position: fixed;*/
/*  bottom: 20px;*/
/*  right: 20px;*/
/*  z-index: 9999;*/
/*  width: 50px;*/
/*  height: 50px;*/
/*  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);*/
/*}*/
</style>
<body id="page-top">
    <div id="wrapper">
        <?php $funciones->menuLateral2($idUsuarioSession, $idPagActual); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>
                   <!-- <button id="bi" class="btn btn-light border rounded-circle" onclick="iniciarTour()" title="Guía rápida">
                      <i class="bi bi-info-circle-fill text-primary fs-3"></i>
                    </button> -->
                    
                <!-- BUTTON DE ACCESO DIRECTO  ********************-->
                <div id="botonesDescargar"><br><br>
                    <!-- Botón 1: Conversar -->
             <?php
                $bloqueados = [28,29,30,31,32,33,34];
                if (!in_array((int)$idUsuarioSession, $bloqueados, true)) :
                ?>
            
                <?php endif; ?>


                    <!-- Botón 2: Actividad -->
                    <!--<button class="btn btn-light border w-100 rounded-pill shadow-sm d-flex justify-content-center align-items-center gap-2 py-2"-->
                    <!--        type="button"-->
                    <!--        data-bs-toggle="offcanvas"-->
                    <!--        data-bs-target="#offcanvasActividad"-->
                    <!--        aria-controls="offcanvasActividad"-->
                    <!--        title="Actividades Recientes">-->
                    <!--    <i class="bi bi-clock-history text-primary fs-5"></i>-->
                    <!--    <span class="text-dark fw-semibold"></span>-->
                    <!--</button>-->

                    <!-- Botón 3: Nuevo Recordatorio -->
                    <!-- <button
                        class="btn btn-light border w-100 rounded-pill shadow-sm d-flex justify-content-center align-items-center gap-2 py-2"
                        type="button" onclick="mostrarAlertas(<?= $idUsuarioSession; ?>)" title="Agregar recordatorio">
                        <i class="bi bi-clipboard-data-fill text-primary fs-10"></i>
                        <span class="text-dark fw-semibold"></span>
                    </button> -->

                    <!-- Botón 4: Perfil Actual -->
                    <?php
                    $bdato = new MySQL("", "", "");
                    $sql = "SELECT id_perfil FROM usuario_perfil WHERE id_usuario = $idUsuarioSession";
                    $resultado = $bdato->consulta($sql);
                    $perfilesUsuario = [];
                    
                    while ($fila = $bdato->fetch_array($resultado)) {
                        $perfilesUsuario[] = $fila['id_perfil'];
                    }
                    $perfilesDashboardDisponibles = [];
                    if (in_array(3, $perfilesUsuario)) {
                        $perfilesDashboardDisponibles[] = ['key' => 'admin', 'label' => 'Admin', 'icon' => 'bi-person-gear'];
                    }
                    if (in_array(1, $perfilesUsuario)) {
                        $perfilesDashboardDisponibles[] = ['key' => 'usuario', 'label' => 'Usuario', 'icon' => 'bi-person-circle'];
                    }
                    if (in_array(2, $perfilesUsuario)) {
                        $perfilesDashboardDisponibles[] = ['key' => 'tecnico', 'label' => 'Tecnico', 'icon' => 'bi-wrench-adjustable-circle'];
                    }
                    $perfilDashboardInicial = 'usuario';
                    if (in_array(3, $perfilesUsuario)) {
                        $perfilDashboardInicial = 'admin';
                    } elseif (in_array(2, $perfilesUsuario)) {
                        $perfilDashboardInicial = 'tecnico';
                    } elseif (in_array(1, $perfilesUsuario)) {
                        $perfilDashboardInicial = 'usuario';
                    }
                    $vistaPerfilSolicitada = $_GET['vista_perfil'] ?? null;
                    $perfilesDashboardKeys = array_column($perfilesDashboardDisponibles, 'key');
                    if ($vistaPerfilSolicitada && in_array($vistaPerfilSolicitada, $perfilesDashboardKeys, true)) {
                        $perfilDashboardInicial = $vistaPerfilSolicitada;
                    }
                    ?>
                </div>
                <!-- *********************************************  -->

                <div class="container-fluid">
                    <?php if (count($perfilesDashboardDisponibles) > 1): ?>
                    <div class="perfil-side-tabs-wrap">
                        <div class="perfil-side-tabs">
                            <?php foreach ($perfilesDashboardDisponibles as $perfilTab): ?>
                                <a class="perfil-side-tab <?= $perfilDashboardInicial === $perfilTab['key'] ? 'is-active' : ''; ?>"
                                   href="principal_2.php?vista_perfil=<?= urlencode($perfilTab['key']) ?>">
                                    <span class="perfil-side-tab-icon">
                                        <i class="bi <?= $perfilTab['icon'] ?>"></i>
                                    </span>
                                    <span class="perfil-side-tab-title"><?= htmlspecialchars($perfilTab['label']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div id="contenedorEstadosUsuario" class="contenedor-estados-ticket" style="display: <?= ($perfilDashboardInicial === 'usuario') ? 'grid' : 'none'; ?>;">
                        <?php
                            $funciones->contenedorTicketRecibidos($idUsuarioSession, 3);
                            $funciones->contenedorTicketAsignados($idUsuarioSession, 3);
                            $funciones->contenedorTicketEnProceso($idUsuarioSession, 3);
                            $funciones->contenedorTicketTerminados($idUsuarioSession, 3);
                            $funciones->contenedorTicketDemorados($idUsuarioSession, 3);
                        ?>
                    </div>
                    <div id="contenedorEstadosTecnico" class="contenedor-estados-ticket" style="display: <?= ($perfilDashboardInicial === 'tecnico') ? 'grid' : 'none'; ?>;">
                        <?php
                            $funciones->contenedorTicketRecibidos($idUsuarioSession, 5);
                            $funciones->contenedorTicketAsignados($idUsuarioSession, 5);
                            $funciones->contenedorTicketEnProceso($idUsuarioSession, 5);
                            $funciones->contenedorTicketTerminados($idUsuarioSession, 5);
                            $funciones->contenedorTicketDemorados($idUsuarioSession, 5);
                        ?>
                    </div>
                    <div id="contenedorEstadosAdmin" class="contenedor-estados-ticket" style="display: <?= ($perfilDashboardInicial === 'admin') ? 'grid' : 'none'; ?>;">
                        <?php
                            $funciones->contenedorTicketRecibidos($idUsuarioSession, 4);
                            $funciones->contenedorTicketAsignados($idUsuarioSession, 4);
                            $funciones->contenedorTicketEnProceso($idUsuarioSession, 4);
                            $funciones->contenedorTicketTerminados($idUsuarioSession, 4);
                            $funciones->contenedorTicketDemorados($idUsuarioSession, 4);
                        ?>
                    </div><br>
                    <div class="row">
                        <!-- CONTENEDOR 1: Gráfico de Tickets (BARRAS SEGUN ESTADOS DE LOS TICKETS) -->
                        <div class="col-lg-6 col-sm-12">
                            <div class="card shadow mb-4 p-4 dashboard-loading-card" data-intro="En este gráfico de barras puedes visualizar la cantidad de tickets clasificados según su estado actual, como Recibido, En proceso, Terminado, entre otros. Esta vista te permite identificar rápidamente el avance y distribución de los tickets en el sistema..">
                                <div class="d-flex justify-content-between align-items-center mb-3" >
                                    <h5 class="card-title m-0 text-primary fw-bold">Gráfico de Tickets</h5>
                                        <?php
                                        $bdato = new MySQL("", "", "");
                                        $sql = "SELECT id_perfil FROM usuario_perfil WHERE id_usuario = $idUsuarioSession";
                                        $resultado = $bdato->consulta($sql);
                                        $perfilesUsuario = [];
                                        while ($fila = $bdato->fetch_array($resultado)) {
                                            $perfilesUsuario[] = $fila['id_perfil'];
                                        }
                                        // Mostrar el menú solo si tiene más de un perfil
                                        if (count($perfilesUsuario) > 1):
                                        ?>
                                            <div class="filter"> 
                                                <a class="icon" href="#" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                                    <li class="dropdown-header text-start">
                                                        <h6>Filtrar por Perfil</h6>
                                                    </li>
                                                    <?php
                                                    if (in_array(1, $perfilesUsuario)) {
                                                        echo '<li><a class="dropdown-item" href="#" onclick="generarGraficoTicket(\'usuario\'); return false;">Usuario</a></li>';
                                                    }
                                                    if (in_array(2, $perfilesUsuario)) {
                                                        echo '<li><a class="dropdown-item" href="#" onclick="generarGraficoTicket(\'tecnico\'); return false;">Técnico</a></li>';
                                                    }
                                                    if (in_array(3, $perfilesUsuario)) {
                                                        echo '<li><a class="dropdown-item" href="#" onclick="generarGraficoTicket(\'admin\'); return false;">Admin</a></li>';
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                </div>

                                <div class="dashboard-loading-overlay" id="loadingGraficoTicket">
                                    <div class="dashboard-loading-spinner"></div>
                                    <p class="dashboard-loading-text">Cargando gráfico...</p>
                                </div>
                                <div id="contenedorGraficoTicket">
                                    <canvas id="canvasGraficoTicket" height="500px"></canvas>
                                </div>

                                <!-- Spinner de carga del gráfico -->
                                <div id="spinnerGrafico" style="display: none; text-align: center; margin-top: 20px;">
                                    <button class="btn btn-primary" type="button" disabled>
                                        <span class="spinner-border spinner-border-sm" role="status"
                                            aria-hidden="true"></span>
                                        <span>Cargando gráfico...</span>
                                    </button>
                                </div>
                                <!-- Spinner de carga del gráfico -->

                            </div>
                        </div>


                        <?php
                        $bdato = new MySQL("", "", "");
                        $sqlPerfil = "SELECT p.nombre FROM usuario_perfil up 
                                      JOIN perfiles p ON up.id_perfil = p.id_perfil 
                                      WHERE up.id_usuario = $idUsuarioSession";
                        $resultPerfil = $bdato->consulta($sqlPerfil);
                        $perfiles = [];
                        while ($row = $bdato->fetch_array($resultPerfil)) {
                            $perfiles[] = strtolower($row['nombre']);
                        }
                    
                        $perfilPorDefecto = null;
                        $orden = ['tecnico', 'usuario', 'administrador'];
                        foreach ($orden as $opcion) {
                            if (in_array($opcion, $perfiles)) {
                                $perfilPorDefecto = $opcion;
                                break;
                            }
                        }
                    ?>
                        <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            const perfilPorDefecto = '<?= $perfilDashboardInicial ?>';
                            if (perfilPorDefecto) {
                                generarGraficoTicket(perfilPorDefecto);
                            } else {
                                generarGraficoTicket('auto');
                            }
                        });

                        document.addEventListener("DOMContentLoaded", () => {
                            cargarGraficoPorPerfil('<?= $perfilDashboardInicial ?>');
                        });
                        </script>

                        <!-- CONTENEDOR 2: Estados por Categoría de Ticket -->
                        <div class="col-lg-6 col-sm-12 ">
                            <div class="card shadow mb-4 p-4 dashboard-loading-card" data-intro="Este gráfico de líneas muestra la distribución de tickets según su estado en cada categoría. Permite visualizar cómo evolucionan los tickets (Recibido, En proceso, Terminado, etc.) dentro de distintas áreas, como Correos u Otros, facilitando el análisis por tipo de solicitud..">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title m-0 text-primary fw-bold">Estados por Categoría de Ticket</h5>
                                        <?php
                                        $bdato = new MySQL("", "", "");
                                        $sqlPerfil = "SELECT id_perfil FROM usuario_perfil WHERE id_usuario = $idUsuarioSession";
                                        $resultPerfil = $bdato->consulta($sqlPerfil);
                                        $perfiles = [];
                                        while ($row = $bdato->fetch_array($resultPerfil)) {
                                            $perfiles[] = $row['id_perfil'];
                                        }
                                        
                                        // Mostrar menú solo si hay más de un perfil
                                        if (count($perfiles) > 1):
                                        ?>
                                            <div class="filter">
                                                <a class="icon" href="#" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </a>
                                                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                                    <li class="dropdown-header text-start">
                                                        <h6>Perfiles</h6>
                                                    </li>
                                                    <?php if (in_array(1, $perfiles)): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="cargarGraficoPorPerfil('usuario'); return false;">Usuario</a></li>
                                                    <?php endif; ?>
                                                    <?php if (in_array(2, $perfiles)): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="cargarGraficoPorPerfil('tecnico'); return false;">Técnico</a></li>
                                                    <?php endif; ?>
                                                    <?php if (in_array(3, $perfiles)): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="cargarGraficoPorPerfil('admin'); return false;">Administrador</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
                                </div>
                                <div class="dashboard-loading-overlay" id="loadingGraficoCategorias">
                                    <div class="dashboard-loading-spinner"></div>
                                    <p class="dashboard-loading-text">Cargando gráfico...</p>
                                </div>
                                <div id="grafico_estados_categoria"></div>
                            </div>
                        </div>

                        <?php
                            $bdato = new MySQL("", "", "");
                            $sqlPerfil = "SELECT p.nombre FROM usuario_perfil up JOIN perfiles p ON up.id_perfil = p.id_perfil WHERE up.id_usuario = $idUsuarioSession";
                            $resultPerfil = $bdato->consulta($sqlPerfil);
                            $perfiles = [];
                            while ($row = $bdato->fetch_array($resultPerfil)) {
                                $perfiles[] = strtolower($row['nombre']);
                            }
                            $perfilPorDefecto = null;
                            $orden = ['tecnico', 'usuario', 'administrador'];
                            foreach ($orden as $opcion) {
                                if (in_array($opcion, $perfiles)) {
                                    $perfilPorDefecto = $opcion;
                                    break;
                                }
                            }
                        ?>

                        <!-- Contenedor Listado de Tickets -->
                        <div class="col-12">
                            <div class="card shadow mb-4 px-0 dashboard-loading-card" data-step=12>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center px-3 mt-2">
                                        <h6 class="m-0 font-weight-bold text-primary">Listado de Tickets</h6>
                                            <?php
                                            $perfiles = []; // Asegúrate de que esta variable se cargue antes
                                            $bdato = new MySQL("", "", "");
                                            $sql = "SELECT id_perfil FROM usuario_perfil WHERE id_usuario = $idUsuarioSession";
                                            $res = $bdato->consulta($sql);
                                            
                                            // Mapear id_perfil a nombres que estás usando en el array (usuario, tecnico, administrador)
                                            while ($fila = $bdato->fetch_array($res)) {
                                                switch ($fila['id_perfil']) {
                                                    case 1: $perfiles[] = 'usuario'; break;
                                                    case 2: $perfiles[] = 'tecnico'; break;
                                                    case 3: $perfiles[] = 'administrador'; break;
                                                }
                                            }
                                            
                                            if (count($perfiles) > 1):
                                            ?>
                                                <div class="dropdown">
                                                    <button class="btn btn-light" type="button" id="dropdownListado"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="bi bi-three-dots-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end"
                                                        aria-labelledby="dropdownListado">
                                                        <?php if (in_array('usuario', $perfiles)): ?>
                                                            <li><a class="dropdown-item" onclick="mostrarListado('usuario')">Usuario</a></li>
                                                        <?php endif; ?>
                                                        <?php if (in_array('tecnico', $perfiles)): ?>
                                                            <li><a class="dropdown-item" onclick="mostrarListado('tecnico')">Técnico</a></li>
                                                        <?php endif; ?>
                                                        <?php if (in_array('administrador', $perfiles)): ?>
                                                            <li><a class="dropdown-item" onclick="mostrarListado('admin')">Administrador</a></li>
                                                        <?php endif; ?>
                                                    </ul>
                                                </div>
                                            <?php endif; ?>
                                    </div>
                                    <div class="dashboard-loading-overlay" id="loadingListadoTickets">
                                        <div class="dashboard-loading-spinner"></div>
                                        <p class="dashboard-loading-text">Cargando listado...</p>
                                    </div>
                                    <div id="contenedorUsuario"
                                        style="display: <?= ($perfilDashboardInicial == 'usuario') ? 'block' : 'none'; ?>;">
                                        <?php  include("componentes/bloque_tabla_usuario.php") ?>
                                    </div>
                                    <div id="contenedorTecnico"
                                        style="display: <?= ($perfilDashboardInicial == 'tecnico') ? 'block' : 'none'; ?>;">
                                        <?php  include("componentes/bloque_tabla_tecnico.php") ?>
                                    </div>
                                    <div id="contenedorAdmin"
                                        style="display: <?= ($perfilDashboardInicial == 'admin') ? 'block' : 'none'; ?>;">
                                        <?php $GLOBALS['ticketAdminColorColumn'] = true; ?>
                                        <?php  include("componentes/bloque_tabla_admin.php") ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

<!-- ******************************************************** -->
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
<!-- ******************************************************** -->
                    <script>
                    function mostrarListado(tipo) {
                        const inicioCarga = Date.now();
                        cambiarVisibilidadCarga('loadingListadoTickets', true);
                        const contenedoresListado = ['contenedorUsuario', 'contenedorTecnico', 'contenedorAdmin'];
                        const contenedoresEstados = ['contenedorEstadosUsuario', 'contenedorEstadosTecnico', 'contenedorEstadosAdmin'];
                        const contenedorId = tipo === 'admin' ? 'contenedorAdmin'
                            : tipo === 'tecnico' ? 'contenedorTecnico'
                            : 'contenedorUsuario';
                        const contenedorEstadosId = tipo === 'admin' ? 'contenedorEstadosAdmin'
                            : tipo === 'tecnico' ? 'contenedorEstadosTecnico'
                            : 'contenedorEstadosUsuario';

                        contenedoresListado.forEach(id => {
                            const el = document.getElementById(id);
                            if (el) {
                                el.style.display = id === contenedorId ? 'block' : 'none';
                            }
                        });

                        contenedoresEstados.forEach(id => {
                            const el = document.getElementById(id);
                            if (el) {
                                el.style.display = id === contenedorEstadosId ? 'grid' : 'none';
                            }
                        });

                        if (typeof inicializarTablaPerfilDashboard === 'function') {
                            inicializarTablaPerfilDashboard(contenedorId);
                        }

                        esperarMinimoCarga(inicioCarga).then(() => {
                            cambiarVisibilidadCarga('loadingListadoTickets', false);
                        });
                    }
                    </script>


                    <a class="scroll-to-top rounded" href="#page-top">
                        <i class="fas fa-angle-up"></i>
                    </a>
                </div>
                <?php $funciones->footer(); ?>
            </div>
        </div>
        <?php include("modal_salir.php") ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <!--<script type='text/javascript' src='template_01/js/funciones.js'></script>-->
        <script type='text/javascript' src='js/apexcharts.min.js'></script>

        <!-- Inicialización de DataTables -->

        <!-- ******************************************************************** -->
        <!-- data.table -->
        <script>
        function normalizarPerfilDashboard(perfil) {
            if (!perfil) {
                return 'usuario';
            }

            return perfil === 'administrador' ? 'admin' : perfil.toLowerCase();
        }

        function esperarMinimoCarga(inicio, minimo = 1000) {
            const transcurrido = Date.now() - inicio;
            const restante = Math.max(0, minimo - transcurrido);
            return new Promise(resolve => setTimeout(resolve, restante));
        }

        function cambiarVisibilidadCarga(idOverlay, visible) {
            const overlay = document.getElementById(idOverlay);
            if (overlay) {
                overlay.classList.toggle('is-visible', visible);
            }
        }

        function mostrarCargaDashboard() {
            cambiarVisibilidadCarga('loadingGraficoTicket', true);
            cambiarVisibilidadCarga('loadingGraficoCategorias', true);
            cambiarVisibilidadCarga('loadingListadoTickets', true);
        }

        function prepararCambioPerfilConCarga(destino) {
            mostrarCargaDashboard();
            setTimeout(() => {
                window.location.href = destino;
            }, 1000);
        }

        // estas 2 funcione sno me correon si als pongo en el archivo funciones.js
        function cargarGraficoPorPerfil(perfil = 'usuario') {
            const perfilNormalizado = normalizarPerfilDashboard(perfil);
            const inicioCarga = Date.now();
            cambiarVisibilidadCarga('loadingGraficoCategorias', true);
            fetch('grafico_estados_categoria.php?perfil=' + perfilNormalizado)
                .then(res => res.json())
                .then(async data => {
                    await esperarMinimoCarga(inicioCarga);
                    const contenedor = document.querySelector("#grafico_estados_categoria");
                    // Validar si hay datos para mostrar
                    const hayDatos = data.series.length > 0 && data.series.some(serie => serie.data
                        .some(val => val > 0));
                    if (!hayDatos) {
                        contenedor.innerHTML = `
                            <div class="text-center text-muted py-5">
                              <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
                                    <p class="mt-2 mb-0">No hay tickets asociados para este perfil.</p>
                            </div>
                        `;
                        cambiarVisibilidadCarga('loadingGraficoCategorias', false);
                        return;
                    }
                    // Si hay datos, renderizar el gráfico
                    contenedor.innerHTML = '<div id="chartEstadosPorCategoria"></div>';

                    new ApexCharts(document.querySelector("#chartEstadosPorCategoria"), {
                        series: data.series,
                        chart: {
                            height: 330,
                            type: 'area',
                            toolbar: {
                                show: false
                            }
                        },
                        markers: {
                            size: 4
                        },
                        colors: [
                            '#4154f1', '#2eca6a', '#ff771d', '#e91e63', '#9c27b0',
                            '#3f51b5', '#00bcd4', '#8bc34a', '#ffc107', '#ff5722'
                        ],
                        fill: {
                            type: "gradient",
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.3,
                                opacityTo: 0.4,
                                stops: [0, 90, 100]
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 2
                        },
                        xaxis: {
                            categories: data.categorias
                        },
                        yaxis: {
                            min: 0,
                            tickAmount: 10,
                            labels: {
                                formatter: val => parseInt(val)
                            }
                        },
                        tooltip: {
                            x: {
                                show: true
                            }
                        }
                    }).render();
                    cambiarVisibilidadCarga('loadingGraficoCategorias', false);
                })
                .catch(() => {
                    cambiarVisibilidadCarga('loadingGraficoCategorias', false);
                });

        }


        function generarGraficoTicket(tipoUsuario = 'auto') {
            const idUsuarioSession = window.ID_USUARIO_SESSION || null;
            const perfilNormalizado = normalizarPerfilDashboard(tipoUsuario);
            const inicioCarga = Date.now();
            cambiarVisibilidadCarga('loadingGraficoTicket', true);
            let url = `modelos/filtros/filtro_estadoTicketGrafico.php?tipoUsuario=${perfilNormalizado}`;
            if (perfilNormalizado === "usuario" || perfilNormalizado === "tecnico") {
                url += `&idUsuario=${idUsuarioSession}`;
            }

            fetch(url)
                .then(res => res.json())
                .then(async data => {
                    await esperarMinimoCarga(inicioCarga);
                    const todosLosEstados = ["Recibido", "Asignado", "En proceso", "Terminado", "Borrador",
                        "Atrasado", "Cerrado"
                    ];
                    const datosMapeados = {};
                    todosLosEstados.forEach(e => {
                        datosMapeados[e] = {
                            cantidad: 0,
                            color: 'rgba(200, 200, 200, 0.3)'
                        };
                    });

                    data.forEach(item => {
                        if (datosMapeados[item.estado]) {
                            datosMapeados[item.estado].cantidad = parseInt(item.cantidad_tickets) || 0;
                            datosMapeados[item.estado].color = item.color_estado ||
                                'rgba(54, 162, 235, 0.5)';
                        }
                    });

                    const todasCero = Object.values(datosMapeados).every(e => e.cantidad === 0);
                    const contenedor = document.querySelector("#contenedorGraficoTicket");

                    if (todasCero) {
                        contenedor.innerHTML = `
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-info-circle" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">No hay tickets registrados para este perfil.</p>
                        </div>
                    `;
                        cambiarVisibilidadCarga('loadingGraficoTicket', false);
                        return;
                    }

                    const cantidades = todosLosEstados.map(e => datosMapeados[e].cantidad);
                    const colores = todosLosEstados.map(e => datosMapeados[e].color);
                    const maxValor = Math.max(...cantidades, 10);

                    contenedor.innerHTML = '<canvas id="canvasGraficoTicket" height="350"></canvas>';
                    const canvas = document.getElementById("canvasGraficoTicket");
                    const ctx = canvas.getContext("2d");

                    if (canvas.chart) {
                        canvas.chart.destroy();
                    }

                    canvas.chart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: todosLosEstados,
                            datasets: [{
                                label: "Cantidad de Tickets",
                                data: cantidades,
                                backgroundColor: colores,
                                borderColor: colores,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `${context.raw} tickets`;
                                        }
                                    }
                                }
                            },
                            scales: {
                                x: {
                                    type: 'linear',
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        callback: function(value) {
                                            return Number.isInteger(value) ? value :
                                            null; // Oculta los decimales
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: "Cantidad de Tickets"
                                    },
                                    suggestedMax: Math.ceil(maxValor), // <-- fuerza a entero mayor
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: "Estados"
                                    }
                                }
                            }

                        }
                    });
                    cambiarVisibilidadCarga('loadingGraficoTicket', false);
                })
                .catch(error => {
                    console.error("Error cargando gráfico:", error);
                    cambiarVisibilidadCarga('loadingGraficoTicket', false);
                });
        }

        //******************************************************************************************

        function inicializarTablaPerfilDashboard(contenedor) {
            const $contenedor = $('#' + contenedor);
            const $tablaUsuario = $contenedor.find('#tablaUsuario');
            const $tablaTecnico = $contenedor.find('#tablaTecnicoTicketAsignados');
            const $tablaAdmin = $contenedor.find('#dataTableAdministrador');

            if ($tablaUsuario.length && !$.fn.DataTable.isDataTable($tablaUsuario)) {
                $tablaUsuario.DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    },
                    responsive: true,
                    pageLength: 10,
                    paging: true,
                    pagingType: 'simple_numbers'
                });
            }

            if ($tablaTecnico.length && !$.fn.DataTable.isDataTable($tablaTecnico)) {
                $tablaTecnico.DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    },
                    responsive: true,
                    pageLength: 10,
                    paging: true,
                    pagingType: 'simple_numbers'
                });
            }

            if ($tablaAdmin.length && !$.fn.DataTable.isDataTable($tablaAdmin)) {
                const dataTableAdmin = $tablaAdmin.DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    },
                    responsive: true,
                    pageLength: 10,
                    paging: true,
                    pagingType: 'simple_numbers'
                });
                if (typeof configurarFiltrosTablaAdmin === 'function') {
                    configurarFiltrosTablaAdmin(dataTableAdmin);
                }
            } else if ($tablaAdmin.length && $.fn.DataTable.isDataTable($tablaAdmin)) {
                if (typeof configurarFiltrosTablaAdmin === 'function') {
                    configurarFiltrosTablaAdmin($tablaAdmin.DataTable());
                }
            }

            $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().draw(false);
        }
        </script>

        <!-- ******************************************************************** -->
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const boton = document.querySelector('button.btn-success');
            if (boton) {
                boton.addEventListener('click', () => {
                    const contenedor = document.getElementById('contenedorAccesosDirectos');
                    if (contenedor) {
                        contenedor.classList.toggle('d-none');
                    } else {
                        console.error(
                            "El contenedor 'contenedorAccesosDirectos' no existe en el DOM.");
                    }
                });
            } else {
                console.error("El botón para mostrar accesos directos no existe en el DOM.");
            }
        });
        </script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.perfil-side-tab').forEach((link) => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            prepararCambioPerfilConCarga(this.href);
        });
    });

    const perfilInicial = document.getElementById('contenedorAdmin')?.style.display === 'block'
        ? 'admin'
        : document.getElementById('contenedorTecnico')?.style.display === 'block'
            ? 'tecnico'
            : 'usuario';

    if (typeof inicializarTablaPerfilDashboard === 'function') {
        const contenedorInicial = perfilInicial === 'admin'
            ? 'contenedorAdmin'
            : perfilInicial === 'tecnico'
                ? 'contenedorTecnico'
                : 'contenedorUsuario';
        inicializarTablaPerfilDashboard(contenedorInicial);
    }

    const textoPerfil = document.getElementById('textoPerfilActual');
    if (textoPerfil) {
        textoPerfil.innerText = perfilInicial === 'admin'
            ? 'Admin'
            : perfilInicial.charAt(0).toUpperCase() + perfilInicial.slice(1);
    }
});

function cambiarPerfil(perfil) {
    const perfilNormalizado = normalizarPerfilDashboard(perfil);
    const nombrePerfil = perfilNormalizado === 'admin'
        ? 'Admin'
        : perfilNormalizado.charAt(0).toUpperCase() + perfilNormalizado.slice(1);
    const iconos = {
        admin: 'bi-person-gear',
        tecnico: 'bi-wrench-adjustable-circle',
        usuario: 'bi-person-circle'
    };

    Swal.fire({
        title: '',
        html: `
            <div class="swal-perfil-card">
                <div class="swal-perfil-badge">
                    <i class="bi ${iconos[perfilNormalizado]}"></i>
                </div>
                <h2 class="swal-perfil-title">Cambiar a ${nombrePerfil}</h2>
                <p class="swal-perfil-text">Se actualizarán los contenedores, el listado y los gráficos según el perfil seleccionado.</p>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        buttonsStyling: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'swal-perfil-popup',
            confirmButton: 'swal-perfil-confirm',
            cancelButton: 'swal-perfil-cancel'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const textoPerfil = document.getElementById('textoPerfilActual');
            if (textoPerfil) {
                textoPerfil.innerText = nombrePerfil;
            }
            mostrarListado(perfilNormalizado);

            // ✅ Este ya lo tienes
            if (typeof generarGraficoTicket === 'function') {
                generarGraficoTicket(perfilNormalizado);
            }

            // ✅ Agrega esta línea para actualizar el otro gráfico
            if (typeof cargarGraficoPorPerfil === 'function') {
                cargarGraficoPorPerfil(perfilNormalizado);
            }

            Swal.fire({
                title: '',
                html: `
                    <div class="swal-perfil-card">
                        <div class="swal-perfil-badge">
                            <i class="bi ${iconos[perfilNormalizado]}"></i>
                        </div>
                        <h2 class="swal-perfil-title">Perfil actualizado</h2>
                        <p class="swal-perfil-text">Ahora estás viendo el panel de <strong>${nombrePerfil}</strong>.</p>
                    </div>
                `,
                icon: 'success',
                timer: 1200,
                showConfirmButton: false,
                customClass: {
                    popup: 'swal-perfil-popup'
                }
            });
        }
    });
}
</script>
        <?php $funciones->script(); ?>
        <script src="js/sb-admin-2.min.js"></script>
        <script src="componentes/js/chat_conversacion.js"></script>
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
      $('#contenidoOffcanvasAsunto').html('<div class="text-danger">Error al cargar la información</div>');
    }
  });
}

</script>

</body>

</html>
