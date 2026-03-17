<?php
    session_start();
    require_once 'class/conexion.php';
    require_once 'class/funciones.php';
    
    $funciones          = new Funciones();
    $idUsuarioSession   = htmlspecialchars($_SESSION['id']);
    $idPagActual        = "9";  // Página de bitácora
    
    // *************** INSTANCIA DE LAS FUNCIONES
    $equipos                    = $funciones->listarEquipos();
    $usuarios                   = $funciones->listarUsuarios();
    $tiposDispositivos          = $funciones->listarTiposDispositivos();
    $listarOtrosDispositivos    = $funciones->listarOtrosDispositivos();
    $listarTodosDispositivosqr  = $funciones->listarTodosDispositivosqr();


?>

<!DOCTYPE html>
<html lang="es">
<meta charset="UTF-8">

<head>
    <?php $funciones->header(); ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Incluir jQuery -->
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>
    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="css/bitacora.css"> <!-- PROPIO DE ESTA PAGINA -->
    <script src="js/comunes.js"></script> <!-- FUNCIONES COMUNES -->
    <script src="js/equipos.js"></script> <!-- FUNCIONES DE COMPUTADORES -->
    <script src="js/dispositivos.js"></script> <!-- FUNCIONES DE DISPOSITIVOS -->

</head>
<style>
    .nav-tabs {
        justify-content: flex-start;
        /* Alinear las pestañas hacia la izquierda */
    }

    .nav-item {
        margin-right: 10px;
        /* Espacio entre las pestañas */
    }

    .nav-link {
        flex: 1;
        /* Ajusta el ancho de las pestañas si es necesario */
        text-align: center;
        /* Centra el texto dentro de cada pestaña */
        white-space: nowrap;
        /* Evita que el texto se divida en varias líneas */
    }
</style>

