<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';

$funciones = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);

// IDs de administradores
$nombre = htmlspecialchars($_SESSION['nombre']) . ' ' . htmlspecialchars($_SESSION['apellido_paterno']);
$AreaTrabajo = htmlspecialchars($_SESSION['id_area_trabajo']);
$idPagActual        = "8";


// Obtener alertas de cumpleaños y tickets
$alertas = $funciones->alertaModal($idUsuarioSession);
$mensajes = $funciones->mensajesModal($idUsuarioSession);
$estados = $funciones->cargarEstadosTicket($idUsuarioSession);

if ($_SESSION['primera_vez'] == 0) {
    if ($AreaTrabajo != 1) {
        // Si el usuario no pertenece al área 1, mostrar una bienvenida simple
        $funciones->bienvenidoUsuario($idUsuarioSession);
    } else {
        // Si el usuario pertenece al área 1, mostrar el modal con los tickets
        $funciones->alertasBienvenida($idUsuarioSession, $AreaTrabajo);
    }

    // Actualizar la variable de sesión para que en futuras visitas no se muestre
    $_SESSION['primera_vez'] = 1;
}


?>




<script>
var idUsuarioSession = <?php echo json_encode($idUsuarioSession); ?>;
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
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <script type="text/javascript" src="js/funciones.js"></script>
    <script type="text/javascript" src="js/mensajes.js"></script>
    <script type="text/javascript" src="js/ticket.js"></script>
    <script type="text/javascript" src="js/buscadores.js"></script>
    <script type="text/javascript" src="js/validacionTicket.js"></script>







</head>

