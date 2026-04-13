<div class="ticket-admin-filtros px-3 pt-3">
    <div class="row g-3">
        <div class="col-md-4">
            <label for="filtroEstadoTecnico" class="form-label fw-semibold text-muted mb-1">Estado</label>
            <select id="filtroEstadoTecnico" class="form-select form-select-sm">
                <option value="">Todos</option>
                <option value="Asignado">Asignado</option>
                <option value="En proceso">En proceso</option>
                <option value="Terminado">Terminado</option>
                <option value="Demorado">Atrasado</option>
                <option value="Cerrado">Cerrado</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="filtroFechaTecnico" class="form-label fw-semibold text-muted mb-1">Fecha creación</label>
            <input type="date" id="filtroFechaTecnico" class="form-control form-control-sm">
        </div>
        <div class="col-md-4">
            <label for="filtroFechaRespuestaTecnico" class="form-label fw-semibold text-muted mb-1">Fecha respuesta</label>
            <input type="date" id="filtroFechaRespuestaTecnico" class="form-control form-control-sm">
        </div>
    </div>
</div>

<div class="table-responsive mt-3">
        <table id="tablaTecnicoTicketAsignados" class="table table-bordered table-hover table-striped w-100">
            <thead class="table-dark">
                <tr>
                    <th class="col-id">ID</th>
                    <th class="col-fecha-hora">FECHA / HORA</th>
                    <th class="col-de">DE</th>
                    <th class="col-asunto">ASUNTO</th>
                    <th class="col-estado">ESTADO</th>
                    <th class="col-fecha-respuesta">FECHA RESPUESTA</th>
                    <th class="col-dias-restantes">DIAS RESTANTES</th>
                    <th class="col-opciones">OPCIONES</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $estado = $GLOBALS['estadoTicketFiltro'] ?? null;

                if (!isset($idUsuarioSession)) {
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }
                    $idUsuarioSession = $_SESSION['id'] ?? null;
                }

                if (!$idUsuarioSession) {
                    echo "<tr><td colspan='8' class='text-danger'>ID del tecnico no definido.</td></tr>";
                    return;
                }

                $resultado = $funciones->obtenerTicketsTecnico($idUsuarioSession, $estado);
                $counter = 1;

                if ($resultado->num_rows > 0) {
                    while ($row = $resultado->fetch_array()) {
                        $nombreUsuarioProblema = trim(($row['nombreUsuario'] ?? '') . ' ' . ($row['apellidoUsuario'] ?? ''));
                        $totalDiasProceso = (int) ($row['dias_administrador_estima'] ?? 0);
                        $diasTranscurridosProceso = 0;
                        $diasRestantes = $totalDiasProceso;
                        $tieneRangoProceso = false;

                        if (
                            $totalDiasProceso > 0 &&
                            !empty($row['fecha_creacion_inicio']) &&
                            $row['fecha_creacion_inicio'] !== '0000-00-00' &&
                            !empty($row['fecha_estimada_admin']) &&
                            $row['fecha_estimada_admin'] !== '0000-00-00'
                        ) {
                            $tieneRangoProceso = true;
                            $fechaCreacion = new DateTime($row['fecha_creacion_inicio']);
                            $fechaActual = new DateTime(date('Y-m-d'));
                            if ($fechaActual < $fechaCreacion) {
                                $diasTranscurridosProceso = 0;
                            } else {
                                $diasTranscurridosProceso = $fechaActual->diff($fechaCreacion)->days;
                            }
                            $diasTranscurridosProceso = min($totalDiasProceso, $diasTranscurridosProceso);
                            $diasRestantes = max(0, $totalDiasProceso - $diasTranscurridosProceso);
                        }

                        $fechaCreada = !empty($row['fecha_creacion_inicio']) && $row['fecha_creacion_inicio'] !== '0000-00-00'
                            ? date('d-m-Y', strtotime($row['fecha_creacion_inicio']))
                            : '--';
                        $horaCreada = !empty($row['hora_creacion_inicio']) && $row['hora_creacion_inicio'] !== '00:00:00'
                            ? htmlspecialchars($row['hora_creacion_inicio'])
                            : '--';

                        $fechaRespuestaTitulo = !empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00'
                            ? date('d-m-Y', strtotime($row['fecha_estimada_admin']))
                            : '00-00-0000';
                        $fechaRespuestaDetalle = !empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00'
                            ? 'Fecha estimada'
                            : 'Sin fecha definida';

                        $diasTitulo = '';
                        $diasDetalle = '';
                        if (in_array((int) $row['id_estado'], [2, 3], true) && $tieneRangoProceso) {
                            $diasTitulo = htmlspecialchars((string) $diasTranscurridosProceso) . ' de ' . htmlspecialchars((string) $totalDiasProceso) . ' dias';
                            if ($diasRestantes === 1) {
                                $diasDetalle = 'Te queda 1 dia';
                            } elseif ($diasRestantes > 1) {
                                $diasDetalle = 'Te quedan ' . htmlspecialchars((string) $diasRestantes) . ' dias';
                            } else {
                                $diasDetalle = 'Plazo cumplido';
                            }
                        } elseif ((int) $row['id_estado'] === 5) {
                            if (!empty($row['tieneCalificacion'])) {
                                $diasTitulo = 'Usuario califico';
                                $diasDetalle = 'Ticket terminado';
                            } else {
                                $diasTitulo = 'Esperando calificacion';
                                $diasDetalle = 'Pendiente usuario';
                            }
                        } elseif ((int) $row['id_estado'] === 2) {
                            $diasTitulo = 'Debes comenzar';
                            $diasDetalle = 'Pendiente inicio';
                        } else {
                            if ($totalDiasProceso === 0) {
                                $diasTitulo = '00-00-0000';
                                $diasDetalle = 'Sin plazo definido';
                            } else {
                                $diasTitulo = htmlspecialchars((string) $totalDiasProceso) . ' dias';
                                $diasDetalle = 'Dias estimados';
                            }
                        }
                        $fechaCreacionIso = !empty($row['fecha_creacion_inicio']) && $row['fecha_creacion_inicio'] !== '0000-00-00'
                            ? date('Y-m-d', strtotime($row['fecha_creacion_inicio']))
                            : '';
                        $fechaRespuestaIso = !empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00'
                            ? date('Y-m-d', strtotime($row['fecha_estimada_admin']))
                            : '';
                        $tecnicoId = isset($row['id_tecnico']) && $row['id_tecnico'] !== null ? (int) $row['id_tecnico'] : '';
                        ?>
                        <tr class="fila-ticket-admin-compacta"
                            data-estado="<?= htmlspecialchars($row['nombreEstado'] ?? ''); ?>"
                            data-fecha-creacion="<?= htmlspecialchars($fechaCreacionIso); ?>"
                            data-tecnico-id="<?= htmlspecialchars((string) $tecnicoId); ?>"
                            data-fecha-respuesta="<?= htmlspecialchars($fechaRespuestaIso); ?>">
                            <td class="celda-id celda-id-con-indicador">
                                <span class="estado-indicador-dot estado-indicador-dot--inline" style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>"></span>
                                <span class="celda-id-numero"><?= $counter++; ?></span>
                            </td>
                            <td class="celda-fecha-hora">
                                <div class="ticket-fecha-hora">
                                    <div class="ticket-fecha-hora__fecha"><?= $fechaCreada; ?></div>
                                    <div class="ticket-fecha-hora__hora"><?= $horaCreada; ?></div>
                                </div>
                            </td>
                            <td class="celda-de"><?= htmlspecialchars($nombreUsuarioProblema); ?></td>
                            <td class="celda-asunto">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <span><?= htmlspecialchars($row['asunto']); ?></span>
                                    <a href="javascript:void(0);" onclick="mostrarOffcanvasAsunto(<?= (int) $row['id_ticket']; ?>)" title="Ver detalle">
                                        <i class="bi bi-info-circle-fill text-primary fs-5 ms-2"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="celda-estado">
                                <div class="ticket-resumen-estado">
                                    <span class="ticket-resumen-estado__dot" style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>;"></span>
                                    <div class="ticket-resumen-estado__body">
                                        <div class="ticket-resumen-estado__titulo"><?= htmlspecialchars($row['nombreEstado']); ?></div>
                                        <div class="ticket-resumen-estado__detalle">Estado actual del ticket</div>
                                    </div>
                                </div>
                            </td>
                            <td class="celda-fecha-respuesta">
                                <div class="ticket-resumen-estado">
                                    <span class="ticket-resumen-estado__dot" style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>;"></span>
                                    <div class="ticket-resumen-estado__body">
                                        <div class="ticket-resumen-estado__titulo"><?= htmlspecialchars($fechaRespuestaTitulo); ?></div>
                                        <div class="ticket-resumen-estado__detalle"><?= htmlspecialchars($fechaRespuestaDetalle); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="celda-dias-restantes">
                                <div class="ticket-resumen-estado">
                                    <span class="ticket-resumen-estado__dot" style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>;"></span>
                                    <div class="ticket-resumen-estado__body">
                                        <div class="ticket-resumen-estado__titulo"><?= $diasTitulo; ?></div>
                                        <div class="ticket-resumen-estado__detalle"><?= htmlspecialchars($diasDetalle); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="celda-opciones">
                                <div class="d-flex flex-nowrap align-items-center justify-content-start gap-1">
                                    <a href="#" class="btn btn-primary btn-icon-split me-2" onclick="modalTicketUsuario('<?= (int) $row['id_ticket']; ?>')" title="Ver ticket">
                                        <i class="bi bi-eye"></i>
                                    </a>

                                    <button type="button"
                                        class="btn btn-primary position-relative"
                                        onclick="cargarTicketConversacion('<?= (int) $row['id_ticket']; ?>', '<?= (int) $row['id_tecnico']; ?>', '<?= (int) $row['id_usuario']; ?>', <?= (int) $row['id_estado']; ?>)"
                                        title="Conversacion">
                                        <i class="bi bi-chat-dots"></i>
                                        <?php if ((int) $row['cantidadMensajes'] > 0): ?>
                                            <span class="position-absolute top-0 start-90 translate-middle badge rounded-pill bg-danger">
                                                <?= (int) $row['cantidadMensajes']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </button>

                                    <?php if ((int) $row['cantidadArchivos'] > 0): ?>
                                        <button type="button" class="btn btn-primary position-relative me-1" onclick="archivosAdjuntos('<?= (int) $row['id_ticket']; ?>')" title="Archivos adjuntos">
                                            <i class="bi bi-paperclip"></i>
                                            <span class="position-absolute top-0 start-90 translate-middle badge rounded-pill bg-danger">
                                                <?= (int) $row['cantidadArchivos']; ?>
                                            </span>
                                        </button>
                                    <?php endif; ?>

                                    <?php if ((int) $row['id_estado'] === 5 && !empty($row['tieneCalificacion'])): ?>
                                        <a href="#" class="btn btn-secondary btn-icon-split ms-2" onclick="calificarTicket(<?= (int) $row['id_ticket']; ?>)" title="Ver calificacion">
                                            <i class="fas fa-star text-warning"></i>
                                        </a>
                                    <?php endif; ?>

                                </div>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
</div>

<script>
if (!window.tecnicoTableFiltersSearchRegistered) {
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'tablaTecnicoTicketAsignados') {
            return true;
        }

        const api = new $.fn.dataTable.Api(settings);
        const rowNode = api.row(dataIndex).node();
        if (!rowNode) {
            return true;
        }

        const normalizarEstadoFiltro = (estado) => {
            const estadoNormalizado = (estado || '').trim().toLowerCase();
            return estadoNormalizado === 'atrasado' ? 'demorado' : estadoNormalizado;
        };

        const estadoFiltro = normalizarEstadoFiltro($('#filtroEstadoTecnico').val());
        const fechaFiltro = ($('#filtroFechaTecnico').val() || '').trim();
        const fechaRespuestaFiltro = ($('#filtroFechaRespuestaTecnico').val() || '').trim();

        const estadoFila = normalizarEstadoFiltro(rowNode.dataset.estado || '');
        const fechaFila = (rowNode.dataset.fechaCreacion || '').trim();
        const fechaRespuestaFila = (rowNode.dataset.fechaRespuesta || '').trim();

        if (estadoFiltro && estadoFila !== estadoFiltro) {
            return false;
        }

        if (fechaFiltro && fechaFila !== fechaFiltro) {
            return false;
        }

        if (fechaRespuestaFiltro && fechaRespuestaFila !== fechaRespuestaFiltro) {
            return false;
        }

        return true;
    });

    window.tecnicoTableFiltersSearchRegistered = true;
}

