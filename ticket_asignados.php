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
    var nombresession       = "<?php echo $nombresession; ?>";
    var idUsuarioSession    = "<?php echo $idUsuarioSession; ?>";
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous">
    </script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <link href="css/contenedor_estados.css" rel="stylesheet">
    <link href="css/modalesTicket.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/cronologiaTicket.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <script type="text/javascript" src="js/chat_ticket.js"></script>
    <script type="text/javascript" src="js/funciones.js"></script>
    <script type="text/javascript" src="js/buscadores.js"></script>
    <script type="text/javascript" src="js/toast.js"></script>
    <script type="text/javascript" src="js/cronologiaTicket.js"></script>
    <script type="text/javascript" src="js/ticket.js"></script>
</head>
<style>
.dropdown-menu .dropdown-item:hover {
  background-color: #f8f9fa;
  font-weight: 500;
  color: #212529;
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

                <div class="container-fluid">
                     <div class="contenedor-estados-ticket" id="idContenedoresEstadosTicket">
                            <?php 
                                $funciones->contenedorTicketRecibidos($idUsuarioSession, $idPagActual); 
                                $funciones->contenedorTicketAsignados($idUsuarioSession, $idPagActual); 
                                $funciones->contenedorTicketEnProceso($idUsuarioSession, $idPagActual); 
                                $funciones->contenedorTicketTerminados($idUsuarioSession, $idPagActual); 
                                $funciones->contenedorTicketDemorados($idUsuarioSession, $idPagActual); 
                            ?>
                        </div><br>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                Tickets Tecnico
                            </h6>

                        </div>
                        <div class="card-body" id="contenedorTablaTecnico">
                            <?php
                            include("componentes/bloque_tabla_tecnico.php")
                            ?>
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
        <?php include("modal_salir.php")?>
        <?php $funciones->script(); ?>
    <script>
        
        function mostrarEstrellas(id_ticket, contenedorID) {
          fetch("modelos/rescatar/calificacionUsuario.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: "id_ticket=" + id_ticket
          })
          .then(response => response.json())
          .then(data => {
            if (data.success && data.data.id_calificacion) {
              const estrellas = parseInt(data.data.id_calificacion);
              let html = "";
        
              for (let i = 1; i <= 4; i++) {
                html += `<i class="${i <= estrellas ? 'fas' : 'far'} fa-star ${i <= estrellas ? 'text-warning' : 'text-muted'}"></i>`;
                
              }
        
              document.getElementById(contenedorID).innerHTML = html;
            } else {
document.getElementById(contenedorID).innerHTML = `
        <span class="estado-badge estado-gris">
          <i class="fas fa-ban me-1"></i>Sin calificación
        </span>`;            }
          })
          .catch(error => {
            console.error("Error al cargar calificación:", error);
  <span class="estado-badge estado-gris">
        <i class="fas fa-exclamation-circle me-1"></i>Error al cargar
      </span>`;          });
        }
        }
        
    </script>
        
    <!--*************************************************** -->
    <!--FILTRO -->
    <script>
    $(document).ready(function () {
        const tablaTecnico = $('#tablaTecnicoTicketAsignados').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            responsive: true,
            pageLength: 10
        });
    
        let filtroActivo = false;
        let estadoFiltrado = null;
    
        function actualizarTablaTecnico() {
            const idUsuarioSession = <?= $_SESSION['id']; ?>;
            const datos = { idUsuarioSession: idUsuarioSession };
    
            if (filtroActivo && estadoFiltrado !== null) {
                datos.estado = estadoFiltrado;
            }
    
            $.post('componentes/ajax/bloque_tabla_tecnico.php', datos, function (data) {
                const nuevasFilas = $('<div>').html(data).find('#tablaTecnicoTicketAsignados tbody').html();
                $('#tablaTecnicoTicketAsignados tbody').html(nuevasFilas);
            });
        }
    
        actualizarTablaTecnico();
        setInterval(actualizarTablaTecnico, 3000);
    
        // 🔍 Lógica de lupa con toggle
        window.filtrarTickets = function (estado) {
            const idUsuarioSession = <?= $_SESSION['id']; ?>;
    
            if (filtroActivo && estadoFiltrado === estado) {
                filtroActivo = false;
                estadoFiltrado = null;
            } else {
                filtroActivo = true;
                estadoFiltrado = estado;
            }
    
            const datos = { idUsuarioSession: idUsuarioSession };
            if (filtroActivo && estadoFiltrado !== null) {
                datos.estado = estadoFiltrado;
            }
    
            $.post('componentes/ajax/bloque_tabla_tecnico.php', datos, function (data) {
                $('#contenedorTablaTecnico').hide().html(data).fadeIn();
            });
        }
    
        // 📦 Mostrar todos manualmente
        window.mostrarTodosLosTickets = function () {
            filtroActivo = false;
            estadoFiltrado = null;
            actualizarTablaTecnico();
        }
    });
    </script>
    <!--*************************************************** -->
    </body>
    </html>