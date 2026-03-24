<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';

$funciones = new Funciones();  // Instancia de la clase Funciones
$idUsuarioSession = htmlspecialchars($_SESSION['id']);

// IDs de administradores
$nombre = htmlspecialchars($_SESSION['nombre']) . ' ' . htmlspecialchars($_SESSION['apellido_paterno']);
$AreaTrabajo = htmlspecialchars($_SESSION['id_area_trabajo']);
$idPagActual = "11";

// Obtener la cantidad de tickets por estado en una variable separada
$datosEstados = $funciones->contarTicketsPorEstado();
$datosCategorias = json_decode($funciones->contarTicketsPorCategoria(), true);
$datosPorcentajes = json_decode($funciones->obtenerPorcentajeEstadosPorCategoria(), true);
$promediosEstados = json_decode($funciones->obtenerPromedioTiempoEstados(), true);
$usuarios = $funciones->obtenerEstadisticasUsuarios();
// Obtener los datos para el gráfico de colegios
$datosGraficoColegios = $funciones->obtenerDatosGraficoColegios();
$datosGraficoColegios2 = $funciones->obtenerDatosGraficoColegios2();
// Obtener el usuario filtrado desde GET
$idUsuarioFiltro = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : null;
?>

<script>
var idUsuarioSession = <?php echo json_encode($idUsuarioSession); ?>;
var datosGraficoColegios = <?php echo $datosGraficoColegios; ?>;
var datosGraficoColegios2 = <?php echo $datosGraficoColegios2; ?>;
</script>

<!DOCTYPE html>
<html lang="en">
<style>
canvas#graficoTorta {
    margin: auto;
    display: block;
    max-width: 100%;
    max-height: 100%;
}

.progress-bar {
    transition: width 1s ease-in-out;
}
</style>
<style>
.custom-card {
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    overflow: hidden;
    background: #fff;
}

.titulo-seccion {
    margin: 0;
    font-weight: bold;
    color: #1b6ec2;
}

.usuario-box {
    border-bottom: 1px solid #eee;
    padding: 10px 0;
}

.usuario-cabecera {
    display: flex;
    justify-content: space-between;
    cursor: pointer;
    font-weight: 600;
    padding: 10px;
    transition: background-color 0.3s ease;
}

.usuario-cabecera:hover {
    background-color: #f0f8ff;
}

.estado {
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 0.85rem;
    font-weight: bold;
}

.estado.activo {
    background-color: #28a745;
    color: white;
}

.estado.inactivo {
    background-color: #6c757d;
    color: white;
}

.usuario-detalle {
    display: none;
    padding: 10px 15px;
    animation: fadeIn 0.3s ease-in-out;
}

.tabla-entradas {
    margin-bottom: 15px;
}

.tabla-entradas .tabla-titulo {
    font-weight: bold;
    margin-bottom: 5px;
}

.tabla-entradas table {
    width: 100%;
    border-collapse: collapse;
}

.tabla-entradas th,
.tabla-entradas td {
    border: 1px solid #ccc;
    padding: 8px;
    text-align: center;
}

.resumen-estadisticas {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    justify-content: space-between;
}

.estadistica {
    flex: 1;
    padding: 10px;
    border-radius: 8px;
    color: #fff;
    font-weight: bold;
    text-align: center;
    min-width: 180px;
}

.verde {
    background-color: #28a745;
}

.amarillo {
    background-color: #ffc107;
    color: #000;
}

.azul {
    background-color: #007bff;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }

    to {
        opacity: 1;
    }
}
</style>


