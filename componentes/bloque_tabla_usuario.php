<?php
$tecnicosFiltroIds = [6, 7, 8, 42];
$consultaTecnicosFiltro = new MySQL("", "", "");
$sqlTecnicosFiltro = "SELECT id, nombre, apellido_paterno
                      FROM usuarios
                      WHERE id IN (" . implode(',', array_map('intval', $tecnicosFiltroIds)) . ")
                      ORDER BY FIELD(id, " . implode(',', array_map('intval', $tecnicosFiltroIds)) . ")";
$resultadoTecnicosFiltro = $consultaTecnicosFiltro->consulta($sqlTecnicosFiltro);
$tecnicosFiltroUsuario = [];
while ($tecnicoFiltro = $consultaTecnicosFiltro->fetch_array($resultadoTecnicosFiltro)) {
    $tecnicosFiltroUsuario[] = $tecnicoFiltro;
}
?>

<div class="ticket-admin-filtros px-3 pt-3">
    <div class="row g-3">
        <div class="col-md-3">
            <label for="filtroEstadoUsuario" class="form-label fw-semibold text-muted mb-1">Estado</label>
            <select id="filtroEstadoUsuario" class="form-select form-select-sm">
                <option value="">Todos</option>
                <option value="Recibido">Recibido</option>
                <option value="Asignado">Asignado</option>
                <option value="En proceso">En proceso</option>
                <option value="Terminado">Terminado</option>
                <option value="Borrador">Borrador</option>
                <option value="Demorado">Atrasado</option>
                <option value="Cerrado">Cerrado</option>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filtroFechaUsuario" class="form-label fw-semibold text-muted mb-1">Fecha creación</label>
            <input type="date" id="filtroFechaUsuario" class="form-control form-control-sm">
        </div>
        <div class="col-md-3">
            <label for="filtroTecnicoUsuario" class="form-label fw-semibold text-muted mb-1">Técnico</label>
            <select id="filtroTecnicoUsuario" class="form-select form-select-sm">
                <option value="">Todos</option>
                <option value="__sin_asignar__">Sin asignar</option>
                <?php foreach ($tecnicosFiltroUsuario as $tecnicoFiltro): ?>
                    <option value="<?= (int) $tecnicoFiltro['id']; ?>">
                        <?= htmlspecialchars(trim(($tecnicoFiltro['nombre'] ?? '') . ' ' . ($tecnicoFiltro['apellido_paterno'] ?? ''))); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="filtroFechaRespuestaUsuario" class="form-label fw-semibold text-muted mb-1">Fecha respuesta</label>
            <input type="date" id="filtroFechaRespuestaUsuario" class="form-control form-control-sm">
        </div>
    </div>
</div>

