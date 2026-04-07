<?php
session_start();
require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';
require_once __DIR__ . '/../class/personas.php';

$funciones = new Funciones();
$personas = new persona();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$idPagActual = 7;

$categorias = $personas->obtenerTodasLasCategorias();
$usuarios = $personas->obtenerTodosLosUsuarios();
$colegios = $funciones->obtenerColegios($idUsuarioSession);
$idColegioSel = isset($_GET['colegio']) ? (int) $_GET['colegio'] : 0;

$assetPrefix = (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/configuracion/') !== false) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php $funciones->header(); ?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Table Usuarios</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">
    <link href="<?php echo $assetPrefix; ?>css/estilo.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css">
    <script type="text/javascript" src="<?php echo $assetPrefix; ?>js/buscadores.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/funciones.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/permisos.js"></script>
    <script>
        window.CONFIG_RELATIVE_ROOT = '<?php echo $assetPrefix; ?>';
    </script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/comun.js"></script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/mantenimiento_tickets.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/comunes.js"></script>
    <link rel="stylesheet" href="<?php echo $assetPrefix; ?>configuracion/css/mantenimiento_tickets.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        #dataTableTecnicos {
            width: 100%;
            table-layout: auto;
        }
    </style>
</head>

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
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card">
                                <div class="card-body pt-3">
                                    <ul class="nav nav-tabs nav-tabs-bordered" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#usuarios-tab" aria-selected="true" role="tab">Usuarios</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#configuracion-tab" aria-selected="false" tabindex="-1" role="tab">Configuración</button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nuevoMenu-tab" aria-selected="false" tabindex="-1" role="tab"> Dashboard Inventario </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#mantenimientoTickets-tab" aria-selected="false" tabindex="-1" role="tab"> Mantenimiento Tickets </button>
                                        </li>
                                    </ul>

                                    <div class="tab-content pt-2">
                                        <div class="tab-pane fade show active pt-1" id="usuarios-tab" role="tabpanel">
                                            <a href="#" class="btn btn-primary btn-icon-split btn-sm" id="buttonAgregarTicket" onclick="agregarUsuario()">
                                                <span class="text">AGREGAR USUARIO</span>
                                            </a>
                                            <div class="row mb-6">
                                                <div class="container mt-4">
                                                    <?php echo $funciones->listaUsuarios(); ?>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="tab-pane fade pt-3" id="configuracion-tab" role="tabpanel">
                                            <form>
                                                <div class="row mb-3"></div>
                                                <div class="row mb-3">
                                                    <?php echo $funciones->listaTecnicos(); ?>
                                                </div>
                                                <div class="text-center d-flex flex-wrap justify-content-center gap-2">
                                                    <button type="button" class="btn btn-primary" onclick="guardarCambios()">Guardar</button>
                                                    <button type="button" class="btn btn-primary" onclick="agregarCategoria()">Agregar Categorias</button>
                                                    <button class="btn btn-primary" type="button" onclick="abrirModalAdministrarCategorias()">
                                                        <i class="bi bi-gear-fill"></i> Administrar Categorias
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="tab-pane fade pt-3" id="nuevoMenu-tab" role="tabpanel">
                                            <?php include __DIR__ . '/../dashboard_inventario_pc.php'; ?>
                                        </div>

                                        <div class="tab-pane fade pt-3" id="mantenimientoTickets-tab" role="tabpanel">
                                            <?php include __DIR__ . '/mantenimiento_tickets.php'; ?>
                                        </div>
                                    </div>
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

    <?php include __DIR__ . '/../modal_salir.php'; ?>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type='text/javascript' src='<?php echo $assetPrefix; ?>template_01/js/funciones.js'></script>

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
    document.addEventListener('DOMContentLoaded', function() {
        const usuarioId = document.getElementById('idUsuario').value;

        function actualizarNumeroAlertas() {
            $.ajax({
                type: 'GET',
                url: 'http://127.0.0.1/ticket/api/obtenerCantidadAlertas',
                dataType: "json",
                data: { id_usuario: usuarioId },
                success: function(data) {
                    $('#numeroAlertas').text(data);
                }
            });
        }

        actualizarNumeroAlertas();
        setInterval(actualizarNumeroAlertas, 3000);
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const usuarioId = document.getElementById('idUsuario').value;

        function actualizarMensajes() {
            $.ajax({
                type: 'GET',
                url: 'http://127.0.0.1/ticket/api/obtenerMensajes',
                data: { id_usuario: usuarioId },
                success: function(data) {
                    $('#contendorMensajes').html(data);
                }
            });
        }

        actualizarMensajes();
        setInterval(actualizarMensajes, 3000);
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const usuarioId = document.getElementById('idUsuario').value;

        function actualizarRecordatorio() {
            $.ajax({
                type: 'GET',
                url: 'http://127.0.0.1/ticket/api/obtenerRecordatorios',
                data: { id_usuario: usuarioId },
                success: function(data) {
                    $('#contendorTicket').html(data);
                }
            });
        }

        actualizarRecordatorio();
        setInterval(actualizarRecordatorio, 3000);
    });
    </script>

    <script>
    function toggleSubmenuColor(elemento) {
        const categoria = elemento.getAttribute('data-id_categoria');
        const esActivo = elemento.classList.contains('green');

        if (!esActivo) {
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
