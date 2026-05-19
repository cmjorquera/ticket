<?php
session_start();
require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';

$funciones        = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$idPagActual      = 7;
$resultado        = $funciones->ticketAdministrador($idUsuarioSession);
$assetPrefix      = (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/configuracion/') !== false) ? '../' : '';
$tituloPagina     = 'Mantenimiento Tickets | Eliminar';
?>
<?php require __DIR__ . '/componentes/head.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo $assetPrefix; ?>js/buscadores.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/funciones.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/permisos.js"></script>
    <script>
        window.CONFIG_RELATIVE_ROOT = '<?php echo $assetPrefix; ?>';
    </script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/comun.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/comunes.js"></script>

    <style>
        /* ── Tabla ── */
        #tablaMantenimientoEliminar { width: 100% !important; }
        #tablaMantenimientoEliminar th,
        #tablaMantenimientoEliminar td { vertical-align: middle; }
        #tablaMantenimientoEliminar th.col-id    { width: 60px !important; }
        #tablaMantenimientoEliminar th.col-fecha { width: 120px !important; }
        #tablaMantenimientoEliminar th.col-opc   { width: 110px !important; white-space: nowrap; }
        #tablaMantenimientoEliminar td.celda-opc { white-space: nowrap; }

        /* ── Estado chip (heredado del sistema) ── */
        .ticket-resumen-estado          { display: inline-flex; align-items: center; gap: .45rem; }
        .ticket-resumen-estado__dot     { width: 10px; height: 10px; border-radius: 50%; flex: 0 0 10px; }
        .ticket-resumen-estado__titulo  { font-size: .82rem; font-weight: 700; line-height: 1.2; }
        .ticket-resumen-estado__detalle { font-size: .72rem; color: #6c757d; line-height: 1.2; }

        /* ── Botón eliminar elegante (mismo estilo que lu-btn) ── */
        .btn-eliminar-ticket {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .3rem;
            height: 34px;
            padding: 0 .85rem;
            border-radius: 9px;
            border: none;
            background: #e74a3b;
            color: #fff;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform .12s, box-shadow .12s, background .12s;
            white-space: nowrap;
        }
        .btn-eliminar-ticket:hover {
            background: #c0392b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231,74,59,.3);
            color: #fff;
        }
        .btn-eliminar-ticket:active { transform: none; box-shadow: none; }

        /* ── Modal preview ── */
        #ticketDeletePreview:empty { display: none; }

        #ticketDeletePreview .list-group-item {
            display: grid !important;
            grid-template-columns: minmax(0,1fr) auto;
            align-items: center;
            gap: .75rem;
        }
        #ticketDeletePreview .badge {
            min-width: 64px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            padding: .4rem .6rem;
        }

        .ticket-delete-modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: .75rem;
            padding: 1rem 1.5rem 1.5rem;
        }
    </style>
</head>

