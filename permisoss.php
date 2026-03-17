<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';

$funciones          = new Funciones();
$idUsuarioSession   = htmlspecialchars($_SESSION['id']);
$nombresession      = htmlspecialchars($_SESSION['nombre']) . '-' . htmlspecialchars($_SESSION['apellido_paterno']);
$nombre             = htmlspecialchars($_SESSION['nombre']) . ' ' . htmlspecialchars($_SESSION['apellido_paterno']);

$idPagActual = 7;

//*******************Obtener alertas de cumpleaños y tickets************************ */ 
$alertas = $funciones->alertaModal($idUsuarioSession);
$mensajes = $funciones->mensajesModal($idUsuarioSession);
//********************************************************************************** */ 

// Verificar si es la primera vez que el usuario entra
$primera_visita = isset($_SESSION['primera_vez']) && $_SESSION['primera_vez'] == 0;

// Actualizar la variable de sesión para que en futuras visitas no muestre el modal
$_SESSION['primera_vez'] = 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
</head>
<style>
    .active-menu-item > a {
    background-color: #d1e7fd; /* Fondo más claro para el menú activo */
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); /* Sombra para el efecto 3D */
    transform: translateY(-2px); /* Levantar ligeramente */
    border-radius: 5px;
}
    @keyframes marquee {
            0% { transform: translateX(10%); }
            100% { transform: translateX(-100%); }
        }
        #teleprompter {
            white-space: nowrap;
            overflow: hidden;
        }
   .contenedor-card {
        border-radius: 15px; /* Bordes redondeados */
        transition: transform 0.3s ease; /* Transición para el efecto de movimiento */
        position: relative; /* Asegura que los hijos absolutos se posicionen en relación a este contenedor */
        overflow: hidden; /* Para mantener la forma redondeada en todos los elementos internos */
    }

    .contenedor-card:hover {
        transform: translateY(-10px); /* Efecto de movimiento hacia arriba */
    }

    .contenedor-imagen {
        border-radius: 15px; /* Bordes redondeados */
        position: relative; /* Para posicionar el nombre del contenedor */
    }

    .contenedor-imagen img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Asegura que la imagen cubra todo el contenedor */
    }

    .contenedor-nombre {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 1.2rem;
        font-weight: bold;
        background-color: rgba(0, 0, 0, 0.5); /* Fondo semi-transparente para mejor visibilidad del texto */
        padding: 0.5rem;
        border-radius: 5px;
    }

    .button-container {
        position: absolute;
        top: 10px;
        right: 10px;
        display: flex;
        flex-direction: column; /* Coloca los botones uno debajo del otro */
        gap: 5px; /* Espacio entre los íconos */
    }

    .btn-sm {
        padding: 0.25rem 0.5rem; /* Tamaño más pequeño para los botones */
        font-size: 0.75rem; /* Tamaño de fuente más pequeño */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .dropdown-menu {
        min-width: auto; /* Ajustar el ancho mínimo del menú desplegable */
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
                <div class="container-fluid" id="idContenedorPrincipal">            
                    <div class="d-sm-flex align-items-center justify-content-between mb-4"></div>
                        <div class="row">
                            
                            sadd
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz4fnFO9gybByC1LzYr6MkiJG6sjid20+VRmYhJs9axSBLlXcrp1KccfQ3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" integrity="sha384-p5JpGJnOt6tW2d2ecXBvm8r3rEN5hAcnM4ygjQ2XJg6roMX9zthHl5OqVM+FQcTf" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <?php $funciones->script(); ?>
    <?php include("modal_salir.php") ?>


</body>
</html>
