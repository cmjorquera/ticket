<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';

$funciones          = new Funciones();
$idUsuarioSession   = htmlspecialchars($_SESSION['id']);
$idPagActual        = "9";  // Página de bitácora

// INSTANCIA DE LAS FUNCIONES
$equipos                    = $funciones->listarEquipos();
$usuarios                   = $funciones->listarUsuarios();
$tiposDispositivos          = $funciones->listarTiposDispositivos();
$listarOtrosDispositivos    = $funciones->listarOtrosDispositivos();
$listarTodosDispositivosqr  = $funciones->listarTodosDispositivosqr();

$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : 'computador'; // Por defecto 'computador'

?>

<!DOCTYPE html>
<html lang="es">
<meta charset="UTF-8">

<head>
    <?php $funciones->header(); ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="css/bitacora.css"> <!-- PROPIO DE ESTA PAGINA -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="js/comunes.js"></script> <!-- PROPIO DE ESTA PAGINA -->
    <script src="js/equipos.js"></script> <!-- FUNCIONES DE COMPUTADORES -->
    <script src="js/dispositivos.js"></script> <!-- FUNCIONES DE DISPOSITIVOS -->


</head>



<body id="page-top">
    <div id="wrapper">
        <?php $funciones->menuLateral($idUsuarioSession, $idPagActual); ?>
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
                        <h1 class="h3 mb-0 text-gray-800">Agregar </h1>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body pt-3">
                                    <!-- Pestañas principales -->
                                    <ul class="nav nav-tabs" id="tabList" role="tablist">
                                        <!-- Pestaña Agregar Computador -->
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link <?php echo $tipo === 'computador' ? 'active' : ''; ?>"
                                                id="computador-tab"
                                                data-bs-toggle="tab"
                                                data-bs-target="#computador"
                                                type="button"
                                                role="tab"
                                                aria-controls="computador"
                                                aria-selected="<?php echo $tipo === 'computador' ? 'true' : 'false'; ?>">
                                                <i class="fas fa-desktop"></i> Agregar Computador
                                            </button>
                                        </li>

                                        <!-- Pestaña Agregar Equipos -->
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link <?php echo $tipo === 'equipo' ? 'active' : ''; ?>"
                                                id="equipo-tab"
                                                data-bs-toggle="tab"
                                                data-bs-target="#equipo"
                                                type="button"
                                                role="tab"
                                                aria-controls="equipo"
                                                aria-selected="<?php echo $tipo === 'equipo' ? 'true' : 'false'; ?>">
                                                <i class="fas fa-cogs"></i> Agregar Equipos
                                            </button>
                                        </li>
                                    </ul>

                                    <div class="tab-content pt-2">

                                        <!--********************************************************** -->
                                        <!--**********************************************************-->
                                        <!-- Pestaña Agregar Equipo -->
                                        <!--********************************************************** -->
                                        <!--**********************************************************-->
                                        <div class="tab-pane fade <?php echo $tipo === 'computador' ? 'show active' : ''; ?>" id="computador" role="tabpanel" aria-labelledby="computador-tab">

                                            <h5 class="mb-4">Registro de Equipos</h5>
                                            <!-- Sub-pestañas dentro de Agregar Equipo -->
                                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link active" id="caracteristicas-tab" data-bs-toggle="tab" href="#caracteristicas" role="tab" aria-controls="caracteristicas" aria-selected="true">
                                                        <i class="fas fa-info-circle"></i> Características del Equipo
                                                    </a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="observaciones-tab" data-bs-toggle="tab" href="#observaciones" role="tab" aria-controls="observaciones" aria-selected="false">
                                                        <i class="fas fa-sticky-note"></i> Observaciones
                                                    </a>
                                                </li>
                                            </ul>

                                            <div class="tab-content" id="innerTabContent">
                                                <!-- Pestaña Característica -->
                                                <div class="tab-pane fade show active" id="caracteristicas" role="tabpanel" aria-labelledby="caracteristicas-tab">
                                                    <form id="formAgregarEquipo">
                                                        <div class="container">
                                                            <!-- Información del Sistema -->
                                                            <div class="row mb-3">
                                                                <div class="col-md-4">
                                                                    <label for="nombre_equipo" class="form-label">Nombre del Equipo</label>
                                                                    <input type="text" class="form-control" id="nombre_equipo" name="nombre_equipo">
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <label for="fabricante" class="form-label">Marca</label>
                                                                    <input type="text" class="form-control" id="fabricante" name="fabricante">
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <label for="producto" class="form-label">Modelo</label>
                                                                    <input type="text" class="form-control" id="producto" name="producto">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="numero_serie" class="form-label">Número de Serie</label>
                                                                    <input type="text" class="form-control" id="numero_serie" name="numero_serie">
                                                                </div>
                                                       
                                                                <div class="col-md-4">
                                                                    <label for="tipo_pc" class="form-label">Tipo de PC</label>
                                                                    <input type="text" class="form-control" id="tipo_pc" name="tipo_pc">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="usuarios_select" class="form-label">Usuarios</label>
                                                                    <select class="form-control" id="usuarios_select" name="usuarios_select">
                                                                        <option value="">Seleccionar usuario</option>
                                                                        <?php
                                                                        $usuarios = $funciones->listarUsuarios();
                                                                        if (!empty($usuarios)) {
                                                                            foreach ($usuarios as $usuario) {
                                                                                echo "<option value='" . htmlspecialchars($usuario['id']) . "'>" . htmlspecialchars($usuario['nombre']) . " " . htmlspecialchars($usuario['apellido_paterno']) . "</option>";
                                                                            }
                                                                        } else {
                                                                            echo "<option value=''>No hay usuarios disponibles</option>";
                                                                        }
                                                                        ?>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!-- Dispositivo de Memoria -->
                                                            <h5>Dispositivo de Memoria</h5>
                                                              <ul class="nav nav-tabs" id="memoryTabList" role="tablist"></ul>
                                                                <div class="tab-content mt-3" id="memoryTabContent"></div>
                                                                    <!-- ********************************************************* -->
                                                                    <!-- ********************************************************* -->

                                                            <!-- Procesador -->
                                                            <h5>Procesador</h5>
                                                            <div class="row mb-3">
                                                                <div class="col-md-4">
                                                                    <label for="fabricante_procesador" class="form-label">Fabricante del Procesador</label>
                                                                    <input type="text" class="form-control" id="fabricante_procesador" name="fabricante_procesador">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="modelo_procesador" class="form-label">Modelo del Procesador</label>
                                                                    <input type="text" class="form-control" id="modelo_procesador" name="modelo_procesador">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="frecuencia_procesador" class="form-label">Frecuencia Procesador</label>
                                                                    <input type="text" class="form-control" id="frecuencia_procesador" name="frecuencia_procesador" oninput="permitirSoloNumeros(event)">
                                                                </div>
                                                            </div>

                                                            <!-- Almacenamiento -->
                                                            <h5>Almacenamiento</h5>
                                                            <div class="row mb-3">
                                                                <div class="col-md-4">
                                                                    <label for="nombre_almacenamiento" class="form-label">Nombre de Almacenamiento</label>
                                                                    <input type="text" class="form-control" id="nombre_almacenamiento" name="nombre_almacenamiento">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="capacidad_almacenamiento" class="form-label">Capacidad de Almacenamiento (GB)</label>
                                                                    <input type="text" class="form-control" id="capacidad_almacenamiento" name="capacidad_almacenamiento" oninput="permitirSoloNumeros(event)">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="tipo_almacenamiento" class="form-label">Tipo de Almacenamiento</label>
                                                                    <input type="text" class="form-control" id="tipo_almacenamiento" name="tipo_almacenamiento">
                                                                </div>

                                                            </div>

                                                            <!-- windows -->
                                                            <h5>Programas</h5>
                                                            <div class="row mb-3">

                                                                <div class="col-md-4">
                                                                    <label for="windows" class="form-label">Windows</label>
                                                                    <input type="text" class="form-control" id="windows" name="windows">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="office" class="form-label">Office</label>
                                                                    <input type="text" class="form-control" id="office" name="office">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="antiVirus" class="form-label">Anti-Virus </label>
                                                                    <input type="text" class="form-control" id="antiVirus" name="antiVirus">
                                                                </div>
                                                            </div>


                                                            <!-- Monitores -->
                                                            <h5>Monitores</h5>
                                                            <ul class="nav nav-tabs" id="monitoresTabs" role="tablist">
                                                                <li class="nav-item" role="presentation">
                                                                    <a class="nav-link active" id="monitor1-tab" data-bs-toggle="tab" href="#monitor1" role="tab" aria-controls="monitor1" aria-selected="true">Monitor 1</a>
                                                                </li>
                                                                <li class="nav-item" role="presentation">
                                                                    <a class="nav-link" id="monitor2-tab" data-bs-toggle="tab" href="#monitor2" role="tab" aria-controls="monitor2" aria-selected="false">Monitor 2</a>
                                                                </li>
                                                            </ul>

                                                            <div class="tab-content" id="monitoresContent">
                                                                <!-- Monitor 1 -->
                                                                <div class="tab-pane fade show active" id="monitor1" role="tabpanel" aria-labelledby="monitor1-tab">
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-4">
                                                                            <label for="monitor_1_modelo" class="form-label">Modelo</label>
                                                                            <input type="text" class="form-control" id="monitor_1_modelo" name="monitor_1_modelo">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label for="monitor_1_codigo" class="form-label">ID</label>
                                                                            <input type="text" class="form-control" id="monitor_1_codigo" name="monitor_1_codigo">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label for="monitor_1_numero_serie" class="form-label">Número de Serie</label>
                                                                            <input type="text" class="form-control" id="monitor_1_numero_serie" name="monitor_1_numero_serie">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label for="monitor_1_tamano" class="form-label">Tamaño (pulgadas)</label>
                                                                            <input type="teext" class="form-control" id="monitor_1_tamano" name="monitor_1_tamano" oninput="permitirSoloNumeros(event)">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label for="monitor_1_resolucion" class="form-label">Resolucion</label>
                                                                            <input type="text" class="form-control" id="monitor_1_resolucion" name="monitor_1_resolucion">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- Monitor 2 -->
                                                                <div class="tab-pane fade" id="monitor2" role="tabpanel" aria-labelledby="monitor2-tab">
                                                                    <div class="row mb-3">
                                                                        <div class="col-md-4">
                                                                            <label for="monitor_2_modelo" class="form-label">Modelo</label>
                                                                            <input type="text" class="form-control" id="monitor_2_modelo" name="monitor_2_modelo">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label for="monitor_2_codigo" class="form-label">ID</label>
                                                                            <input type="text" class="form-control" id="monitor_2_codigo" name="monitor_2_codigo">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label for="monitor_2_numero_serie" class="form-label">Número de Serie</label>
                                                                            <input type="text" class="form-control" id="monitor_2_numero_serie" name="monitor_2_numero_serie">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label for="monitor_2_tamano" class="form-label">Tamaño (pulgadas)</label>
                                                                            <input type="text" class="form-control" id="monitor_2_tamano" name="monitor_2_tamano" oninput="permitirSoloNumeros(event)">
                                                                        </div>
                                                                        <div class="col-md-4">
                                                                            <label for="monitor_2_resolucion" class="form-label">Resolucion</label>
                                                                            <input type="text" class="form-control" id="monitor_2_resolucion" name="monitor_2_resolucion">
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>

                                                <!-- Pestaña de Observaciones -->
                                                <div class="tab-pane fade" id="observaciones" role="tabpanel" aria-labelledby="observaciones-tab">
                                                    <form id="pestanaObservacion">
                                                        <div class="container">
                                                            <div class="row mb-3">
                                                                <div class="col-md-4">
                                                                    <label for="valor_equipo" class="form-label">Valor del Equipo</label>
                                                                    <input type="text" class="form-control" id="valor_equipo" name="valor_equipo" oninput="formatearPesos(event)">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="proveedor" class="form-label">Proveedor</label>
                                                                    <input type="text" class="form-control" id="proveedor" name="proveedor">
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <label for="numero_factura" class="form-label">Número de Factura</label>
                                                                    <input type="text" class="form-control" id="numero_factura" name="numero_factura" oninput="permitirSoloNumeros(event)">
                                                                </div>
                                                                
                                                                    <div class="col-md-4">
                                                                    <label for="fecha_compra_equipo" class="form-label">Fecha Compra</label>
                                                                    <input type="date" class="form-control" id="fecha_compra_equipo" name="fecha_compra_equipo">
                                                                </div>
                                                            </div>


                                                            <div class="row mb-3">
                                                                <div class="col-md-12">
                                                                    <label for="observacion" class="form-label">Observaciones</label>
                                                                    <textarea class="form-control" id="observacion" name="observacion" rows="3"></textarea>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </form>
                                                </div>
                                                <!-- Botones comunes para ambas pestañas -->
                                                <div id="botonesAccion">
                                                    <button type="button" class="btn btn-success" onclick="crearEquipo()">Guardar</button>
                                                    <button type="button" class="btn btn-secondary" onclick="limpiarFormularioComputador()">Limpiar</button>
                                                    <button type="button" class="btn btn-info" id="adjuntarTxtBtn" onclick="document.getElementById('archivoTxt').click()">Adjuntar TXT</button>
                                                    <input type="file" id="archivoTxt" style="display: none;" accept=".txt">
                                                </div>

                                            </div>
                                        </div>

                                        <!--********************************************************** -->
                                        <!--**********************************************************-->
                                        <!-- Pestaña Agregar Dispositivos -->
                                        <!--********************************************************** -->
                                        <!--**********************************************************-->
                                        <!-- <div class="tab-pane fade" id="otrosdoDispositivos" role="tabpanel"> -->
                                        <div class="tab-pane fade <?php echo $tipo === 'equipo' ? 'show active' : ''; ?>" id="equipo" role="tabpanel" aria-labelledby="equipo-tab">

                                            <h5 class="mb-4">Registro de Equipos</h5>
                                            <!-- Sub-pestañas dentro de Agregar Dispositivos -->
                                            <ul class="nav nav-tabs" id="myTabDevices" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link active" id="dispositivosCaracteristicas-tab" data-bs-toggle="tab" href="#dispositivosCaracteristicas" role="tab" aria-controls="dispositivosCaracteristicas" aria-selected="true">
                                                        <i class="fas fa-info-circle"></i> Características del Dispositivo
                                                    </a>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <a class="nav-link" id="dispositivosObservacion-tab" data-bs-toggle="tab" href="#dispositivosObservacion" role="tab" aria-controls="dispositivosObservacion" aria-selected="false">
                                                        <i class="fas fa-sticky-note"></i> Observaciones
                                                    </a>
                                                </li>
                                            </ul>

                                            <!-- Contenido de las Pestañas -->
                                            <div class="tab-content" id="innerTabContentDevices">
                                                <!-- Pestaña Características -->
                                                <div class="tab-pane fade show active" id="dispositivosCaracteristicas" role="tabpanel" aria-labelledby="dispositivosCaracteristicas-tab">
                                   <form id="formAgregarDispositivo">
    <div class="container">
        <div class="row mb-3">
            <!-- Primera fila con 3 inputs -->
            <div class="col-md-4 mb-3">
                <label for="tipo" class="form-label">Tipo de Dispositivo</label>
                <select class="form-select" id="tipo" name="tipo">
                    <option value="">Seleccione un tipo de dispositivo</option>
                    <?php foreach ($tiposDispositivos as $dispositivo): ?>
                        <option value="<?php echo htmlspecialchars($dispositivo['id_tipo_dispositivo']); ?>">
                            <?php echo htmlspecialchars($dispositivo['nombre_dispositivo']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="marca" class="form-label">Marca</label>
                <input type="text" class="form-control" id="marca" name="marca" placeholder="Marca del dispositivo">
            </div>
            <div class="col-md-4 mb-3">
                <label for="modelo" class="form-label">Modelo</label>
                <input type="text" class="form-control" id="modelo" name="modelo" placeholder="Modelo específico">
            </div>
        </div>
        <div class="row mb-3">
            <!-- Segunda fila con 2 inputs y un espacio vacío -->
            <div class="col-md-4 mb-3">
                <label for="n_serie" class="form-label">Número de Serie</label>
                <input type="text" class="form-control" id="n_serie" name="n_serie" placeholder="Número de serie">
            </div>
            <div class="col-md-4 mb-3">
                <label for="asignado" class="form-label">Asignado a</label>
                <select class="form-control" id="asignado" name="asignado">
                    <option value="">Seleccionar usuario</option>
                    <?php
                    $usuarios = $funciones->listarUsuarios(); // Obtener lista de usuarios
                    if (!empty($usuarios)) {
                        foreach ($usuarios as $usuario) {
                            echo "<option value='" . htmlspecialchars($usuario['id']) . "'>"
                                . htmlspecialchars($usuario['nombre']) . " "
                                . htmlspecialchars($usuario['apellido_paterno']) . "</option>";
                        }
                    } else {
                        echo "<option value=''>No hay usuarios disponibles</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <!-- Espacio vacío para mantener el diseño consistente -->
            </div>
        </div>
    </div>
</form>

                                                </div>

                                                <!-- Pestaña Observaciones -->
                                                <div class="tab-pane fade" id="dispositivosObservacion" role="tabpanel" aria-labelledby="dispositivosObservacion-tab">
                                                    <form id="formObservacionesDispositivo">
                                                      <div class="container">
                                                            <div class="row mb-3">
                                                                <div class="col-md-3 mb-3">
                                                                    <label for="proveedor" class="form-label">Proveedor</label>
                                                                    <input type="text" class="form-control" id="proveedor" name="proveedor" placeholder="Proveedor">
                                                                </div>
                                                                <div class="col-md-3 mb-3">
                                                                    <label for="fecha_compra" class="form-label">Fecha de Compra</label>
                                                                    <input type="date" class="form-control" id="fecha_compra" name="fecha_compra" oninput="validarFecha(event)">

                                                                </div>
                                                                <div class="col-md-3 mb-3">
                                                                    <label for="numero_factura" class="form-label">Número Factura</label>
                                                                    <input type="text" class="form-control" id="numero_factura" name="numero_factura" oninput="permitirSoloNumeros(event)">

                                                                </div>
                                                                <div class="col-md-3 mb-3">
                                                                    <label for="precio" class="form-label">Precio</label>
                                                                <input type="text"  class="form-control" id="precio" name="precio" placeholder="Precio en $"   oninput="formatearPesos(event)">

 

                                                                </div>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-md-12 mb-3">
                                                                    <label for="observaciones" class="form-label">Observaciones</label>
                                                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="3" placeholder="Observaciones adicionales"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </form>
                                                </div>
                                            </div>

                                            <!-- Botones de acción -->
                                            <div id="botonesAccion">
                                                <button type="button" class="btn btn-success" onclick="crearDispositivo()">Guardar </button>
                                                <button type="button" class="btn btn-secondary" onclick="limpiarFormularioDispositivo()">Limpiar</button>
                                                <button type="button" class="btn btn-secondary" onclick="agregarDispositivo()">Agregar Dispositivo a la lista</button>
                                            </div>
                                        </div>


                                        <a class="scroll-to-top rounded" href="#page-top">
                                            <i class="fas fa-angle-up"></i>
                                        </a>
                                    </div>
                                    <?php $funciones->footer(); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/otros_equipos.js"></script>

<?php $funciones->script(); ?>



<script>
    // ***************************************************************************************
    //************************ ADJUNTAR TXT  ************************************************ */
    // Función para leer el archivo TXT y mostrar los datos en los inputs
    document.getElementById('archivoTxt').addEventListener('change', function() {
        const archivo = this.files[0];
        if (archivo) {
            if (archivo.type === "text/plain") {
                leerArchivoTXT(archivo); // Llama a la función para procesar el archivo
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivo no válido',
                    text: 'Por favor, adjunta un archivo TXT válido.',
                });
            }
        }
    });
    // ***************************************************************************************
    //************************ ADJUNTAR TXT  ************************************************ */
</script>

</html>