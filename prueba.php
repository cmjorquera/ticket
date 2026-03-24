<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';
$funciones = new Funciones();

$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$nombresession    = htmlspecialchars($_SESSION['nombre']) . '--' . htmlspecialchars($_SESSION['apellido_paterno']);
$idPagActual      = 5;  // PAGINA DEL TECNICO
?>

<script>
    var nombresession = "<?php echo $nombresession; ?>";
    var idUsuarioSession = "<?php echo $idUsuarioSession; ?>";
</script>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script type="text/javascript" src="js/funciones.js"></script>
    <script type="text/javascript" src="js/mensajes.js"></script>
    <script type="text/javascript" src="js/ticket.js"></script>
    <script type="text/javascript" src="js/buscadores.js"></script>

    <style>
        .border-start-success {
            border-left: 5px solid #28a745 !important;
        }
        .border-start-danger {
            border-left: 5px solid #dc3545 !important;
        }
        .border-start-warning {
            border-left: 5px solid #ffc107 !important;
        }
    </style>
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

                    <div class="row">
                        <!-- CARD: Nuevos Mensajes -->
                        <div class="col-md-4 mb-4">
                            <div class="card shadow border-start-success">
                                <div class="card-body d-flex align-items-center">
                                    <i class="bi bi-envelope-paper-fill text-success fs-1 me-3"></i>
                                    <div>
                                        <h6 class="text-muted mb-1">Nuevos Mensajes</h6>
                                        <h4 class="mb-0">12</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CARD: Denuncias por Acoso -->
                        <div class="col-md-4 mb-4">
                            <div class="card shadow border-start-danger">
                                <div class="card-body d-flex align-items-center">
                                    <i class="bi bi-person-exclamation text-danger fs-1 me-3"></i>
                                    <div>
                                        <h6 class="text-muted mb-1">Acoso Laboral</h6>
                                        <h4 class="mb-0">3 denuncias</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CARD: Tickets Abiertos -->
                        <div class="col-md-4 mb-4">
                            <div class="card shadow border-start-warning">
                                <div class="card-body d-flex align-items-center">
                                    <i class="bi bi-tools text-warning fs-1 me-3"></i>
                                    <div>
                                        <h6 class="text-muted mb-1">Tickets abiertos</h6>
                                        <h4 class="mb-0">24</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card shadow">
                                <div class="card-header">Tipo de Tickets</div>
                                <div class="card-body">
                                    <canvas id="pieChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="card shadow">
                                <div class="card-header">Tickets por Estado</div>
                                <div class="card-body">
                                    <canvas id="barChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Proyectos activos -->
                        <div class="col-md-8">
                            <!-- (Tu tabla de proyectos activos aquí, omitida por espacio) -->
                        </div>
                        <!-- Actividad reciente -->
                        <div class="col-md-4">
                            <!-- (Tu lista de actividad reciente aquí, omitida por espacio) -->
                        </div>
                    </div>

                    <div class="row">
                        <!-- Objetivos (Goals estilo) -->
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header">
                                    <h6 class="m-0 fw-bold text-primary">Metas del Área Técnica</h6>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-primary rounded-circle me-3" style="width: 15px; height: 15px;"></span>
                                                <div>
                                                    <div class="fw-bold">Responder tickets en 24h</div>
                                                    <small>Meta actual: 85%</small>
                                                </div>
                                            </div>
                                            <span class="text-muted small">Mar 15</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-warning rounded-circle me-3" style="width: 15px; height: 15px;"></span>
                                                <div>
                                                    <div class="fw-bold">Reducir reabrimientos</div>
                                                    <small>Target: -30%</small>
                                                </div>
                                            </div>
                                            <span class="text-muted small">Abr 10</span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-success rounded-circle me-3" style="width: 15px; height: 15px;"></span>
                                                <div>
                                                    <div class="fw-bold">Mejorar satisfacción</div>
                                                    <small>90% feedback positivo</small>
                                                </div>
                                            </div>
                                            <span class="text-muted small">May 01</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Listados</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Categoría</th>
                                        <th>Recibido (%)</th><th>Asignado (%)</th><th>En proceso (%)</th><th>Terminado (%)</th><th>Cerrado (%)</th>                                        <th>Detalles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                                                        <tr>
                                        <td><b>Seduc Servicios</b></td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                <td>
                                            <button class="btn btn-primary" onclick="mostrarPromedioEstados()">📊 Ver
                                                Cronología</button>

                                        </td>
                                    </tr>
                                                                        <tr>
                                        <td><b>Siae</b></td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                <td>
                                            <button class="btn btn-primary" onclick="mostrarPromedioEstados()">📊 Ver
                                                Cronología</button>

                                        </td>
                                    </tr>
                                                                        <tr>
                                        <td><b>Redes</b></td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                <td>
                                            <button class="btn btn-primary" onclick="mostrarPromedioEstados()">📊 Ver
                                                Cronología</button>

                                        </td>
                                    </tr>
                                                                        <tr>
                                        <td><b>Telefonica</b></td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                <td>
                                            <button class="btn btn-primary" onclick="mostrarPromedioEstados()">📊 Ver
                                                Cronología</button>

                                        </td>
                                    </tr>
                                                                        <tr>
                                        <td><b>Impresiones</b></td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 16.67%;" aria-valuenow="16.67" aria-valuemin="0" aria-valuemax="100">
                                                    16.67%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 83.33%;" aria-valuenow="83.33" aria-valuemin="0" aria-valuemax="100">
                                                    83.33%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                <td>
                                            <button class="btn btn-primary" onclick="mostrarPromedioEstados()">📊 Ver
                                                Cronología</button>

                                        </td>
                                    </tr>
                                                                        <tr>
                                        <td><b>Soporte</b></td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                <td>
                                            <button class="btn btn-primary" onclick="mostrarPromedioEstados()">📊 Ver
                                                Cronología</button>

                                        </td>
                                    </tr>
                                                                        <tr>
                                        <td><b>Correos</b></td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                <td>
                                            <button class="btn btn-primary" onclick="mostrarPromedioEstados()">📊 Ver
                                                Cronología</button>

                                        </td>
                                    </tr>
                                                                        <tr>
                                        <td><b>Moddle</b></td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                <td>
                                            <button class="btn btn-primary" onclick="mostrarPromedioEstados()">📊 Ver
                                                Cronología</button>

                                        </td>
                                    </tr>
                                                                        <tr>
                                        <td><b>Follet</b></td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                <td>
                                            <button class="btn btn-primary" onclick="mostrarPromedioEstados()">📊 Ver
                                                Cronología</button>

                                        </td>
                                    </tr>
                                                                        <tr>
                                        <td><b>Otros</b></td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                                    0%
                                                </div>
                                            </div>
                                        </td>
                                                                                <td>
                                            <button class="btn btn-primary" onclick="mostrarPromedioEstados()">📊 Ver
                                                Cronología</button>

                                        </td>
                                    </tr>
                                                                    </tbody>
                            </table>
                        </div>

                    </div>
                </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card shadow-sm mb-4">
                                <div class="card-header">
                                    <h6 class="m-0 fw-bold text-primary">Estados de los Tickets</h6>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="list-group list-group-flush">
                                        <!-- Lista de estados -->
                                    </ul>
                                </div>
                            </div>
                        </div>
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
    <?php $funciones->script(); ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctxPie = document.getElementById('pieChart');
        const pieChart = new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: ['Urgentes', 'Pendientes', 'Resueltos'],
                datasets: [{
                    label: 'Tickets',
                    data: [5, 10, 8],
                    backgroundColor: ['#dc3545', '#ffc107', '#28a745'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                cutout: '70%'
            }
        });

        const ctxBar = document.getElementById('barChart');
        const barChart = new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie'],
                datasets: [{
                    label: 'Tickets atendidos',
                    data: [5, 9, 6, 4, 7],
                    backgroundColor: '#0d6efd'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>