<body id="page-top">
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

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Listado </h1>
                    </div>

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body pt-3">
                                    <!-- // contenedor de las pestañas -->
                                    <div class="tab-content pt-2">
                                        <div class="tab-pane fade show active" id="tab-equipos" role="tabpanel">
                                            <!-- <h5 class="mb-4">Listado</h5> -->
                                            <ul class="nav nav-tabs" id="equiposTab" role="tablist">
                                                <!-- Listado Computadores -->
                                                <li class="nav-item position-relative" role="presentation">
                                                    <a href="agregar_equipos.php?tipo=computador"
                                                        class="icono-circular-superior" title="Agregar Computador">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                    <a class="nav-link active" id="pestaña1-tab" data-bs-toggle="tab"
                                                        href="#pestaña1" role="tab" aria-controls="pestaña1"
                                                        aria-selected="true">
                                                        Listado Computadores
                                                    </a>
                                                </li>

                                                <!-- Listado Equipos -->
                                                <li class="nav-item position-relative" role="presentation">
                                                    <a href="agregar_equipos.php?tipo=equipo"
                                                        class="icono-circular-superior" title="Agregar Equipo">
                                                        <i class="fas fa-plus"></i>
                                                    </a>
                                                    <a class="nav-link" id="pestaña2-tab" data-bs-toggle="tab"
                                                        href="#pestaña2" role="tab" aria-controls="pestaña2"
                                                        aria-selected="false">
                                                        Listado Equipos
                                                    </a>
                                                </li>
                                            </ul>




                                            <!-- Contenido de las subpestañas -->
                                            <div class="tab-content" id="equiposTabContent">
                                                <!--BOTONES QUE SE ACTIVAN SEGUN SEA EL CASO -->
                                                <!-- ************************************************** -->
                                                <!-- Pestaña 1: LISTADO COMPUTADORES--->
                                                <!-- ****************************************************** -->
                                                <div class="tab-pane fade show active" id="pestaña1" role="tabpanel"
                                                    aria-labelledby="pestaña1-tab">
                                                    <h5 class="mb-4">
                                                        <div id="botonesDescargar">
                                                            <!-- Botón Descargar Excel -->
                                                            <button id="descargarExcel" class="btn btn-success"
                                                                style="display: none; position: relative;">
                                                                <span id="contadorExcel" class="badge"
                                                                    style="position: absolute; top: -10px; right: -10px; background-color: red; color: white; border-radius: 50%; padding: 5px; display: none;"></span>
                                                                <i class="fas fa-file-excel"></i> Descargar Excel
                                                            </button>

                                                            <!-- Botón Descargar QR -->
                                                            <button id="descargarQR" class="btn btn-primary"
                                                                style="display: none; position: relative;">
                                                                <span id="contadorQR" class="badge"
                                                                    style="position: absolute; top: -10px; right: -10px; background-color: red; color: white; border-radius: 50%; padding: 5px; display: none;"></span>
                                                                <i class="fas fa-qrcode"></i> Descargar QR
                                                            </button>
                                                        </div>

                                                        <div id="botonesAccion">
                                                            <button id="editarEquipo" class="btn btn-primary"
                                                                style="display: none;"
                                                                onclick="activarEdicionEquipos()">
                                                                <i class="fas fa-edit"></i> Guardar Computador
                                                            </button>
                                                        </div>
                                                    </h5>
                                                    <div class="table-responsive">
                                                        <!-- ****************************************************** -->
                                                        <!-- BUTTON DE BUSQUEDA-->
                                                        <!-- ****************************************************** -->
                                                        <!-- <div class="mb-3">
                                                            <input type="text" id="buscadorEquipos" class="form-control"
                                                                placeholder="Buscar computador..."
                                                                onkeyup="filtrarEquipos()">
                                                        </div> -->
                                                        <!-- ************************************************** -->
                                                        <!-- BUTTON DE BUSQUEDA-->
                                                        <!-- ****************************************************** -->

                                                        <table id="tablaConmputadores"
                                                            class="table table-bordered table-hover table-striped">
                                                            <thead class="table-dark text-center">
                                                                <tr>
                                                                    <th><input type="checkbox" id="seleccionarTodos">
                                                                    </th>
                                                                    <th>Tipo Equipo</th>
                                                                    <th>Marca</th>
                                                                    <th>Modelo</th>
                                                                    <th>Procesador</th>
                                                                    <th>RAM</th>
                                                                    <th>Disco</th>
                                                                    <th>Windows</th>
                                                                    <th>Usuario</th>
                                                                    <th>Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php if (!empty($equipos)): ?>
                                                                <?php foreach ($equipos as $equipo): ?>
                                                                <tr id="equipo_<?php echo $equipo['id_equipo']; ?>"
                                                                    style="background-color:red">
                                                                    <td>
                                                                        <input type="checkbox" class="seleccionEquipo"
                                                                            value="<?php echo $equipo['id_equipo']; ?>">
                                                                    </td>
                                                                    <td><?php echo !empty($equipo['tipo_pc']) ? htmlspecialchars($equipo['tipo_pc']) : '-----'; ?>
                                                                    </td>
                                                                    <td><?php echo !empty($equipo['fabricante']) ? htmlspecialchars($equipo['fabricante']) : '-----'; ?>
                                                                    </td>
                                                                    <td><?php echo !empty($equipo['producto']) ? htmlspecialchars($equipo['producto']) : '------'; ?>
                                                                    </td>
                                                                    <td><?php echo !empty($equipo['modelo_procesador']) ? htmlspecialchars($equipo['modelo_procesador']) : '------'; ?>
                                                                    </td>
                                                                    <td><?php echo !empty($equipo['tamano_total_memoria']) ? htmlspecialchars($equipo['tamano_total_memoria']) . ' GB' : '------'; ?>
                                                                    </td>
                                                                    <td><?php echo !empty($equipo['capacidad_almacenamiento']) ? htmlspecialchars($equipo['capacidad_almacenamiento']) . ' GB' : '------'; ?>
                                                                    </td>
                                                                    <td><?php echo !empty($equipo['windows']) ? htmlspecialchars($equipo['windows']) : '------'; ?>
                                                                    </td>
                                                                    <td><?php echo !empty($equipo['nombre_usuario']) ? htmlspecialchars($equipo['nombre_usuario']) : '------'; ?>
                                                                    </td>
                                                                    <td>
                                                                        <div class="d-flex gap-2">
                                                                            <button class="btn btn-primary btn-sm"
                                                                                onclick="activaAcordeonComputador(<?php echo $equipo['id_equipo']; ?>)">
                                                                                <i class="fas fa-edit"></i>
                                                                            </button>
                                                                            <button class="btn btn-danger btn-sm"
                                                                                onclick="eliminarEquipo(<?php echo $equipo['id_equipo']; ?>)">
                                                                                <i class="fas fa-trash-alt"></i>
                                                                            </button>
                                                                            <button class="btn btn-secondary btn-sm"
                                                                                onclick="verQrEquipo(<?php echo $equipo['id_equipo']; ?>)">
                                                                                <i class="fas fa-qrcode"></i>
                                                                            </button>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php endforeach; ?>
                                                                <?php else: ?>
                                                                <tr>
                                                                    <td colspan="10">No se encontraron equipos
                                                                        registrados.</td>
                                                                </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>

                                                <!-- ****************************************************** -->
                                                <!-- Pestaña 2: LISTADOS EQUIPOS -->
                                                <!-- ****************************************************** -->
                                                <div class="tab-pane fade" id="pestaña2" role="tabpanel"
                                                    aria-labelledby="pestaña2-tab">
                                                    <h5 class="mb-4">
                                                        <!-- Botones de descarga -->
                                                        <div id="botonesDescargar">
                                                            <!-- Botón para Descargar Excel -->
                                                            <button id="descargarExcelDispositivos"
                                                                class="btn btn-success"
                                                                style="display: none; position: relative;">
                                                                <i class="fas fa-file-excel"></i> Descargar Dispositivos
                                                                <span id="contadorExcelDispositivos" class="badge"
                                                                    style="position: absolute; top: -10px; right: -10px; background-color: red; color: white; border-radius: 50%; padding: 5px; display: none;"></span>
                                                            </button>
                                                            <!-- Botón para Imprimir Códigos QR -->
                                                            <button id="descargarQrDispositivos" class="btn btn-primary"
                                                                style="display: none; position: relative;">
                                                                <i class="fas fa-qrcode"></i> Descargar QR
                                                                <span id="contadorQrDispositivos" class="badge"
                                                                    style="position: absolute; top: -10px; right: -10px; background-color: red; color: white; border-radius: 50%; padding: 5px; display: none;"></span>
                                                            </button>
                                                        </div>
                                                    </h5>
              <!-- ************************************************** -->
                                                        <!-- BUTTON DE BUSQUEDA-->
                                                        <!-- ****************************************************** -->
                                                        <!-- <div class="mb-3">
                                                            <input type="text" id="buscadorDispositivos"
                                                                class="form-control"
                                                                placeholder="Buscar dispositivos..."
                                                                onkeyup="filtrarDispositivos()">
                                                        </div> -->

                                                        <!-- ************************************************** -->
                                                        <!-- BUTTON DE BUSQUEDA-->
                                                        <!-- ****************************************************** -->
                                                    <div class="table-responsive">
                                          
                                                        <table id="tablaDispositivos"
                                                            class="table table-bordered table-hover table-striped">
                                                            <thead class="table-dark text-center">
                                                                <tr>
                                                                    <th><input type="checkbox"
                                                                            id="seleccionarTodosDispositivos"></th>
                                                                    <th>Tipo Dispositivo</th>
                                                                    <th>Marca</th>
                                                                    <th>Modelo</th>
                                                                    <th>Número de Serie</th>
                                                                    <th>Proveedor</th>
                                                                    <th>Asignado</th>
                                                                    <th>Acciones</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody id="otrosDispositivosTableBody">
                            <?php if (!empty($listarOtrosDispositivos)): ?>
                                <?php foreach ($listarOtrosDispositivos as $dispositivo): ?>
                                    <tr id="dispositivo_<?php echo $dispositivo['id_dispositivo']; ?>">
                                        <td><input type="checkbox" class="seleccionDispositivo"
                                            name="dispositivo_<?php echo $dispositivo['id_dispositivo']; ?>"
                                            id="dispositivo_<?php echo $dispositivo['id_dispositivo']; ?>"
                                            value="<?php echo $dispositivo['id_dispositivo']; ?>"></td>

                                        <td><?php echo !empty($dispositivo['tipo']) ? htmlspecialchars(mb_convert_encoding($dispositivo['tipo'], 'UTF-8', 'auto')) : '-----'; ?></td>
                                        <td><?php echo !empty($dispositivo['marca']) ? htmlspecialchars($dispositivo['marca']) : '-----'; ?></td>
                                        <td><?php echo !empty($dispositivo['modelo']) ? htmlspecialchars($dispositivo['modelo']) : '-----'; ?></td>
                                        <td><?php echo !empty($dispositivo['n_serie']) ? htmlspecialchars($dispositivo['n_serie']) : '-----'; ?></td>
                                        <td><?php echo !empty($dispositivo['proveedor']) ? htmlspecialchars($dispositivo['proveedor']) : '-----'; ?></td>
                                        <td>
                                            <?php echo (!empty($dispositivo['usuario_nombre']) && !empty($dispositivo['usuario_apellido']))
                                                ? htmlspecialchars($dispositivo['usuario_nombre'] . ' ' . $dispositivo['usuario_apellido'])
                                                : '-----'; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn btn-primary btn-sm"
                                                    onclick="editarDispositivo(<?php echo $dispositivo['id_dispositivo']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-danger btn-sm"
                                                    onclick="eliminarDispositivo(<?php echo $dispositivo['id_dispositivo']; ?>)">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                                <button class="btn btn-secondary btn-sm"
                                                    onclick="verQrDispositivo(<?php echo $dispositivo['id_dispositivo']; ?>)">
                                                    <i class="fas fa-qrcode"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8">No se encontraron dispositivos registrados.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                                                        </table>
                                                    </div>
                                                </div>
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



