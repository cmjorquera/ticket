<?php
session_start();
require_once 'class/conexion.php'; 
require_once 'class/funciones.php';
$funciones = new Funciones(); 
$idUsuarioSession   = htmlspecialchars($_SESSION['id']);
$nombresession      = htmlspecialchars($_SESSION['nombre']) . '-' . htmlspecialchars($_SESSION['apellido_paterno']);
$idPagActual        = 2;

?>

<!DOCTYPE html>
<html lang="es">

<head>
<?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <script type="text/javascript" src="js/funciones.js"></script>

</head>
<style>
       .badge-counter {
        position: absolute;
        top: 22px; /* Ajusta este valor según sea necesario */
        right: 0px; /* Ajusta este valor según sea necesario */
        transform: translate(50%, -50%);
        z-index: 10;
        font-size: 0.75rem;
        font-weight: 700;
        color: #fff;
        background-color: #e74a3b;
        border-radius: 10rem;
        padding: 0.25rem 0.5rem;
    }

</style> 

<body id="page-top">
    <div id="wrapper">
    <?php  $funciones->menuLateral($idUsuarioSession,$idPagActual);   ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>
                <div class="container-fluid">       
                    <!-- DataTales Example -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary" id="titulo"
                                data-intro="Aquí puedes ver y gestionar tus mensajes.">MENSAJES ARCHIVADOS</h6>
                        </div>
                        <?php $funciones->mensajes_archivados($idUsuarioSession); ?>
                    </div>
                </div>
                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>

    <!-- Scripts de Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>
    <script type='text/javascript' src='template_01/js/funciones.js'></script>

    </script>
    <?php $funciones->script(); ?>
</body>

    <!-- ********************ACTUALIZACIONES************************************************ -->
    <script>
        // < !--- FUNCION PARA ACTUALIZAR EL NUMERO DE MENSAJES-- >

        document.addEventListener('DOMContentLoaded', function() {
            const usuarioId = document.getElementById('idUsuario').value;

            // Función para cargar y actualizar el número de mensajes
            function actualizarNumeroMensajes() {
                $.ajax({
                    type: 'GET',
                    url: 'http://acceso.seduc.cl/api/obtenerCantidadMensajes',
                    dataType: "json",
                    data: {
                        id_usuario: usuarioId
                    },  
                    success: function(data) {
                        $('#numeroMensajes').text(data);
                    }
                });
            }

            actualizarNumeroMensajes();

            setInterval(actualizarNumeroMensajes, 3000);
            // setInterval(actualizarNumeroMensajes, 120000);
        });
    </script>
    <!-- < !--- FUNCION PARA ACTUALIZAR EL NUMERO DE ALERTAS-- > -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const usuarioId = document.getElementById('idUsuario').value;

            // Función para cargar y actualizar el número de mensajes
            function actualizarNumeroAlertas() {
                $.ajax({
                    type: 'GET',
                    url: 'http://acceso.seduc.cl/api/obtenerCantidadAlertas',
                    dataType: "json",
                    data: {
                        id_usuario: usuarioId
                    },
                    success: function(data) {
                        $('#numeroAlertas').text(data);
                    }
                });
            }

            actualizarNumeroAlertas();

            setInterval(actualizarNumeroAlertas, 3000);
        });
    </script>

    <!-- // < !--- FUNCION PARA ACTUALIZAR LOS MENSAJES-- > -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const usuarioId = document.getElementById('idUsuario').value;

            // Función para cargar y actualizar el contenedor de mensajes
            function actualizarMensajes() {
                $.ajax({
                    type: 'GET',
                    url: 'http://acceso.seduc.cl/api/obtenerMensajes',
                    data: {
                        id_usuario: usuarioId
                    },
                    success: function(data) {
                        // Actualizar el contenido del div con el HTML devuelto por la API
                        $('#contendorMensajes').html(data);
                    }
                });
            }

            // Llamar a la función inmediatamente cuando la página cargue
            actualizarMensajes();

            // Actualizar cada 3 segundos
            setInterval(actualizarMensajes, 3000);
        });
    </script>

    <!-- // < !--- FUNCION PARA ACTUALIZAR LOS RECORDATORIOS-- > -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const usuarioId = document.getElementById('idUsuario').value;

            // Función para cargar y actualizar el contenedor de mensajes
            function actualizarRecordatorio() {
                $.ajax({
                    type: 'GET',
                    url: 'http://acceso.seduc.cl/api/obtenerRecordatorios',
                    data: {
                        id_usuario: usuarioId
                    },
                    success: function(data) {
                        // Actualizar el contenido del div con el HTML devuelto por la API
                        $('#contendorTicket').html(data);
                    }
                });
            }

            // Llamar a la función inmediatamente cuando la página cargue
            actualizarRecordatorio();

            // Actualizar cada 3 segundos
            setInterval(actualizarRecordatorio, 3000);
        });
    </script>

<!-- *********************************************************************************** -->
</html>