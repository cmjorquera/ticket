<?php  
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';
$funciones = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$nombresession = htmlspecialchars($_SESSION['nombre']) . '--' . htmlspecialchars($_SESSION['apellido_paterno']);
$idAreaTrabajo = htmlspecialchars($_SESSION['id_area_trabajo']);
$idPagActual = 7;  // PAGINA DEL USUARIO CON PROBLEMAS


?>
<script>
    var nombresession       = "<?php echo $nombresession; ?>";
    var idUsuarioSession    =   "<?php echo $idUsuarioSession; ?>";
</script>


<!DOCTYPE html>
<html lang="en">


<head>
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- <link href="css/estilo.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">-->
    <link href="css/graficos.css" rel="stylesheet">
</head>
<style>
       .active-menu-item {
        background-color: #d1e7fd; /* Fondo más claro para el menú activo */
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2); /* Sombra para el efecto 3D */
        transform: translateY(-2px); /* Levantar ligeramente */
        border-radius: 5px;
        

    z-index: 1000; /* Asegura que se vea por encima de otros elementos */
    position: relative; /* Necesario para que z-index funcione */

        
    }
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
    <?php  $funciones->menuLateral2($idUsuarioSession,$idPagActual);   ?>
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
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold text-primary">
                                INFORMACIÓN RELEVANTE
                            </h6>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <section class="section dashboard">
                                    <div class="row">
                                        <div class="col-lg-8">
                                            <div class="row">
                                                <!-- RECTANGULAR-->
                                                <div class="col-xxl-4 col-xl-12">
                                                    <div class="card info-card customers-card">
                                                        <div class="filter">
                                                        <?php
                                                            // Asume que ya incluiste la conexión
                                                            $db = new MySQL('', '', '');
                                                            $sql = "SELECT id, nombre FROM estados_ticket";
                                                            $resultEstados = $db->consulta($sql);
                                                            ?>
                                                            <a class="icon" href="#" data-bs-toggle="dropdown">
                                                                <i class="bi bi-three-dots"></i>
                                                            </a>
                                                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                                                <li class="dropdown-header text-start">
                                                                    <h6>Filtros</h6>
                                                                </li>
                                                                <?php while($area = $db->fetch_array($resultEstados)): ?>
                                                                    <li><a class="dropdown-item" href="#" data-estado="<?= $area['id'] ?>"><?= htmlspecialchars($area['nombre']) ?></a></li>
                                                                <?php endwhile; ?>

                                                            </ul>
                                                        </div>


                                                        <div class="card-body">
                                                            <h5 class="card-title">Ticket <span>| </span></h5>

                                                            <div class="d-flex align-items-center">
                                                                <div
                                                                    class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                                    <i class="bi bi-ticket-perforated"></i>
                                                                </div>
                                                                <div class="ps-3">
                                                                    <h6>terminados</h6>
                                                                    <span class="text-danger small pt-1 fw-bold"></span>
                                                                    <span class="text-muted small pt-2 ps-1"></span>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- GRÁFICO -->
                                                <div class="col-12">
                                                    <div class="card">
                                                        <div class="card-body">
                                                            <h5 class="card-title">Gráfico de Tickets</h5>
                                                            <!-- Doughnut Chart -->
                                                            <canvas id="doughnutChart"
                                                                style="max-height: 400px; display: block; box-sizing: border-box; height: 592px; width: 592px;"
                                                                width="592" height="400"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!--CONTENEDOR DE LA DERECHA -->
                                        <div class="col-lg-4">
                                            <div class="card">
                                                <?php
                                                // Asume que ya incluiste la conexión
                                                $db = new MySQL('', '', '');
                                                $queryAreas = "SELECT id_area, nombre_area FROM area_trabajo";
                                                $resultAreas = $db->consulta($queryAreas);
                                                ?>
                                                <div class="filter">
                                                    <a class="icon" href="#" data-bs-toggle="dropdown"><i
                                                            class="bi bi-three-dots"></i></a>
                                                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                                        <li class="dropdown-header text-start">
                                                            <h6>Filtros</h6>
                                                        </li>
                                                        <?php while($area = $db->fetch_array($resultAreas)): ?>
                                                        <li><a class="dropdown-item" href="#"
                                                                data-area="<?= $area['id_area'] ?>"><?= htmlspecialchars($area['nombre_area']) ?></a>
                                                        </li>
                                                        <?php endwhile; ?>
                                                    </ul>
                                                </div>


                                                <div class="card-body">
                                                    <h5 class="card-title">categorias <span>|</span></h5>
                                                    <div class="activity">
                                                        <!-- Bar Chart -->
                                                        <div id="barChart" style="height: 400px;">


                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <?php include("modal_salir.php"); ?>
    <?php $funciones->script(); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script>


    <!--GRAFICOD E LAS CATEGORIAS-->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const updateChart = (areaId = '') => {
                fetch(`modelos/filtros/filtro_categoria.php${areaId ? '?area=' + areaId : ''}`)
                    .then(response => response.json())
                    .then(data => {
                        apexChart.updateOptions({
                            series: [{
                                name: 'Cantidad de Tickets',
                                data: data.data
                            }],
                            xaxis: {
                                categories: data.categories,
                            }
                        });
                    })
                    .catch(error => console.error('Error al cargar datos de categorías: ', error));
            };

            const apexChart = new ApexCharts(document.querySelector("#barChart"), {
                series: [{
                    data: []
                }],
                chart: {
                    type: 'bar',
                    height: 350
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: [],
                }
            });

            apexChart.render();
            updateChart(); // Llama a updateChart sin argumentos para cargar todos los datos inicialmente

            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const areaId = this.getAttribute('data-area');
                    updateChart(areaId);
                });
            });
        });

    </script>

    <!--GRAFICOD E LAS TICKET-->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('doughnutChart');
            const doughnutChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Recibidos', 'Asignados', 'En Proceso', 'Terminados'],
                    datasets: [{
                        data: [],
                        backgroundColor: [
                            'rgb(255, 99, 132)',
                            'rgb(54, 162, 235)',
                            'rgb(255, 205, 86)',
                            'rgb(75, 192, 192)'
                        ],
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    }
                }
            });

            // Carga inicial de datos
            fetch('modelos/filtros/filtros_ticket.php')
                .then(response => response.json())
                .then(data => {
                    doughnutChart.data.datasets[0].data = data;
                    doughnutChart.update();
                })
                .catch(error => console.error('Error al cargar los datos iniciales: ', error));

            // Actualización de datos según filtro
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const estado = this.getAttribute('data-estado');

                    fetch(`modelos/filtros/filtros_ticket.php?estado=${estado}`)
                        .then(response => response.json())
                        .then(data => {
                            if (!data.error) {
                                document.querySelector('.ps-3 h6').textContent = data.total;
                                document.querySelector('.text-danger small').textContent = data
                                    .porcentaje + '%';
                                document.querySelector('.text-muted small').textContent = data
                                    .estadoTexto;

                                // Opcional: actualizar el gráfico si necesario
                                // doughnutChart.data.datasets[0].data = [data.total];
                                // doughnutChart.update();
                            } else {
                                console.error(data.error);
                            }
                        })
                        .catch(error => console.error('Error al actualizar los datos: ', error));
                });
            });
        });
    </script>


</body>

</html>
