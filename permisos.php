<?php
session_start();
require_once 'class/conexion.php'; 
require_once 'class/funciones.php';
require_once 'class/personas.php';

$funciones = new Funciones(); 
$personas = new persona(); 
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$idPagActual = 7;

// Obtener todas las categorías y todos los usuarios
$categorias = $personas->obtenerTodasLasCategorias();
$usuarios   = $personas->obtenerTodosLosUsuarios();
$colegios = $funciones->obtenerColegios($idUsuarioSession);

$idColegioSel = isset($_GET['colegio']) ? (int)$_GET['colegio'] : 0;
// $data = $funciones->obtenerDashboardInventarioPcData($idUsuarioSession, $idColegioSel);



?>
<!DOCTYPE html>
<html lang="es">

<head>

    <head>
        <?php $funciones->header(); ?>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Data Table Usuarios</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">          <!-- Incluir Bootstrap CSS -->
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">                 <!-- Incluir DataTables CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">            <!-- Incluir Bootstrap Icons -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>                                             <!-- Incluir jQuery -->
        <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>                          <!-- Incluir DataTables JS -->
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
            integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy" crossorigin="anonymous">
        </script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
        <link href="css/estilo.css" rel="stylesheet">
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
        <script type="text/javascript" src="js/buscadores.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
        <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
        <script src="js/funciones.js"></script>                                                                         <!-- FUNCIONES DE DISPOSITIVOS -->
        <script src="js/permisos.js"></script>                                                                          <!-- FUNCIONES DE DISPOSITIVOS -->
        <script src="js/comunes.js"></script>                                                                           <!-- FUNCIONES DE DISPOSITIVOS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


    </head>

<style>
      #dataTableTecnicos {
        width: 100%;
        table-layout: auto; /* Ajusta las columnas al contenido */
      }
</style>

<body id="page-top">
    <div id="wrapper">
        <?php $funciones->menuLateral2($idUsuarioSession, $idPagActual);   ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>

                <div class="container-fluid">
                    <!-- <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Permisos</h1>
                    </div> -->

                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body pt-3">
                                    <!-- ********************** -->
                                    <ul class="nav nav-tabs nav-tabs-bordered" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" data-bs-toggle="tab"
                                                data-bs-target="#usuarios-tab" aria-selected="true"
                                                role="tab">Usuarios</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" data-bs-toggle="tab"
                                                data-bs-target="#configuracion-tab" aria-selected="false" tabindex="-1"
                                                role="tab">Configuración</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" data-bs-toggle="tab"
                                                data-bs-target="#nuevoMenu-tab" aria-selected="false" tabindex="-1"
                                                role="tab"> Dashboard Inventario </button>
                                        </li>
                                    </ul>


                                    <!-- <div id="dataTable_filter" class="dataTables_filter">
                                        <label>Buscar:<input type="search" id="buscarUsuario"
                                                class="form-control form-control-sm" placeholder=""
                                                aria-controls="dataTable"></label>
                                    </div> -->

                                    <div class="tab-content pt-2">
                                        <!-- MENU USUARIO -->
                                        <div class="tab-pane fade show active pt-1" id="usuarios-tab" role="tabpanel">
                                            <a href="#" class="btn btn-primary btn-icon-split btn-sm"
                                                id="buttonAgregarTicket" onclick="agregarUsuario()">
                                                <span class="text">AGREGAR USUARIO</span>
                                            </a>
                                            <div class="row mb-6">
                                                <!-- En permisos.php, donde se imprime listaUsuarios() -->
                                                <div class="container mt-4">
                                                    <?php echo $funciones->listaUsuarios(); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- MENU CONFIGURACION -->
                                        <div class="tab-pane fade pt-3" id="configuracion-tab" role="tabpanel">
                                            <!-- Settings Form -->
                                            <form>
                                                <div class="row mb-3">
                                                    <!-- Aquí puedes agregar otros campos si es necesario -->
                                                </div>
                                                <div class="row mb-3">
                                                    <?php echo $funciones->listaTecnicos(); ?>
                                                </div>
                                                <div class="text-center d-flex flex-wrap justify-content-center gap-2">
                                                    <button type="button" class="btn btn-primary"
                                                        onclick="guardarCambios()">Guardar</button>
                                                    <button type="button" class="btn btn-primary"
                                                        onclick="agregarCategoria()">Agregar Categorias</button>
                                                    <button class="btn btn-primary" type="button"
                                                        onclick="abrirModalAdministrarCategorias()">
                                                        <i class="bi bi-gear-fill"></i> Administrar Categorias
                                                    </button>
                                                </div>
                                            </form>
                                            <!-- End settings Form -->
                                        </div>

                                        <!-- MENU NUEVO -->

                            
                            <div class="tab-pane fade pt-3" id="nuevoMenu-tab" role="tabpanel">
                            <?php
                            include("dashboard_inventario_pc.php") ?>
                            
                            </div>

                                    </div><!-- End Bordered Tabs -->

                                </div>
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
    <?php include("modal_salir.php") ?>

    <div class="modal fade" id="modalAdministrarCategorias" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-tags-fill me-2"></i>Administrar Categorias
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body bg-light">
                    <div id="contenedorAdministrarCategorias">
                        <div class="d-flex justify-content-center align-items-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Categoria
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form id="formEditarCategoria">
                    <div class="modal-body">
                        <input type="hidden" id="editar_id_categoria" name="id_categoria">
                        <div class="mb-3">
                            <label for="editar_nombre_categoria" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="editar_nombre_categoria" name="nombre_categoria" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar_abreviacion" class="form-label">Abreviacion</label>
                            <input type="text" class="form-control" id="editar_abreviacion" name="abreviacion">
                        </div>
                        <div class="mb-3">
                            <label for="editar_icono" class="form-label">Icono</label>
                            <input type="text" class="form-control" id="editar_icono" name="icono" placeholder="bi-tag-fill">
                            <div class="form-text">Usa clases de Bootstrap Icons, por ejemplo: <code>bi-tag-fill</code>.</div>
                        </div>
                        <div class="mb-0">
                            <label for="editar_orden" class="form-label">Orden</label>
                            <input type="number" class="form-control" id="editar_orden" name="orden" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i>Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- <?php $funciones->script(); ?> -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/intro.js/minified/intro.min.js"></script> -->
    <script type='text/javascript' src='template_01/js/funciones.js'></script>
    <!-- Inicialización de DataTables -->
    
    
    <script>
    $(document).ready(function() {
        $('#dataTableUsuarios').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "pageLength": 10
        });
    });
    </script>
    <script>
    $(document).ready(function(){
        $('#dataTableTecnicos').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "pageLength": 10
        });
    });