<?php $funciones->script(); ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Si la tabla ya fue inicializada previamente, la destruyes antes
    if ($.fn.DataTable.isDataTable('#tablaConmputadores')) {
        $('#tablaConmputadores').DataTable().destroy();
    }
    $('#tablaConmputadores').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        ordering: true
    });
});


$(document).ready(function() {
    // Si la tabla ya fue inicializada previamente, la destruimos antes
    if ($.fn.DataTable.isDataTable('#tablaDispositivos')) {
        $('#tablaDispositivos').DataTable().destroy();
    }
    $('#tablaDispositivos').DataTable({
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        pageLength: 10,
        responsive: true,
        ordering: true
    });
});
</script>






















<!-- LOGICA D ELOS BUTTON DESCARGAR Y IMNPRIMIR  PERO DE COMPUTADORES  -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const descargarExcelBtn = document.getElementById('descargarExcel');
    const descargarQrBtn = document.getElementById('descargarQR'); // Botón para QR
    const seleccionarTodosCheckbox = document.getElementById('seleccionarTodos');
    const checkboxesEquipos = document.querySelectorAll('.seleccionEquipo');

    const contadorExcel = document.getElementById('contadorExcel'); // Badge para Excel
    const contadorQR = document.getElementById('contadorQR'); // Badge para QR

    // Mostrar/ocultar botones y actualizar contadores
    function actualizarBotones() {
        const seleccionados = document.querySelectorAll('.seleccionEquipo:checked');
        const cantidadSeleccionados = seleccionados.length;

        const haySeleccionados = cantidadSeleccionados > 0;

        // Mostrar botones si hay selección
        descargarExcelBtn.style.display = haySeleccionados ? 'inline-block' : 'none';
        descargarQrBtn.style.display = haySeleccionados ? 'inline-block' : 'none';

        // Actualizar contadores
        contadorExcel.textContent = cantidadSeleccionados;
        contadorQR.textContent = cantidadSeleccionados;

        contadorExcel.style.display = haySeleccionados ? 'inline' : 'none';
        contadorQR.style.display = haySeleccionados ? 'inline' : 'none';
    }

    // Evento para cada checkbox de equipo
    checkboxesEquipos.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            actualizarBotones();
        });
    });

    // Seleccionar/deseleccionar todos los checkboxes
    seleccionarTodosCheckbox.addEventListener('change', function() {
        checkboxesEquipos.forEach(checkbox => {
            checkbox.checked = seleccionarTodosCheckbox.checked;
        });
        actualizarBotones();
    });

    // Descargar Excel: enviar IDs seleccionados al archivo PHP
    descargarExcelBtn.addEventListener('click', function() {
        const idsSeleccionados = Array.from(document.querySelectorAll('.seleccionEquipo:checked')).map(
            checkbox => checkbox.value);

        if (idsSeleccionados.length > 0) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'modelos/descarga/descarga_excel.php';
            idsSeleccionados.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'equiposSeleccionados[]';
                input.value = id;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        }
    });

    // Descargar QR: redirigir con IDs seleccionados
    descargarQrBtn.addEventListener('click', function() {
        const idsSeleccionados = Array.from(document.querySelectorAll('.seleccionEquipo:checked')).map(
            checkbox => checkbox.value);

        if (idsSeleccionados.length > 0) {
            const baseUrl = 'modelos/imprimir/computadores.php';
            const url = `${baseUrl}?id_computador=${idsSeleccionados.join(',')}`;
            window.open(url, '_blank'); // Abre la URL en una nueva pestaña
        }
    });
});
</script>



