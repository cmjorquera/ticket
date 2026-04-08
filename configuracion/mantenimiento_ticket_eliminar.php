<?php
session_start();
require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';

$funciones = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$idPagActual = 7;
$db = new MySQL("", "", "");
$resultado = $funciones->ticketAdministrador($idUsuarioSession);
$assetPrefix = (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/configuracion/') !== false) ? '../' : '';
$tituloPagina = 'Mantenimiento Tickets | Eliminar';
?>
<?php require __DIR__ . '/componentes/head.php'; ?>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="<?php echo $assetPrefix; ?>js/buscadores.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/funciones.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/permisos.js"></script>
    <script>
        window.CONFIG_RELATIVE_ROOT = '<?php echo $assetPrefix; ?>';
    </script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/comun.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/comunes.js"></script>
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
                    <div class="ticket-maint-page">
                        <div class="ticket-maint-page__hero">
                            <div>
                                <div class="ticket-maint-page__eyebrow">Mantenimiento de tickets</div>
                                <h1 class="ticket-maint-page__title">Eliminar tickets</h1>
                                <p class="ticket-maint-page__text">
                                    Revisa el listado completo y prepara una eliminacion administrativa sin tocar componentes compartidos.
                                </p>
                            </div>
                            <div class="ticket-maint-page__actions">
                                <a href="index.php" class="btn btn-outline-primary ticket-maint-pill">
                                    <i class="bi bi-arrow-left me-1"></i>Volver a permisos
                                </a>
                            </div>
                        </div>

                        <div class="ticket-maint-card">
                            <div class="ticket-maint-card__header">
                                <div>
                                    <h2>Listado general de tickets</h2>
                                    <p>Selecciona un ticket y usa el boton rojo para revisar su eliminacion.</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="tablaMantenimientoEliminar" class="table table-bordered table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th class="col-id">ID</th>
                                            <th class="col-fecha-hora">Fecha / Hora</th>
                                            <th class="col-de">De</th>
                                            <th class="col-asunto">Asunto</th>
                                            <th class="col-estado">Estado</th>
                                            <th class="col-tecnico">Tecnico</th>
                                            <th class="col-opciones">Opciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $resultado->fetch_array()): ?>
                                            <?php
                                                $nombreUsuario = trim(($row['nombreUsuario'] ?? '') . ' ' . ($row['apellidoUsuario'] ?? ''));
                                                $nombreTecnico = trim(($row['nombreTecnico'] ?? '') . ' ' . ($row['apellidoTecnico'] ?? ''));
                                                $fechaCreada = !empty($row['fecha_creacion_inicio']) ? date('d-m-Y', strtotime($row['fecha_creacion_inicio'])) : '--';
                                                $horaCreada = !empty($row['hora_creacion_inicio']) ? htmlspecialchars($row['hora_creacion_inicio']) : '--';
                                                $estadoColor = htmlspecialchars($row['colorEstado'] ?? '#d9e4f0');
                                                $estadoNombre = htmlspecialchars($row['nombreEstado'] ?? 'Sin estado');
                                            ?>
                                            <tr>
                                                <td class="celda-id"><?= (int)$row['id_ticket']; ?></td>
                                                <td class="celda-fecha-hora">
                                                    <div class="ticket-fecha-hora">
                                                        <span class="ticket-fecha-hora__fecha"><?= $fechaCreada; ?></span>
                                                        <span class="ticket-fecha-hora__hora"><?= $horaCreada; ?></span>
                                                    </div>
                                                </td>
                                                <td class="celda-de"><?= htmlspecialchars($nombreUsuario ?: 'Sin usuario'); ?></td>
                                                <td class="celda-asunto"><?= htmlspecialchars($row['asunto'] ?? 'Sin asunto'); ?></td>
                                                <td class="celda-estado">
                                                    <div class="ticket-resumen-estado">
                                                        <span class="ticket-resumen-estado__dot" style="background-color: <?= $estadoColor; ?>;"></span>
                                                        <div class="ticket-resumen-estado__body">
                                                            <div class="ticket-resumen-estado__titulo"><?= $estadoNombre; ?></div>
                                                            <div class="ticket-resumen-estado__detalle">Estado actual</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="celda-tecnico">
                                                    <div class="ticket-resumen-estado ticket-resumen-estado--tecnico">
                                                        <span class="ticket-resumen-estado__dot" style="background-color: <?= $estadoColor; ?>;"></span>
                                                        <div class="ticket-resumen-estado__body">
                                                            <div class="ticket-resumen-estado__titulo"><?= htmlspecialchars($nombreTecnico ?: 'Sin tecnico'); ?></div>
                                                            <div class="ticket-resumen-estado__detalle">Tecnico asignado</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="celda-opciones">
                                                    <button type="button" class="btn btn-danger ticket-maint-delete-btn" onclick="abrirEliminarTicket(<?= (int)$row['id_ticket']; ?>)">
                                                        <i class="bi bi-trash3"></i>
                                                        <span>Eliminar</span>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>

    <div class="modal fade" id="modalEliminarTicketMantenimiento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header ticket-maint-modal-header">
                    <div>
                        <div class="ticket-maint-page__eyebrow mb-1 text-white-50">Revision administrativa</div>
                        <h5 class="modal-title mb-0">Eliminar ticket</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="ticket-maint-delete-panel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="ticketDeleteId" class="form-label">Ticket a eliminar</label>
                                <input type="text" class="form-control" id="ticketDeleteId" placeholder="Ej: #1320">
                            </div>
                            <div class="col-md-6">
                                <label for="ticketDeleteTipo" class="form-label">Tipo de eliminacion</label>
                                <select class="form-select" id="ticketDeleteTipo">
                                    <option value="completa">Eliminacion completa</option>
                                    <option value="ocultar">Solo ocultar del sistema</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="ticketDeleteMotivo" class="form-label">Justificacion administrativa</label>
                                <textarea class="form-control" id="ticketDeleteMotivo" rows="3" placeholder="Ej: ticket duplicado, prueba interna o error de creacion."></textarea>
                            </div>
                        </div>

                        <div class="ticket-maint-delete-actions">
                            <button type="button" class="btn btn-danger ticket-maint-pill" id="btnRevisarEliminarTicket">
                                <i class="bi bi-exclamation-triangle me-1"></i>Revisar antes de eliminar
                            </button>
                            <button type="button" class="btn btn-outline-secondary ticket-maint-pill" id="btnGuardarBorradorEliminar">
                                <i class="bi bi-clock-history me-1"></i>Guardar como borrador
                            </button>
                        </div>

                        <div class="ticket-maint-preview" id="ticketDeletePreview"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type='text/javascript' src='<?php echo $assetPrefix; ?>template_01/js/funciones.js'></script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/mantenimiento_ticket_eliminar.js"></script>
</body>
</html>
