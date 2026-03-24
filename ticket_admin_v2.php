<?php
session_start();
require_once 'class/conexion.php';
require_once 'class/funciones.php';

$funciones = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$nombresession = htmlspecialchars($_SESSION['nombre']) . '--' . htmlspecialchars($_SESSION['apellido_paterno']);
$idPagActual = 4;
$versionModalesTicket = @filemtime(__DIR__ . '/css/modalesTicket.css') ?: time();
$versionTicketAdminCss = @filemtime(__DIR__ . '/css/ticket_admin.css') ?: time();
$versionTicketJs = @filemtime(__DIR__ . '/js/ticket.js') ?: time();
?>
<script>
var nombresession = <?php echo json_encode($nombresession); ?>;
var idUsuarioSession = <?php echo json_encode($idUsuarioSession); ?>;
</script>

<!DOCTYPE html>
<html lang="en">
<head>
<?php $funciones->header(); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="css/contenedor_estados.css" rel="stylesheet">
    <link href="css/modalesTicket.css?v=<?php echo $versionModalesTicket; ?>" rel="stylesheet">
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/cronologiaTicket.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <link href="css/ticket_admin.css?v=<?php echo $versionTicketAdminCss; ?>" rel="stylesheet">
    <script type="text/javascript" src="js/chat_ticket.js"></script>
    <script type="text/javascript" src="js/funciones.js"></script>
    <script type="text/javascript" src="js/buscadores.js"></script>
    <script type="text/javascript" src="js/toast.js"></script>
    <script type="text/javascript" src="js/cronologiaTicket.js"></script>
    <script type="text/javascript" src="js/ticket.js?v=<?php echo $versionTicketJs; ?>"></script>
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
                <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasAsunto" aria-labelledby="offcanvasAsuntoLabel">
                  <div class="offcanvas-header bg-light border-bottom shadow-sm" style="background-color: #f5f7fa;">
                    <h5 class="offcanvas-title text-primary fw-bold" id="offcanvasAsuntoLabel">
                      <i class="bi bi-chat-left-text me-2"></i>Detalle del Asunto
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
                  </div>
                  <div class="offcanvas-body p-4" id="contenidoOffcanvasAsunto" style="background-color: #f5f7fa;"></div>
                </div>
                <div class="container-fluid">
                    <div class="contenedor-estados-ticket" id="idContenedoresEstadosTicket">
                        <?php
                            $funciones->contenedorTicketRecibidos($idUsuarioSession, $idPagActual);
                            $funciones->contenedorTicketAsignados($idUsuarioSession, $idPagActual);
                            $funciones->contenedorTicketEnProceso($idUsuarioSession, $idPagActual);
                            $funciones->contenedorTicketTerminados($idUsuarioSession, $idPagActual);
                            $funciones->contenedorTicketDemorados($idUsuarioSession, $idPagActual);
                        ?>
                    </div><br>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                           <h6 class="m-0 font-weight-bold text-primary">Tickets Administrador</h6>
                        </div>
                        <div class="card-body" id="contenedorTablaAdmin">
                            <?php
                                $GLOBALS['ticketAdminColorColumn'] = true;
                                include('componentes/bloque_tabla_admin.php');
                            ?>
                        </div>
                    </div>
                    <?php $funciones->footer(); ?>
                </div>
            </div>
        </div>
        <a class="scroll-to-top rounded" href="#page-top">
            <i class="fas fa-angle-up"></i>
        </a>
    </div>
    <?php include('modal_salir.php')?>
    <?php $funciones->script(); ?>

    <script>
    function asignacionTecnico(id_ticket) {
        $.ajax({
            url: 'modelos/rescatar/ticket.php',
            type: 'POST',
            data: { id: id_ticket },
            dataType: 'json',
            success: function (data) {
                if (!data) return;
                let nombreUsuario = data.nombre_usuario || 'No disponible';
                let apePaternoUsuario = data.apellido_paterno_usuario || '';
                let asunto = data.asunto || 'Sin asunto';
                let descripcion_ticket = data.descripcion_ticket || 'Sin descripcion';
                let nombre_categoria = data.nombre_categoria || 'Sin categoria';
                $.ajax({
                    url: 'modelos/rescatar/tecnicos.php',
                    type: 'POST',
                    dataType: 'json',
                    success: function (usuarios) {
                        let htmlAvatares = '';
                        usuarios.forEach(user => {
                            const nombreCompleto = `${user.nombre} ${user.apellido_paterno}`;
                            htmlAvatares += `<div class="user-avatar-item text-center p-2 rounded border" style="cursor:pointer;transition:all .2s ease-in-out;width:80px;" data-id="${user.id}"><img src="img/undraw_profile.svg" alt="Avatar" class="rounded-circle mb-2" style="width:40px;height:40px;object-fit:cover;"><span style="font-size:11px;display:block">${nombreCompleto}</span></div>`;
                        });
                        setTimeout(() => {
                            $('#contenedorAvatares').html(htmlAvatares);
                            $('.user-avatar-item').on('click', function () {
                                $('.user-avatar-item').removeClass('bg-primary text-white').css('opacity', '0.5');
                                $(this).addClass('bg-primary text-white').css('opacity', '1');
                                const userId = $(this).data('id');
                                $('#tecnicoSeleccionado').val(userId).trigger('change');
                                const nombreSeleccionado = $(this).text().trim();
                                $('#badgeResponsable').removeClass('d-none').html(`<i class='fas fa-user'></i> ${nombreSeleccionado}`);
                            });
                        }, 100);
                        const html = `<div class="contenedor-badges d-flex gap-2 justify-content-center mb-3 flex-wrap"><span class="insignia_ticket bg-primary text-white"><i class="fas fa-layer-group"></i> ${nombre_categoria}</span><span id="badgeResponsable" class="insignia_ticket bg-success text-white d-none"><i class="fas fa-user"></i> Tecnico</span></div><form class="row g-3"><div class="col-md-6"><div class="form-floating mb-3"><input type="text" class="form-control" id="nombreUsuarioProblema" value="${nombreUsuario} ${apePaternoUsuario}" disabled><label for="nombreUsuarioProblema">De</label></div></div><div class="col-md-6"><div class="form-floating mb-3"><input type="text" class="form-control" id="asuntoTicket" value="${asunto}" disabled><label for="asuntoTicket">Asunto</label></div></div><div class="col-md-6"><div class="form-floating mb-3"><select class="form-select" id="categoriaTicket"><option value="">Cargando categorias...</option></select><label for="categoriaTicket">Categoria</label></div></div><div class="col-md-6"><div id="contenedorAvatares" class="d-flex flex-wrap gap-3 justify-content-start p-2" style="max-height:100px;overflow-y:auto;"></div></div><div class="col-md-12"><div class="form-floating mb-3"><textarea class="form-control" id="descripcionTicket" disabled style="height:120px;">${descripcion_ticket}</textarea><label for="descripcionTicket">Descripcion</label></div></div><div class="col-md-12"><div class="form-floating"><textarea class="form-control" id="comentarioTicket" placeholder="Comentario al Tecnico" style="height:120px;"></textarea><label for="comentarioTicket">Comentario al Tecnico</label></div></div><input type="hidden" id="tecnicoSeleccionado"></form>`;
                        Swal.fire({
                            title: '<div class="alert alert-dark">ASIGNAR TECNICO</div>',
                            html: html,
                            showCancelButton: true,
                            confirmButtonText: 'Asignar',
                            cancelButtonText: 'Cancelar',
                            width: '1000px',
                            customClass: { popup: 'cuerpo_modal_guardar', confirmButton: 'bt_crear', cancelButton: 'bt_eliminar' },
                            didOpen: () => {
                                $.ajax({
                                    url: 'modelos/rescatar/categoria_de_ticket.php',
                                    type: 'POST',
                                    dataType: 'json',
                                    success: function (categorias) {
                                        const $select = $('#categoriaTicket');
                                        $select.html('<option value="">Seleccionar categoria</option>');
                                        categorias.forEach(cat => {
                                            $select.append(`<option value="${cat.id}">${cat.nombre_categoria}</option>`);
                                        });
                                        $select.on('change', function () {
                                            const categoriaId = $(this).val();
                                            const selected = $(this).find('option:selected').text();
                                            $('.insignia_ticket.bg-primary').html(`<i class='fas fa-layer-group'></i> ${selected}`);
                                            $.ajax({
                                                url: 'modelos/rescatar/tecnico_por_categoria.php',
                                                method: 'POST',
                                                data: { id_categoria: categoriaId },
                                                dataType: 'json',
                                                success: function (data) {
                                                    if (data.success && data.id_tecnico) {
                                                        const tecnicoId = data.id_tecnico;
                                                        $('#tecnicoSeleccionado').val(tecnicoId);
                                                        $('.user-avatar-item').removeClass('bg-primary text-white').css('opacity', '0.5');
                                                        $(`.user-avatar-item[data-id="${tecnicoId}"]`).addClass('bg-primary text-white').css('opacity', '1');
                                                        const nombreSeleccionado = $(`.user-avatar-item[data-id="${tecnicoId}"]`).text().trim();
                                                        $('#badgeResponsable').removeClass('d-none').html(`<i class='fas fa-user'></i> ${nombreSeleccionado}`);
                                                    }
                                                }
                                            });
                                        });
                                    }
                                });
                            },
                            preConfirm: () => {
                                const responsable = $('#tecnicoSeleccionado').val();
                                const comentario = $('#comentarioTicket').val();
                                const categoria = $('#categoriaTicket').val();
                                if (!responsable || !categoria) {
                                    Swal.showValidationMessage('Debe seleccionar tecnico y categoria');
                                    return false;
                                }
                                return { responsable, comentario, categoria, id_ticket };
                            }
                        }).then(result => {
                            if (result.isConfirmed) {
                                const datos = result.value;
                                $.ajax({
                                    url: 'modelos/guardar/guardar_responsable_ticket.php',
                                    type: 'POST',
                                    data: { id_responsable: datos.responsable, comentario: datos.comentario, categoria: datos.categoria, idTicket: datos.id_ticket },
                                    success: function () {
                                        Swal.fire({ icon: 'success', title: 'Tecnico asignado', timer: 2000, showConfirmButton: false });
                                    }
                                });
                            }
                        });
                    }
                });
            }
        });
    }
    </script>

    <script>
    $(document).ready(function () {
        let tablaAdmin = null;
        let filtroActivo = false;
        let estadoFiltrado = null;
        let ultimaTablaAdminHtml = null;
        let actualizacionTablaEnCurso = false;

        if (!window.adminTableFiltersSearchRegistered) {
            $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
                if (!settings.nTable || settings.nTable.id !== 'dataTableAdministrador') {
                    return true;
                }

                const api = new $.fn.dataTable.Api(settings);
                const rowNode = api.row(dataIndex).node();
                if (!rowNode) {
                    return true;
                }

                const estadoFiltro = ($('#filtroEstadoAdmin').val() || '').trim().toLowerCase();
                const fechaFiltro = ($('#filtroFechaAdmin').val() || '').trim();
                const tecnicoFiltro = ($('#filtroTecnicoAdmin').val() || '').trim();
                const fechaRespuestaFiltro = ($('#filtroFechaRespuestaAdmin').val() || '').trim();

                const estadoFila = (rowNode.dataset.estado || '').trim().toLowerCase();
                const fechaFila = (rowNode.dataset.fechaCreacion || '').trim();
                const tecnicoFila = (rowNode.dataset.tecnicoId || '').trim();
                const fechaRespuestaFila = (rowNode.dataset.fechaRespuesta || '').trim();

                if (estadoFiltro && estadoFila !== estadoFiltro) {
                    return false;
                }

                if (fechaFiltro && fechaFila !== fechaFiltro) {
                    return false;
                }

                if (tecnicoFiltro) {
                    if (tecnicoFiltro === '__sin_asignar__' && tecnicoFila !== '') {
                        return false;
                    }

                    if (tecnicoFiltro !== '__sin_asignar__' && tecnicoFila !== tecnicoFiltro) {
                        return false;
                    }
                }

                if (fechaRespuestaFiltro && fechaRespuestaFila !== fechaRespuestaFiltro) {
                    return false;
                }

                return true;
            });

            window.adminTableFiltersSearchRegistered = true;
        }

        function configurarFiltrosTablaAdmin() {
            const $tabla = $('#dataTableAdministrador');
            if (!$tabla.length || !tablaAdmin) {
                return;
            }

            if ($tabla.data('admin-filters-bound') === '1') {
                tablaAdmin.draw();
                return;
            }

            $('#filtroEstadoAdmin, #filtroFechaAdmin, #filtroTecnicoAdmin, #filtroFechaRespuestaAdmin')
                .off('.adminFilters')
                .on('change.adminFilters input.adminFilters', function () {
                    tablaAdmin.draw();
                });

            $tabla.data('admin-filters-bound', '1');
            tablaAdmin.draw();
        }

        function inicializarTablaAdmin() {
            if ($.fn.DataTable.isDataTable('#dataTableAdministrador')) {
                tablaAdmin = $('#dataTableAdministrador').DataTable();
                configurarFiltrosTablaAdmin();
                return;
            }

            tablaAdmin = $('#dataTableAdministrador').DataTable({
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                responsive: true,
                pageLength: 10,
                paging: true,
                pagingType: 'simple_numbers'
            });

            configurarFiltrosTablaAdmin();
        }

        function mostrarEstrellas(id_ticket, contenedorID) {
            const el = document.getElementById(contenedorID);
            if (!el) return;
            fetch('modelos/rescatar/calificacionUsuario.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
                body: new URLSearchParams({ id_ticket })
            }).then(async (res) => {
                if (!res.ok) throw new Error('HTTP ' + res.status);
                const ct = res.headers.get('content-type') || '';
                if (!ct.includes('application/json')) return { success: true, data: {} };
                return res.json();
            }).then((data) => {
                const estrellas = parseInt(data?.data?.id_calificacion ?? 0, 10) || 0;
                if (estrellas > 0) {
                    let html = '';
                    for (let i = 1; i <= 4; i++) html += `<i class="${i <= estrellas ? 'fas text-warning' : 'far text-muted'} fa-star"></i>`;
                    el.innerHTML = html;
                } else {
                    el.innerHTML = `<span class="estado-badge estado-azul"><i class="fas fa-ban me-1"></i>Sin calificacion</span>`;
                }
            }).catch(() => {
                el.innerHTML = `<span class="estado-badge estado-azul"><i class="fas fa-ban me-1"></i>Sin calificacion</span>`;
            });
        }

        function renderizarCalificacionesTablaAdmin() {
            document.querySelectorAll('.contenedor-estrellas-admin').forEach((contenedor) => {
                const idTicket = contenedor.dataset.ticketId;
                if (!idTicket || !contenedor.id) return;
                mostrarEstrellas(idTicket, contenedor.id);
            });
        }

        function actualizarTablaAdministrador() {
            if (actualizacionTablaEnCurso) return;
            actualizacionTablaEnCurso = true;
            const datos = { idUsuarioSession: <?= (int) $_SESSION['id']; ?>, vista: 'ticket_admin' };
            if (filtroActivo && estadoFiltrado !== null) datos.estado = estadoFiltrado;
            $.post('componentes/ajax/bloque_tabla_admin.php', datos, function (data) {
                const nuevasFilas = $('<div>').html(data).find('#dataTableAdministrador tbody').html();
                const htmlNormalizado = (nuevasFilas || '').replace(/\s+/g, ' ').trim();
                if (ultimaTablaAdminHtml !== htmlNormalizado) {
                    inicializarTablaAdmin();
                    const filasNuevas = $('<table><tbody>' + nuevasFilas + '</tbody></table>').find('tbody tr').toArray();
                    tablaAdmin.clear();
                    tablaAdmin.rows.add(filasNuevas);
                    tablaAdmin.draw(false);
                    ultimaTablaAdminHtml = htmlNormalizado;
                    renderizarCalificacionesTablaAdmin();
                }
            }).always(function () {
                actualizacionTablaEnCurso = false;
            });
        }

        inicializarTablaAdmin();
        ultimaTablaAdminHtml = ($('#dataTableAdministrador tbody').html() || '').replace(/\s+/g, ' ').trim();
        actualizarTablaAdministrador();
        setInterval(actualizarTablaAdministrador, 3000);
        renderizarCalificacionesTablaAdmin();

        window.filtrarTickets = function (estado) {
            if (filtroActivo && estadoFiltrado === estado) {
                filtroActivo = false;
                estadoFiltrado = null;
            } else {
                filtroActivo = true;
                estadoFiltrado = estado;
            }
            const datos = { idUsuarioSession: <?= (int) $_SESSION['id']; ?>, vista: 'ticket_admin' };
            if (filtroActivo && estadoFiltrado !== null) datos.estado = estadoFiltrado;
            $.post('componentes/ajax/bloque_tabla_admin.php', datos, function (data) {
                const nuevasFilas = $('<div>').html(data).find('#dataTableAdministrador tbody').html();
                inicializarTablaAdmin();
                const filasNuevas = $('<table><tbody>' + nuevasFilas + '</tbody></table>').find('tbody tr').toArray();
                tablaAdmin.clear();
                tablaAdmin.rows.add(filasNuevas);
                tablaAdmin.draw(false);
                ultimaTablaAdminHtml = (nuevasFilas || '').replace(/\s+/g, ' ').trim();
                renderizarCalificacionesTablaAdmin();
            });
        };

        window.mostrarTodosLosTickets = function () {
            filtroActivo = false;
            estadoFiltrado = null;
            actualizarTablaAdministrador();
        };
    });
    </script>

    <script>
    function mostrarOffcanvasAsunto(idTicket) {
      const myOffcanvas = new bootstrap.Offcanvas(document.getElementById('offcanvasAsunto'));
      myOffcanvas.show();
      $.ajax({
        url: 'modelos/rescatar/asunto_del_ticket.php',
        type: 'POST',
        data: { id_ticket: idTicket },
        beforeSend: function () {
          $('#contenidoOffcanvasAsunto').html('<div class="text-center text-muted">Cargando...</div>');
        },
        success: function (data) {
          $('#contenidoOffcanvasAsunto').html(data);
        },
        error: function () {
          $('#contenidoOffcanvasAsunto').html('<div class="text-danger">Error al cargar la informacion</div>');
        }
      });
    }
    </script>
</body>
</html>