<!-- LOGICA D ELOS BUTTON DESCARGAR Y IMNPRIMIR  PERO DE DISPOSITIVOS  -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const descargarExcelBtn = document.getElementById('descargarExcelDispositivos');
    const descargarQrBtn = document.getElementById('descargarQrDispositivos');
    const seleccionarTodosCheckbox = document.getElementById(
        'seleccionarTodosDispositivos'); // Asegúrate de que el ID coincide con el HTML
    const checkboxesDispositivos = document.querySelectorAll('.seleccionDispositivo');

    const contadorExcel = document.getElementById('contadorExcelDispositivos');
    const contadorQR = document.getElementById('contadorQrDispositivos');

    // Mostrar/ocultar botones y actualizar contadores
    function actualizarBotones() {
        const seleccionados = document.querySelectorAll('.seleccionDispositivo:checked');
        const cantidadSeleccionados = seleccionados.length;

        const haySeleccionados = cantidadSeleccionados > 0;

        // Mostrar botones si hay selección
        descargarExcelBtn.style.display = haySeleccionados ? 'inline-block' : 'none';
        descargarQrBtn.style.display = haySeleccionados ? 'inline-block' : 'none';

        // Actualizar contadores
        contadorExcel.textContent = cantidadSeleccionados;
        contadorQR.textContent = cantidadSeleccionados;

        contadorExcel.style.display = haySeleccionados ? 'inline' : 'none';
        contadorQR.style.display = haySeleccionados ? 'inline' : 'none';
    }

    // Evento para cada checkbox de dispositivo
    checkboxesDispositivos.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            actualizarBotones();

            // Si se desmarca un checkbox, desmarcar el "Seleccionar Todos"
            if (!checkbox.checked) {
                seleccionarTodosCheckbox.checked = false;
            }

            // Si todos los checkboxes están marcados, marcar el "Seleccionar Todos"
            const todosMarcados = Array.from(checkboxesDispositivos).every(c => c.checked);
            if (todosMarcados) {
                seleccionarTodosCheckbox.checked = true;
            }
        });
    });

    // Seleccionar/deseleccionar todos los checkboxes
    seleccionarTodosCheckbox.addEventListener('change', function() {
        const seleccionarTodos = seleccionarTodosCheckbox.checked;

        // Marcar/desmarcar todos los checkboxes de dispositivos
        checkboxesDispositivos.forEach(checkbox => {
            checkbox.checked = seleccionarTodos;
        });

        // Actualizar botones y contadores después de la acción
        actualizarBotones();
    });

    // Descargar Excel: enviar IDs seleccionados al archivo PHP
    descargarExcelBtn.addEventListener('click', function() {
        const idsSeleccionados = Array.from(document.querySelectorAll('.seleccionDispositivo:checked'))
            .map(checkbox => checkbox.value);

        if (idsSeleccionados.length > 0) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action =
                'modelos/descarga/descarga_otro_dispositivo.php'; // Cambiar a la ruta de tu archivo PHP
            idsSeleccionados.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'dispositivosSeleccionados[]';
                input.value = id;
                form.appendChild(input);
            });
            document.body.appendChild(form);
            form.submit();
        }
    });

    // Descargar QR: redirigir con IDs seleccionados
    descargarQrBtn.addEventListener('click', function() {
        const idsSeleccionados = Array.from(document.querySelectorAll('.seleccionDispositivo:checked'))
            .map(checkbox => checkbox.value);

        if (idsSeleccionados.length > 0) {
            const baseUrl = 'modelos/imprimir/dispositivo.php'; // Cambiar a la ruta de tu archivo PHP
            const url = `${baseUrl}?id_dispositivos=${idsSeleccionados.join(',')}`;
            window.open(url, '_blank'); // Abre la URL en una nueva pestaña
        }

    });
});
</script>



<script>
document.addEventListener('DOMContentLoaded', () => {
    // Obtener el parámetro 'tab' de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab'); // El parámetro será 'pestaña1' o 'pestaña2'

    // Validar qué pestaña debe activarse
    if (tab === 'pestaña2') {
        const targetTab = document.querySelector('#pestaña2-tab'); // Pestaña "Listado Equipos"
        if (targetTab) {
            const tabInstance = new bootstrap.Tab(targetTab); // Crear instancia de la pestaña
            tabInstance.show(); // Mostrar la pestaña
        }
    } else {
        // Por defecto, activa la pestaña "Listado Computadores" (pestaña1)
        const targetTab = document.querySelector('#pestaña1-tab');
        if (targetTab) {
            const tabInstance = new bootstrap.Tab(targetTab);
            tabInstance.show();
        }
    }
});
</script>










</html>
