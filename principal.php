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
            // Mostrar bienvenida unificada con botones si corresponde
            $funciones->mostrarSoloBotonesPerfiles($idUsuarioSession);
        
            // Marcar que ya se mostró
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <!-- Incluir DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Incluir Bootstrap Icons -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Incluir jQuery -->
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
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
    <script type="text/javascript" src="js/ticket.js?v=<?php echo $versionTicketJs; ?>"></script>
    <script type="text/javascript" src="js/buscadores.js"></script>
    <script type="text/javascript" src="js/validacionTicket.js"></script>
    <script type="text/javascript" src="js/tour.js"></script>
    <script type="text/javascript" src="js/cronologiaTicket.js"></script> <!-- PARA MOSTRAR EL MODAL DE CRONOLOGIA-->
    <script type="text/javascript" src="js/chat_ticket.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
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
                   <button id="bi" class="btn btn-light border rounded-circle" onclick="iniciarTour()" title="Guía rápida">
                      <i class="bi bi-info-circle-fill text-primary fs-3"></i>
                    </button>
                    
                <!-- BUTTON DE ACCESO DIRECTO  ********************-->
                <div id="botonesDescargar"><br><br>
                    <!-- Botón 1: Conversar -->
             <?php
                $bloqueados = [28,29,30,31,32,33,34];
                if (!in_array((int)$idUsuarioSession, $bloqueados, true)) :
                ?>
                  <button
                    class="btn btn-light border w-100 rounded-pill shadow-sm d-flex justify-content-center align-items-center gap-2 py-2"
                    data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" title="Ver conversación">
                    <i class="bi bi-chat-dots-fill text-primary fs-10"></i>
                    <span class="text-dark fw-semibold"></span>
                  </button>
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
                    <button
                        class="btn btn-light border w-100 rounded-pill shadow-sm d-flex justify-content-center align-items-center gap-2 py-2"
                        type="button" onclick="mostrarAlertas(<?= $idUsuarioSession; ?>)" title="Agregar recordatorio">
                        <i class="bi bi-clipboard-data-fill text-primary fs-10"></i>
                        <span class="text-dark fw-semibold"></span>
                    </button>

                    <!-- Botón 4: Perfil Actual -->
                    <?php
                    $bdato = new MySQL("", "", "");
                    $sql = "SELECT id_perfil FROM usuario_perfil WHERE id_usuario = $idUsuarioSession";
                    $resultado = $bdato->consulta($sql);
                    $perfilesUsuario = [];
                    
                    while ($fila = $bdato->fetch_array($resultado)) {
                        $perfilesUsuario[] = $fila['id_perfil'];
                    }
                    
                    // Mostrar solo si hay más de un perfil
                    if (count($perfilesUsuario) > 1) {
                    ?>
                        <div class="dropdown w-100 position-relative">
                            <button class="btn btn-light border w-100 rounded-pill shadow-sm d-flex justify-content-center align-items-center gap-2 py-2 dropdown-toggle"
                                    type="button"
                                    id="dropdownPerfil"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    title="Seleccionar perfil">
                                <i class="bi bi-person-badge-fill text-primary fs-5"></i>
                                <span class="text-dark fw-semibold" id="textoPerfilActual">Perfil</span>
                            </button>
                    
                            <ul class="dropdown-menu w-100 shadow-xm" aria-labelledby="dropdownPerfil">
                                <li class="dropdown-header text-start px-4 py-2">
                                    <strong>Perfiles</strong>
                                </li>
                    
                                <?php
                                if (in_array(3, $perfilesUsuario)) {
                                    echo '<li><a class="dropdown-item text-dark" href="#" onclick="cambiarPerfil(\'admin\')">Admin</a></li>';
                                }
                                if (in_array(2, $perfilesUsuario)) {
                                    echo '<li><a class="dropdown-item text-dark" href="#" onclick="cambiarPerfil(\'tecnico\')">Técnico</a></li>';
                                }
                                if (in_array(1, $perfilesUsuario)) {
                                    echo '<li><a class="dropdown-item text-dark" href="#" onclick="cambiarPerfil(\'usuario\')">Usuario</a></li>';
                                }
                                ?>
                            </ul>
                        </div>
                    <?php
                    }
                    ?>
                </div>
                <!-- *********************************************  -->

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
                    <div class="row">
                        <!-- CONTENEDOR 1: Gráfico de Tickets (BARRAS SEGUN ESTADOS DE LOS TICKETS) -->
                        <div class="col-lg-6 col-sm-12">
                            <div class="card shadow mb-4 p-4" data-intro="En este gráfico de barras puedes visualizar la cantidad de tickets clasificados según su estado actual, como Recibido, En proceso, Terminado, entre otros. Esta vista te permite identificar rápidamente el avance y distribución de los tickets en el sistema..">
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
                                        echo $fila['id_perfil'];
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
                                                        echo '<li><a class="dropdown-item" href="#" onclick="generarGraficoTicket(\'usuario\')">Usuario</a></li>';
                                                    }
                                                    if (in_array(2, $perfilesUsuario)) {
                                                        echo '<li><a class="dropdown-item" href="#" onclick="generarGraficoTicket(\'tecnico\')">Técnico</a></li>';
                                                    }
                                                    if (in_array(3, $perfilesUsuario)) {
                                                        echo '<li><a class="dropdown-item" href="#" onclick="generarGraficoTicket(\'admin\')">Admin</a></li>';
                                                    }
                                                    ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
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
                            const perfilPorDefecto = '<?= $perfilPorDefecto ?>';
                            if (perfilPorDefecto) {
                                generarGraficoTicket(perfilPorDefecto);
                            } else {
                                generarGraficoTicket('auto');
                            }
                        });

                        document.addEventListener("DOMContentLoaded", () => {
                            cargarGraficoPorPerfil();
                        });
                        </script>

                        <!-- CONTENEDOR 2: Estados por Categoría de Ticket -->
                        <div class="col-lg-6 col-sm-12 ">
                            <div class="card shadow mb-4 p-4" data-intro="Este gráfico de líneas muestra la distribución de tickets según su estado en cada categoría. Permite visualizar cómo evolucionan los tickets (Recibido, En proceso, Terminado, etc.) dentro de distintas áreas, como Correos u Otros, facilitando el análisis por tipo de solicitud..">
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
                                                        <li><a class="dropdown-item" href="#" onclick="cargarGraficoPorPerfil('usuario')">Usuario</a></li>
                                                    <?php endif; ?>
                                                    <?php if (in_array(2, $perfiles)): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="cargarGraficoPorPerfil('tecnico')">Técnico</a></li>
                                                    <?php endif; ?>
                                                    <?php if (in_array(3, $perfiles)): ?>
                                                        <li><a class="dropdown-item" href="#" onclick="cargarGraficoPorPerfil('admin')">Administrador</a></li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        <?php endif; ?>
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
                            <div class="card shadow mb-4 px-0" data-step=12>
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
                                    <div id="contenedorUsuario"
                                        style="display: <?= ($perfilPorDefecto == 'usuario') ? 'block' : 'none'; ?>;">
                                        <?php  include("componentes/bloque_tabla_usuario.php") ?>
                                    </div>
                                    <div id="contenedorTecnico"
                                        style="display: <?= ($perfilPorDefecto == 'tecnico') ? 'block' : 'none'; ?>;">
                                        <?php  include("componentes/bloque_tabla_tecnico.php") ?>
                                    </div>
                                    <div id="contenedorAdmin"
                                        style="display: <?= ($perfilPorDefecto == 'administrador') ? 'block' : 'none'; ?>;">
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
                        const contenedores = ['contenedorUsuario', 'contenedorTecnico', 'contenedorAdmin'];
                        const contenedorId = tipo === 'admin' ? 'contenedorAdmin'
                            : tipo === 'tecnico' ? 'contenedorTecnico'
                            : 'contenedorUsuario';

                        contenedores.forEach(id => {
                            const el = document.getElementById(id);
                            if (el) el.style.display = id === contenedorId ? 'block' : 'none';
                        });

                        inicializarTablaPerfilDashboard(contenedorId);
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
        // estas 2 funcione sno me correon si als pongo en el archivo funciones.js
        function cargarGraficoPorPerfil(perfil = 'usuario') {
            fetch('grafico_estados_categoria.php?perfil=' + perfil)
                .then(res => res.json())
                .then(data => {
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
                });

        }


        function generarGraficoTicket(tipoUsuario = 'auto') {
            const idUsuarioSession = window.ID_USUARIO_SESSION || null;
            let url = `modelos/filtros/filtro_estadoTicketGrafico.php?tipoUsuario=${tipoUsuario}`;
            if (tipoUsuario === "usuario" || tipoUsuario === "tecnico") {
                url += `&idUsuario=${idUsuarioSession}`;
            }

            fetch(url)
                .then(res => res.json())
                .then(data => {
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
                })
                .catch(error => {
                    console.error("Error cargando gráfico:", error);
                });
        }

        //******************************************************************************************


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
        $tablaAdmin.DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            responsive: true,
            pageLength: 10,
            paging: true,
            pagingType: 'simple_numbers'
        });
    }

    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust().draw(false);
}