<head>
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

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
                    <div class="card shadow mb-4">
                        <!-- Título principal -->
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">GRÁFICOS DE TICKETS</h6>
                        </div>

                        <div class="card-body">
                            <!-- 🔹 PESTAÑAS -->
                            <ul class="nav nav-tabs" id="graficoTicketsTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="contenedorGraficos-tab" data-bs-toggle="tab"
                                        href="#contenedorGraficos" role="tab">Gráficos</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="contenedorEstados-tab" data-bs-toggle="tab"
                                        href="#contenedorEstados" role="tab">Area de Trabajo</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="contenedorCategorias-tab" data-bs-toggle="tab"
                                        href="#contenedorCategorias" role="tab">Categoría</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="contenedorColegios-tab" data-bs-toggle="tab"
                                        href="#contenedorColegios" role="tab">Colegios</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="contadorUsuario-tab" data-bs-toggle="tab"
                                        href="#contadorUsuario" role="tab">Usuario</a>
                                </li>
                            </ul>

                            <!-- 🔹 CONTENIDO DE LAS PESTAÑAS -->
                            <div class="tab-content mt-3" id="graficoTicketsContent">
                                <!-- 📌 Pestaña: GRÁFICOS -->
                                <div class="tab-pane fade show active" id="contenedorGraficos" role="tabpanel">
                                    <div class="row">
                                        <!-- Gráfico por Estados -->
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <div class="card shadow px-0">
                                                <div class="card-header py-3">
                                                    <h6 class="m-0 font-weight-bold text-primary">Gráfico Técnico</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <!-- Leyenda -->
                                                        <div class="col-md-4">
                                                            <ul class="list-group" id="legendList">

                                                            </ul>
                                                        </div>
                                                        <!-- Gráfico -->
                                                        <div class="col-md-8">
                                                            <canvas id="graficoTorta"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <!-- Gráfico por Categorías -->
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <div class="card shadow px-0">
                                                <div class="card-header py-3">
                                                    <h6 class="m-0 font-weight-bold text-primary">Gráfico Categorías
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <div style="height: 300px;">
                                                        <canvas id="graficoTicketsPorCategoria"></canvas>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- 📌 Pestaña: Estados de Ticket -->
                                <div class="tab-pane fade" id="contenedorEstados" role="tabpanel">
                                    <div class="row contenedor-tickets" id="idContenedoresEstadosTicket">
                                        <?php $funciones->contenedorPorAreaTrabajo(); ?>
                                    </div>
                                </div>

                                <!-- 📌 Pestaña: Por Categoría -->
                                <div class="tab-pane fade" id="contenedorCategorias" role="tabpanel">
                                    <div class="row" id="idContenedoresCategoriasTickets">
                                        <?php $funciones->obtenerTicketsPorCategoria(); ?>
                                    </div>
                                </div>

                                <!-- 📌 Pestaña: Colegios -->
                                <div class="tab-pane fade" id="contenedorColegios" role="tabpanel">
                                    <div class="row">
                                        <?php $funciones->obtenerUsuariosColegios(); ?>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <div class="card shadow px-0">
                                                <div class="card-header py-3">
                                                    <h6 class="m-0 font-weight-bold text-primary">Gráfico Colegios 1
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="graficoColegios1"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-sm-12 mb-4">
                                            <div class="card shadow px-0">
                                                <div class="card-header py-3">
                                                    <h6 class="m-0 font-weight-bold text-primary">Gráfico Colegios 2
                                                    </h6>
                                                </div>
                                                <div class="card-body">
                                                    <canvas id="graficoColegios2"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 📌 Pestaña: Usuario -->
                                <div class="tab-pane fade show active" id="contadorUsuario" role="tabpanel">
                                    <div class="row">
                                        <div class="col-lg-12 col-sm-12 mb-4">
                                            <div class="card shadow px-0">
                                                <div class="card-header py-3">
                                                    <h6 class="m-0 font-weight-bold text-primary">Usuarios</h6>
                                                </div>
                                                <div class="card-body">
                                                    <div class="listado-usuarios">

                                                        <?php
                                                            $usuarios = [
                                                                [
                                                                    'id' => 1,
                                                                    'nombre_completo_usuario' => 'Alejandro Rojas Schweitzer',
                                                                    'activo' => true,
                                                                    'tickets_terminados' => 7,
                                                                    'tickets_ingresados' => 12,
                                                                    'mensajes' => 34,
                                                                    'ultimas_entradas' => [
                                                                        ['id' => 1, 'fecha' => '2025-04-09', 'hora' => '08:12:34', 'ip' => '190.113.208.50'],
                                                                        ['id' => 2, 'fecha' => '2025-04-09', 'hora' => '10:30:10', 'ip' => '201.221.128.90'],
                                                                        ['id' => 3, 'fecha' => '2025-04-08', 'hora' => '09:00:55', 'ip' => '179.56.80.23'],
                                                                        ['id' => 4, 'fecha' => '2025-04-07', 'hora' => '13:45:20', 'ip' => '152.173.215.10'],
                                                                        ['id' => 5, 'fecha' => '2025-04-06', 'hora' => '07:25:00', 'ip' => '138.117.200.1'],
                                                                    ]
                                                                    ],
                                                                [
                                                                    'id' => 12,
                                                                    'nombre_completo_usuario' => 'Alejandro Rojas Schweitzer',
                                                                    'activo' => true,
                                                                    'tickets_terminados' => 7,
                                                                    'tickets_ingresados' => 12,
                                                                    'mensajes' => 34,
                                                                    'ultimas_entradas' => [
                                                                        ['id' => 1, 'fecha' => '2025-04-09', 'hora' => '08:12:34', 'ip' => '190.113.208.50'],
                                                                        ['id' => 2, 'fecha' => '2025-04-09', 'hora' => '10:30:10', 'ip' => '201.221.128.90'],
                                                                        ['id' => 3, 'fecha' => '2025-04-08', 'hora' => '09:00:55', 'ip' => '179.56.80.23'],
                                                                        ['id' => 4, 'fecha' => '2025-04-07', 'hora' => '13:45:20', 'ip' => '152.173.215.10'],
                                                                        ['id' => 5, 'fecha' => '2025-04-06', 'hora' => '07:25:00', 'ip' => '138.117.200.1'],
                                                                    ]
                                                                ]
                                                            ];
                                                            $usuarios = [
                                                                [
                                                                    'id' => 2,
                                                                    'nombre_completo_usuario' => 'Aleeejandro Rojas Schweitzer',
                                                                    'activo' => true,
                                                                    'tickets_terminados' => 7,
                                                                    'tickets_ingresados' => 12,
                                                                    'mensajes' => 34,
                                                                    'ultimas_entradas' => [
                                                                        ['id' => 1, 'fecha' => '2025-04-09', 'hora' => '08:12:34', 'ip' => '190.113.208.50'],
                                                                        ['id' => 2, 'fecha' => '2025-04-09', 'hora' => '10:30:10', 'ip' => '201.221.128.90'],
                                                                        ['id' => 3, 'fecha' => '2025-04-08', 'hora' => '09:00:55', 'ip' => '179.56.80.23'],
                                                                        ['id' => 4, 'fecha' => '2025-04-07', 'hora' => '13:45:20', 'ip' => '152.173.215.10'],
                                                                        ['id' => 5, 'fecha' => '2025-04-06', 'hora' => '07:25:00', 'ip' => '138.117.200.1'],
                                                                    ]
                                                                ]
                                                            ];
                                                            ?>

                                                        <?php foreach ($usuarios as $index => $usuario) : ?>
                                                        <div class="usuario-item mb-3 border rounded p-2">
                                                            <!-- Cabecera clickeable -->
                                                            <div class="usuario-titulo fw-bold d-flex justify-content-between text-primary"
                                                                style="cursor:pointer"
                                                                onclick="toggleTabla('usuario<?= $index ?>')">
                                                                <span><?= htmlspecialchars($usuario['nombre_completo_usuario']) ?></span>
                                                                <span
                                                                    class="badge <?= $usuario['activo'] ? 'bg-success' : 'bg-secondary' ?>">
                                                                    <?= $usuario['activo'] ? 'Activo' : 'Inactivo' ?>
                                                                </span>
                                                            </div>

                                                            <!-- Contenido oculto -->
                                                            <div id="usuario<?= $index ?>" class="usuario-detalle mt-3"
                                                                style="display:none;">

                                                                <div class="table-responsive mb-3">
                                                                    <h6 class="mb-2">Últimas Entradas
                                                                        (<?= count($usuario['ultimas_entradas']) ?>)
                                                                    </h6>
                                                                    <table class="table table-bordered text-center">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th>ID</th>
                                                                                <th>Fecha</th>
                                                                                <th>Hora</th>
                                                                                <th>IP (ubicación)</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php foreach ($usuario['ultimas_entradas'] as $entrada): ?>
                                                                            <tr>
                                                                                <td><?= $entrada['id'] ?></td>
                                                                                <td><?= $entrada['fecha'] ?></td>
                                                                                <td><?= $entrada['hora'] ?></td>
                                                                                <td>
                                                                                    <a href="https://www.iplocation.net/ip-lookup?query=<?= $entrada['ip'] ?>"
                                                                                        target="_blank">
                                                                                        <?= $entrada['ip'] ?>
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                            <?php endforeach; ?>
                                                                        </tbody>
                                                                    </table>
                                                                </div>

                                                                <!-- Estadísticas -->
                                                                <div class="row text-center">
                                                                    <div class="col-md-4 mb-2">
                                                                        <div
                                                                            class="p-2 bg-success text-white fw-bold rounded">
                                                                            ✅ Tickets Terminados:
                                                                            <?= $usuario['tickets_terminados'] ?></div>
                                                                    </div>
                                                                    <div class="col-md-4 mb-2">
                                                                        <div
                                                                            class="p-2 bg-warning text-dark fw-bold rounded">
                                                                            📥 Tickets Ingresados:
                                                                            <?= $usuario['tickets_ingresados'] ?></div>
                                                                    </div>
                                                                    <div class="col-md-4 mb-2">
                                                                        <div
                                                                            class="p-2 bg-primary text-white fw-bold rounded">
                                                                            💬 Mensajes: <?= $usuario['mensajes'] ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <?php endforeach; ?>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                            </div> <!-- fin tab-content -->


                        </div> <!-- fin card-body -->
                    </div> <!-- fin card -->

                    <!-- 🔻 CONTENEDOR DE LISTADO FINAL -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Listado de Tickets</h6>
                        </div>
                        <div class="card-body">
                            <?php echo $funciones->generarContenedorListados($datosPorcentajes); ?>
                        </div>
                    </div>
                </div>














            </div>
        </div>
    </div>









    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <?php $funciones->script(); ?>

    <script>
    function toggleTabla(id) {
        const tabla = document.getElementById(id);
        tabla.style.display = (tabla.style.display === 'none' || tabla.style.display === '') ? 'block' : 'none';
    }
    </script>
    <script>
    function toggleTabla(id) {
        const tabla = document.getElementById(id);
        tabla.style.display = (tabla.style.display === 'none' || tabla.style.display === '') ? 'block' : 'none';
    }
    </script>
    <script>
    function toggleDetalle(id) {
        const div = document.getElementById(id);
        div.style.display = (div.style.display === 'none' || div.style.display === '') ? 'block' : 'none';
    }
    </script>

    <script>
    function toggleTabla(id) {
        const tablaContainer = document.getElementById(id);
        const tabla = tablaContainer.querySelector('.tabla-dinamica');

        if (tablaContainer.style.display === 'none' || tablaContainer.style.display === '') {
            tablaContainer.style.display = 'block';
            if (tabla && !$(tabla).hasClass('dataTable')) {
                $(tabla).DataTable({
                    paging: false,
                    searching: false,
                    info: false
                });
            }
        } else {
            tablaContainer.style.display = 'none';
        }
    }
    </script>


    <!-- /// GRAFICO TORTA  ESTADOS -->

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Obtener el contexto del canvas
        let ctx1 = document.getElementById("graficoColegios1").getContext("2d");

        // Procesar los datos para el gráfico
        let usuarios = Object.keys(datosGraficoColegios);
        let estados = ["Recibido", "Asignado", "En proceso", "Terminado", "Borrador",
            "Cerrado"
        ];
        let colores = ["#D7DBDD", "#F7DC6F", "#F0B27A", "#82E0AA", "#85929E", "#85929E"];

        let datasets = estados.map((estado, index) => {
            return {
                label: estado,
                data: usuarios.map(usuario => datosGraficoColegios[usuario][index +
                    1
                ] || 0),
                backgroundColor: colores[index],
                borderColor: colores[index],
                borderWidth: 1
            };
        });

        // Crear el gráfico de barras
        let graficoColegios1 = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: usuarios,
                datasets: datasets
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 // Asegurar que solo se muestren números enteros
                        }
                    }
                }
            }
        });
    });
    </script>

    <!-- /// GRAFICO BARRAAS CATEGORIAS  -->

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Obtener el contexto del canvas
        let ctx2 = document.getElementById("graficoColegios2").getContext("2d");

        // Procesar los datos para el gráfico
        let meses = Object.keys(datosGraficoColegios2);
        let estados = ["Recibido", "Asignado", "En Proceso", "Terminado", "Cerrado"];
        let colores = ["#D7DBDD", "#F7DC6F", "#82E0AA", "", "#85929E"];

        let datasets = estados.map((estado, index) => {
            return {
                label: estado,
                data: meses.map(mes => datosGraficoColegios2[mes][estado] || 0),
                backgroundColor: colores[index],
                borderColor: colores[index],
                borderWidth: 1
            };
        });

        // Crear el gráfico de barras apiladas
        let graficoColegios2 = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: datasets
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1 // Asegurar que solo se muestren números enteros
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    title: {
                        display: true,
                        text: 'Distribución de Tickets por Mes y Estado'
                    }
                }
            }
        });
    });
    </script>

    <!-- /// FUNCION PARA MOSTRAT LA CRONOLOGIA DEL TICKET  -->
    <script>
    function mostrarPromedioEstados() {
        let promedios = <?php echo json_encode($promediosEstados); ?>;

        let contenido = `
        <h3 class="text-center">Cronología del Ticket</h3>
        <div style="border-top: 4px solid red; width: 100%; margin-bottom: 15px;"></div>
        <div class="d-flex justify-content-between align-items-center text-center">
            <div>
                <div style="width: 15px; height: 15px; background-color: red; border-radius: 50%; margin: auto;"></div>
                <p>Creado</p>
                <small>${promedios.recibido_a_asignado} hrs</small>
            </div>
            <div>
                <div style="width: 15px; height: 15px; background-color: red; border-radius: 50%; margin: auto;"></div>
                <p>Asignado</p>
                <small>${promedios.asignado_a_en_proceso} hrs</small>
            </div>
            <div>
                <div style="width: 15px; height: 15px; background-color: red; border-radius: 50%; margin: auto;"></div>
                <p>En proceso</p>
                <small>${promedios.en_proceso_a_terminado} hrs</small>
            </div>
            <div>
                <div style="width: 15px; height: 15px; background-color: red; border-radius: 50%; margin: auto;"></div>
                <p>Terminado</p>
                <small>${promedios.terminado_a_cerrado} hrs</small>
            </div>
            <div>
                <div style="width: 15px; height: 15px; background-color: red; border-radius: 50%; margin: auto;"></div>
                <p>Cerrado</p>
            </div>
        </div>
        `;

        Swal.fire({
            html: contenido,
            showConfirmButton: false,
            showCloseButton: true,
            width: '600px',
            customClass: {
                popup: 'cuerpo_modal_guardar',
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Obtener datos desde PHP
        let datos = <?php echo $funciones->contarTicketsPorEstado(); ?>;

        let ctx = document.getElementById("graficoTorta").getContext("2d");

        let colores = ["#4e73df", "#1cc88a", "#36b9cc", "#f6c23e", "#e74a3b", "#858796",
            "#2e59d9"
        ];

        let myPieChart = new Chart(ctx, {
            type: "pie",
            data: {
                labels: datos.estados,
                datasets: [{
                    data: datos.cantidades,
                    backgroundColor: colores,
                    hoverBackgroundColor: colores.map(c => c + "B3"),
                    hoverBorderColor: "#fff"
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false // Oculta la leyenda por defecto
                    },
                    title: {
                        display: true,
                        text: "Distribución de Tickets por Estado"
                    }
                }
            }
        });

        // Generar la leyenda manualmente
        let legendList = document.getElementById("legendList");
        datos.estados.forEach((estado, index) => {
            let listItem = document.createElement("li");
            listItem.className =
                "list-group-item d-flex justify-content-between align-items-center";
            listItem.innerHTML = `
                        <span>
                            <span style="background-color: ${colores[index]}; width: 12px; height: 12px; display: inline-block; border-radius: 50%; margin-right: 8px;"></span>
                            ${estado}
                        </span>
                           <div class="d-flex align-items-center">
                    
                        <span class="badge-danger badge-pill">${datos.cantidades[index]} </span>
                        </div>
                    `;
            legendList.appendChild(listItem);
        });
    });
    </script>

    <!--PESTAÑA 1 GRAFICOS 2 -->
    <script>
    const response = <?php echo $funciones->contarTicketsPorCategoria(); ?>;

    const datosCategorias = response.datos;
    const coloresPorEstado = response.colores;

    const categorias = Object.keys(datosCategorias);
    const estados = Object.keys(datosCategorias[categorias[0]]);

    const datasets = estados.map(estado => ({
        label: estado,
        data: categorias.map(categoria => datosCategorias[categoria][estado]),
        backgroundColor: coloresPorEstado[estado] || "#ccc"
    }));

    const ctx = document.getElementById('graficoTicketsPorCategoria').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: categorias,
            datasets: datasets
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1, // Paso de 1 en 1
                        precision: 0, // Sin decimales
                        callback: function(value) {
                            return Number.isInteger(value) ? value : null;
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top'
                }
            }
        }
    });
    </script>







    <script>
    document.addEventListener("DOMContentLoaded", function() {
        obtenerDatos(true); // Cargar el gráfico general por defecto

        document.getElementById("filtroUsuario").addEventListener("change", function() {
            let idUsuario = this.value;
            obtenerDatos(false, idUsuario);
        });
    });

    function obtenerDatos(esGraficoGeneral, idUsuario = null) {
        let url = "api_tickets.php";
        if (idUsuario) url += "?id_usuario=" + idUsuario;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                actualizarGrafico(data, esGraficoGeneral);
            })
            .catch(error => console.error("Error al obtener datos:", error));
    }

    function actualizarGrafico(data, esGraficoGeneral = false) {
        if (Object.keys(data).length === 0) {
            document.querySelector("#barChart").innerHTML =
                "<p class='text-center'>No hay tickets disponibles.</p>";
            return;
        }

        let categorias = [];
        let seriesData = [];

        if (esGraficoGeneral) {
            // 📌 Para el gráfico por defecto (Todos los técnicos)
            let tecnicos = Object.keys(data); // Nombres de los técnicos
            let estados = Object.keys(data[tecnicos[0]]); // Todos los estados
            seriesData = estados.map(estado => ({
                name: estado,
                data: tecnicos.map(tecnico => data[tecnico][
                    estado
                ]) // Cantidad de tickets por estado para cada técnico
            }));
            categorias = tecnicos; // Los técnicos en el eje Y
        } else {
            // 📌 Para el gráfico filtrado (Un técnico específico)
            let tecnico = Object.keys(data)[0]; // Solo un técnico
            let estados = Object.keys(data[tecnico]);
            seriesData = [{
                name: tecnico,
                data: estados.map(estado => data[tecnico][
                    estado
                ]) // Cantidad de tickets por estado
            }];
            categorias = estados; // Los estados en el eje X
        }

        document.querySelector("#barChart").innerHTML = ""; // Limpiar gráfico anterior

        new ApexCharts(document.querySelector("#barChart"), {
            series: seriesData,
            chart: {
                type: 'bar',
                height: categorias.length * 40,
                stacked: true,
                toolbar: {
                    show: false
                },
            },
            plotOptions: {
                bar: {
                    horizontal: true, // 📌 Cambia a horizontal para que los nombres de los técnicos estén en el eje Y
                    dataLabels: {
                        position: "center"
                    }
                }
            },
            xaxis: {
                categories: categorias, // Técnicos o estados según el tipo de gráfico
                title: {
                    text: esGraficoGeneral ? "Cantidad de Tickets" : "Estados del Ticket"
                }
            },
            yaxis: {
                title: {
                    text: esGraficoGeneral ? "Técnicos" : "Cantidad de Tickets"
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " tickets";
                    }
                }
            }
        }).render();
    }
    </script>



</body>

</html>
