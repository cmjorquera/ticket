<?php
    session_start();
    require_once 'class/conexion.php';
    require_once 'class/funciones.php';
    $funciones = new Funciones();
    $idUsuarioSession   = htmlspecialchars($_SESSION['id']);
    $nombresession      = htmlspecialchars($_SESSION['nombre']) . '--' . htmlspecialchars($_SESSION['apellido_paterno']);
    $AreaTrabajo        = htmlspecialchars($_SESSION['id_area_trabajo']);
    $idPagActual        = 3;  // PAGINA DEL USUARIO CON PROBLEMAS
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <?php $funciones->header(); ?>
     <!-- CSS y JS de Bootstrap, DataTables, jQuery, FontAwesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <link href="css/contenedor_estados.css" rel="stylesheet">
    <link href="css/modalesTicket.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/cronologiaTicket.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <script src="js/funciones.js"></script>
    <script src="js/mensajes.js"></script>
    <script src="js/ticket.js"></script>
    <script src="js/buscadores.js"></script>
    <script src="js/validacionTicket.js"></script>
    <script src="js/chat_ticket.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Intro.js -->
    <link rel="stylesheet" href="https://unpkg.com/intro.js/minified/introjs.min.css">
    <script src="https://unpkg.com/intro.js/minified/intro.min.js"></script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        
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
        <link href="css/tour.css" rel="stylesheet">

</head>

<script>
    function iniciarTour() {
      introJs().start();
    }

</script>



<style>
#bi {
/*  position: fixed;*/
/*  bottom: 20px;*/
/*  right: 20px;*/
/*  z-index: 9999;*/
/*  width: 50px;*/
/*  height: 50px;*/
/*  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);*/
/*}*/
</style>


<body id="page-to3p">
    <div id="wrapper">
        <?php $funciones->menuLateral($idUsuarioSession, $idPagActual); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>   
                
                    <button id="bi" class="btn btn-light border rounded-circle" onclick="iniciarTour()" title="Gu®™a r®¢pida">
                      <i class="bi bi-info-circle-fill text-primary fs-3"></i>
                    </button>
                    
                <div class="container-fluid">
                     <div class="contenedor-estados-ticket" id="idContenedoresEstadosTicket">
                            <?php 
                                $funciones->contenedorTicketRecibidos($idUsuarioSession, $idPagActual); 
                                $funciones->contenedorTicketAsignados($idUsuarioSession, $idPagActual); 
                                $funciones->contenedorTicketEnProceso($idUsuarioSession, $idPagActual); 
                                $funciones->contenedorTicketTerminados($idUsuarioSession, $idPagActual); 
                                $funciones->contenedorTicketDemorados($idUsuarioSession, $idPagActual); 
                                // $funciones->contenedorTicketBorrador($idUsuarioSession, $idPagActual); 
                                ?>
                        </div><br>
                    <!-- Tabla de tickets del usuario -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary" data-intro="aca puedes ver los graficos  nde Barra para ver los ticket segun sus estados.">
                                <a href="#" class="btn btn-primary btn-icon-split" id="buttonAgregarTicket"    onclick="crearTicketGuadalupe(<?php echo $idUsuarioSession; ?>)">
                                    <span class="text">Agregar ticket</span>
                                </a>
                            </h6>
                        </div>
                        <div class="card-body" id="contenedorTablaUsuario">
                            <?php include("componentes/bloque_tabla_usuario.php"); ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Offcanvas -->
            <div id="offcanvasContainerTicket"></div>

            <!-- Scroll to Top Button-->
            <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
            </a>

            <!-- Footer -->
            <?php $funciones->footer(); ?>
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


    <!-- Bootstrap + Script personalizados -->
    <?php $funciones->script(); ?>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<!--*************************************************** -->
<!--POPPER********************************************* -->

    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.forEach(function (popoverTriggerEl) {
          new bootstrap.Popover(popoverTriggerEl);
        });
      });
    </script>
    
<!--*************************************************** -->
<!--*************************************************** -->
    <!--FILTRO -->
<script>
$(document).ready(function () {
    const tablaUsuario = $('#tablaUsuario').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        responsive: true,
        pageLength: 10
    });

    let filtroActivo = false;
    let estadoFiltrado = null;

    function actualizarTablaUsuario() {
        const idUsuarioSession = <?= $_SESSION['id']; ?>;
        const datos = { idUsuarioSession };

        if (filtroActivo && estadoFiltrado !== null) {
            datos.estado = estadoFiltrado;
        }

        $.post('componentes/ajax/bloque_tabla_usuario.php', datos, function (data) {
            const nuevasFilas = $('<div>').html(data).find('tbody').html();
            $('#tablaUsuario tbody').html(nuevasFilas);
        });
    }

    // üîÅ Auto-actualizaci√≥n cada 3 segundos
    actualizarTablaUsuario();
    setInterval(actualizarTablaUsuario, 3000);

    // üîç Filtro por estado
    window.filtrarTickets = function (estado) {
        const idUsuarioSession = <?= $_SESSION['id']; ?>;

        if (filtroActivo && estadoFiltrado === estado) {
            filtroActivo = false;
            estadoFiltrado = null;
        } else {
            filtroActivo = true;
            estadoFiltrado = estado;
        }

        const datos = { idUsuarioSession };
        if (filtroActivo && estadoFiltrado !== null) {
            datos.estado = estadoFiltrado;
        }

        $.post('componentes/ajax/bloque_tabla_usuario.php', datos, function (data) {
            const nuevasFilas = $('<div>').html(data).find('tbody').html();
            $('#tablaUsuario tbody').html(nuevasFilas);
        });
    };

    // Mostrar todos (quitar filtro)
    window.mostrarTodosLosTickets = function () {
        filtroActivo = false;
        estadoFiltrado = null;
        actualizarTablaUsuario();
    };
});
</script>

<!--*************************************************** -->


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
      $('#contenidoOffcanvasAsunto').html('<div class="text-danger">Error al cargar la informaci®Æn</div>');
    }
  });
}


</script>



</body>
</html>