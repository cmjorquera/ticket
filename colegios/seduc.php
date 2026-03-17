<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once '../class/conexion.php';
require_once '../class/funciones.php';
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

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="../css/sb-admin-2.min.css" rel="stylesheet">

    <link href="../css/modalesTicket.css" rel="stylesheet">
    <link href="../css/estilo.css" rel="stylesheet">
    <link href="../css/bitacora.css" rel="stylesheet">
    <link href="../css/contenedor.css" rel="stylesheet">
    <link href="../css/tour.css" rel="stylesheet">
    <link href="../css/tour.css" rel="stylesheet">
    <link href="../css/principal.css" rel="stylesheet">
    <script type="text/javascript" src="../js/funciones.js"></script>
    <script type="text/javascript" src="../js/mensajes.js"></script>
    <script type="text/javascript" src="../js/ticket.js"></script>
    <script type="text/javascript" src="../js/buscadores.js"></script>
    <script type="text/javascript" src="../js/validacionTicket.js"></script>
    <script type="text/javascript" src="../js/toast.js"></script>

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
    margin-bottom: 40px;
}

.grid-categorias {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 30px;
    max-width: 700px;
    margin: auto;
}

.categoria {
    background-color: white;
    border-radius: 12px;
    padding: 30px 10px;
    text-align: center;
    font-weight: bold;
    font-size: 18px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: all 0.2s;
}

.categoria:hover {
    background-color: #e9ecef;
    transform: scale(1.05);
}

.categoria i {
    font-size: 36px;
    margin-bottom: 10px;
    display: block;
}
</style>

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
                                <h6 class="m-0 font-weight-bold text-primary">Inventario</h6>
                            </div>
                            <div class="card-body">
                                <div class="grid-categorias">
                                    <div class="categoria" onclick="window.location.href='../guadalupej.php'">
                                        <i class="fas fa-broom"></i>
                                        Aseo
                                    </div>
                                    <div class="categoria" onclick="window.location.href='../bitacora.php'">
                                        <i class="fas fa-laptop"></i>
                                        Equipos
                                    </div>

                                    <div class="categoria" onclick="irA('inventario_muebles.php')">
                                        <i class="fas fa-chair"></i>
                                        Muebles
                                    </div>
                                    <div class="categoria" onclick="irA('inventario_oficina.php')">
                                        <i class="fas fa-pencil-alt"></i>
                                        Opcion 4
                                    </div>
                                    <div class="categoria" onclick="irA('inventario_oficina.php')">
                                        <i class="fas fa-pencil-alt"></i>
                                        Opcion 5
                                    </div>
                                    <div class="categoria" onclick="irA('inventario_oficina.php')">
                                        <i class="fas fa-pencil-alt"></i>
                                        Opcion 6

                                    </div>
                                    <div class="categoria" onclick="irA('inventario_oficina.php')">
                                        <i class="fas fa-pencil-alt"></i>
                                        Opcion 7
                                    </div>
                                    <div class="categoria" onclick="irA('inventario_oficina.php')">
                                        <i class="fas fa-pencil-alt"></i>
                                        Opcion 8
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



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>

    <?php $funciones->script(); ?>
</body>

</html>