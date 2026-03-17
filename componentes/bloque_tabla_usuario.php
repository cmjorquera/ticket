<div class="table-responsive mt-3" 
     data-intro=" <strong>Tabla de Tickets Creados:</strong><br>
     Aquí se listan todos los tickets registrados por el usuario.<br><br>
    ◻️  ️<span style='color:#000;'>Ticket No Asignados </span><br>
     🟩 <span style='color:#000;'>Ticket Terminado</span><br>
     🟨 <span style='color:#000;'>Ticket Asignado</span><br>
     🟥 <span style='color:#000;'>Ticket Atrasado o con vencimiento</span><br><br>.">


    <table id="tablaUsuario" class="table table-bordered table-hover table-striped">
        <thead class="table-dark">
            <tr>
                <th >N°</th>
                <th data-intro="Fecha de creación del ticket. Indica cuándo fue registrado.">FECHA</th>
                <th data-intro="Hora exacta en la que se creó el ticket.">HORA</th>
                <th data-intro="Nombre del usuario que ingresó el ticket.">DE</th>
                <th data-intro="Asunto o título del ticket que resume el problema o requerimiento.">ASUNTO</th>
                <th data-intro="Estado actual del ticket, como Recibido, Asignado, En proceso, etc.">ESTADO</th>
                <th data-intro="Fecha estimada en que debería resolverse el ticket.">FEC RES</th>
                <th data-intro="Cantidad de días que faltan para cumplir el plazo estimado.">DIAS RESTANTES</th>
                <th data-intro="Barra de avance que muestra el progreso del ticket en porcentaje.">PROGRESO</th>
                <th data-intro="Opciones disponibles para ver, editar o gestionar el ticket.">OPCIONES</th>

            </tr>
        </thead>
        <tbody>
            <?php 
                $estado = $GLOBALS['estadoTicketFiltro'] ?? null;
                $resultado = $funciones->ticketUsuario($idUsuarioSession, $estado);
                
                $counter = 1; 
                    if ($resultado->num_rows > 0) { 
                        while ($row = $resultado->fetch_array()) {                  
                        $nombreUsuarioProblema = $row['nombre'] . " " . $row['apellido_paterno'];
                        $diasRestantes = $row['dias_administrador_estima'];
                        if ($row['id_estado'] == 3) {
                            $fechaCreacion = new DateTime($row['fecha_creacion_inicio']);
                            $fechaActual = new DateTime();
                            $diasPasados = $fechaActual->diff($fechaCreacion)->days;
                            $diasRestantes = max(0, $diasRestantes - $diasPasados);
                            $tieneCalificacion = isset($row['tieneCalificacion']) ? $row['tieneCalificacion'] : null;
                        }
            ?>
            <tr style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> ;">
                <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> ;">  <?= $counter++; ?></td>
                <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>;">   <?= (!empty($row['fecha_creacion_inicio']) && $row['fecha_creacion_inicio'] !== '0000-00-00')? date('d-m-Y', strtotime($row['fecha_creacion_inicio'])): '---'; ?>                </td>
                <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>  ;"> <?= (!empty($row['hora_creacion_inicio']) && $row['hora_creacion_inicio'] !== '00:00:00') ? htmlspecialchars($row['hora_creacion_inicio']) : '----'; ?> </td>
                <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>  ;"> <?= htmlspecialchars($nombreUsuarioProblema); ?></td>
                <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>;">  <div class="d-flex justify-content-between align-items-center"><span><?= htmlspecialchars($row['asunto']); ?></span>    <a href="javascript:void(0);" onclick="mostrarOffcanvasAsunto(<?= $row['id_ticket']; ?>)" title="Ver detalle"><i class="bi bi-info-circle-fill text-primary fs-5 ms-2"></i> </a>  </div></td>
                <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>; text-align: center;">  <span class="estado-badge estado-azul"><?=strtoupper(htmlspecialchars($row['nombreEstado'])); ?></span></td>
                       
<!-- *********************************************************************************************************************************************************************-->
<!--FECHA RESTANTE-->
<!-- *********************************************************************************************************************************************************************-->       
   <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>;">
    <?php 
        if ($row['id_estado'] == 1) {
            echo '<span class="estado-badge estado-azul"><i class="fas fa-user-clock me-1"></i>Esperando técnico</span>';
        } elseif ($row['id_estado'] == 4) {
            echo '<span class="estado-badge estado-gris"><i class="fas fa-pencil-alt me-1"></i>Borrador</span>';
        } elseif (!empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00') {
            $fechaFormateada = date('d-m-Y', strtotime($row['fecha_estimada_admin']));
            echo '<span class="estado-badge estado-azul"><i class="fas fa-calendar-alt me-1"></i>' . $fechaFormateada . '</span>';
        } else {
            echo '<span class="estado-badge estado-gris"><i class="fas fa-calendar-alt me-1"></i>00-00-0000</span>';
        }
    ?>
</td>

                       
<!-- *********************************************************************************************************************************************************************-->
<!--DIAS RESTANTES-->
<!-- *********************************************************************************************************************************************************************-->     
            <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>;">
                <?php 
                    if ($row['id_estado'] == 1) {
                    echo '<span class="estado-badge estado-azul"><i class="fas fa-user-clock me-1"></i>Esperando técnico</span>';
                    } elseif ($row['id_estado'] == 3) {
                        if ($diasRestantes == 0 || $diasRestantes == 1) {
                        echo '<span class="estado-badge estado-rojo"><i class="status-icon in-process fas fa-hourglass-half" style="animation: blinkingText 1.2s infinite;"></i> HOY ESTARA LISTO</span>';

                        } elseif ($diasRestantes > 1) {
                            echo '<i class="status-icon in-process fas fa-hourglass-half" style="animation: blinkingText 1.2s infinite;"></i> ';
                            echo '<strong> ' . htmlspecialchars($diasRestantes) . ' días </strong>';
                            echo '<i class="status-icon in-process fas fa-hourglass-half" style="animation: blinkingText 1.2s infinite;"></i> ';
                        } else {
                            echo '<span class="not-started">--</span>';
                        }
                    } elseif ($row['id_estado'] == 4) {
                    echo '<span class="estado-badge estado-azul"><i class="fas fa-pencil-alt me-1"></i>Borrador</span>';
                    } elseif ($row['id_estado'] == 5 && $row['tieneCalificacion']) {
                    echo '<span class="estado-badge estado-azul"><i class="fas fa-check-circle me-1"></i>RESUELTO</span>';
                    } elseif ($row['id_estado'] == 2) {
                    echo '<span class="estado-badge estado-gris"><i class="fas fa-user-clock me-1"></i>PENDIENTE</span>';
                    } else {
                       echo (empty($row['tieneCalificacion']) || $row['tieneCalificacion'] == 0)
                    ? '<span class="estado-badge estado-azul"><i class="fas fa-exclamation-triangle me-1"></i>CALIFICAR</span>'
                    : '<i class="status-icon resolved fas fa-check-circle" style="color: green;"></i> CALIFICADO';

                    }
                ?>
            </td>
                          
<!-- *********************************************************************************************************************************************************************-->
<!--PROGRESO-->
<!-- *********************************************************************************************************************************************************************-->  
        <?php
        $totalPasos = 4;
        $pasosCompletos = 0;
        $idEstado = $row['id_estado'];
        
        // Asignar pasos según estado
        if ($idEstado == 1) {
            $pasosCompletos = 1;
        } elseif ($idEstado == 2) {
            $pasosCompletos = 2;
        } elseif ($idEstado == 3) {
            $pasosCompletos = 3;
        } elseif ($idEstado == 5) {
            $pasosCompletos = 4;
        }
        
        // Calcular porcentaje y color
        $porcentaje = intval(($pasosCompletos / $totalPasos) * 100);
        $colorBarra = ($porcentaje === 100) ? 'success' : (($porcentaje >= 50) ? 'warning' : 'danger');
        
        // Mostrar barra
        echo "<td style='background-color: " . htmlspecialchars($row['colorEstado']) . "; text-align: center;'>
                <div class='progress' style='height: 18px; min-width: 120px;'>
                    <div class='progress-bar bg-{$colorBarra}' style='width: {$porcentaje}%; transition: width 0.6s ease-in-out;'>
                        {$pasosCompletos}/{$totalPasos}
                    </div>
                </div>
              </td>";
        ?>


                
<!-- *********************************************************************************************************************************************************************-->
<!--OPCIONES-->
<!-- *********************************************************************************************************************************************************************-->
                <!-- *********************************************************-->
                <!-- VER TICKET CARACTERISTICAS-->
                <!-- *********************************************************-->
                <td class="p-2" style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>;">
                    <div class="d-flex flex-nowrap align-items-center justify-content-start gap-1" style="min-width: 180px;">
                        <?php if ($row['id_estado'] == 4): ?>
                        <a href="#" class="btn btn-primary btn-icon-split"onclick="ticketBorrador('<?= $row['id_ticket']; ?>')"data-bs-toggle="popover" data-bs-placement="top">
                            <i class="bi bi-eye"></i>
                        </a>
                        <?php else: ?>
                      <a href="#" class="btn btn-primary btn-icon-split"
                           onclick="modalTicketUsuario('<?= $row['id_ticket']; ?>')"
                           data-bs-toggle="popover"
                           data-bs-placement="top"
                           data-intro="Presiona este botón para ver todos los detalles del ticket seleccionado, incluyendo su descripción, estado, prioridad y seguimiento.">
                            <i class="bi bi-eye"></i>
                        </a>

                        <?php endif; ?>
                        
                    <!-- *********************************************************-->
                     <!-- CONVERSACION  -->
                    <!-- *********************************************************-->

                  <button type="button" class="btn btn-primary position-relative"
                    onclick="cargarTicketConversacion('<?= $row['id_ticket']; ?>', '<?= $row['id_usuario']; ?>', '<?= $row['id_tecnico']; ?>', <?= $row['id_estado']; ?>)"
                    data-bs-toggle="popover"
                    data-intro=" Aquí puedes conversar directamente con el técnico sobre el ticket, enviar mensajes y adjuntar archivos como imágenes, documentos o capturas de pantalla.">
                    <i class="bi bi-chat-dots"></i>
                    <?php if ($row['cantidadMensajes'] > 0): ?>
                        <span class="position-absolute top-0 start-90 translate-middle badge rounded-pill bg-danger">
                            <span class="visually"><?= $row['cantidadMensajes']; ?></span>
                        </span>
                    <?php endif; ?>
                </button>

                        <!-- ****************************************************************** -->
                        <!-- ADJUNTOS-->
                        <!-- *********************************************************-->

                        <?php if ($row['cantidadArchivos'] > 0) { ?>
                        <button type="button" class="btn btn-primary position-relative"onclick="archivosAdjuntos('<?= $row['id_ticket']; ?>')" data-bs-toggle="popover"data-bs-placement="top">
                            <i class="bi bi-paperclip"></i>
                            <span class="position-absolute top-0 start-90 translate-middle badge rounded-pill bg-danger">
                                <span class="visually-"><?= $row['cantidadArchivos']; ?></span>
                            </span>
                        </button>
                    
                    <?php } ?>
    
                <!-- ****************************************************************** -->
                        <!-- CALIFICACION-->
                <!-- ****************************************************************** -->
                        <?php if ($row['id_estado'] == 5 && empty($row['tieneCalificacion'])) { ?>
                            <a href="#" class="btn btn-secondary btn-icon-split me-2 <?= ($row['id_estado'] == 5 && empty($row['tieneCalificacion'])) ? 'btn-llamativo' : 'disabled opacity-50' ?>"
                               onclick="validacionTicketPorUsuario('<?= $row['id_ticket']; ?>', '<?= $row['id_usuario']; ?>','<?= $row['id_tecnico']; ?>')"
                                      data-bs-toggle="popover" data-bs-placement="top">
                                 <i class="fas fa-star text-warning"></i>
                            </a>
                        <?php } ?>
                    </div>
                </td>

                
            </tr>
            <?php 
                }
            } else { 
            ?>
    <!--<tr>-->
    <!--    <td colspan="10">No hay tickets disponibles.</td>-->
    <!--</tr>-->
    <?php }?>
    </tbody>
    </table>
</div>