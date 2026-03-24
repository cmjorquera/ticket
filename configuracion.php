<?php
    session_start();
    require_once 'class/conexion.php';
    require_once 'class/funciones.php';
    require_once 'class/personas.php';

    
    $funciones          = new Funciones();
    $personas           = new Persona();

    $idUsuarioSession   = htmlspecialchars($_SESSION['id']);

    $idPagActual        = "9";  // Página de bitácora
    
    // *************** INSTANCIA DE LAS FUNCIONES
    $usuario                       = $personas->datosUsuario($idUsuarioSession );
    // $usuarios                   = $funciones->listarUsuarios();
    // $tiposDispositivos          = $funciones->listarTiposDispositivos();
    // $listarOtrosDispositivos    = $funciones->listarOtrosDispositivos();
    // $listarTodosDispositivosqr  = $funciones->listarTodosDispositivosqr();


?>

<!DOCTYPE html>
<html lang="es">
<meta charset="UTF-8">

<head>
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="css/bitacora.css"> <!-- PROPIO DE ESTA PAGINA -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="js/comunes.js"></script> <!-- FUNCIONES COMUNES -->
    <script src="js/equipos.js"></script> <!-- FUNCIONES DE COMPUTADORES -->
    <script src="js/dispositivos.js"></script> <!-- FUNCIONES DE DISPOSITIVOS -->

</head>

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
                <div class="container-fluid">
                    <!-- Encabezado de página -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Configuración</h1>
                    </div>

                    <div class="row">
                        <!-- Columna Izquierda -->
                        <div class="col-lg-8">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Actividad</h6>
                                    <div class="dropdown no-arrow">
                                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                            <div class="dropdown-header">Filtros:</div>
                                            <a class="dropdown-item" href="#">Por estado</a>
                                            <a class="dropdown-item" href="#">Por fecha</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="progress mb-4">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 45%" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">Completados 3/7</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="small text-gray-500 mb-2">Hoy</div>
                                        <div class="card mb-3 border-left-danger shadow h-100 py-2">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Atrasado</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">Llamar a proveedor</div>
                                                        <div class="small text-gray-500 mt-1">
                                                            <i class="bi bi-calendar"></i> 13 de abril, 10:00 &nbsp;
                                                            <i class="bi bi-person"></i> Miguel &nbsp;
                                                            <i class="bi bi-briefcase"></i> Negocios
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <button class="btn btn-sm btn-primary">Hecho</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card mb-3 border-left-primary shadow h-100 py-2">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Hoy</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">Enviar informe</div>
                                                        <div class="small text-gray-500 mt-1">
                                                            <i class="bi bi-alarm"></i> 14:00 &nbsp;
                                                            <i class="bi bi-person"></i> Emily &nbsp;
                                                            <i class="bi bi-archive"></i> Trabajo
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <button class="btn btn-sm btn-primary">Hecho</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="small text-gray-500 mb-2">Mañana</div>
                                        <div class="card mb-3 border-left-warning shadow h-100 py-2">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pendiente</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">Pedir cita médica</div>
                                                        <div class="small text-gray-500 mt-1">
                                                            <i class="bi bi-calendar"></i> 15 de abril, 09:00 &nbsp;
                                                            <i class="bi bi-person"></i> Kristin &nbsp;
                                                            <i class="bi bi-tag"></i> Personal
                                                        </div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <button class="btn btn-sm btn-primary">Hecho</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Columna Derecha -->
                        <div class="col-lg-4">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Recordatorios</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <div class="btn-group w-100" role="group">
                                            <button type="button" class="btn btn-outline-secondary btn-sm">Hoy</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm">Mañana</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm">Prox. Semana</button>
                                        </div>
                                    </div>
                                    <p class="small text-muted">Ajustes rápidos</p>
                                    <div class="d-flex flex-column gap-2">
                                        <button class="btn btn-light btn-sm text-start border">Repetir</button>
                                        <button class="btn btn-light btn-sm text-start border">Notificaciones</button>
                                        <button class="btn btn-light btn-sm text-start border">Solo atrasados</button>
                                    </div>
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
</body>
<?php $funciones->script(); ?>
<?php include("modal_salir.php"); ?>


</html>
