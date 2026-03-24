<?php
    session_start();
    require_once 'class/conexion.php';
    require_once 'class/funciones.php';
    require_once 'class/CursosCodigosQR.php';

    $funciones        = new Funciones();
    $CodigosQR        = new CodigosQR();
    $idUsuarioSession = htmlspecialchars($_SESSION['id']);
    $nombre           = htmlspecialchars($_SESSION['nombre']) . ' ' . htmlspecialchars($_SESSION['apellido_paterno']);
    $idPagActual      = "10";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php $funciones->header(); ?>


    <!-- Librerías -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Incluir Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <!-- Incluir DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <!-- Incluir Bootstrap Icons -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Incluir jQuery -->
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <!-- Incluir DataTables JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
        integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
    </script>

    <!-- Estilos personalizados -->
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">

    <!-- Scripts -->
    <script type="text/javascript" src="js/funciones.js"></script>
    <script type="text/javascript" src="js/comun.js"></script>
    <script type="text/javascript" src="js/ticket.js"></script>
</head>

<body id="page-top">
    <input type="hidden" id="idUsuario" value="<?php echo $idUsuarioSession; ?>" />
    <div id="wrapper">
        <?php $funciones->menuLateral3($idUsuarioSession, $idPagActual); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Navbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle me-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>

    

                <div class="container-fluid px-4">
                    <div class="row g-4">
                        <!-- FORMULARIO GENERADOR QR -->
                        <div class="col-12 col-lg-5">
                            <div class="card shadow h-100">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        TicketsGenerador de QR para Taller
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <form id="formGenerarQR">
                                        <input type="hidden" id="idQR">
                                        <input type="hidden" id="idUsuarioFormulario"
                                            value="<?php echo $idUsuarioSession; ?>">

                                        <div class="mb-3">
                                            <label for="nombreTaller" class="form-label">Nombre del Taller</label>
                                            <input type="text" class="form-control" id="nombreTaller" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fechaTaller" class="form-label">Fecha del Taller</label>
                                            <input type="date" class="form-control" id="fechaTaller" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="urlFormulario" class="form-label">URL del Formulario</label>
                                            <input type="url" class="form-control" id="urlFormulario"
                                                placeholder="https://example.com" required>
                                        </div>
                                        <div class="d-grid">
                                            <button type="button" id="btnGenerarQR" class="btn btn-success"
                                                onclick="ButtongenerarQRCursos()">
                                                <i class="bi bi-qr-code"></i> Generar QR
                                            </button>
                                        </div>
                                    </form>
                                    <div id="qrContainer" class="text-center mt-3"></div>
                                </div>
                            </div>
                        </div>

                        <!-- TABLA QR GENERADOS -->
                        <div class="col-12 col-lg-7">
                            <div class="card shadow h-100">
                                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        Generador de QR para Taller </h6>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table id="tablaGeneradorCursosQR"
                                            class="table table-bordered table-hover table-striped">
                                            <thead class="table-dark text-center">
                                                <tr>
                                                    <th>n°</th>
                                                    <th>Nombre del Taller</th>
                                                    <th>Fecha</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody id="qrList">
                                                <?php
                                $contador = 1;
                                $qrs = $CodigosQR->ObtenerCursoQR();
                                foreach ($qrs as $qr) {
                                    echo "<tr>
                                            <td class='text-center'>" . $contador++ . "</td>
                                            <td>" . htmlspecialchars($qr['nombre_taller']) . "</td>
                                            <td class='text-center'>" . htmlspecialchars($qr['fecha_taller']) . "</td>
                                            <td class='text-center'>
                                                <button class='btn btn-sm btn-secondary' onclick='reimprimirQR(\"{$qr['id_qr']}\")' title='Ver QR'>
                                                    <i class='fas fa-qrcode'></i>
                                                </button>
                                                <button class='btn btn-sm btn-primary' onclick='modificarQR(\"{$qr['id_qr']}\")' title='Modificar QR'>
                                                    <i class='fas fa-edit'></i>
                                                </button>
                                                <button class='btn btn-sm btn-danger' onclick='EliminarCursoQR(\"{$qr['id_qr']}\")' title='Eliminar QR'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </td>
                                        </tr>";
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

                <!-- Botón scroll-top -->
                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>

    <?php include("modal_salir.php")?>
    <?php $funciones->script(); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



    <!-- Inicializar DataTable -->
    <script>
    $(document).ready(function() {
        if ($.fn.DataTable.isDataTable('#tablaGeneradorCursosQR')) {
            $('#tablaGeneradorCursosQR').DataTable().destroy();
        }
        $('#tablaGeneradorCursosQR').DataTable({
            language: {
                url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json",
                infoCallback: function(settings, start, end, total, pre) {
                    if(total === 5) {
                        return "Mostrar cursos del " + start + " al " + end + " de un total de " + total + " registros";
                    } else {
                        return "Mostrar cursos del " + start + " al " + end + " de un total de " + total + " cursos";
                    }
                }
            },
            pageLength: 10,
            responsive: true,
            ordering: true
        });
    });
    </script>

    <!-- Validación visual -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        let inputs = ["nombreTaller", "fechaTaller", "urlFormulario"];
        inputs.forEach(id => {
            let input = document.getElementById(id);
            input.addEventListener("input", function() {
                if (this.value.trim() !== "") {
                    this.classList.remove("is-invalid");
                }
            });
        });
    });
    </script>
</body>

</html>
