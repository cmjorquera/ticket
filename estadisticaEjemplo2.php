<?php 
error_reporting(E_ALL);
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';

// Instancias
$funciones = new Funciones();  
$bd = new MySQL('', '', ';'); // Conexión BD

// Variables de sesión
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$nombre           = htmlspecialchars($_SESSION['nombre']) . ' ' . htmlspecialchars($_SESSION['apellido_paterno']);
$AreaTrabajo      = htmlspecialchars($_SESSION['id_area_trabajo']);
$idPagActual      = "11";

// Filtro opcional desde GET
$idUsuarioFiltro = isset($_GET['id_usuario']) ? (int)$_GET['id_usuario'] : null;

// ======== ESTADÍSTICAS GENERALES ========
$datosEstados            = $funciones->contarTicketsPorEstado();
$datosCategorias         = json_decode($funciones->contarTicketsPorCategoria(), true);
$datosPorcentajes        = json_decode($funciones->obtenerPorcentajeEstadosPorCategoria(), true);
$promediosEstados        = json_decode($funciones->obtenerPromedioTiempoEstados(), true);
$usuarios                = $funciones->obtenerEstadisticasUsuarios();
$datosGraficoColegios    = $funciones->obtenerDatosGraficoColegios();
$datosGraficoColegios2   = $funciones->obtenerDatosGraficoColegios2();

// ======== FUNCIONES AUXILIARES ACTUALIZADAS ========
function contarPorEstado($bd, $estado) {
    $sql = "SELECT COUNT(*) AS total 
            FROM tickets 
            WHERE id_estado = $estado";
    $res = $bd->consulta($sql);
    $row = $bd->fetch_assoc($res);
    return (int)$row['total'];
}

function calcPorcentaje($cantidad, $total) {
    return ($total > 0) ? round(($cantidad * 100) / $total) : 0;
}

// ======== VARIABLES DE FECHA ========
$mesActual  = date('m');
$anioActual = date('Y');

// ======== TOTAL TICKETS AÑO ACTUAL ========
$sqlTotal = "SELECT COUNT(*) AS total FROM proceso_tickets";

$resTotalAnual = $bd->consulta($sqlTotal);
$rowTotalAnual = $bd->fetch_assoc($resTotalAnual);
$totalTicketsAnuales = (int)$rowTotalAnual['total'];

// ======== TOTALES ACTUALES DEL SISTEMA (ESTADOS PRINCIPALES) ========
$totalSinAsignar = contarPorEstado($bd, 1); // Recibido / Sin asignar
$totalAsignado   = contarPorEstado($bd, 2); // Asignado
$totalProceso    = contarPorEstado($bd, 3); // En proceso
$totalTerminado  = contarPorEstado($bd, 5); // Terminado

$total = $totalSinAsignar + $totalAsignado + $totalProceso + $totalTerminado;

$pSinAsignar = calcPorcentaje($totalSinAsignar, $total);
$pRetrasado  = calcPorcentaje($totalAsignado, $total);
$pProceso    = calcPorcentaje($totalProceso, $total);
$pTerminado  = calcPorcentaje($totalTerminado, $total);

// ======== TOTALES POR ESTADO EN EL AÑO ACTUAL ========
$totalProcesoAnio    = contarPorEstado($bd, 3); // En Proceso
$totalTerminadosAnio = contarPorEstado($bd, 5); // Terminados
$totalDemoradosAnio  = contarPorEstado($bd, 4); // Demorados
$totalSinAsignarAnio = contarPorEstado($bd, 1); // Sin Asignar


// ======== COMPARACIÓN CON EL MISMO MES DEL AÑO PASADO ========
$sqlAnterior = "SELECT COUNT(*) AS total 
                FROM proceso_tickets 
                WHERE MONTH(fecha_creacion_inicio) = '$mesActual' 
                  AND YEAR(fecha_creacion_inicio) = '".($anioActual - 1)."'";
$resAnterior = $bd->consulta($sqlAnterior);
$rowAnterior = $bd->fetch_assoc($resAnterior);
$totalAnterior = (int)$rowAnterior['total'];

$porcentajeCrecimiento = ($totalAnterior > 0)
    ? round((($totalTicketsAnuales - $totalAnterior) / $totalAnterior) * 100)
    : 100;