</script>

    <script>
    // < !--- FUNCION PARA ACTUALIZAR EL NUMERO DE MENSAJES-- >

    document.addEventListener('DOMContentLoaded', function() {
        const usuarioId = document.getElementById('idUsuario').value;

        // Función para cargar y actualizar el número de mensajes
        function actualizarNumeroMensajes() {
            $.ajax({
                type: 'GET',
                url: 'http://127.0.0.1/ticket/api/obtenerCantidadMensajes',
                dataType: "json",
                data: {
                    id_usuario: usuarioId
                },
                success: function(data) {
                    $('#numeroMensajes').text(data);
                }
            });
        }

        actualizarNumeroMensajes();

        setInterval(actualizarNumeroMensajes, 3000);
        // setInterval(actualizarNumeroMensajes, 120000);
    });
    </script>
    <!-- < !--- FUNCION PARA ACTUALIZAR EL NUMERO DE ALERTAS-- > -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const usuarioId = document.getElementById('idUsuario').value;

        // Función para cargar y actualizar el número de mensajes
        function actualizarNumeroAlertas() {
            $.ajax({
                type: 'GET',
                url: 'http://127.0.0.1/ticket/api/obtenerCantidadAlertas',
                dataType: "json",
                data: {
                    id_usuario: usuarioId
                },
                success: function(data) {
                    $('#numeroAlertas').text(data);
                }
            });
        }

        actualizarNumeroAlertas();

        setInterval(actualizarNumeroAlertas, 3000);
    });
    </script>
    <!-- // < !--- FUNCION PARA ACTUALIZAR LOS MENSAJES-- > -->

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const usuarioId = document.getElementById('idUsuario').value;

        // Función para cargar y actualizar el contenedor de mensajes
        function actualizarMensajes() {
            $.ajax({
                type: 'GET',
                url: 'http://127.0.0.1/ticket/api/obtenerMensajes',
                data: {
                    id_usuario: usuarioId
                },
                success: function(data) {
                    // Actualizar el contenido del div con el HTML devuelto por la API
                    $('#contendorMensajes').html(data);
                }
            });
        }

        // Llamar a la función inmediatamente cuando la página cargue
        actualizarMensajes();

        // Actualizar cada 3 segundos
        setInterval(actualizarMensajes, 3000);
    });
    </script>
    <!-- // < !--- FUNCION PARA ACTUALIZAR LOS RECORDATORIOS-- > -->

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const usuarioId = document.getElementById('idUsuario').value;

        // Función para cargar y actualizar el contenedor de mensajes
        function actualizarRecordatorio() {
            $.ajax({
                type: 'GET',
                url: 'http://127.0.0.1/ticket/api/obtenerRecordatorios',
                data: {
                    id_usuario: usuarioId
                },
                success: function(data) {
                    // Actualizar el contenido del div con el HTML devuelto por la API
                    $('#contendorTicket').html(data);
                }
            });
        }

        // Llamar a la función inmediatamente cuando la página cargue
        actualizarRecordatorio();

        // Actualizar cada 3 segundos
        setInterval(actualizarRecordatorio, 3000);
    });
    </script>
    
    
    <script>
        function toggleSubmenuColor(elemento) {
    const categoria = elemento.getAttribute('data-id_categoria');
    const esActivo = elemento.classList.contains('green');

    if (!esActivo) {
        // Buscar si otra celda ya tiene esta categoría activa
        const yaAsignada = document.querySelectorAll('.submenu-item.green[data-id_categoria="' + categoria + '"]');
        if (yaAsignada.length > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Ya asignada',
                text: 'Esta categoría ya fue asignada a otro técnico.',
                timer: 2000,
                showConfirmButton: false,
                  customClass: {
            popup: 'cuerpo_modal_guardar',
    
        }
            });
            return;
        }
    }

    elemento.classList.toggle('green');
    elemento.classList.toggle('red');
}

    </script>

</body>

</html>