function configurarFiltrosTablaTecnico(dataTableTecnico) {
    const $tabla = $('#tablaTecnicoTicketAsignados');
    if (!$tabla.length || !dataTableTecnico) {
        return;
    }

    if ($tabla.data('tecnico-filters-bound') === '1') {
        dataTableTecnico.draw();
        return;
    }

    $('#filtroEstadoTecnico, #filtroFechaTecnico, #filtroFechaRespuestaTecnico')
        .off('.tecnicoFilters')
        .on('change.tecnicoFilters input.tecnicoFilters', function () {
            dataTableTecnico.draw();
        });

    $tabla.data('tecnico-filters-bound', '1');
    dataTableTecnico.draw();
}

$(document).on('init.dt', function (event, settings) {
    if (settings && settings.nTable && settings.nTable.id === 'tablaTecnicoTicketAsignados') {
        const dataTableTecnico = new $.fn.dataTable.Api(settings);
        configurarFiltrosTablaTecnico(dataTableTecnico);
        dataTableTecnico.columns.adjust();
        if (dataTableTecnico.responsive) {
            dataTableTecnico.responsive.recalc();
        }
    }
});

$(function () {
    if ($.fn.DataTable.isDataTable('#tablaTecnicoTicketAsignados')) {
        const dataTableTecnico = $('#tablaTecnicoTicketAsignados').DataTable();
        configurarFiltrosTablaTecnico(dataTableTecnico);
        dataTableTecnico.columns.adjust();
        if (dataTableTecnico.responsive) {
            dataTableTecnico.responsive.recalc();
        }
    }
});
</script>