// ======== DATOS PARA GRÁFICO DE BARRAS POR MES (ESTADOS) ========
$datosPorMes = [];
for ($mes = 1; $mes <= 12; $mes++) {
    $datosPorMes['en_proceso'][]  = $funciones->contarPorEstadoYMes($bd, 3, $mes); // En Proceso
    $datosPorMes['recibidos'][]   = $funciones->contarPorEstadoYMes($bd, 1, $mes); // Recibidos
    $datosPorMes['terminados'][]  = $funciones->contarPorEstadoYMes($bd, 5, $mes); // Terminados
}

// ======== TOTAL TICKETS CREADOS POR MES (LÍNEA DE COMPARACIÓN) ========
$totalTicketsPorMes = array_fill(0, 12, 0);
$sqlMeses = "SELECT MONTH(fecha_creacion_inicio) AS mes, COUNT(*) AS total 
             FROM proceso_tickets 
             WHERE YEAR(fecha_creacion_inicio) = '$anioActual'
             GROUP BY mes";
$resMeses = $bd->consulta($sqlMeses);
while ($row = $bd->fetch_assoc($resMeses)) {
    $mesIndex = (int)$row['mes'] - 1;
    $totalTicketsPorMes[$mesIndex] = (int)$row['total'];
}








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
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">    <link href="css/cssAdmin.css" rel="stylesheet">
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
.dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
  margin: 0 2px;
}

.progress-circle {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  font-weight: bold;
  font-size: 14px;
  color: #333;
  background: #f4f4f4;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  border: 5px solid transparent;
}