function actualizarGraficosPerfil(perfil) {
    if (typeof generarGraficoTicket === 'function') {
        generarGraficoTicket(perfil);
    }

    if (typeof cargarGraficoPorPerfil === 'function') {
        cargarGraficoPorPerfil(perfil);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const textoPerfil = document.getElementById('textoPerfilActual');
    const perfilInicial = document.getElementById('contenedorAdmin')?.style.display === 'block'
        ? 'admin'
        : document.getElementById('contenedorTecnico')?.style.display === 'block'
            ? 'tecnico'
            : 'usuario';

    inicializarTablaPerfilDashboard('contenedorUsuario');
    inicializarTablaPerfilDashboard('contenedorTecnico');
    inicializarTablaPerfilDashboard('contenedorAdmin');
    mostrarListado(perfilInicial);

    if (textoPerfil) {
        textoPerfil.innerText = perfilInicial === 'admin'
            ? 'Admin'
            : perfilInicial.charAt(0).toUpperCase() + perfilInicial.slice(1);
    }
});

function cambiarPerfil(perfil) {
    const nombrePerfil = perfil.charAt(0).toUpperCase() + perfil.slice(1);
    const iconos = {
        admin: 'bi-person-gear',
        tecnico: 'bi-wrench-adjustable-circle',
        usuario: 'bi-person-circle'
    };

    Swal.fire({
        title: `¿Cambiar a perfil ${nombrePerfil}?`,
        html: `<i class="bi ${iconos[perfil]} fs-1 text-primary mb-3"></i><br>Estás a punto de cambiar de vista.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
            popup: 'cuerpo_modal_guardar'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('textoPerfilActual').innerText = nombrePerfil;

            mostrarListado(perfil);
            actualizarGraficosPerfil(perfil);

            Swal.fire({
                title: 'Perfil cambiado',
                html: `<i class="bi ${iconos[perfil]} fs-1 text-success mb-2"></i><br>Ahora estás en el perfil <b>${nombrePerfil}</b>`,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false,
                customClass: {
                    popup: 'cuerpo_modal_guardar'
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
