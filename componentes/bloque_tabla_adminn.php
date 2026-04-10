<?php
$tecnicosFiltroIds = [6, 7, 8, 42];
$consultaTecnicosFiltro = new MySQL("", "", "");
$sqlTecnicosFiltro = "SELECT id, nombre, apellido_paterno
                      FROM usuarios
                      WHERE id IN (" . implode(',', array_map('intval', $tecnicosFiltroIds)) . ")
                      ORDER BY FIELD(id, " . implode(',', array_map('intval', $tecnicosFiltroIds)) . ")";
$resultadoTecnicosFiltro = $consultaTecnicosFiltro->consulta($sqlTecnicosFiltro);
$tecnicosFiltroAdmin = [];
while ($tecnicoFiltro = $consultaTecnicosFiltro->fetch_array($resultadoTecnicosFiltro)) {
    $tecnicosFiltroAdmin[] = $tecnicoFiltro;
}
?>

                                <div class="ticket-admin-filtros px-3 pt-3">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label for="filtroEstadoAdmin" class="form-label fw-semibold text-muted mb-1">Estado</label>
                                            <select id="filtroEstadoAdmin" class="form-select form-select-sm">
                                                <option value="">Todos</option>
                                                <option value="Recibido">Recibido</option>
                                                <option value="Asignado">Asignado</option>
                                                <option value="En proceso">En proceso</option>
                                                <option value="Terminado">Terminado</option>
                                                <option value="Demorado">Atrasado</option>
                                                <option value="Cerrado">Cerrado</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="filtroFechaAdmin" class="form-label fw-semibold text-muted mb-1">Fecha creación</label>
                                            <input type="date" id="filtroFechaAdmin" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-md-3">
                                            <label for="filtroTecnicoAdmin" class="form-label fw-semibold text-muted mb-1">Técnico</label>
                                            <select id="filtroTecnicoAdmin" class="form-select form-select-sm">
                                                <option value="">Todos</option>
                                                <option value="__sin_asignar__">Sin asignar</option>
                                                <?php foreach ($tecnicosFiltroAdmin as $tecnicoFiltro): ?>
                                                    <option value="<?= (int)$tecnicoFiltro['id']; ?>">
                                                        <?= htmlspecialchars(trim(($tecnicoFiltro['nombre'] ?? '') . ' ' . ($tecnicoFiltro['apellido_paterno'] ?? ''))); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label for="filtroFechaRespuestaAdmin" class="form-label fw-semibold text-muted mb-1">Fecha respuesta</label>
                                            <input type="date" id="filtroFechaRespuestaAdmin" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive mt-3">
                                    <div class="table-responsive">
                                        <table id="dataTableAdministrador" class="table table-bordered table-hover table-striped">
                                            <thead class="table-dark">
                                                <tr>
                                                    <?php $usarIndicadorEstado = !empty($GLOBALS['ticketAdminColorColumn']); ?>
                                                    <th class="col-id">ID</th>
                                                    <th class="col-fecha-hora" style="width:120px;">FECHA / HORA</th>
                                                    <th class="col-de">DE</th>
                                                    <th class="col-asunto">ASUNTO</th>
                                                    <th class="col-tecnico">TECNICO</th>
                                                    <th class="col-fecha-respuesta">FECHA RESPUESTA</th>
                                                    <th class="col-dias-restantes">DIAS RESTANTES</th>
                                                    <th class="col-calificacion">CALIFICACION</th> 
                                                    <th class="col-opciones">OPCIONES</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $estado = $GLOBALS['estadoTicketFiltro'] ?? null;
                                                // $resultado = $funciones->ticketAdministrador($idUsuarioSession);
                                                $resultado = $funciones->ticketAdministrador($idUsuarioSession, $estado);

                                                
                                                $contador = 1;

                                                if ($resultado->num_rows > 0) { 
                                                    while ($row = $resultado->fetch_array()) {
                                                        $color = $row['colorEstado'];
                                                        $estadoClase = 'estado-ticket-' . (int)$row['id_estado'];
                                                        $estiloFila = $usarIndicadorEstado ? '' : 'background-color: ' . $color . ' !important;';
                                                        $estiloCelda = $usarIndicadorEstado ? '' : 'background-color: ' . htmlspecialchars($row['colorEstado']) . ' !important;';
                                                        $nombreUsuarioProblema = $row['nombreUsuario'] . " " . $row['apellidoUsuario'];
                                                        $nombreUsuarioTecnico = $row['nombreTecnico'] . " " . $row['apellidoTecnico'];
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
                                                        $mostrarAvisoUltimoDiaAdmin = ((int) $row['id_estado'] === 3 && $tieneRangoProceso && $diasRestantes === 1 && !empty($row['id_tecnico']));
                                                ?>
                                                <?php
                                                    $fechaCreacionIso = !empty($row['fecha_creacion_inicio']) ? date('Y-m-d', strtotime($row['fecha_creacion_inicio'])) : '';
                                                    $fechaRespuestaIso = (!empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00')
                                                        ? date('Y-m-d', strtotime($row['fecha_estimada_admin']))
                                                        : '';
                                                    $tecnicoId = isset($row['id_tecnico']) && $row['id_tecnico'] !== null ? (int)$row['id_tecnico'] : '';
                                                ?>
                                                <tr class="<?= $estadoClase ?><?= $usarIndicadorEstado ? ' fila-ticket-admin-compacta' : '' ?>"
                                                    style="<?= $estiloFila ?>"
                                                    data-estado="<?= htmlspecialchars($row['nombreEstado'] ?? ''); ?>"
                                                    data-fecha-creacion="<?= htmlspecialchars($fechaCreacionIso); ?>"
                                                    data-tecnico-id="<?= htmlspecialchars((string)$tecnicoId); ?>"
                                                    data-fecha-respuesta="<?= htmlspecialchars($fechaRespuestaIso); ?>">
                                                    <td class="celda-id<?= $usarIndicadorEstado ? ' celda-id-con-indicador' : '' ?>" style="<?= $estiloCelda ?>">
                                                        <?php if ($usarIndicadorEstado): ?>
                                                            <span class="estado-indicador-dot estado-indicador-dot--inline" style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>"></span>
                                                        <?php endif; ?>
                                                        <span class="celda-id-numero"><?= $contador++; ?></span>
                                                    </td>
                                                    <td class="celda-fecha-hora" style="<?= $estiloCelda ?>">
                                                        <?php
                                                            $fechaCreada = !empty($row['fecha_creacion_inicio']) ? date('d-m-Y', strtotime($row['fecha_creacion_inicio'])) : '--';
                                                            $horaCreada = !empty($row['hora_creacion_inicio']) ? htmlspecialchars($row['hora_creacion_inicio']) : '--';
                                                        ?>
                                                        <div class="ticket-fecha-hora">
                                                            <div class="ticket-fecha-hora__fecha"><?= $fechaCreada; ?></div>
                                                            <div class="ticket-fecha-hora__hora"><?= $horaCreada; ?></div>
                                                        </div>
                                                    </td>
                                                    <td class="celda-de" style="<?= $estiloCelda ?>"><?= htmlspecialchars($nombreUsuarioProblema); ?></td>
                                                    <td class="celda-asunto" style="<?= $estiloCelda ?>">  <div class="d-flex justify-content-between align-items-center gap-2"><span><?= htmlspecialchars($row['asunto']); ?></span>    <a href="javascript:void(0);" onclick="mostrarOffcanvasAsunto(<?= $row['id_ticket']; ?>)" title="Ver detalle"><i class="bi bi-info-circle-fill text-primary fs-5 ms-2"></i> </a>  </div></td>
                                                    
                                                    <!--TECNICO ASIGNADO-->
<td class="celda-tecnico" style="<?= $estiloCelda ?>">

<?php if ($row['id_estado'] == 1 || $row['id_estado'] == 2): ?>

    <button type="button"
        class="btn btn-primary btn-sm btn-tecnico-accion"
        onclick="asignacionTecnico('<?= $row['id_ticket']; ?>')"
        data-bs-toggle="tooltip"
        data-bs-placement="top"
        title="<?= $row['id_estado']==2 ? 'Técnico actual: '.htmlspecialchars($nombreUsuarioTecnico ?: 'No asignado') : 'Asignar técnico al ticket'; ?>">

        <i class="bi bi-person-plus-fill"></i>
        <?= $row['id_estado'] == 1 ? 'Asignar Técnico' : 'Cambiar Técnico'; ?>

    </button>

<?php else: ?>

    <div class="ticket-resumen-estado ticket-resumen-estado--tecnico">
        <span class="ticket-resumen-estado__dot" style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>;"></span>
        <div class="ticket-resumen-estado__body">
            <div class="ticket-resumen-estado__titulo"><?= htmlspecialchars($nombreUsuarioTecnico ?: 'No asignado'); ?></div>
            <div class="ticket-resumen-estado__detalle">Tecnico asignado</div>
        </div>
    </div>

<?php endif; ?>

</td>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    tooltipTriggerList.forEach(function (el) {
        new bootstrap.Tooltip(el);
    });
});
</script>

                                                    <!--FECHA DE ACCION-->
                                                       <td class="celda-fecha-respuesta" style="<?= $estiloCelda ?>">
                                                              <?php 
                                                                $fechaRespuestaTitulo = (!empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00')
                                                                    ? date('d-m-Y', strtotime($row['fecha_estimada_admin']))
                                                                    : '00-00-0000';
                                                                $fechaRespuestaDetalle = (!empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00')
                                                                    ? 'Fecha estimada'
                                                                    : 'Sin fecha definida';
                                                                echo '<div class="ticket-resumen-estado">
                                                                        <span class="ticket-resumen-estado__dot" style="background-color: ' . htmlspecialchars($row['colorEstado']) . ';"></span>
                                                                        <div class="ticket-resumen-estado__body">
                                                                            <div class="ticket-resumen-estado__titulo">' . htmlspecialchars($fechaRespuestaTitulo) . '</div>
                                                                            <div class="ticket-resumen-estado__detalle">' . htmlspecialchars($fechaRespuestaDetalle) . '</div>
                                                                        </div>
                                                                      </div>';
                                                                ?>

                                                            </td>
                                                        
                                                    <!--FECHA DE RESPUESTA-->
                                                    <td class="celda-dias-restantes" style="<?= $estiloCelda ?>">
                                                        <?php 
                                                            $idEstado           = $row['id_estado'];
                                                            $idTicket           = $row['id_ticket'];
                                                            $usuarioCalifico    = $row['tieneCalificacion'];
                                                            $diasTitulo = '';
                                                            $diasDetalle = '';
                                                            if (in_array((int) $row['id_estado'], [2, 3, 7], true) && $tieneRangoProceso) {
                                                                $diasTitulo = htmlspecialchars((string) $diasTranscurridosProceso) . ' de ' . htmlspecialchars((string) $totalDiasProceso) . ' dias';
                                                                if ($diasRestantes === 1) {
                                                                    $diasDetalle = 'Al tecnico le queda 1 dia';
                                                                } elseif ($diasRestantes > 1) {
                                                                    $diasDetalle = 'Al tecnico le quedan ' . htmlspecialchars((string) $diasRestantes) . ' dias';
                                                                } else {
                                                                    $diasDetalle = 'Plazo cumplido';
                                                                }
                                                            
                                                            } elseif ($idEstado == 5) {
                                                                if ($usuarioCalifico == 1) {
                                                                    $diasTitulo = 'Usuario califico';
                                                                    $diasDetalle = 'Ticket terminado';
                                                                } else {
                                                                    $diasTitulo = 'Esperando calificacion';
                                                                    $diasDetalle = 'Pendiente usuario';
                                                                }
                                                            } elseif ($idEstado == 5) {
                                                                     $idContenedor = 'estrellas_' . $idTicket;
                                                                        echo '<div class="d-flex align-items-center justify-content-between gap-2">';
                                                                    
                                                                        // Estrellas (JS rellena dinámicamente)
                                                                        echo '<div id="' . $idContenedor . '"></div>';
                                                                        echo "<script>mostrarEstrellas($idTicket, '$idContenedor');</script>";
                                                                    
                                                                        // Botón con ícono (tooltip opcional)
                                                                        echo '<button class="btn btn-sm btn-primary" onclick="calificarTicket(' . $idTicket . ')" title="Ver Comentario">';
                                                                        echo '<i class="fas fa-comment-dots"></i>'; // puedes cambiarlo por fa-eye, fa-info-circle, etc.
                                                                        echo '</button>';
                                                                    
                                                                        echo '</div>';
                                                                    
                                                                        echo '</div>';
                                                                            } elseif ($idEstado == 2) {
                                                                        $diasTitulo = 'Tecnico no ha comenzado';
                                                                        $diasDetalle = 'Pendiente inicio';
                                                                            } else {
                                                                                if ($totalDiasProceso == 0) {
                                                                                    $diasTitulo = '00-00-0000';
                                                                                    $diasDetalle = 'Sin plazo definido';
                                                                                        } else {
                                                                                    $diasTitulo = htmlspecialchars((string) $totalDiasProceso);
                                                                                    $diasDetalle = 'Dias configurados';
                                                                                }
                                                                            }
                                                            if ($diasTitulo !== '') {
                                                                echo '<div class="ticket-resumen-estado">
                                                                        <span class="ticket-resumen-estado__dot" style="background-color: ' . htmlspecialchars($row['colorEstado']) . ';"></span>
                                                                        <div class="ticket-resumen-estado__body">
                                                                            <div class="ticket-resumen-estado__titulo">' . $diasTitulo . '</div>
                                                                            <div class="ticket-resumen-estado__detalle">' . $diasDetalle . '</div>
                                                                        </div>
                                                                      </div>';
                                                            }
                                                                        ?>
                                                    </td>

                                                    <!--CALIFICACION-->
                                                    <td class="celda-calificacion" style="<?= $estiloCelda ?>">
                                                        <?php 
                                                            $idEstado = $row['id_estado'];
                                                            $idTicket = $row['id_ticket'];
                                                            $tieneCalificacionTicket = !empty($row['tieneCalificacion']);
                                                    
                                                            if ($idEstado == 1) {
                                                                echo '<div class="ticket-resumen-estado">
                                                                        <span class="ticket-resumen-estado__dot" style="background-color: ' . htmlspecialchars($row['colorEstado']) . ';"></span>
                                                                        <div class="ticket-resumen-estado__body">
                                                                            <div class="ticket-resumen-estado__titulo">Esperando asignacion</div>
                                                                            <div class="ticket-resumen-estado__detalle">De tecnico</div>
                                                                        </div>
                                                                      </div>';
                                                            } elseif ($idEstado == 2) {
                                                                echo '<div class="ticket-resumen-estado">
                                                                        <span class="ticket-resumen-estado__dot" style="background-color: ' . htmlspecialchars($row['colorEstado']) . ';"></span>
                                                                        <div class="ticket-resumen-estado__body">
                                                                            <div class="ticket-resumen-estado__titulo">Tecnico asignado</div>
                                                                            <div class="ticket-resumen-estado__detalle">Pendiente inicio</div>
                                                                        </div>
                                                                      </div>';
                                                            } elseif ($idEstado == 3) {
                                                                echo '<div class="ticket-resumen-estado">
                                                                        <span class="ticket-resumen-estado__dot" style="background-color: ' . htmlspecialchars($row['colorEstado']) . ';"></span>
                                                                        <div class="ticket-resumen-estado__body">
                                                                            <div class="ticket-resumen-estado__titulo">En proceso</div>
                                                                            <div class="ticket-resumen-estado__detalle">Trabajo del tecnico</div>
                                                                        </div>
                                                                      </div>';
                                                            } elseif ($idEstado == 7) {
                                                                echo '<div class="ticket-resumen-estado">
                                                                        <span class="ticket-resumen-estado__dot" style="background-color: ' . htmlspecialchars($row['colorEstado']) . ';"></span>
                                                                        <div class="ticket-resumen-estado__body">
                                                                            <div class="ticket-resumen-estado__titulo">Ticket demorado</div>
                                                                            <div class="ticket-resumen-estado__detalle">Fuera de plazo</div>
                                                                        </div>
                                                                      </div>';
                                                            } elseif ($idEstado == 5) {
                                                                if ($tieneCalificacionTicket) {
                                                                    $idContenedor = 'estrellas_' . $idTicket;
                                                                    echo '<div class="d-flex align-items-center justify-content-between gap-2 pointer">';
                                                                    echo '<div id="' . $idContenedor . '" class="contenedor-estrellas-admin" data-ticket-id="' . $idTicket . '"></div>';
                                                                    echo '</div>';
                                                                } else {
                                                                    echo '<div class="ticket-resumen-estado">
                                                                            <span class="ticket-resumen-estado__dot" style="background-color: ' . htmlspecialchars($row['colorEstado']) . ';"></span>
                                                                            <div class="ticket-resumen-estado__body">
                                                                                <div class="ticket-resumen-estado__titulo">Esperando calificacion</div>
                                                                                <div class="ticket-resumen-estado__detalle">Del tecnico</div>
                                                                            </div>
                                                                          </div>';
                                                                }
                                                            }
                                                                    ?>
                                                    </td>
                                                            
                                                    <!--BOTONES-->
                                                    <td class="p-2 celda-opciones" style="<?= $estiloCelda ?>">
                                                      <div class="dashboard d-flex flex-nowrap align-items-center justify-content-start gap-1 position-relative pe-5 pt-2">                                                               
                                                      <a href="#" class="btn btn-primary btn-icon-split me-2" id="idVerTicket" onclick="modalTicketAdministrativo('<?= $row['id_ticket']; ?>')" data-bs-toggle="popover" data-bs-placement="top">                                                         
                                                                        <i class="bi bi-eye"></i>
                                                                    </a>
                                                                    <?php if ($row['id_estado'] == 5): ?>
                                                                    <!--<a href="#" class="btn btn-success btn-icon-split ms-2" onclick="descargarTicket('<?= $row['id_ticket']; ?>')" -->
                                                                    <!--    data-bs-toggle="popover" -->
                                                                    <!--    data-bs-placement="top" -->
                                                                    <!--    title="Descargar ticket">-->
                                                                    <!--    <i class="bi bi-download"></i>-->
                                                                    <!--</a>-->
                                                                    <!--<a href="#" class="btn btn-primary btn-icon-split ms-2" title="Enviar ticket por correo" onclick="enviarTicketPorCorreo(<?= $row['id_ticket']; ?>)">-->
                                                                    <!--   <i class="bi bi-envelope"></i>-->
                                                                    <!--</a>-->
                                                                    
                                                              
                                                                <?php endif; ?>
                                                            <?php if ($row['cantidadArchivos'] > 0) { ?>
                                                                <button type="button" class="btn btn-primary position-relative"
                                                                    onclick="archivosAdjuntos('<?= $row['id_ticket']; ?>')">
                                                                    <i class="bi bi-paperclip"></i>
                                                                    <span class="position-absolute top-0 start-90 translate-middle badge rounded-pill bg-danger"> <?= $row['cantidadArchivos']; ?>
                                                                        <span class="visually-hidden">unread messages</span>
                                                                    </span>
                                                                </button>
                                                            <?php } ?>
                                                            <?php if (!empty($row['tieneCalificacion'])) { ?>
                                                                      <a href="#" class="btn btn-secondary btn-icon-split ms-2" id="idverConversacion" onclick="calificarTicket(<?= $row['id_ticket']; ?>)" data-bs-toggle="popover" data-bs-placement="top">
                                                                        <i class="fas fa-star text-warning"></i>
                                                                    </a>
                                                            <?php } ?>
                                                            <?php if ($mostrarAvisoUltimoDiaAdmin) { ?>
                                                                <button type="button"
                                                                    class="btn btn-warning ms-2"
                                                                    onclick="enviarAvisoUltimoDiaTecnico(<?= (int) $row['id_ticket']; ?>)"
                                                                    title="Avisar al tecnico que le queda 1 dia">
                                                                    <i class="bi bi-envelope-exclamation"></i>
                                                                </button>
                                                            <?php } ?>

                                                            <div class="filter" style="position: absolute; top: 2px; right: 4px; margin: 0;">
                                                                <a class="icon" href="#" data-bs-toggle="dropdown" aria-expanded="false" title="Más opciones" style="padding-right: 0; padding-bottom: 0; display: inline-flex; align-items: flex-start; justify-content: flex-end;">
                                                                    <i class="bi bi-three-dots"></i>
                                                                </a>
                                                                <ul class="dropdown-menu dropdown-menu-end">
                                                                    <li>
                                                                        <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); eliminarTicketDesdeMenu(<?= (int) $row['id_ticket']; ?>);">
                                                                            <i class="bi bi-trash me-2"></i>Eliminar
                                                                        </a>
                                                                    </li>
                                                                    <li>
                                                                        <a class="dropdown-item" href="#" onclick="event.preventDefault(); fusionarTicketDesdeMenu(<?= (int) $row['id_ticket']; ?>);">
                                                                            <i class="bi bi-intersect me-2"></i>Fusionar
                                                                        </a>
                                                                    </li>
                                                                </ul>
                                                            </div>
                                                            </div>
                                                        </td>
                                                </tr>
                                                <?php 
                                                    }
                                                } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

            <script>
                
              function asignacionTecnico(id_ticket) {
  $.ajax({
    url: "modelos/rescatar/ticket.php",
    type: "POST",
    data: { id: id_ticket },
    dataType: "json",
    success: function (data) {
      if (!data) return;

      let nombreUsuario = data.nombre_usuario || 'No disponible';
      let apePaternoUsuario = data.apellido_paterno_usuario || '';
      let asunto = data.asunto || 'Sin asunto';
      let descripcion_ticket = data.descripcion_ticket || 'Sin descripción';
      let nombre_categoria = data.nombre_categoria || 'Sin categoría';
      const descripcion_ticket_render = sanitizarDescripcionHtml(descripcion_ticket);

      function sanitizarDescripcionHtml(html) {
        const permitidas = new Set(['BR', 'P', 'OL', 'UL', 'LI', 'B', 'STRONG', 'I', 'EM', 'U']);
        const contenedor = document.createElement('div');
        contenedor.innerHTML = String(html || '');

        const limpiarNodo = (nodo) => {
          if (nodo.nodeType === Node.TEXT_NODE) return;
          if (nodo.nodeType !== Node.ELEMENT_NODE) {
            nodo.remove();
            return;
          }

          const etiqueta = nodo.tagName;
          const hijos = Array.from(nodo.childNodes);
          hijos.forEach(limpiarNodo);

          if (!permitidas.has(etiqueta)) {
            const padre = nodo.parentNode;
            while (nodo.firstChild) {
              padre.insertBefore(nodo.firstChild, nodo);
            }
            padre.removeChild(nodo);
            return;
          }

          Array.from(nodo.attributes).forEach(attr => nodo.removeAttribute(attr.name));
        };

        Array.from(contenedor.childNodes).forEach(limpiarNodo);
        const limpio = contenedor.innerHTML.trim();
        return limpio || 'Sin descripción';
      }

      $.ajax({
        url: "modelos/rescatar/tecnicos.php",
        type: "POST",
        dataType: "json",
        success: function (usuarios) {
          let htmlAvatares = '';
          usuarios.forEach(user => {
            const nombreCompleto = `${user.nombre} ${user.apellido_paterno}`;
            htmlAvatares += `
              <div class="user-avatar-item text-center p-2 rounded border" 
                   style="cursor: pointer; transition: all 0.2s ease-in-out; width: 80px;" 
                   data-id="${user.id}">
                <img src="img/undraw_profile.svg" alt="Avatar" class="rounded-circle mb-2" 
                     style="width: 40px; height: 40px; object-fit: cover;">
                <span style="font-size: 11px; display:block">${nombreCompleto}</span>
              </div>
            `;
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

          const html = `
            <div class="contenedor-badges d-flex gap-2 justify-content-center mb-3 flex-wrap">
              <span class="insignia_ticket bg-primary text-white"><i class="fas fa-layer-group"></i> ${nombre_categoria}</span>
              <span id="badgeResponsable" class="insignia_ticket bg-success text-white d-none"><i class="fas fa-user"></i> Técnico</span>
            </div>

            <form class="row g-3">
              <div class="col-md-6">
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="nombreUsuarioProblema" value="${nombreUsuario} ${apePaternoUsuario}" disabled>
                  <label for="nombreUsuarioProblema">De</label>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-floating mb-3">
                  <input type="text" class="form-control" id="asuntoTicket" value="${asunto}" disabled>
                  <label for="asuntoTicket">Asunto</label>
                </div>
              </div>

              <div class="col-md-6">
                <div class="form-floating mb-3">
                  <select class="form-select" id="categoriaTicket">
                    <option value="">Cargando categorías...</option>
                  </select>
                  <label for="categoriaTicket">Categoría</label>
                </div>
              </div>

              <div class="col-md-6">
                <div id="contenedorAvatares" class="d-flex flex-wrap gap-3 justify-content-start p-2" style="max-height: 100px; overflow-y: auto;"></div>
              </div>

              <div class="col-md-12">
                <div class="mb-3">
                  <label for="descripcionTicketRender" class="form-label">Descripción</label>
                  <div class="form-control" id="descripcionTicketRender" style="height: 120px; overflow-y: auto;"></div>
                </div>
              </div>
              <div class="col-md-12">
                <div class="form-floating">
                  <textarea class="form-control" id="comentarioTicket" placeholder="Comentario al Técnico" style="height: 120px;"></textarea>
                  <label for="comentarioTicket">Comentario al Técnico</label>
                </div>
              </div>

              <input type="hidden" id="tecnicoSeleccionado">
            </form>`;

          Swal.fire({
            title: '<div class="alert alert-dark">ASIGNAR TÉCNICO</div>',
            html: html,
            showCancelButton: true,
            confirmButtonText: 'Asignar',
            cancelButtonText: 'Cancelar',
            width: '1000px',
            customClass: {
              popup: 'cuerpo_modal_guardar',
              confirmButton: 'bt_crear',
              cancelButton: 'bt_eliminar'
            },

            didOpen: () => {
              $('#descripcionTicketRender').html(descripcion_ticket_render);
              $.ajax({
                url: "modelos/rescatar/categoria_de_ticket.php",
                type: "POST",
                dataType: "json",
                success: function (categorias) {
                  const $select = $('#categoriaTicket');
                  $select.html('<option value="">Seleccionar categoría</option>');
                  categorias.forEach(cat => {
                    $select.append(`<option value="${cat.id}">${cat.nombre_categoria}</option>`);
                  });

                  $select.on('change', function () {
                    const categoriaId = $(this).val();
                    const selected = $(this).find("option:selected").text();
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
                },
                error: function () {
                  console.error('Error cargando categorías');
                }
              });
            },

            preConfirm: () => {
              const responsable = $('#tecnicoSeleccionado').val();
              const comentario = $('#comentarioTicket').val();
              const categoria = $('#categoriaTicket').val();

              if (!responsable || !categoria) {
                Swal.showValidationMessage('Debe seleccionar técnico y categoría');
                return false;
              }

              return { responsable, comentario, categoria, id_ticket };
            }
          }).then(result => {
            if (result.isConfirmed) {
              const datos = result.value;
              $.ajax({
                url: "modelos/guardar/guardar_responsable_ticket.php",
                type: "POST",
                data: {
                  id_responsable: datos.responsable,
                  comentario: datos.comentario,
                  categoria: datos.categoria,
                  idTicket: datos.id_ticket
                },
                success: function () {
                  Swal.fire({
                    icon: 'success',
                    title: 'Técnico asignado',
                    timer: 2000,
                    showConfirmButton: false
                  });
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
function enviarAvisoUltimoDiaTecnico(idTicket) {
  Swal.fire({
    icon: 'warning',
    title: 'Avisar al tecnico',
    text: 'Se enviara un correo avisando que queda 1 dia para cerrar el ticket.',
    showCancelButton: true,
    confirmButtonText: 'Enviar correo',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (!result.isConfirmed) {
      return;
    }

    $.ajax({
      url: 'modelos/guardar/avisar_ultimo_dia_tecnico.php',
      type: 'POST',
      dataType: 'json',
      data: { id_ticket: idTicket },
      success: function (resp) {
        if (resp && resp.success) {
          Swal.fire('Correo enviado', resp.message || 'Se envio el aviso al tecnico.', 'success');
          return;
        }
        Swal.fire('Error', (resp && (resp.message || resp.error)) || 'No se pudo enviar el aviso.', 'error');
      },
      error: function () {
        Swal.fire('Error', 'No se pudo conectar con el servidor.', 'error');
      }
    });
  });
}

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

    const normalizarEstadoFiltro = (estado) => {
      const estadoNormalizado = (estado || '').trim().toLowerCase();
      return estadoNormalizado === 'atrasado' ? 'demorado' : estadoNormalizado;
    };

    const estadoFiltro = normalizarEstadoFiltro($('#filtroEstadoAdmin').val());
    const fechaFiltro = ($('#filtroFechaAdmin').val() || '').trim();
    const tecnicoFiltro = ($('#filtroTecnicoAdmin').val() || '').trim();
    const fechaRespuestaFiltro = ($('#filtroFechaRespuestaAdmin').val() || '').trim();

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

  window.adminTableFiltersSearchRegistered = true;
}

function configurarFiltrosTablaAdmin(dataTableAdmin) {
  const $tabla = $('#dataTableAdministrador');
  if (!$tabla.length || !dataTableAdmin) {
    return;
  }

  if ($tabla.data('admin-filters-bound') === '1') {
    dataTableAdmin.draw();
    return;
  }

  $('#filtroEstadoAdmin, #filtroFechaAdmin, #filtroTecnicoAdmin, #filtroFechaRespuestaAdmin')
    .off('.adminFilters')
    .on('change.adminFilters input.adminFilters', function () {
      dataTableAdmin.draw();
    });

  $tabla.data('admin-filters-bound', '1');
  dataTableAdmin.draw();
}
</script>

                            
               