<body id="page-top">
    <input type="hidden" id="idUsuario" value="<?php echo (int)$idUsuarioSession; ?>">

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
                            <div>
                                <h6 class="m-0 font-weight-bold text-danger">
                                    <i class="bi bi-trash3-fill me-2"></i>Eliminar tickets
                                </h6>
                                <p class="mb-0 text-muted" style="font-size:.8rem;">
                                    Eliminación administrativa. Requiere código de verificación por correo.
                                </p>
                            </div>
                            <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-left me-1"></i>Volver
                            </a>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="tablaMantenimientoEliminar"
                                       class="table table-bordered table-hover table-striped align-middle mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="col-id">ID</th>
                                            <th class="col-fecha">Fecha / Hora</th>
                                            <th>De</th>
                                            <th>Asunto</th>
                                            <th>Estado</th>
                                            <th>Técnico</th>
                                            <th class="col-opc text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php while ($row = $resultado->fetch_array()): ?>
                                    <?php
                                        $nombreUsuario  = trim(($row['nombreUsuario']  ?? '') . ' ' . ($row['apellidoUsuario']  ?? ''));
                                        $nombreTecnico  = trim(($row['nombreTecnico']  ?? '') . ' ' . ($row['apellidoTecnico']  ?? ''));
                                        $fechaCreada    = !empty($row['fecha_creacion_inicio']) ? date('d-m-Y', strtotime($row['fecha_creacion_inicio'])) : '--';
                                        $horaCreada     = !empty($row['hora_creacion_inicio'])  ? htmlspecialchars($row['hora_creacion_inicio']) : '--';
                                        $colorEstado    = htmlspecialchars($row['colorEstado'] ?? '#cbd5e1');
                                        $estadoNombre   = htmlspecialchars($row['nombreEstado'] ?? 'Sin estado');
                                        $idTicket       = (int)$row['id_ticket'];
                                    ?>
                                    <tr>
                                        <td><?= $idTicket; ?></td>
                                        <td>
                                            <div style="font-size:.82rem;font-weight:600;line-height:1.2;"><?= $fechaCreada; ?></div>
                                            <div style="font-size:.72rem;color:#6c757d;"><?= $horaCreada; ?></div>
                                        </td>
                                        <td style="font-size:.83rem;"><?= htmlspecialchars($nombreUsuario ?: 'Sin usuario'); ?></td>
                                        <td style="font-size:.83rem;"><?= htmlspecialchars($row['asunto'] ?? 'Sin asunto'); ?></td>
                                        <td>
                                            <div class="ticket-resumen-estado">
                                                <span class="ticket-resumen-estado__dot"
                                                      style="background:<?= $colorEstado; ?>;"></span>
                                                <div>
                                                    <div class="ticket-resumen-estado__titulo"><?= $estadoNombre; ?></div>
                                                    <div class="ticket-resumen-estado__detalle">Estado actual</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="ticket-resumen-estado">
                                                <span class="ticket-resumen-estado__dot"
                                                      style="background:<?= $colorEstado; ?>;"></span>
                                                <div>
                                                    <div class="ticket-resumen-estado__titulo"><?= htmlspecialchars($nombreTecnico ?: 'Sin técnico'); ?></div>
                                                    <div class="ticket-resumen-estado__detalle">Responsable</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="celda-opc text-center">
                                            <button type="button"
                                                    class="btn-eliminar-ticket"
                                                    onclick="abrirEliminarTicket(<?= $idTicket; ?>)">
                                                <i class="bi bi-trash3"></i>Eliminar
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

                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>

    <!-- ── Modal Eliminar Ticket ─────────────────────────────────────────── -->
    <div class="modal fade" id="modalEliminarTicketMantenimiento" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">

                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-trash3-fill me-2"></i>Eliminar ticket
                        <span id="modalTicketIdLabel" class="fw-normal ms-1" style="font-size:.9rem;opacity:.85;"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <!-- ID oculto — no editable por el usuario -->
                    <input type="hidden" id="ticketDeleteId">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="ticketDeleteTipo" class="form-label fw-bold">
                                <i class="bi bi-sliders me-1 text-danger"></i>Tipo de eliminación
                            </label>
                            <select class="form-select" id="ticketDeleteTipo">
                                <option value="completa">Eliminación completa</option>
                                <option value="ocultar">Solo ocultar del sistema</option>
                            </select>
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="alert alert-warning mb-0 py-2 px-3 w-100" style="font-size:.8rem;">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Esta acción requiere código de verificación por correo.
                            </div>
                        </div>
                        <div class="col-12">
                            <label for="ticketDeleteMotivo" class="form-label fw-bold">
                                <i class="bi bi-card-text me-1 text-secondary"></i>Justificación administrativa
                            </label>
                            <textarea class="form-control" id="ticketDeleteMotivo" rows="2"
                                      placeholder="Ej: ticket duplicado, prueba interna o error de creación."></textarea>
                        </div>

                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-outline-danger" id="btnRevisarEliminarTicket">
                                <i class="bi bi-search me-1"></i>Revisar impacto
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="btnGuardarBorradorEliminar">
                                <i class="bi bi-clock-history me-1"></i>Guardar borrador
                            </button>
                        </div>

                        <!-- Preview del impacto -->
                        <div class="col-12">
                            <div id="ticketDeletePreview"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer ticket-delete-modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarEliminarTicket" disabled>
                        <i class="bi bi-trash3 me-1"></i>Eliminar ticket
                    </button>
                </div>

            </div>
        </div>
    </div>

    <script type="text/javascript" src="<?php echo $assetPrefix; ?>template_01/js/funciones.js"></script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/mantenimiento_ticket_eliminar.js"></script>

    <script>
    // ── DataTable ────────────────────────────────────────────────────────────
    $(function () {
        if ($.fn.DataTable.isDataTable('#tablaMantenimientoEliminar')) {
            $('#tablaMantenimientoEliminar').DataTable().destroy();
        }
        $('#tablaMantenimientoEliminar').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[0, 'desc']],
            autoWidth: false,
            columnDefs: [{ orderable: false, targets: [6] }]
        });
    });

    // ── abrirEliminarTicket — sobreescribe la versión del JS externo
    //    para usar el input hidden y mostrar el ID en el título del modal ────
    function abrirEliminarTicket(idTicket) {
        const modalEl  = document.getElementById('modalEliminarTicketMantenimiento');
        const inputId  = document.getElementById('ticketDeleteId');
        const label    = document.getElementById('modalTicketIdLabel');
        const preview  = document.getElementById('ticketDeletePreview');
        const btnConf  = document.getElementById('btnConfirmarEliminarTicket');
        const motivo   = document.getElementById('ticketDeleteMotivo');

        if (!modalEl || !inputId) return;

        // Limpiar estado anterior
        inputId.value      = idTicket;
        if (label)   label.textContent   = `#${idTicket}`;
        if (preview) { preview.innerHTML = ''; }
        if (btnConf) btnConf.disabled    = true;
        if (motivo)  motivo.value        = '';
        document.getElementById('ticketDeleteTipo').value = 'completa';

        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    // ── Parchar obtenerRutaEliminarTicket para usar CONFIG_RELATIVE_ROOT ────
    // La función original en el .js externo usa '../modelos/...' hardcodeado.
    // La redefinimos aquí (cargada después) con la ruta dinámica.
    function obtenerRutaEliminarTicket() {
        const root = (typeof window.CONFIG_RELATIVE_ROOT === 'string' ? window.CONFIG_RELATIVE_ROOT : '');
        return root + 'modelos/guardar/mantenimiento_ticket_eliminar.php';
    }
    </script>
</body>
</html>