<div class="table-responsive mt-3"
     data-intro="<strong>Tabla de Tickets Creados:</strong><br>
     Aqui se listan todos los tickets registrados por el usuario.">
    <table id="tablaUsuario" class="table table-bordered table-hover table-striped">
        <thead class="table-dark">
            <tr>
                <th class="col-id">N°</th>
                <th class="col-fecha-hora">FECHA / HORA</th>
                <th class="col-de">DE</th>
                <th class="col-asunto">ASUNTO</th>
                <th class="col-estado">ESTADO</th>
                <th class="col-fecha-respuesta">FEC RES</th>
                <th class="col-dias-restantes">DIAS RESTANTES</th>
                <th class="col-progreso">PROGRESO</th>
                <th class="col-opciones">OPCIONES</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $estado = $GLOBALS['estadoTicketFiltro'] ?? null;
            $resultado = $funciones->ticketUsuario($idUsuarioSession, $estado);
            $counter = 1;

            if ($resultado->num_rows > 0) {
                while ($row = $resultado->fetch_array()) {
                    $nombreUsuarioProblema = trim(($row['nombre'] ?? '') . ' ' . ($row['apellido_paterno'] ?? ''));
                    $diasRestantes = (int) ($row['dias_administrador_estima'] ?? 0);

                    if ((int) $row['id_estado'] === 3 && !empty($row['fecha_creacion_inicio'])) {
                        $fechaCreacion = new DateTime($row['fecha_creacion_inicio']);
                        $fechaActual = new DateTime();
                        $diasPasados = $fechaActual->diff($fechaCreacion)->days;
                        $diasRestantes = max(0, $diasRestantes - $diasPasados);
                    }

                    $fechaCreada = !empty($row['fecha_creacion_inicio']) && $row['fecha_creacion_inicio'] !== '0000-00-00'
                        ? date('d-m-Y', strtotime($row['fecha_creacion_inicio']))
                        : '--';
                    $horaCreada = !empty($row['hora_creacion_inicio']) && $row['hora_creacion_inicio'] !== '00:00:00'
                        ? htmlspecialchars($row['hora_creacion_inicio'])
                        : '--';

                    $estadoTitulo = strtoupper(htmlspecialchars($row['nombreEstado']));
                    $estadoDetalle = 'Estado actual del ticket';

                    if ((int) $row['id_estado'] === 1) {
                        $fechaRespuestaTitulo = 'Esperando tecnico';
                        $fechaRespuestaDetalle = 'Sin asignacion';
                    } elseif ((int) $row['id_estado'] === 4) {
                        $fechaRespuestaTitulo = 'Borrador';
                        $fechaRespuestaDetalle = 'Aun no enviado';
                    } elseif (!empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00') {
                        $fechaRespuestaTitulo = date('d-m-Y', strtotime($row['fecha_estimada_admin']));
                        $fechaRespuestaDetalle = 'Fecha estimada';
                    } else {
                        $fechaRespuestaTitulo = '00-00-0000';
                        $fechaRespuestaDetalle = 'Sin fecha definida';
                    }

                    if ((int) $row['id_estado'] === 1) {
                        $diasTitulo = 'Esperando tecnico';
                        $diasDetalle = 'Pendiente asignacion';
                    } elseif ((int) $row['id_estado'] === 3) {
                        if ($diasRestantes <= 1) {
                            $diasTitulo = 'Hoy estara listo';
                            $diasDetalle = 'Vence hoy';
                        } elseif ($diasRestantes > 1) {
                            $diasTitulo = htmlspecialchars((string) $diasRestantes) . ' dias';
                            $diasDetalle = 'Tiempo restante';
                        } else {
                            $diasTitulo = '--';
                            $diasDetalle = 'No disponible';
                        }
                    } elseif ((int) $row['id_estado'] === 4) {
                        $diasTitulo = 'Borrador';
                        $diasDetalle = 'Aun no enviado';
                    } elseif ((int) $row['id_estado'] === 5 && !empty($row['tieneCalificacion'])) {
                        $diasTitulo = 'Resuelto';
                        $diasDetalle = 'Ticket calificado';
                    } elseif ((int) $row['id_estado'] === 2) {
                        $diasTitulo = 'Pendiente';
                        $diasDetalle = 'Asignado';
                    } else {
                        $diasTitulo = empty($row['tieneCalificacion']) ? 'Calificar' : 'Calificado';
                        $diasDetalle = 'Accion del usuario';
                    }

                    $totalPasos = 4;
                    $pasosCompletos = 0;
                    if ((int) $row['id_estado'] === 1) {
                        $pasosCompletos = 1;
                    } elseif ((int) $row['id_estado'] === 2) {
                        $pasosCompletos = 2;
                    } elseif ((int) $row['id_estado'] === 3) {
                        $pasosCompletos = 3;
                    } elseif ((int) $row['id_estado'] === 5) {
                        $pasosCompletos = 4;
                    }
                    $porcentaje = (int) (($pasosCompletos / $totalPasos) * 100);
                    $colorBarra = ($porcentaje === 100) ? 'success' : (($porcentaje >= 50) ? 'warning' : 'danger');
                    $fechaCreacionIso = !empty($row['fecha_creacion_inicio']) && $row['fecha_creacion_inicio'] !== '0000-00-00'
                        ? date('Y-m-d', strtotime($row['fecha_creacion_inicio']))
                        : '';
                    $fechaRespuestaIso = !empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00'
                        ? date('Y-m-d', strtotime($row['fecha_estimada_admin']))
                        : '';
                    $tecnicoId = isset($row['id_tecnico']) && $row['id_tecnico'] !== null ? (int) $row['id_tecnico'] : '';
                    ?>
                    <tr class="estado-ticket-<?= (int) $row['id_estado']; ?> fila-ticket-admin-compacta"
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
                                    <div class="ticket-resumen-estado__titulo"><?= $estadoTitulo; ?></div>
                                    <div class="ticket-resumen-estado__detalle"><?= htmlspecialchars($estadoDetalle); ?></div>
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
                        <td class="celda-progreso">
                            <div class="ticket-progreso">
                                <div class="ticket-progreso__meta">
                                    <span>Avance del ticket</span>
                                    <strong><?= $pasosCompletos; ?>/<?= $totalPasos; ?></strong>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-<?= $colorBarra; ?>" style="width: <?= $porcentaje; ?>%; transition: width 0.6s ease-in-out;">
                                        <?= $porcentaje; ?>%
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="celda-opciones">
                            <div class="d-flex flex-nowrap align-items-center justify-content-start gap-1">
                                <?php if ((int) $row['id_estado'] === 4): ?>
                                    <a href="#" class="btn btn-primary btn-icon-split" onclick="ticketBorrador('<?= (int) $row['id_ticket']; ?>')">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="#" class="btn btn-primary btn-icon-split" onclick="modalTicketUsuario('<?= (int) $row['id_ticket']; ?>')">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                <?php endif; ?>

                                <button type="button" class="btn btn-primary position-relative"
                                    onclick="cargarTicketConversacion('<?= (int) $row['id_ticket']; ?>', '<?= (int) $row['id_usuario']; ?>', '<?= (int) $row['id_tecnico']; ?>', <?= (int) $row['id_estado']; ?>)">
                                    <i class="bi bi-chat-dots"></i>
                                    <?php if ((int) $row['cantidadMensajes'] > 0): ?>
                                        <span class="position-absolute top-0 start-90 translate-middle badge rounded-pill bg-danger">
                                            <?= (int) $row['cantidadMensajes']; ?>
                                        </span>
                                    <?php endif; ?>
                                </button>

                                <?php if ((int) $row['cantidadArchivos'] > 0): ?>
                                    <button type="button" class="btn btn-primary position-relative" onclick="archivosAdjuntos('<?= (int) $row['id_ticket']; ?>')">
                                        <i class="bi bi-paperclip"></i>
                                        <span class="position-absolute top-0 start-90 translate-middle badge rounded-pill bg-danger">
                                            <?= (int) $row['cantidadArchivos']; ?>
                                        </span>
                                    </button>
                                <?php endif; ?>

                                <?php if ((int) $row['id_estado'] === 5 && empty($row['tieneCalificacion'])): ?>
                                    <a href="#" class="btn btn-secondary btn-icon-split me-2 btn-llamativo"
                                       onclick="validacionTicketPorUsuario('<?= (int) $row['id_ticket']; ?>', '<?= (int) $row['id_usuario']; ?>', '<?= (int) $row['id_tecnico']; ?>')">
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
if (!window.usuarioTableFiltersSearchRegistered) {
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (!settings.nTable || settings.nTable.id !== 'tablaUsuario') {
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

        const estadoFiltro = normalizarEstadoFiltro($('#filtroEstadoUsuario').val());
        const fechaFiltro = ($('#filtroFechaUsuario').val() || '').trim();
        const tecnicoFiltro = ($('#filtroTecnicoUsuario').val() || '').trim();
        const fechaRespuestaFiltro = ($('#filtroFechaRespuestaUsuario').val() || '').trim();

        const estadoFila = normalizarEstadoFiltro(rowNode.dataset.estado || '');
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

    window.usuarioTableFiltersSearchRegistered = true;
}

function configurarFiltrosTablaUsuario(dataTableUsuario) {
    const $tabla = $('#tablaUsuario');
    if (!$tabla.length || !dataTableUsuario) {
        return;
    }

    if ($tabla.data('usuario-filters-bound') === '1') {
        dataTableUsuario.draw();
        return;
    }

    $('#filtroEstadoUsuario, #filtroFechaUsuario, #filtroTecnicoUsuario, #filtroFechaRespuestaUsuario')
        .off('.usuarioFilters')
        .on('change.usuarioFilters input.usuarioFilters', function () {
            dataTableUsuario.draw();
        });

    $tabla.data('usuario-filters-bound', '1');
    dataTableUsuario.draw();
}

$(document).on('init.dt', function (event, settings) {
    if (settings && settings.nTable && settings.nTable.id === 'tablaUsuario') {
        configurarFiltrosTablaUsuario(new $.fn.dataTable.Api(settings));
    }
});

$(function () {
    if ($.fn.DataTable.isDataTable('#tablaUsuario')) {
        configurarFiltrosTablaUsuario($('#tablaUsuario').DataTable());
    }
});
</script>