.progress-circle.red { border-color: #e74c3c; }
.progress-circle.blue { border-color: #3498db; }
.progress-circle.orange { border-color: #f39c12; }
.progress-circle.green { border-color: #2ecc71; }

</style>


<body id="page-to3p">
    <div id="wrapper">
        <?php $funciones->menuLateral2($idUsuarioSession, $idPagActual); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>   
       <div id="botonesDescargar">
                    <div class="dropdown" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                      <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-download"></i> Descargar
                      </button>
                      <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="descargarPDF()">Informe PDF</a></li>
                        <li><a class="dropdown-item" href="#" onclick="descargarExcel()">Reporte Excel</a></li>
                        <li><a class="dropdown-item" href="#" onclick="descargarImagen('graficoEstado')">Gráfico de Estado</a></li>
                      </ul>
                    </div>
                </div>
                    
            <div class="card mb-4">
              <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">GRÁFICOS ADMINISTRADOR</h6>
              </div>
            
              <div class="card-body">
                <!-- 🔹 PESTAÑAS -->
                <ul class="nav nav-tabs" id="graficoTicketsTab" role="tablist">
                  <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="resumen-tab" data-bs-toggle="tab" data-bs-target="#resumen" type="button" role="tab" aria-controls="resumen" aria-selected="true">
                      Resumen General
                    </button>
                  </li>
                  <li class="nav-item" role="presentation">
                    <button class="nav-link" id="graficos-tab" data-bs-toggle="tab" data-bs-target="#graficos" type="button" role="tab" aria-controls="graficos" aria-selected="false">
                     Resumen  Colegios
                    </button>
                  </li>
                </ul>
            
                <!-- 🔹 CONTENIDO DE LAS PESTAÑAS -->
                <div class="tab-content pt-4" id="graficoTicketsTabContent">
                    
                  <!-- 🟢 TAB RESUMEN GENERAL -->
                  <div class="tab-pane fade show active" id="resumen" role="tabpanel" aria-labelledby="resumen-tab">
                           <div class="container-fluid mb-4">
                                <div class="row g-4">
                                  <!-- En Proceso -->
                                  <div class="col-12 col-md-6 col-lg-3" >
                                    <div class="card-metric position-relative" style="background: linear-gradient(135deg, #FFE0E0, #FFF0F0);">
                                      <div class="card-options"><i class="bi bi-three-dots-vertical"></i></div>
                                      <div class="icon-circle"><i class="bi bi-clock-history"></i></div>
                                      <h5><?= $totalProceso ?>/<?= $total ?></h5>
                                      <p>Tickets en Proceso</p>
                                      <div class="d-flex justify-content-between mt-3">
                                        <span class="progress-bar-title">Procesando</span>
                                        <span class="progress-value"><?= $totalProceso ?> Tickets (<?= $pProceso ?>%)</span>
                                      </div>
                                      <div class="progress">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pProceso ?>%;"></div>
                                      </div>
                                    </div>
                                  </div>
                    
                                  <!-- Terminados -->
                                  <div class="col-12 col-md-6 col-lg-3" >
                                    <div class="card-metric position-relative"  style="background: linear-gradient(135deg, #D8F8D8, #F0FFF0);">
                                      <div class="card-options"><i class="bi bi-three-dots-vertical"></i></div>
                                      <div class="icon-circle"><i class="bi bi-check2-circle"></i></div>
                                      <h5><?= $totalTerminado ?>/<?= $total ?></h5>
                                      <p>Tickets Terminados </p>
                                      <div class="d-flex justify-content-between mt-3">
                                        <span class="progress-bar-title">Completados</span>
                                        <span class="progress-value"><?= $totalTerminado ?> Tickets (<?= $pTerminado ?>%)</span>
                                      </div>
                                      <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: <?= $pTerminado ?>%;"></div>
                                      </div>
                                    </div>
                                  </div>
                    
                                  <!-- Retrasados -->
                                  <div class="col-12 col-md-6 col-lg-3">
                                    <div class="card-metric position-relative" style="background: linear-gradient(135deg, #FFD6A5, #FFF0E0);" >
                                      <div class="card-options"><i class="bi bi-three-dots-vertical"></i></div>
                                      <div class="icon-circle"><i class="bi bi-exclamation-triangle"></i></div>
                                      <h5><?= $totalAsignado ?>/<?= $total ?></h5>
                                      <p>Tickets Retrasados</p>
                                      <div class="d-flex justify-content-between mt-3">
                                        <span class="progress-bar-title">Con Retraso</span>
                                        <span class="progress-value"><?= $totalAsignado ?> Tickets (<?= $pRetrasado ?>%)</span>
                                      </div>
                                      <div class="progress">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $pRetrasado ?>%;"></div>
                                      </div>
                                    </div>
                                  </div>
                    
                                  <!-- Sin Asignar -->
                                  <div class="col-12 col-md-6 col-lg-3">
                                    <div class="card-metric position-relative" style="background: linear-gradient(135deg, #E6E6FA, #FFFFFF);">
                                      <div class="card-options"><i class="bi bi-three-dots-vertical"></i></div>
                                      <div class="icon-circle"><i class="bi bi-person-x"></i></div>
                                      <h5><?= $totalSinAsignar ?>/<?= $total ?></h5>
                                      <p>Tickets Sin Asignar</p>
                                      <div class="d-flex justify-content-between mt-3">
                                        <span class="progress-bar-title">Sin Técnico</span>
                                        <span class="progress-value"><?= $totalSinAsignar ?> Tickets (<?= $pSinAsignar ?>%)</span>
                                      </div>
                                      <div class="progress">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $pSinAsignar ?>%;"></div>
                                      </div>
                                    </div>
                                  </div>
                                </div>  
                            </div>
                            
                            
                            <div class="container-fluid">
                              <div class="row g-4 align-items-stretch"> <!-- 🔹 Esto permite que las columnas igualen altura -->
                                                          <!-- Gráfico Principal -->
                                                   <!-- 📊 Gráfico Principal -->
                                <div class="col-md-8">
                                  <div class="card-grafico h-100"> <!-- Se adapta automáticamente a altura total -->
                                    <h5 class="mb-4 fw-bold">Tickets por Estado - Año Actual</h5>
                                    <canvas id="paymentChart" height="290"></canvas>
                                  </div>
                                </div>
                              
                              
                              
                              
                              
                                <div class="col-md-4">
                                  <div class="card-total-sales shadow rounded-3 overflow-hidden">
                                    <div class="sales-header text-white bg-primary px-4 py-3">
                                      <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                          <h4 class="fw-bold mb-1"><?= $totalTicketsAnuales;?></h4>
                                          <small>Total Tickets Año</small>
                                        </div>
                                        <span class="badge bg-white text-primary fw-semibold mt-1">
                                          <?= $porcentajeCrecimiento >= 0 ? '+' : '' ?><?= $porcentajeCrecimiento ?>%
                                        </span>
                                      </div>
                                      <canvas id="waveChart" height="60"></canvas>
                                    </div>
                                    <div class="sales-list p-3 bg-white">
                                      <div class="mb-2 d-flex align-items-center">
                                        <i class="bi bi-arrow-repeat text-primary me-2 fs-5"></i>
                                        <span class="text-muted">En Proceso: <strong><?= $totalProcesoAnio; ?></strong></span>
                                      </div>
                                      <div class="mb-2 d-flex align-items-center">
                                        <i class="bi bi-check-circle text-success me-2 fs-5"></i>
                                        <span class="text-muted">Terminados: <strong><?= $totalTerminadosAnio; ?></strong></span>
                                      </div>
                                      <div class="mb-2 d-flex align-items-center">
                                        <i class="bi bi-exclamation-circle text-warning me-2 fs-5"></i>
                                        <span class="text-muted">Demorados: <strong><?= $totalDemoradosAnio; ?></strong></span>
                                      </div>
                                      <div class="mb-2 d-flex align-items-center">
                                        <i class="bi bi-person-x text-danger me-2 fs-5"></i>
                                        <span class="text-muted">Sin Asignar: <strong><?= $totalSinAsignarAnio; ?></strong></span>
                                      </div>
                                    </div>
                                    <div class="text-center py-2 border-top bg-light">
                                      <a href="#" class="text-decoration-none fw-semibold text-primary">FULL DETAILS</a>
                                    </div>
                                  </div>
                                </div>
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            
                            </div>  
                          </div>

                    <div class="card-body" id="contenedorTablaUsuario">
                      <?php include("componentes/bloque_tabla_admin.php"); ?>
                    </div>
                  </div>
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
                  
            
                  <!-- 🔵 TAB GRÁFICOS -->
                  <div class="tab-pane fade" id="graficos" role="tabpanel" aria-labelledby="graficos-tab">
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
            <h6 class="m-0 font-weight-bold text-primary">Gráfico Colegios 2</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <?php $funciones->obtenerResumenTicketsPorUsuarios(); ?>
            </div>
        </div>
    </div>
</div>

                                    </div>
                  </div>
                </div>
              </div>
            </div>
                        
                  
                        
            </div>
    

            <!-- Scroll to Top Button-->
            <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
            </a>

            <!-- Footer -->
            <?php $funciones->footer(); ?>
        </div>
    </div>

    <?php $funciones->script(); ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <script>
        const waveChart = new Chart(document.getElementById('waveChart'), {
              type: 'line',
              data: {
                labels: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
                datasets: [{
                  label: 'Tickets',
                  data: <?= json_encode($totalTicketsPorMes) ?>, // array PHP con totales mensuales
                  borderColor: '#fff',
                  backgroundColor: 'rgba(255,255,255,0.2)',
                  fill: true,
                  tension: 0.3,
                  pointRadius: 0,
                }]
              },
              options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                  x: { display: false },
                  y: { display: false }
                }
              }
            });

    </script>
    
<script>
new Chart(document.getElementById('paymentChart'), {
  type: 'bar',
  data: {
    labels: ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'],
    datasets: [
 
      {
        label: 'En Proceso',
        data: <?= json_encode($datosPorMes['en_proceso']) ?>,
        backgroundColor: '#0d6efd',
        borderRadius: 5,
        barThickness: 20
      },
      {
        label: 'Recibidos',
        data: <?= json_encode($datosPorMes['recibidos']) ?>,
        backgroundColor: '#adb5bd',
        borderRadius: 5,
        barThickness: 20
      },
      {
        label: 'Terminados',
        data: <?= json_encode($datosPorMes['terminados']) ?>,
        backgroundColor: '#198754',
        borderRadius: 5,
        barThickness: 20
      }
    ]
  },
  options: {
    responsive: true,
    plugins: {
      legend: {
        display: true,
        position: 'bottom'
      },
      tooltip: {
        mode: 'index',
        intersect: false
      },
      title: {
        display: true,
        text: 'Tickets por Estado - Mes a Mes'
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        ticks: {
          stepSize: 1,
          precision: 0
        }
      }
    }
  }
});
</script>


</body>
</html>
