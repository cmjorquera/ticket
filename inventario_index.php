<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';
$funciones = new Funciones();
$bdato = new MySQL("", "", "");

$idUsuarioSession   = htmlspecialchars($_SESSION['id']);
$nombre             = htmlspecialchars($_SESSION['nombre']) . ' ' . htmlspecialchars($_SESSION['apellido_paterno']);
$AreaTrabajo        = htmlspecialchars($_SESSION['id_area_trabajo']);
$idPagActual        = "9";
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

<style>
    h1 {
        text-align: center;
        margin-bottom: 30px;
    }

    .grid-colegios {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 20px;
        max-width: 900px;
        margin: auto;
    }

    .colegio {
        background-color: white;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: all 0.2s;
    }

    .colegio:hover {
        background-color: #e9ecef;
        transform: scale(1.05);
    }

    .colegio img {
        max-width: 100px;
        margin-bottom: 10px;
    }

    .colegio:hover {
        background-color: #f8f9fa;
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .colegio {
        cursor: pointer;
    }

    .grid-colegios {
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    }
</style>

<body id="page-top">
    <input type="hidden" id="idUsuario" value="<?php echo $idUsuarioSession; ?>" />
    <div id="wrapper">
        <?php $funciones->menuLateral2($idUsuarioSession, $idPagActual); ?>
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
                                <h6 class="m-0 font-weight-bold text-primary">Inventario</h6>
                            </div>
                            <div class="card-body">
                                <h1>Selecciona un Colegio</h1>
                                <!-- <input type="text" placeholder="Buscar colegio..."
                                    oninput="filtrarColegios(this.value)"> -->

                                <div class="grid-colegios">
                                    <?php
                            $sql = "SELECT id_colegio, nom_colegio, url_pagina 
                                    FROM colegio 
                                    WHERE id_colegio NOT IN (17, 22, 23, 24, 25) 
                                    ORDER BY orden ASC";
                            $resultado = $bdato->consulta($sql);

                            while ($row = $bdato->fetch_array($resultado)):
                                $idColegio      = $row['id_colegio'];
                                $nombreColegio = $row['nom_colegio'];
                                $urlDestino = htmlspecialchars($row['url_pagina'] ?? '#', ENT_QUOTES, 'UTF-8');
                                $rutaImg = "img/colegios/colegio_" . $idColegio . ".png";

                                if (!file_exists($rutaImg)) {
                                    $rutaImg = "img/colegios/default.png";
                                }
                            ?>
                                    <!-- <div class="colegio" onclick="window.location.href='colegios/<?= $urlDestino ?>'"> -->
                                    <div class="colegio" title="<?= $nombreColegio ?>"
                                        onclick="window.location.href='colegios/<?= $urlDestino ?>'">

                                        <img src="<?= $rutaImg ?>" alt="<?= $nombreColegio ?>">
                                        <!-- <div><?= $nombreColegio ?></div> -->
                                    </div>
                                    <?php endwhile; ?>
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



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>

    <?php $funciones->script(); ?>
</body>

</html>
