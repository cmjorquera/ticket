<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';

if (!isset($_SESSION['id'])) {
    exit('Acceso no autorizado');
}

$funciones = new Funciones();
$idUsuarioSession = (int)$_SESSION['id'];
$nombre = htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido_paterno']);
$AreaTrabajo = (int)$_SESSION['id_area_trabajo'];
$idPagActual = 14;
$eventos = json_decode($funciones->obtenerEventos(), true);

// Filtrado de eventos de la semana actual
$eventosSemana = array_filter($eventos, function ($evento) {
    $fechaEvento = new DateTime($evento['start']);
    $semanaEvento = (int)$fechaEvento->format("W");
    $semanaActual = (int)date("W");
    return $semanaEvento === $semanaActual;
});
$totalSemana = count($eventosSemana);

// Filtrado de eventos de hoy y otros contadores
$eventosHoy = array_filter($eventos, fn($e) => (new DateTime($e['start']))->format('Y-m-d') === date('Y-m-d'));
$totalPresentacion = array_filter($eventos, fn($e) => $e['solo_presentacion'] == 1);
$totalConAudio = array_filter($eventos, fn($e) => $e['con_audio'] == 1);
$totalConMusica = array_filter($eventos, fn($e) => $e['musica_ambiental'] == 1);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php $funciones->header(); ?>

    <!-- Estilos -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
    .blinking-border {
        border: 2px solid red;
        animation: blinkingBorder 1s infinite;
    }

    @keyframes blinkingBorder {
        0% {
            border-color: red;
        }

        50% {
            border-color: transparent;
        }

        100% {
            border-color: red;
        }
    }
    </style>

    <!-- CSS personalizados -->
    <link href="css/modalesTicket.css" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">

    <!-- Scripts globales -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/funciones.js"></script>
    <script type="text/javascript" src="js/mensajes.js"></script>
    <script type="text/javascript" src="js/ticket.js"></script>
    <script type="text/javascript" src="js/buscadores.js"></script>
    <script type="text/javascript" src="js/validacionTicket.js"></script>
    <script type="text/javascript" src="js/chat_ticket.js"></script>
    <script type="text/javascript" src="js/calendarioEventos.js"></script>

    <script>
    const idUsuarioSession = <?= json_encode($idUsuarioSession) ?>;
    const eventos = <?= json_encode($eventos) ?>;
    </script>
</head>

