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
$datosEstados       = $funciones->contarTicketsPorEstado();
$datosCategorias    = json_decode($funciones->contarTicketsPorCategoria(), true);
$datosPorcentajes   = json_decode($funciones->obtenerPorcentajeEstadosPorCategoria(), true);
$promediosEstados   = json_decode($funciones->obtenerPromedioTiempoEstados(), true);
$usuarios = $funciones->obtenerEstadisticasUsuarios();
// Obtener los datos para el gr��fico de colegios
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>
<style>
  .resumen-card {
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
  }

  .card h6 {
    font-size: 14px;
    font-weight: 600;
  }

  .card canvas {
    max-height: 200px;
  }

  .border-start {
    border-left-width: 4px !important;
  }
  
  /*grafico 1*/
  #graficoEstados {
    max-width: 100%;
    max-height: 100%;
  }

</style>


<body id="page-top">
    <input type="hidden" id="idUsuario" value="<?php echo $idUsuarioSession; ?>" />
    <div id="wrapper">
        <?php $funciones->menuLateral2($idUsuarioSession, $idPagActual); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>
                
                       <!--****************BUTTON *****************-->
                <div id="botonesDescargar">
                    <div class="dropdown" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                      <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download"></i> Descargar
                      </button>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="descargarPDF()">Informe PDF</a></li>
                        <li><a class="dropdown-item" href="#" onclick="descargarExcel()">Reporte Excel</a></li>
                        <li><a class="dropdown-item" href="#" onclick="descargarImagen('graficoEstado')">Gr��fico de Estado</a></li>
                      </ul>
                    </div>
                </div>
                <div class="container-fluid">
                    <div class="card shadow mb-4">
                        <!-- T��tulo principal -->
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">GRAFICOS DE TICKETS</h6>
                        </div>
                        <div class="card-body">
                            <!-- �9�7 PESTA�0�5AS -->
                            <ul class="nav nav-tabs" id="graficoTicketsTab" role="tablist">
                                   <li class="nav-item">
                                        <a class="nav-link active" href="#">Resumen General</a>
                                    </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="contenedorGraficos-tab" data-bs-toggle="tab"
                                        href="#contenedorGraficos" role="tab">Graficos</a>
                                </li>
           
                            </ul>
                        </div> 
                        
                         <div class="tab-pane fade show active" id="tabResumen" role="tabpanel" aria-labelledby="tabResumen-tab">
                            <div class="container mt-4">
                              <!-- FILA DE M�0�7TRICAS -->
                              <div class="row g-3 mb-3">
                                <div class="col-lg-3 col-sm-6">
                                    <div class="card shadow px-0 mb-3 border-start border-danger border-4">
                                      <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-danger">Tickets Actqivos</h6>
                                      </div>
                                      <div class="card-body">
                                        <div class="d-flex align-items-center">
                                          <div class="fs-3 me-3"></div>
                                          <div>
                                            <div class="text-muted small">Actualmente Abiertos</div>
                                            <div class="fw-bold text-danger fs-5">32</div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>

                                </div>
                                <div class="col-lg-3 col-sm-6">
                                    <div class="card shadow px-0 mb-3 border-start border-primary border-4">
                                      <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Tickets Cerrados</h6>
                                      </div>
                                      <div class="card-body">
                                        <div class="d-flex align-items-center">
                                          <div class="fs-3 me-3"></div>
                                          <div>
                                            <div class="text-muted small">Actualmente Cerrados</div>
                                            <div class="fw-bold text-primary fs-5">850</div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6">
                                    <div class="card shadow px-0 mb-3 border-start border-primary border-4">
                                      <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Tickets Retrasados</h6>
                                      </div>
                                      <div class="card-body">
                                        <div class="d-flex align-items-center">
                                          <div class="fs-3 me-3"></div>
                                          <div>
                                            <div class="text-muted small">Total Retrasados</div>
                                            <div class="fw-bold text-primary fs-5">5</div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-sm-6">
                                    <div class="card shadow px-0 mb-3 border-start border-primary border-4">
                                      <div class="card-header py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Porcentaje de cumplimiento (SLA)</h6>
                                      </div>
                                      <div class="card-body">
                                        <div class="d-flex align-items-center">
                                          <div class="fs-3 me-3">4.0 Horas </div>
                                          <div>
                                            <div class="text-muted small">Total Registrados</div>
                                            <div class="fw-bold text-primary fs-5">850</div>
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                              </div>
                        <!-- GR�0�9FICOS EN 2 COLUMNAS -->
                            <div class="row g-3 mb-3">
                                <div class="col-lg-8">
                                  <div class="row g-3">
                                    <div class="col-md-6 mb-3">
                                      <div class="card shadow px-0 mb-3">
                                        
                                        <div class="card-header d-flex justify-content-between align-items-center py-3">
                                          <h6 class="m-0 font-weight-bold text-primary">Estado de Tickets</h6>
                                          <div class="dropdown">
                                            <a class="icon text-secondary" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                              <i class="bi bi-three-dots"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                              <li class="dropdown-header text-start">
                                                <h6>Filtrar por Perfil</h6>
                                              </li>
                                              <li><a class="dropdown-item" href="#" onclick="generarGraficoTicket('usuario')">Usuario</a></li>
                                              <li><a class="dropdown-item" href="#" onclick="generarGraficoTicket('tecnico')">T��cnico</a></li>
                                              <li><a class="dropdown-item" href="#" onclick="generarGraficoTicket('admin')">Administrador</a></li>
                                            </ul>
                                          </div>
                                        </div>
                                    
                                        <div class="card-body p-3">
                                          <div style="height: 300px;">
                                            <canvas id="graficoEstados"></canvas>
                                          </div>
                                        </div>
                                    
                                      </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                      <div class="card shadow px-0 mb-3">
                                        <div class="card-header d-flex justify-content-between align-items-center py-3">
                                          <h6 class="m-0 font-weight-bold text-primary">Estado de Tickrwerwerets</h6>
                                          <div class="dropdown">
                                            <a class="icon text-secondary" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                              <i class="bi bi-three-dots"></i>
                                            </a>
                                            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                              <li class="dropdown-header text-start">
                                                <h6>Filtrar por Perfil</h6>
                                              </li>
                                              <li><a class="dropdown-item" href="#" onclick="generarGraficoTicket('usuario')">Usuario</a></li>
                                              <li><a class="dropdown-item" href="#" onclick="generarGraficoTicket('tecnico')">T��cnico</a></li>
                                              <li><a class="dropdown-item" href="#" onclick="generarGraficoTicket('admin')">Administrador</a></li>
                                            </ul>
                                          </div>
                                        </div>
                                    
                                        <div class="card-body p-3">
                                          <div style="height: 300px;">
                                            <canvas id="graficoColegios"></canvas>
                                          </div>
                                        </div>
                                    
                                      </div>
                                    </div>

                                  </div>
                                </div>
                                <!-- INDICADORES LATERALES -->
                                <div class="col-lg-4">
                                    <div class="card shadow px-0 mb-3">
                                      <div class="card-header d-flex justify-content-between align-items-center py-3">
                                        <h6 class="m-0 font-weight-bold text-primary">Tiempo Promedio de Respuesta</h6>
                                        <div class="dropdown">
                                          <a class="icon text-secondary" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots"></i>
                                          </a>
                                          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                            <li class="dropdown-header text-start">
                                              <h6>Acciones</h6>
                                            </li>
                                            <li><a class="dropdown-item" href="#" onclick="descargarPDF()">Descargar PDF</a></li>
                                            <li><a class="dropdown-item" href="#" onclick="descargarImagen()">Descargar Imagen</a></li>
                                          </ul>
                                        </div>
                                      </div>
                                    
                                      <div class="card-body">
                                        <div class="text-center fs-3 fw-bold">38 h</div>
                                      </div>
                                    </div>
                                    <div class="card shadow px-0 mb-3">
                                          <div class="card-header d-flex justify-content-between align-items-center py-3">
                                            <h6 class="m-0 font-weight-bold text-primary">Tiempo Promedio de Respuesta</h6>
                                            <div class="dropdown">
                                              <a class="icon text-secondary" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots"></i>
                                              </a>
                                              <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                                <li class="dropdown-header text-start">
                                                  <h6>Acciones</h6>
                                                </li>
                                                <li><a class="dropdown-item" href="#" onclick="descargarPDF()">Descargar PDF</a></li>
                                                <li><a class="dropdown-item" href="#" onclick="descargarImagen()">Descargar Imagen</a></li>
                                              </ul>
                                            </div>
                                          </div>

                                  <div class="card-body">
                                    <div class="text-center fs-3 fw-bold">38 h</div>
                                  </div>
                                </div>    
                                    <div class="card shadow px-0 mb-3">
                              <div class="card-header d-flex justify-content-between align-items-center py-3">
                                <h6 class="m-0 font-weight-bold text-primary">Tiempo Promedio de Respuesta</h6>
                                <div class="dropdown">
                                  <a class="icon text-secondary" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                  </a>
                                  <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                    <li class="dropdown-header text-start">
                                      <h6>Acciones</h6>
                                    </li>
                                    <li><a class="dropdown-item" href="#" onclick="descargarPDF()">Descargar PDF</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="descargarImagen()">Descargar Imagen</a></li>
                                  </ul>
                                </div>
                              </div>
                            
                              <div class="card-body">
                                <div class="text-center fs-3 fw-bold">38 h</div>
                              </div>
                            </div>
                                </div>
                              </div>
                            </div>
                         </div>
                    </div> 
                    
                    
                    
                    
                    <div class="col-md-12 mb-3">
                      <div class="card shadow px-0 mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center py-3">
                          <h6 class="m-0 font-weight-bold text-primary"> </h6>
                          <div class="dropdown">
                           
                       
                          </div>
                        </div>
                    
                        <div class="card-body">
                          <div class="accordion" id="acordeonColegios">
                            
                            <!-- Colegio 1 -->
                                    <div class="accordion-item">
                                                        <div class="accordion-header bg-light p-3 d-flex justify-content-between align-items-center" onclick="toggleAcordeon(this)">
                                                          <strong>Cordilelra</strong>
                                                          <i class="bi bi-chevron-down"></i>
                                                        </div>
                                                         <div class="accordion-body" style="display: none;">
                                                              <div class="table-responsive">
                                                                <table class="table table-striped table-bordered table-sm text-center align-middle mb-0">
                                                                  <thead class="table-light">
                                                                    <tr>
                                                                      <th>ID</th>
                                                                      <th>Asunto</th>
                                                                      <th>Estado</th>
                                                                      <th>Fecha</th>
                                                                      <th>Responsable</th>
                                                                      <th>Caracteristicas</th>
                        
                                                                    </tr>
                                                                  </thead>
                                                        <tbody>
                                                          <?php
                                                            for ($i = 1; $i <= 5; $i++) {
                                                              $id = 0 + $i;
                                                              $asunto = "Ticket ejemplo $i";
                                                              $estados = ["Abierto", "Cerrado", "Asignado", "En proceso", "Demorado"];
                                                              $estado = $estados[array_rand($estados)];
                                                              $fecha = date("Y-m-d", strtotime("-$i days"));
                                                              $responsable = "Usuario $i";
                                                        
                                                              echo "<tr>
                                                                      <td>$id</td>
                                                                      <td>$asunto</td>
                                                                      <td>$estados[0]</td>
                                                                      <td>$fecha</td>
                                                                      <td>$responsable</td>
<td class='text-center'>
  <div class='flex justify-content-center gap-1'>
    <button type='button' class='btn btn-sm btn-outline-primary' onclick='modalTicketTecnico()'>Ver</button>

  </div>
  
</td>

<td class='text-center'>
  <div class='flex justify-content-center gap-1'>
    <button type='button' class='btn btn-sm btn-outline-primary' onclick='modalTicketTecnico()'>Ver</button>

  </div>
  
</td>


                                                                      
                                                                      </tr>";
                                                            }
                                                          ?>
                                                        </tbody>
                        
                                                                </table>
                                                              </div>
                                                            </div>
                                                      </div>
                                                    </div><br>

                            


                            <!-- Repite para m��s colegios -->
                            
                          </div>
                        </div>
                      </div>
                    </div>
                    
                    
                    
                    

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Bootstrap Bundle (incluye Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
function modalTicketTecnico(){
        alert("hola");

}
</script>


    <?php $funciones->script(); ?>

<script>
const ctxEstados = document.getElementById("graficoEstados").getContext("2d");

const totalTickets = 100;
const porcentajeCerrado = 72;

const graficoEstados = new Chart(ctxEstados, {
  type: "doughnut",
  data: {
    labels: ["Cerrado", "En proceso", "Demorado", "Abierto", "Asignado"],
    datasets: [{
      data: [72, 15, 5, 4, 4],
      backgroundColor: [
        "#2563eb",  // Cerrado - azul
        "#22c55e",  // En proceso - verde
        "#facc15",  // Demorado - amarillo
        "#fb923c",  // Abierto - naranja
        "#94a3b8"   // Asignado - gris
      ],
      borderWidth: 0
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    cutout: "70%",
    plugins: {
      legend: {
        position: "right",
        labels: {
          boxWidth: 12,
          padding: 12
        }
      },
      tooltip: {
        enabled: true
      },
      // Plugin para mostrar el % en el centro (manual)
      beforeDraw: (chart) => {
        const { width } = chart;
        const { height } = chart;
        const ctx = chart.ctx;
        ctx.restore();
        const fontSize = (height / 100).toFixed(2);
        ctx.font = `${fontSize}em sans-serif`;
        ctx.textBaseline = "middle";
        const text = `${porcentajeCerrado}%`;
        const textX = Math.round((width - ctx.measureText(text).width) / 2);
        const textY = height / 2;
        ctx.fillText(text, textX, textY);
        ctx.save();
      }
    }
  }
});
</script>

<script>
  const ctxColegios = document.getElementById('graficoColegios').getContext('2d');
  new Chart(ctxColegios, {
    type: 'bar',
    data: {
      labels: ['Cordillera', 'Tabancura', 'Los Andes', 'Los Alerces', 'Huel��n'],
      datasets: [{
        label: 'Tickets',
        data: [120, 100, 85, 65, 40],
        backgroundColor: [
          '#2563eb', // azul
          '#10b981', // verde
          '#facc15', // amarillo
          '#fb923c', // naranja
          '#3b82f6'  // azul claro
        ],
        borderRadius: 4,
        barThickness: 20
      }]
    },
    options: {
      indexAxis: 'y', // �7�3 barras horizontales
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: {
            stepSize: 20
          }
        },
        y: {
          ticks: {
            font: {
              size: 13
            }
          }
        }
      }
    }
  });
</script>

<script>
    const ctxTipo = document.getElementById("graficoTipo").getContext("2d");
    const graficoTipo = new Chart(ctxTipo, {
      type: "bar",
      data: {
        labels: ["Mantenimiento", "Soporte TI", "Infraestructura"],
        datasets: [{
          label: "Cantidad",
          data: [180, 160, 120],
          backgroundColor: [
            "#22c55e",  // verde
            "#3b82f6",  // azul
            "#facc15"   // amarillo
          ]
        }]
      },
      options: {
        responsive: true,
        indexAxis: "y",
        scales: {
          x: { beginAtZero: true }
        },
        plugins: {
          legend: { display: false }
        }
      }
    });
</script>

<script>
  $(document).ready(function () {
    $('#tablaTickets').DataTable({
      responsive: true,
      language: {
        url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
      },
      pageLength: 10
    });
  });
</script>

<script>
    function descargarPDF() {
  alert("Funci��n para generar y descargar el informe PDF");
  // Aqu�� ir��a la l��gica para generar el PDF (usando jsPDF o fetch a backend PHP)
}

function descargarExcel() {
  alert("Funci��n para generar y descargar el Excel");
  // Aqu�� podr��as usar SheetJS o simplemente redirigir a un .php que genere Excel
}

function descargarImagen(idCanvas) {
  const canvas = document.getElementById(idCanvas);
  const url = canvas.toDataURL("image/png");
  const link = document.createElement("a");
  link.href = url;
  link.download = `${idCanvas}.png`;
  link.click();
}

</script>


<script>
function toggleAcordeon(headerElement) {
  const body = headerElement.nextElementSibling;
  const icon = headerElement.querySelector('i');

  if (body.style.display === "none" || body.style.display === "") {
    body.style.display = "block";
    icon.classList.remove("bi-chevron-down");
    icon.classList.add("bi-chevron-up");
  } else {
    body.style.display = "none";
    icon.classList.remove("bi-chevron-up");
    icon.classList.add("bi-chevron-down");
  }
}
</script>

</body>

</html>
