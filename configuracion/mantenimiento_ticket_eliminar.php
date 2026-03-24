<?php
session_start();
require_once '../class/conexion.php';
require_once '../class/funciones.php';

$funciones = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$idPagActual = 7;
$db = new MySQL("", "", "");
$resultado = $funciones->ticketAdministrador($idUsuarioSession);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php $funciones->header(); ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mantenimiento Tickets | Eliminar</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/estilo.css">
    <link rel="stylesheet" href="../css/menuLateral.css">
    <link rel="stylesheet" href="css/mantenimiento_tickets.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                                            <th>ID</th>
                                            <th>Fecha / Hora</th>
                                            <th>De</th>
                                            <th>Asunto</th>
                                            <th>Estado</th>
                                            <th>Tecnico</th>
                                            <th>Opciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $resultado->fetch_array()): ?>
                                            <?php
                                                $nombreUsuario = trim(($row['nombreUsuario'] ?? '') . ' ' . ($row['apellidoUsuario'] ?? ''));
                                                $nombreTecnico = trim(($row['nombreTecnico'] ?? '') . ' ' . ($row['apellidoTecnico'] ?? ''));
                                                $fechaCreada = !empty($row['fecha_creacion_inicio']) ? date('d-m-Y', strtotime($row['fecha_creacion_inicio'])) : '--';
                                                $horaCreada = !empty($row['hora_creacion_inicio']) ? htmlspecialchars($row['hora_creacion_inicio']) : '--';
                                            ?>
                                            <tr>
                                                <td>#<?= (int)$row['id_ticket']; ?></td>
                                                <td>
                                                    <div class="ticket-maint-date">
                                                        <strong><?= $fechaCreada; ?></strong>
                                                        <span><?= $horaCreada; ?></span>
                                                    </div>
                                                </td>
                                                <td><?= htmlspecialchars($nombreUsuario ?: 'Sin usuario'); ?></td>
                                                <td><?= htmlspecialchars($row['asunto'] ?? 'Sin asunto'); ?></td>
                                                <td>
                                                    <span class="ticket-maint-badge" style="--estado-color: <?= htmlspecialchars($row['colorEstado'] ?? '#d9e4f0'); ?>">
                                                        <?= htmlspecialchars($row['nombreEstado'] ?? 'Sin estado'); ?>
                                                    </span>
                                                </td>
                                                <td><?= htmlspecialchars($nombreTecnico ?: 'Sin tecnico'); ?></td>
                                                <td>
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

    <script src="js/mantenimiento_ticket_eliminar.js"></script>
</body>
</html>
