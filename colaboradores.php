<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';

$funciones           = new Funciones();
$idUsuarioSession    = htmlspecialchars($_SESSION['id']);
$nombresession       = htmlspecialchars($_SESSION['nombre']) . '-' . htmlspecialchars($_SESSION['apellido_paterno']);
$nombre              = htmlspecialchars($_SESSION['nombre']) . ' ' . htmlspecialchars($_SESSION['apellido_paterno']);

$idPagActual        = '1';  // PAGINA DEL ADMINISTRADOR
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/colaboradores.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <script type="text/javascript" src="js/funciones.js"></script>
    <script type="text/javascript" src="js/mensajes.js"></script>
    <script type="text/javascript" src="js/actualizaciones.js"></script>

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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Colaboradores</h1>
                    </div>
                    <div id="dataTable_filter" class="dataTables_filter">
                        <label>Buscar:
                            <input type="search" id="buscarColaborador" class="form-control form-control-sm"
                                placeholder="buscador...." aria-controls="dataTable">
                        </label>
                    </div>
                    <div id="collaboratorContainer" class="row">
                        <?php $funciones->colaboradores($idUsuarioSession); ?>
                    </div>
              
                </div>
                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>
                <!-- Botón flotante para enviar mensaje a seleccionados -->
                   <button class="btn btn-primary" id="enviarMensajeGrupo">
                            Enviar Mensaje a Seleccionados <span id="contador" class="badge-counter">0</span>
                        </button>


   
                    </div>
            <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
            </a>
            <?php $funciones->footer(); ?>
        </div>
    </div>
    
    
    
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" 
        integrity="sha384-oBqDVmMz4fnFO9gybByC1LzYr6MkiJG6sjid20+VRmYhJs9axSBLlXcrp1KccfQ3" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-p5JpGJnOt6tW2d2ecXBvm8r3rEN5hAcnM4ygjQ2XJg6roMX9zthHl5OqVM+FQcTf" crossorigin="anonymous">
    </script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <?php $funciones->script(); ?>

    <script>
        $(document).ready(function() {
            busca_colaborador("#buscarColaborador", "#collaboratorContainer", ".col-xl-3.col-md-6.mb-4");
        });

        $(document).ready(function() {
            // Detectar cambios en los checkboxes
            $('.colaborador-checkbox').on('change', function() {
                var cardId = $(this).data('card-id'); // Obtener el id de la tarjeta

                if ($(this).is(':checked')) {
                    // Si el checkbox está marcado, añadimos la clase .card-seleccionada
                    $('#' + cardId).addClass('card-seleccionada');
                } else {
                    // Si se desmarca, quitamos la clase .card-seleccionada
                    $('#' + cardId).removeClass('card-seleccionada');
                }
            });
        });
    </script>
    
    
    <!-- BUTTON ENVIAR mENSAJE A SELECIONADOS-->
   <script>
     let contador = 0; // Inicializar contador de checkboxes seleccionados
    const checkboxes = document.querySelectorAll('.colaborador-checkbox');
    const contadorElement = document.getElementById('contador');

    // Función para actualizar el contador en el botón
    function actualizarContador() {
        contadorElement.textContent = contador;
    }

    // Detectar cambios en los checkboxes
    checkboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            if (checkbox.checked) {
                contador++;
            } else {
                contador--;
            }
            actualizarContador();
        });
    });

    // Evento para el botón 'Enviar Mensaje a Seleccionados'
    document.getElementById('enviarMensajeGrupo').addEventListener('click', function () {
        const seleccionados = [];

        // Obtener IDs de colaboradores seleccionados
        document.querySelectorAll('.colaborador-checkbox:checked').forEach(function (checkbox) {
            seleccionados.push(checkbox.value);
        });

        // Validar si hay 2 o más seleccionados
        if (seleccionados.length >= 2) {
            // Llamar a la función para mostrar el modal con múltiples destinatarios
            mostrarModalConTextareaMultiple(seleccionados);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Debe seleccionar al menos 2 colaboradores',
                customClass: {
                    popup: 'cuerpo_modal_guardar',
                    confirmButton: 'bt_crear',
                },
            });
        }
    });

</script>






</body>

</html>
