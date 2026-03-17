<div class="table-responsive mt-3">
    <div class="table-responsive">
        <table id="tablaTecnicoTicketAsignados" class="table table-bordered table-hover table-striped">
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

                        $fechaRespuestaTitulo = !empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00'
                            ? date('d-m-Y', strtotime($row['fecha_estimada_admin']))
                            : '00-00-0000';
                        $fechaRespuestaDetalle = !empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00'
                            ? 'Fecha estimada'
                            : 'Sin fecha definida';

                        $diasTitulo = '';
                        $diasDetalle = '';
                        if ((int) $row['id_estado'] === 3) {
                            if ($diasRestantes <= 1) {
                                $diasTitulo = 'Debes terminar hoy';
                                $diasDetalle = 'Vence hoy';
                            } elseif ($diasRestantes > 1) {
                                $diasTitulo = htmlspecialchars((string) $diasRestantes) . ' dias';
                                $diasDetalle = 'Tiempo restante';
                            } else {
                                $diasTitulo = 'Sin plazo activo';
                                $diasDetalle = 'No disponible';
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
                            if ((int) $row['dias_administrador_estima'] === 0) {
                                $diasTitulo = '00-00-0000';
                                $diasDetalle = 'Sin plazo definido';
                            } else {
                                $diasTitulo = htmlspecialchars((string) $row['dias_administrador_estima']) . ' dias';
                                $diasDetalle = 'Dias estimados';
                            }
                        }
                        ?>
                        <tr class="estado-ticket-<?= (int) $row['id_estado']; ?> fila-ticket-admin-compacta">
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
                                <div class="d-flex flex-nowrap align-items-center justify-content-start gap-1" style="min-width: 180px;">
                                    <a href="#" class="btn btn-primary btn-icon-split me-2" onclick="modalTicketTecnico('<?= (int) $row['id_ticket']; ?>')" title="Ver ticket">
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
                } else {
                    ?>
                    <tr>
                        <td colspan="8">No hay tickets disponibles.</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