<body id="page-top">
    <input type="hidden" id="idUsuario" value="<?php echo $idUsuarioSession; ?>" />
    <div id="wrapper">
        <?php $funciones->menuLateral($idUsuarioSession, $idPagActual);   ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>

                <!--****************ACCESOS DIRECTOS *****************-->
                <div id="botonesDescargar">
                    <button type="button" class="btn btn-success"
                        onclick="obtenerAccesosDirectos('<?php echo $idUsuarioSession; ?>')">
                        <i class="bi bi-grid-fill"></i>
                    </button>

                    <?php $funciones->obtenerAccesosDirectos($idUsuarioSession); ?>
                </div>
                <!--****************ACCESOS DIRECTOS ********************-->

                <div class="d-sm-flex align-items-center justify-content-between mb-4"></div>
                <div class="row mx-4">

                    <div class="col-lg-6 col-sm-12">
                        <div class="card shadow mb-4 px-0">
                            <!-- Encabezado de la tarjeta -->
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Gráfico de Tickets</h6>
                            </div>

                            <div class="card-body altoMax">
                                <!-- Menú de pestañas -->
                                <ul class="nav nav-tabs" id="graficoTicketsTab" role="tablist">

                                    <?php if (!in_array($idUsuarioSession, [6, 7, 8, 36, 42])): ?>
                                    <!-- Pestaña: Gráfico Usuario (Solo para usuarios normales) -->
                                    <li class="nav-item position-relative" role="presentation">
                                        <a class="nav-link active" id="graficoUsuario-tab" data-bs-toggle="tab"
                                            href="#graficoUsuario" role="tab" aria-controls="graficoUsuario"
                                            aria-selected="true">
                                            Gráfico Usuario
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <?php if (in_array($idUsuarioSession, [6, 7, 8, 36, 42])): ?>
                                    <!-- Pestaña: Gráfico Técnico -->
                                    <li class="nav-item position-relative" role="presentation">
                                        <a class="nav-link <?php echo (in_array($idUsuarioSession, [6, 7, 8, 36, 42])) ? 'active' : ''; ?>"
                                            id="graficoTecnico-tab" data-bs-toggle="tab" href="#graficoTecnico"
                                            role="tab" aria-controls="graficoTecnico" aria-selected="true">
                                            Gráfico Técnico
                                        </a>
                                    </li>

                                    <!-- Pestaña: Gráfico Administrador -->
                                    <li class="nav-item position-relative" role="presentation">
                                        <a class="nav-link" id="graficoAdministrador-tab" data-bs-toggle="tab"
                                            href="#graficoAdministrador" role="tab" aria-controls="graficoAdministrador"
                                            aria-selected="false">
                                            Gráfico Administrador
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                </ul>

                                <!-- Contenido de las pestañas -->
                                <div class="tab-content mt-3" id="graficoTicketsContent">

                                    <?php if (!in_array($idUsuarioSession, [6, 7, 8, 36, 42])): ?>
                                    <!-- Contenedor para Gráfico Usuario -->
                                    <div class="tab-pane fade show active" id="graficoUsuario" role="tabpanel"
                                        aria-labelledby="graficoUsuario-tab">
                                        <canvas id="horizontalBarChartUsuario" width="708" height="343"
                                            style="display: block; box-sizing: border-box; height: 250px; width: 515px;"></canvas>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (in_array($idUsuarioSession, [6, 7, 8, 36, 42])): ?>
                                    <!-- Contenedor para Gráfico Técnico -->
                                    <div class="tab-pane fade show <?php echo (in_array($idUsuarioSession, [6, 7, 8, 36, 42])) ? 'active' : ''; ?>"
                                        id="graficoTecnico" role="tabpanel" aria-labelledby="graficoTecnico-tab">
                                        <canvas id="horizontalBarChartTecnico" width="708" height="343"
                                            style="display: block; box-sizing: border-box; height: 250px; width: 515px;"></canvas>
                                    </div>

                                    <!-- Contenedor para Gráfico Administrador -->
                                    <div class="tab-pane fade" id="graficoAdministrador" role="tabpanel"
                                        aria-labelledby="graficoAdministrador-tab">
                                        <canvas id="horizontalBarChartAdministrador"></canvas>
                                    </div>
                                    <?php endif; ?>

                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- CONTENDOR DE MENSAJES -->

                    <div class="col-lg-6 col-sm-12">
                        <div class="card shadow mb-4 px-0">
                            <div class="card-header py-3 ">
                                <h6 class="m-0 font-weight-bold text-primary">Mensajes Recibidos</h6>
                            </div>
                            <div class="card-body altoMax">
                                <div class="table-responsive">
                                    <div id="dataTable_filter" class="dataTables_filter">
                                        <label>Buscar:<input type="search" id="idBuscarmensaje"
                                                class="form-control form-control-sm" placeholder=""
                                                aria-controls="dataTable"></label>
                                    </div>
                                    <div id="ticketContainer">
                                        <table class="table table-striped" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th onclick="fil_Ticket_Usario(0)">N° <i
                                                            class="bi bi-arrow-down-short" id="icon-0"></i></th>
                                                    <th onclick="fil_Ticket_Usario(1)">MENSAJE <i
                                                            class="bi bi-arrow-down-short" id="icon-1"></i></th>
                                                    <th onclick="fil_Ticket_Usario(2)">USUARIO <i
                                                            class="bi bi-arrow-down-short" id="icon-2"></i></th>
                                                    <th onclick="fil_Ticket_Usario(2)">FECHA <i
                                                            class="bi bi-arrow-down-short" id="icon-3"></i></th>
                                                    <th>OPCIONES</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                        $mensajesPrincipal = $funciones->obtenerMensajes($idUsuarioSession);
                                        $contador = 1;
                            
                                        if (!empty($mensajesPrincipal)) { // Verificar si hay mensajes
                                            foreach ($mensajesPrincipal as $row) { ?>
                                                <tr>
                                                    <td><?= $contador++; ?></td>
                                                    <td><?= htmlspecialchars($row['mensaje']); ?></td>
                                                    <td><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellido_paterno']); ?>
                                                    </td>
                                                    <td><?= htmlspecialchars($row['fecha']); ?></td>
                                                    <td>
                                                        <div class="d-flex gap-2">
                                                            <!-- Archivar mensaje -->
                                                            <a href="#" class="btn btn-warning btn-icon-split"
                                                                title="Archivar mensaje"
                                                                onclick="mensajeArchivado(<?= $row['id_mensaje']; ?>)"
                                                                data-bs-toggle="popover" data-bs-placement="top"
                                                                title="Archivar "
                                                                data-bs-content="Aquí podrás Archivar el mensaje .">
                                                                <i class="bi bi-archive"></i>
                                                            </a>
                                                            <!-- Eliminar mensaje -->
                                                            <a href="#" class="btn btn-danger btn-icon-split"
                                                                title="Eliminar mensaje"
                                                                onclick="eliminarMensaje(<?= $row['id_mensaje']; ?>)"
                                                                data-bs-toggle="popover" data-bs-placement="top"
                                                                title="Eliminar "
                                                                data-bs-content="Aquí podrás Eliminar el mensaje.">
                                                                <i class="bi bi-trash3"></i>
                                                            </a>
                                                            <!-- Ver Conversación -->
                                                            <a href="#" class="btn btn-primary btn-icon-split"
                                                                title="Ver Conversación"
                                                                onclick="cargarMensajesConversacion(<?= $row['id_mensaje']; ?>, <?= $row['de']; ?>, <?= $row['para']; ?>);"
                                                                data-bs-toggle="popover" data-bs-placement="top"
                                                                title="Conversacion"
                                                                data-bs-content="Aquí podrás generar una conversacion a partir de un mensaje.">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <?php }
                                        } else { ?>
                                                <tr>
                                                    <td colspan="8">No hay mensajes enviados.</td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!-- Contenedor del Offcanvas -->
                                    <div id="offcanvasContainer"></div>
                                </div>
                            </div>
                        </div>
                    </div>



                    <!--Contenedor para usuario y admin  Tecnico-->
                    <div class="col-12">
                        <div class="card shadow mb-4 px-0">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Listado de Tickets</h6>
                            </div>

                            <div class="card-body">
                                <!-- Pestañas -->
                                <ul class="nav nav-tabs" id="graficoTicketsTab" role="tablist">
                                    <?php if ($AreaTrabajo == 1) { ?>
                                    <li class="nav-item position-relative" role="presentation">
                                        <a class="nav-link active" id="listadoUsuario-tab" data-bs-toggle="tab"
                                            href="#listadoUsuario" role="tab" aria-controls="listadoUsuario"
                                            aria-selected="true">Técnico</a>
                                    </li>

                                    <li class="nav-item position-relative" role="presentation">
                                        <a class="nav-link" id="listadoAdministrador-tab" data-bs-toggle="tab"
                                            href="#listadoAdministrador" role="tab" aria-controls="listadoAdministrador"
                                            aria-selected="false">Administrador</a>
                                    </li>
                                    <?php } ?>

                                    <li class="nav-item position-relative" role="presentation">
                                        <a class="nav-link <?= ($AreaTrabajo != 1) ? 'active' : ''; ?>"
                                            id="listadoUsuarioGeneral-tab" data-bs-toggle="tab"
                                            href="#listadoUsuarioGeneral" role="tab"
                                            aria-controls="listadoUsuarioGeneral"
                                            aria-selected="<?= ($AreaTrabajo != 1) ? 'true' : 'false'; ?>">Usuario</a>
                                    </li>
                                </ul>

                                <!-- Campo de búsqueda -->
                                <div class="table-responsive">
                                    <div id="dataTable_filter" class="dataTables_filter">
                                        <label>Buscar:
                                            <input type="search" id="idBuscarTicket"
                                                class="form-control form-control-sm" placeholder="Buscar tickets..."
                                                aria-controls="dataTable">
                                        </label>
                                    </div>
                                </div>

                                <!-- Contenedor de pestañas -->
                                <div class="tab-content mt-3">
                                    <?php if ($AreaTrabajo == 1) { ?>
                                    <!-- Pestaña Técnico -->
                                    <div class="tab-pane fade show active" id="listadoUsuario" role="tabpanel"
                                        aria-labelledby="listadoUsuario-tab">

                                        <div class="table-responsive">
                                            <table class="table table-striped" id="tablaListadoUsuario" width="100%"
                                                cellspacing="0">
                                                <thead>
                                                    <tr>
                                                        <th onclick="sortTable(0)">ID
                                                            <i class="bi bi-arrow-down-short float-end" id="icon-0"></i>
                                                        </th>
                                                        <th onclick="sortTable(1)">FECHA
                                                            <i class="bi bi-arrow-down-short float-end" id="icon-1"></i>
                                                        </th>
                                                        <th onclick="sortTable(2)">HORA
                                                            <i class="bi bi-arrow-down-short float-end" id="icon-2"></i>
                                                        </th>
                                                        <th onclick="sortTable(3)">DE
                                                            <i class="bi bi-arrow-down-short float-end" id="icon-3"></i>
                                                        </th>
                                                        <th onclick="sortTable(4)">ASUNTO
                                                            <i class="bi bi-arrow-down-short float-end" id="icon-4"></i>
                                                        </th>
                                                        <th onclick="sortTable(5)">ESTADO
                                                            <i class="bi bi-arrow-down-short float-end" id="icon-5"></i>
                                                        </th>
                                                        <th onclick="sortTable(6)">FEC RES
                                                            <i class="bi bi-arrow-down-short float-end" id="icon-6"></i>
                                                        </th>
                                                        <th onclick="sortTable(7)">DIAS RESTANTES
                                                            <i class="bi bi-arrow-down-short float-end" id="icon-7"></i>
                                                        </th>
                                                        <th>OPCIONES</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $resultado = $funciones->obtenerTicketsTecnico($idUsuarioSession);
                                                    $counter = 1; 

                                                    if ($resultado->num_rows > 0) { 
                                                        while ($row = $resultado->fetch_array()) {                  
                                                        
                                                            $nombreUsuarioProblema = $row['nombre'] . " " . $row['apellido_paterno'];

                                                            // Calcular los días restantes solo para id_estado = 3
                                                            $diasRestantes = $row['dias_administrador_estima'];
                                                            if ($row['id_estado'] == 3) {
                                                                $fechaCreacion      = new DateTime($row['fecha_creacion_inicio']);
                                                                $fechaActual        = new DateTime();
                                                                $diasPasados        = $fechaActual->diff($fechaCreacion)->days;
                                                                $diasRestantes      = max(0, $diasRestantes - $diasPasados); // Asegurarse de no mostrar valores negativos
                                                            }
                                                          
                                                ?>
                                                    <tr
                                                        style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important;">
                                                        <td><?= $counter++; ?></td>
                                                        <td><?= date('d-m-Y', strtotime($row['fecha_creacion_inicio'])); ?>
                                                        </td>
                                                        <td><?= htmlspecialchars($row['hora_creacion_inicio']); ?></td>
                                                        <td><?= htmlspecialchars($nombreUsuarioProblema); ?></td>
                                                        <td><?= htmlspecialchars($row['asunto']); ?></td>
                                                        <td
                                                            style="color: black; text-shadow: 2px 2px 2px rgba(150, 150, 150, 0.5);">
                                                            <?= htmlspecialchars($row['nombreEstado']); ?></td>
                                                        <td>
                                                            <?php 
                                                            if ($row['fecha_estimada_admin'] == '0000-00-00') {
                                                                echo '--';
                                                            } else {
                                                                echo date('d-m-Y', strtotime($row['fecha_estimada_admin']));
                                                            }
                                                        ?>
                                                        </td>
                                                        <td>
                                                            <?php 
                                                            if ($row['id_estado'] == 3) {
                                                                if ($diasRestantes == 0) {
                                                                    echo '<span class="not-started">--</span>';
                                                                } else {
                                                                    echo '<i class="status-icon in-process fas fa-hourglass-half" style="animation: blinkingText 1.2s infinite;"></i> ' . htmlspecialchars($diasRestantes);
                                                                }
                                                            } elseif ($row['id_estado'] == 5) {
                                                                echo '<i class="status-icon resolved fas fa-check-circle" style="color: green;"></i> Resuelto';
                                                            } elseif ($row['id_estado'] == 2) {
                                                                echo '--';
                                                            } else {
                                                                if ($row['dias_administrador_estima'] == 0) {
                                                                    echo '<span class="not-started">---</span>';
                                                                } else {
                                                                    echo htmlspecialchars($row['dias_administrador_estima']);
                                                                }
                                                            }
                                                        ?>
                                                        </td>
                                                        <td class="d-flex justify-content-start align-items-center">
                                                            <a href="#" class="btn btn-primary btn-icon-split me-2"
                                                                id="idVerTicket"
                                                                onclick="verTicketTecnico_Asignado('<?= $row['id_ticket']; ?>')"
                                                                data-bs-toggle="popover" data-bs-placement="top"
                                                                title="Ver Ticket"
                                                                data-bs-content="Aquí podrás ver todas las características de tu ticket.">
                                                                <i class="bi bi-eye"></i>
                                                            </a>

                                                            <a href="#" class="btn btn-secondary btn-icon-split me-2"
                                                                id="idverConversacion"
                                                                onclick="cargarTicketConversacion('<?= $row['id_ticket']; ?>', '<?= $idUsuarioSession; ?>', '<?= $row['id_tecnico']; ?>')"
                                                                data-bs-toggle="popover" data-bs-placement="top"
                                                                title="Ver Conversación"
                                                                data-bs-content="Aquí podrás ver la conversación relacionada con este ticket.">
                                                                <i class="bi bi-chat-dots"></i>
                                                            </a>

                                                            <?php if ($row['cantidadArchivos'] > 0) { ?>
                                                            <button type="button"
                                                                class="btn btn-primary position-relative"
                                                                onclick="archivosAdjuntos('<?= $row['id_ticket']; ?>')"
                                                                data-bs-toggle="popover" data-bs-placement="top"
                                                                title="Archivos Adjuntos"
                                                                data-bs-content="Haz clic para ver los archivos adjuntos a este ticket.">
                                                                <i class="bi bi-paperclip"></i>
                                                                <span
                                                                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                                    <?= $row['cantidadArchivos']; ?>
                                                                    <span class="visually-hidden">unread messages</span>
                                                                </span>
                                                            </button>
                                                            <?php } ?>
                                                        </td>
                                                    </tr>
                                                    <?php 
                                                            }
                                                        } else { 
                                                    ?>
                                                    <tr>
                                                        <td colspan="9">No hay tickets disponibles.</td>
                                                    </tr>
                                                    <?php 
                                                            } 
                                                        ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- Pestaña Administrador -->
                                    <div class="tab-pane fade" id="listadoAdministrador" role="tabpanel"
                                        aria-labelledby="listadoAdministrador-tab">
                                        <table class="table table-striped table-hover align-middle"
                                            id="IDdataTablaAdmin" width="100%" cellspacing="0" data-sort-order="asc">
                                            <thead class="table-light">
                                                <tr>
                                                    <th onclick="ordenarTablaAdministrador(0)" style="width:50px;">ID
                                                        <i class="bi bi-arrow-down-short float-end" id="icon-0"></i>
                                                    </th>
                                                    <th onclick="ordenarTablaAdministrador(1)" style="width:100px;">
                                                        FECHA <i class="bi bi-arrow-down-short float-end"
                                                            id="icon-1"></i></th>
                                                    <th onclick="ordenarTablaAdministrador(2)" style="width:100px;">HORA
                                                        <i class="bi bi-arrow-down-short float-end" id="icon-2"></i>
                                                    </th>
                                                    <th onclick="ordenarTablaAdministrador(3)">DE <i
                                                            class="bi bi-arrow-down-short float-end" id="icon-3"></i>
                                                    </th>
                                                    <th onclick="ordenarTablaAdministrador(4)">ASUNTO <i
                                                            class="bi bi-arrow-down-short float-end" id="icon-4"></i>
                                                    </th>
                                                    <th onclick="ordenarTablaAdministrador(5)">ESTADO <i
                                                            class="bi bi-arrow-down-short float-end" id="icon-5"></i>
                                                    </th>
                                                    <th>TECNICO ASIGNADO</th>
                                                    <th>OPCIONES</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $resultado = $funciones->ticketAdministrador($idUsuarioSession);
                                                $contador = 1;

                                                if ($resultado->num_rows > 0) { 
                                                    while ($row = $resultado->fetch_array()) {
                                                        $color = $row['colorEstado'];
                                                        $nombreUsuarioProblema = $row['nombreUsuario'] . " " . $row['apellidoUsuario'];
                                                        $nombreUsuarioTecnico = $row['nombreTecnico'] . " " . $row['apellidoTecnico'];

                                                        $diasRestantes = $row['dias_administrador_estima'];
                                                        if ($row['id_estado'] == 3) {
                                                            $fechaCreacion = new DateTime($row['fecha_creacion_inicio']);
                                                            $fechaActual = new DateTime();
                                                            $diasPasados = $fechaActual->diff($fechaCreacion)->days;
                                                            $diasRestantes = max(0, $diasRestantes - $diasPasados);
                                                        }
                                                ?>
                                                <tr style="background-color: <?= $color; ?> !important;">
                                                    <td style="background-color: <?= $color; ?> !important;">
                                                        <?= $contador++; ?></td>
                                                    <td style="background-color: <?= $color; ?> !important;">
                                                        <?= date('d-m-Y', strtotime($row['fecha_creacion_inicio'])); ?>
                                                    </td>
                                                    <td style="background-color: <?= $color; ?> !important;">
                                                        <?= htmlspecialchars($row['hora_creacion_inicio']); ?></td>
                                                    <td style="background-color: <?= $color; ?> !important;">
                                                        <?= htmlspecialchars($nombreUsuarioProblema); ?></td>
                                                    <td style="background-color: <?= $color; ?> !important;">
                                                        <?= htmlspecialchars($row['asunto']); ?></td>

                                                    <td style="background-color: <?= $color; ?> !important;">
                                                        <button class="btn btn-primary btn-sm fixed-width-button"
                                                            onclick="mostrarToast(<?= $row['id_ticket']; ?>, this)"
                                                            data-bs-toggle="popover" data-bs-placement="top"
                                                            title="Ver Línea de Tiempo"
                                                            data-bs-content="Aquí podrás ver una línea de tiempo de los estados del ticket.">
                                                            <?= htmlspecialchars($row['nombreEstado']); ?>
                                                        </button>
                                                    </td>

                                                    <!-- Aquí llamamos a la función que genera el toast -->
                                                    <?= $funciones->mostrarToastTicket($row, $nombreUsuarioTecnico); ?>

                                                    <td style="background-color: <?= $color; ?> !important;">
                                                        <?php if ($row['id_tecnico'] == null || $row['id_tecnico'] == '') { ?>
                                                        <?= $funciones->tecnicos($row['id_ticket']); ?>
                                                        <?php } else { ?>
                                                        <?= htmlspecialchars($nombreUsuarioTecnico); ?>
                                                        <?php } ?>
                                                    </td>

                                                    <td class="d-flex justify-content-start align-items-center"
                                                        style="background-color: <?= $color; ?> !important;">
                                                        <a href="#" class="btn btn-primary btn-icon-split me-2"
                                                            id="idVerTicket"
                                                            onclick="verTicketAdministrativo('<?= $row['id_ticket']; ?>')"
                                                            data-bs-toggle="popover" data-bs-placement="top"
                                                            title="Ver Ticket"
                                                            data-bs-content="Aquí podrás ver todas las características de tu ticket.">
                                                            <i class="bi bi-eye"></i>
                                                        </a>

                                                        <?php if ($row['cantidadArchivos'] > 0) { ?>
                                                        <button type="button" class="btn btn-primary position-relative"
                                                            onclick="archivosAdjuntos('<?= $row['id_ticket']; ?>')"
                                                            data-bs-toggle="popover" data-bs-placement="top"
                                                            title="Archivos Adjuntos"
                                                            data-bs-content="Haz clic para ver los archivos adjuntos a este ticket.">
                                                            <i class="bi bi-paperclip"></i>
                                                            <span
                                                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                                <?= $row['cantidadArchivos']; ?>
                                                                <span class="visually-hidden">unread messages</span>
                                                            </span>
                                                        </button>
                                                        <?php } ?>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    }
                                                } else { 
                                                ?>
                                                <tr>
                                                    <td colspan="9">No hay tickets disponibles.</td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>

                                        </table>
                                    </div>
                                    <?php } ?>

                                    <!-- Pestaña Usuario -->
                                    <div class="tab-pane fade <?= ($AreaTrabajo != 1) ? 'show active' : ''; ?>"
                                        id="listadoUsuarioGeneral" role="tabpanel"
                                        aria-labelledby="listadoUsuarioGeneral-tab">

                                        <div id="ticketContainer">
                                            <div class="table-responsive">
                                                <table class="table table-striped" id="idTablaTicket" width="100%"
                                                    cellspacing="0">
                                                    <thead>
                                                        <tr>
                                                            <th onclick="sortTable(0)">ID
                                                                <i class="bi bi-arrow-down-short float-end"
                                                                    id="icon-0"></i>
                                                            </th>
                                                            <th onclick="sortTable(1)">FECHA <i
                                                                    class="bi bi-arrow-down-short float-end"
                                                                    id="icon-1"></i>
                                                            </th>
                                                            <th onclick="sortTable(2)">HORA <i
                                                                    class="bi bi-arrow-down-short float-end"
                                                                    id="icon-2"></i>
                                                            </th>
                                                            <th onclick="sortTable(3)">DE <i
                                                                    class="bi bi-arrow-down-short float-end"
                                                                    id="icon-3"></i>
                                                            </th>
                                                            <th onclick="sortTable(4)">ASUNTO <i
                                                                    class="bi bi-arrow-down-short float-end"
                                                                    id="icon-4"></i>
                                                            </th>
                                                            <th onclick="sortTable(5)">ESTADO <i
                                                                    class="bi bi-arrow-down-short float-end"
                                                                    id="icon-5"></i>
                                                            </th>
                                                            <th onclick="sortTable(6)">FEC RES <i
                                                                    class="bi bi-arrow-down-short float-end"
                                                                    id="icon-6"></i>
                                                            </th>
                                                            <th onclick="sortTable(7)">DIAS RESTANTES <i
                                                                    class="bi bi-arrow-down-short float-end"
                                                                    id="icon-7"></i>
                                                            </th>
                                                            <th>OPCIONES</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php 
                                                                                                   
                                                            $resultado = $funciones->ticketUsuario($idUsuarioSession);
                                                  
                                                        $counter = 1; 

                                                        if ($resultado->num_rows > 0) { 
                                                            while ($row = $resultado->fetch_array()) {                  

                                                                $nombreUsuarioProblema = $row['nombre'] . " " . $row['apellido_paterno'];

                                                                // Calcular los días restantes solo para id_estado = 3
                                                                $diasRestantes = $row['dias_administrador_estima'];
                                                                if ($row['id_estado'] == 3) {
                                                                    $fechaCreacion = new DateTime($row['fecha_creacion_inicio']);
                                                                    $fechaActual = new DateTime();
                                                                    $diasPasados = $fechaActual->diff($fechaCreacion)->days;
                                                                    $diasRestantes = max(0, $diasRestantes - $diasPasados); // Asegurar que no sea negativo
                                                                }
                                                    ?>
                                                        <tr
                                                            style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important;">
                                                            <td
                                                                style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important;">
                                                                <?= $counter++; ?>
                                                            </td>
                                                            <td
                                                                style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important;">
                                                                <?= date('d-m-Y', strtotime($row['fecha_creacion_inicio'])); ?>
                                                            </td>
                                                            <td
                                                                style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important;">
                                                                <?= htmlspecialchars($row['hora_creacion_inicio']); ?>
                                                            </td>
                                                            <td
                                                                style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important;">
                                                                <?= htmlspecialchars($nombreUsuarioProblema); ?>
                                                            </td>
                                                            <td
                                                                style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important;">
                                                                <?= htmlspecialchars($row['asunto']); ?>
                                                            </td>
                                                            <td
                                                                style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important; 
                                                                color: black; text-shadow: 2px 2px 2px rgba(150, 150, 150, 0.5);">
                                                                <?= htmlspecialchars($row['nombreEstado']); ?>
                                                            </td>
                                                            <td
                                                                style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important;">
                                                                <?php 
                                                                if ($row['fecha_estimada_admin'] == '0000-00-00') {
                                                                    echo '--';
                                                                } else {
                                                                    echo date('d-m-Y', strtotime($row['fecha_estimada_admin']));
                                                                }
                                                            ?>
                                                            </td>
                                                            <td
                                                                style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important;">
                                                                <?php 
                                                                if ($row['id_estado'] == 3) {
                                                                    if ($diasRestantes == 0) {
                                                                        echo '<span class="not-started">--</span>';
                                                                    } else {
                                                                        echo '<i class="status-icon in-process fas fa-hourglass-half" style="animation: blinkingText 1.2s infinite;"></i> ' . htmlspecialchars($diasRestantes);
                                                                    }
                                                                } elseif ($row['id_estado'] == 5) {
                                                                    echo '<i class="status-icon resolved fas fa-check-circle" style="color: green;"></i> Resuelto';
                                                                } elseif ($row['id_estado'] == 2) {
                                                                    echo '--';
                                                                } else {
                                                                    if ($row['dias_administrador_estima'] == 0) {
                                                                        echo '<span class="not-started">--</span>';
                                                                    } else {
                                                                        echo htmlspecialchars($row['dias_administrador_estima']);
                                                                    }
                                                                }
                                                            ?>
                                                            </td>
                                                            <td class="d-flex justify-content-start align-items-center"
                                                                style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important;">
                                                                <a href="#" class="btn btn-primary btn-icon-split me-2"
                                                                    id="idVerTicket"
                                                                    onclick="<?= ($AreaTrabajo == 1) ? 'verTicketTecnico_Asignado(' . $row['id_ticket'] . ')' : 'verTicketAdministrativo(' . $row['id_ticket'] . ')' ?>"
                                                                    data-bs-toggle="popover" data-bs-placement="top"
                                                                    title="Ver Ticket"
                                                                    data-bs-content="Aquí podrás ver todas las características de tu ticket.">
                                                                    <i class="bi bi-eye"></i>
                                                                </a>

                                                                <a href="#"
                                                                    class="btn btn-secondary btn-icon-split me-2"
                                                                    id="idverConversacion"
                                                                    onclick="cargarTicketConversacion('<?= $row['id_ticket']; ?>', '<?= $idUsuarioSession; ?>', '<?= $row['id_tecnico']; ?>')"
                                                                    data-bs-toggle="popover" data-bs-placement="top"
                                                                    title="Ver Conversación"
                                                                    data-bs-content="Aquí podrás ver la conversación relacionada con este ticket.">
                                                                    <i class="bi bi-chat-dots"></i>
                                                                </a>

                                                                <?php if ($row['id_estado'] == 5) { ?>
                                                                <a href="#"
                                                                    class="btn btn-danger btn-icon-split me-2 btn-llamativo"
                                                                    id="idverConversacion"
                                                                    onclick="validacionTicketPorUsuario('<?= $row['id_ticket']; ?>')"
                                                                    data-bs-toggle="popover" data-bs-placement="top"
                                                                    title="Validar Ticket"
                                                                    data-bs-content="Haz clic para validar la calidad del ticket.">
                                                                    <i class="bi bi-clipboard-check"></i>
                                                                </a>
                                                                <?php } ?>



                                                                <?php if ($row['cantidadArchivos'] > 0) { ?>
                                                                <button type="button"
                                                                    class="btn btn-primary position-relative"
                                                                    onclick="archivosAdjuntos('<?= $row['id_ticket']; ?>')"
                                                                    data-bs-toggle="popover" data-bs-placement="top"
                                                                    title="Archivos Adjuntos"
                                                                    data-bs-content="Haz clic para ver los archivos adjuntos a este ticket.">
                                                                    <i class="bi bi-paperclip"></i>
                                                                    <span
                                                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                                        <?= $row['cantidadArchivos']; ?>
                                                                        <span class="visually-hidden">unread
                                                                            messages</span>
                                                                    </span>
                                                                </button>


                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                        <?php 
                                                            }
                                                        } else { 
                                                        ?>
                                                        <tr>
                                                            <td colspan="9">No hay tickets disponibles.</td>
                                                        </tr>
                                                        <?php 
                                                    } 
                                                    ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>






                    <!-- container para  ver los el char de conversacion  -->
                    <div id="offcanvasContainerTicket"></div>
                    <!-- container para  ver los el char de conversacion  -->

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