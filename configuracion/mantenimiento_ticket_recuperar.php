<?php
session_start();
require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';

$funciones = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$idPagActual = 7;
$db = new MySQL("", "", "");
$resultado = $funciones->ticketAdministrador($idUsuarioSession, 2);
$assetPrefix = (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/configuracion/') !== false) ? '../' : '';
$tituloPagina = 'Mantenimiento Tickets | Eliminados';
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
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="m-0 font-weight-bold text-primary">Tickets eliminados</h6>
                            <a href="mantenimiento_ticket_eliminar.php" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Volver a eliminar tickets
                            </a>
                        </div>
                        <div class="card-body">
                            <p class="mb-4">Listado de tickets con estado eliminado. Puedes recuperar un ticket para devolverlo al estado activo.</p>

                            <div class="table-responsive">
                                <table id="tablaMantenimientoRecuperar" class="table table-bordered table-hover table-striped align-middle">
                                    <thead class="table-dark">
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
                                                $estadoNombre = htmlspecialchars($row['nombreEstado'] ?? 'Sin estado');
                                            ?>
                                            <tr class="fila-ticket-admin-compacta">
                                                <td class="celda-id"><?= (int)$row['id_ticket']; ?></td>
                                                <td class="celda-fecha-hora">
                                                    <div class="ticket-fecha-hora">
                                                        <div class="ticket-fecha-hora__fecha"><?= $fechaCreada; ?></div>
                                                        <div class="ticket-fecha-hora__hora"><?= $horaCreada; ?></div>
                                                    </div>
                                                </td>
                                                <td class="celda-de"><?= htmlspecialchars($nombreUsuario ?: 'Sin usuario'); ?></td>
                                                <td class="celda-asunto"><?= htmlspecialchars($row['asunto'] ?? 'Sin asunto'); ?></td>
                                                <td class="celda-estado">
                                                    <div class="ticket-resumen-estado">
                                                        <span class="ticket-resumen-estado__dot" style="background-color: <?= htmlspecialchars($row['colorEstado'] ?? '#cbd5e1'); ?>;"></span>
                                                        <div class="ticket-resumen-estado__body">
                                                            <div class="ticket-resumen-estado__titulo"><?= $estadoNombre; ?></div>
                                                            <div class="ticket-resumen-estado__detalle">Estado actual</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="celda-tecnico">
                                                    <div class="ticket-resumen-estado">
                                                        <span class="ticket-resumen-estado__dot" style="background-color: <?= htmlspecialchars($row['colorEstado'] ?? '#cbd5e1'); ?>;"></span>
                                                        <div class="ticket-resumen-estado__body">
                                                            <div class="ticket-resumen-estado__titulo"><?= htmlspecialchars($nombreTecnico ?: 'Sin tecnico'); ?></div>
                                                            <div class="ticket-resumen-estado__detalle">Responsable</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="celda-opciones">
                                                    <button type="button" class="btn btn-success btn-sm" onclick="recuperarTicket(<?= (int)$row['id_ticket']; ?>, '<?= htmlspecialchars(addslashes($row['asunto'] ?? 'Sin asunto')); ?>')">
                                                        <i class="bi bi-arrow-counterclockwise me-1"></i>Recuperar
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

    <script type='text/javascript' src='<?php echo $assetPrefix; ?>template_01/js/funciones.js'></script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/mantenimiento_ticket_recuperar.js"></script>
</body>
</html>