<body id="page-top">
    <input type="hidden" id="idUsuario" value="<?= htmlspecialchars($idUsuarioSession) ?>" />

    <div id="wrapper">
        <?php $funciones->menuLateral3($idUsuarioSession, $idPagActual); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>

                <!-- Contenido principal -->
                <div class="container-fluid">
                    <!-- Fila 1: Dos contenedores lado a lado con mismo alto -->
                    <div class="row mb-4 d-flex align-items-stretch">
                        <!-- Tarjetas de Resumen (col-lg-4) -->
                        <div class="col-lg-4 col-sm-6">
                            <div class="card shadow mb-6 h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Tarjetas de Resumen</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <!-- Tarjeta: Eventos Hoy -->
                                        <div class="col-md-12 mb-3">
                                            <div
                                                class="card bg-warning text-dark <?= count($eventosHoy) > 0 ? 'blinking-border' : '' ?>">
                                                <div class="card-header py-2">
                                                    <h6 class="m-0 font-weight-bold text-primary">Eventos Hoy</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong>Hoy</strong>
                                                            <div class="h5 mb-0 fw-bold"><?= count($eventosHoy) ?></div>
                                                        </div>
                                                        <i class="bi bi-calendar-event-fill fa-2x"></i>
                                                    </div>

                                                    <!-- Lista de eventos con iconos -->
                                                    <div class="list-group mt-3">
                                                        <?php if(count($eventosHoy) > 0): ?>
                                                        <?php foreach($eventosHoy as $evento): ?>
                                                        <div class="list-group-item list-group-item-action flex-column align-items-start" style="background-color: #ffc107;">
                                                            <div class="d-flex w-100 justify-content-between">
                                                                <h5 class="mb-1">
                                                                    <i class="fas fa-ticket-alt"></i>
                                                                    <?= htmlspecialchars($evento['title']) ?>
                                                                </h5>
                                                                <small>
                                                                    <i class="far fa-clock"></i>
                                                                    <?= htmlspecialchars($evento['horaEvento']) ?>
                                                                </small>
                                                            </div>
                                                            <p class="mb-1">
                                                                <i class="far fa-calendar-alt"></i>
                                                                <?= htmlspecialchars($evento['start']) ?>
                                                            </p>
                                                            <p class="mb-1">
                                                                <?= htmlspecialchars($evento['descripcion']) ?></p>
                                                            <small>
                                                                <i class="fas fa-user"></i>    <?= htmlspecialchars($evento['personaResponsable']) ?>
                                                            </small>
                                                        </div>
                                                        <?php endforeach; ?>
                                                        <?php else: ?>
                                                        <div class="list-group-item text-center">No hay eventos hoy
                                                        </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Puedes agregar más tarjetas acá -->
                                    </div> <!-- Fin row de tarjetas -->
                                </div>
                            </div>
                        </div>

                        <!-- Gráfico de Eventos (col-lg-8) -->
                        <div class="col-lg-8 col-sm-12">
                            <div class="card shadow mb-4 h-100">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Gráfico de Eventos</h6>
                                </div>
                                <div class="card-body">
                                    <canvas id="graficoEventos" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Tabla de eventos -->
                    <div class="row">
                        <div class="col-lg-12 col-sm-12">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">Lista de Eventos</h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="eventosTable" class="table table-striped">
                                            <thead class="thead-dark">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Título</th>
                                                    <th>Descripción</th>
                                                    <th>Fecha de Inicio</th>
                                                    <th>Hora del Evento</th>
                                                    <th>Días restantes</th>
                                                    <th>Encargado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                $contador = 1;
                                                if (!empty($eventos)) :
                                                    foreach ($eventos as $evento) :
                                                        $fechaEvento    = new DateTime($evento['start']);
                                                        $fechaHoy       = new DateTime('today');
                                                        $interval       = $fechaHoy->diff($fechaEvento);
                                                        $dias = $interval->days;
                                                        if ($interval->invert == 1) {
                                                            $diasFaltantes = "Evento vencido";
                                                        } else {
                                                            if ($dias === 0) {
                                                                $diasFaltantes = "ES HOY";
                                                            } elseif ($dias === 1) {
                                                                $diasFaltantes = "Es para mañana";
                                                            } else {
                                                                $diasFaltantes = $dias . " días";
                                                            }
                                                        }
                                                        $claseFila = ($diasFaltantes === 'ES HOY') ? '#ffc107' : '';
                                                ?>
                                                <tr class="<?= $claseFila ?>"
                                                    style="background-color: <?= $diasFaltantes == 'ES HOY' ? '#ffc107' : '' ?>;">
                                                    <td class="<?= $claseFila ?>"
                                                        style="background-color: <?= $diasFaltantes == 'ES HOY' ? '#ffc107' : '' ?>;">
                                                        <?= $contador++; ?>
                                                    </td>
                                                    <td class="<?= $claseFila ?>"
                                                        style="background-color: <?= $diasFaltantes == 'ES HOY' ? '#ffc107' : '' ?>;">
                                                        <?= htmlspecialchars($evento['title']) ?>
                                                    </td>
                                                    <td class="<?= $claseFila ?>"
                                                        style="background-color: <?= $diasFaltantes == 'ES HOY' ? '#ffc107' : '' ?>;">
                                                        <?= htmlspecialchars($evento['descripcion']) ?>
                                                    </td>
                                                    <td class="<?= $claseFila ?>"
                                                        style="background-color: <?= $diasFaltantes == 'ES HOY' ? '#ffc107' : '' ?>;">
                                                        <?= htmlspecialchars($evento['start']) ?>
                                                    </td>
                                                    <td class="<?= $claseFila ?>"
                                                        style="background-color: <?= $diasFaltantes == 'ES HOY' ? '#ffc107' : '' ?>;">
                                                        <?= htmlspecialchars($evento['horaEvento']) ?>
                                                    </td>
                                                    <td class="<?= $claseFila ?>"
                                                        style="background-color: <?= $diasFaltantes == 'ES HOY' ? '#ffc107' : '' ?>;">
                                                        <?= $diasFaltantes ?>
                                                    </td>
                                                    <td class="<?= $claseFila ?>"
                                                        style="background-color: <?= $diasFaltantes == 'ES HOY' ? '#ffc107' : '' ?>;">
                                                        <?= htmlspecialchars($evento['personaResponsable']) ?>
                                                    </td>
                                                    <td class="<?= $claseFila ?>"
                                                        style="background-color: <?= $diasFaltantes == 'ES HOY' ? '#ffc107' : '' ?>;">
                                                        <a href="#" class="btn btn-secondary"
                                                            onclick="cerrarEvento(<?= $evento['id'] ?>)">
                                                            <i class="fas fa-door-closed text-white-50"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-info"
                                                            onclick="mostrarCronologia(<?= $evento['id'] ?>)">
                                                            <i class="fas fa-info-circle" style="color:black"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                                <?php 
                                                    endforeach;
                                                else:
                                                ?>
                                                <tr>
                                                    <td colspan="8" class="text-center">No se encontraron eventos</td>
                                                </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Fin filas -->
                </div> <!-- cierre container-fluid -->
            </div> <!-- cierre #content -->

            <?php $funciones->footer(); ?>
        </div> <!-- cierre #content-wrapper -->
    </div> <!-- cierre #wrapper -->

    <!-- Scroll to Top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <?php include("modal_salir.php"); ?>
    <?php $funciones->script(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#eventosTable').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json"
            }
        });
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    // Obtener la fecha actual y el mes en curso
    const currentDate = new Date();
    const monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre",
        "Octubre", "Noviembre", "Diciembre"
    ];
    const currentMonthName = monthNames[currentDate.getMonth()];
    const currentYear = currentDate.getFullYear();

    // Filtrar los eventos del mes en curso
    const eventsThisMonth = eventos.filter(evento => {
        let d = new Date(evento.start);
        return d.getMonth() === currentDate.getMonth() && d.getFullYear() === currentYear;
    });

    // Agrupar los eventos por semana del mes
    // Se calcula la semana ordinal en el mes: (día - 1) / 7 + 1
    const weeklyCounts = {};
    eventsThisMonth.forEach(evento => {
        let d = new Date(evento.start);
        let weekOrdinal = Math.floor((d.getDate() - 1) / 7) + 1;
        weeklyCounts[weekOrdinal] = (weeklyCounts[weekOrdinal] || 0) + 1;
    });

    // Siempre mostrar 4 semanas (1ra, 2da, 3ra y 4ta)
    const totalWeeks = 4;
    const weeksOrdinals = [1, 2, 3, 4];
    const ordinalLabels = weeksOrdinals.map(week => {
        if (week === 1) return "1ra";
        else if (week === 2) return "2da";
        else if (week === 3) return "3er";
        else return "4ta";
    });
    const eventsPerWeek = weeksOrdinals.map(week => weeklyCounts[week] || 0);

    // Crear el gráfico de barras con el eje Y para las semanas y eje X para la cantidad de eventos
    new Chart(document.getElementById("graficoEventos"), {
        type: 'bar',
        data: {
            labels: ordinalLabels, // ["1ra", "2da", "3er", "4ta"]
            datasets: [{
                label: 'Cantidad de eventos',
                data: eventsPerWeek,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2
            }]
        },
        options: {
            indexAxis: 'y', // eje Y: semanas; eje X: cantidad de eventos
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Ticket del mes: ' + currentMonthName
                }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'Semana'
                    }
                },
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Cantidad de eventos'
                    },
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
    </script>
</body>

</html>
