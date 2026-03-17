
                
                            <div class="table-responsive">         
                                <div id="ticketContainer">
                                    <div class="table-responsive">
                                        <table id="tablaTecnicoTicketAsignados"class="table table-bordered table-hover table-striped">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>FECHA</th>
                                                    <th>HORA</th>
                                                    <th>DE</th>
                                                    <th>ASUNTO</th>
                                                    <th>ESTADO</th>
                                                    <th>FEC RES</th>
                                                    <th>DIAS RESTANTES</th>
                                                    <th>OPCIONES</th>
                                                </tr>
                                            </thead>
                                                <tbody>
                                                   <?php
                                                            $estado = $GLOBALS['estadoTicketFiltro'] ?? null;
                                                            if (!isset($idUsuarioSession)) {
                                                                session_start();
                                                                $idUsuarioSession = isset($_SESSION['id']) ? $_SESSION['id'] : null;
                                                            }
                                                            
                                                            if (!$idUsuarioSession) {
                                                                echo "<p style='color:red;'>ID del tÃ©cnico no definido.</p>";
                                                                return;
                                                            }
                                                            $resultado = $funciones->obtenerTicketsTecnico($idUsuarioSession,$estado);
                                                    $counter = 1; 
                                                    if ($resultado->num_rows > 0) { 
                                                        while ($row = $resultado->fetch_array()) {                  
                                                            $nombreUsuarioProblema = $row['nombreUsuario'] . " " . $row['apellidoUsuario'];
                                                            // Calcular dias restantes solo para id_estado = 3
                                                           $diasRestantes = $row['dias_administrador_estima'];
                                                        if ($row['id_estado'] == 3) {
                                                          $fechaCreacion = new DateTime($row['fecha_creacion_inicio']);
                                                        $fechaActual = new DateTime();
                                                        $diasPasados = $fechaActual->diff($fechaCreacion)->days;
                                                        $diasRestantes = max(0, $diasRestantes - $diasPasados);
                                                        $tieneCalificacion = isset($row['tieneCalificacion']) ? $row['tieneCalificacion'] : null;
    
                                                            
                                                        }
                                                    ?>
                                                      <tr style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important;">
                                                         <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> ;">  <?= $counter++; ?></td>
                                                            <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>;">   <?= (!empty($row['fecha_creacion_inicio']) && $row['fecha_creacion_inicio'] !== '0000-00-00')? date('d-m-Y', strtotime($row['fecha_creacion_inicio'])): '---'; ?>                </td>
                                                            <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>  ;"> <?= (!empty($row['hora_creacion_inicio']) && $row['hora_creacion_inicio'] !== '00:00:00') ? htmlspecialchars($row['hora_creacion_inicio']) : '----'; ?> </td>
                                                            <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>  ;"> <?= htmlspecialchars($nombreUsuarioProblema); ?></td>
                                                            <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>;">  <div class="d-flex justify-content-between align-items-center"><span><?= htmlspecialchars($row['asunto']); ?></span>    <a href="javascript:void(0);" onclick="mostrarOffcanvasAsunto(<?= $row['id_ticket']; ?>)" title="Ver detalle"><i class="bi bi-info-circle-fill text-primary fs-5 ms-2"></i> </a>  </div></td>
                                                            <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>; color: black; text-align: center;"><span class="estado-badge estado-azul"><?= htmlspecialchars($row['nombreEstado']); ?></span></td>
                                                            <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>  ;">
                                                              <?php 
                                                                echo (!empty($row['fecha_estimada_admin']) && $row['fecha_estimada_admin'] !== '0000-00-00') 
                                                                    ? '<div class="estado-badge estado-azul d-inline-flex align-items-center px-2 py-1 rounded shadow-sm">
                                                                           <i class="fas fa-user-clock me-1"></i>' . date('d-m-Y', strtotime($row['fecha_estimada_admin'])) . '
                                                                       </div>'
                                                                    : '<div class="estado-badge estado-azul d-inline-flex align-items-center px-2 py-1 rounded shadow-sm">
                                                                           <i class="fas fa-user-clock me-1"></i> 00-00-0000
                                                                       </div>';
                                                                ?>

                                                            </td>
                                                        <td style="background-color: <?= htmlspecialchars($row['colorEstado']); ?> !important; text-align: center;">
                                                            <?php 
                                                                $idEstado = $row['id_estado'];
                                                                $idTicket = $row['id_ticket'];
                                                                $usuarioCalifico = $row['tieneCalificacion'];
                                                        
                                                                if ($idEstado == 3) {
                                                                    if ($diasRestantes == 0 || $diasRestantes == 1) {
                                                                        echo '<span class="estado-badge estado-rojo"><i class="fas fa-hourglass-half me-1" style="animation: blinkingText 1.2s infinite;"></i> Tienes que terminar el ticket hoy</span>';
                                                                    } elseif ($diasRestantes > 1) {
                                                                        echo '<span class="estado-badge estado-gris"><i class="fas fa-hourglass-half me-1" style="animation: blinkingText 1.2s infinite;"></i> Quedan ' . htmlspecialchars($diasRestantes) . ' dias</span>';
                                                                    } else {
                                                                        echo '<span class="estado-badge estado-gris">--</span>';
                                                                    }
                                                        
                                                                } elseif ($idEstado == 5) {
                                                                    if ($usuarioCalifico != 1) {
                                                                        $idContenedor = 'estrellas_' . $idTicket;
                                                                        // echo '<div class="d-flex align-items-center justify-content-between gap-2">';
                                                                        echo '<div id="' . $idContenedor . '"></div>';
                                                                        echo "<script>mostrarEstrellas($idTicket, '$idContenedor');</script>";
                                                                        // echo '</div>';
                                                                    }
                                                        
                                                                } elseif ($idEstado == 2) {
                                                                    echo '<span class="estado-badge estado-azul"><i class="fas fa-play-circle me-1"></i> Debes Comenzar el Ticket</span>';
                                                        
                                                                } else {
                                                                    if ($row['dias_administrador_estima'] == 0) {
                                                                        echo '<span class="estado-badge estado-gris">--</span>';
                                                                    } else {
                                                                        echo '<span class="estado-badge estado-azul"><i class="fas fa-clock me-1"></i> ' . htmlspecialchars($row['dias_administrador_estima']) . ' dias estimados</span>';
                                                                    }
                                                                }
                                                            ?>
                                                        </td>
                        
                                        <!--BUTON-->
                                        <td class="p-2" style="background-color: <?= htmlspecialchars($row['colorEstado']); ?>;">
                                            <div class="d-flex flex-nowrap align-items-center justify-content-start gap-1" style="min-width: 180px;">                    
                                                <a href="#" class="btn btn-primary btn-icon-split me-2" id="idVerTicket" onclick="modalTicketTecnico('<?= $row['id_ticket']; ?>')">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                                                                    
                                                                                                        <!--<a href="#" class="btn btn-secondary btn-icon-split me-2" id="idverConversacion"-->
                                                                                                        <!--onclick="cargarTicketConversacion('<?= $row['id_ticket']; ?>', '<?= $row['id_tecnico']; ?>', '<?= $row['id_usuario']; ?>')">-->
                                                                                                        <!--    <i class="bi bi-chat-dots"></i>-->
                                                                                                        <!--</a>-->
                                                                                                        
                                                                                                                    <!-- <a href="#" class="btn btn-secondary position-relative me-2"-->
                                                                                                                    <!--   onclick="cargarTicketConversacion('<?= $row['id_ticket']; ?>', '<?= $row['id_tecnico']; ?>', '<?= $row['id_usuario']; ?>')"-->
                                                                                                                    <!--   data-bs-toggle="popover"-->
                                                                                                                    <!--   data-bs-placement="top">-->
                                                                                                                    <!--    <i class="bi bi-chat-dots"></i>-->
                                                                                                                    <!--    <?php if ($row['cantidadMensajes'] > 0): ?>-->
                                                                                                                    <!--    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle">-->
                                                                                                                    <!--        ðŸ’¬-->
                                                                                                                    <!--    </span>-->
                                                                                                                    <!--    <?php endif; ?>-->
                                                                                                                    
                                                                                                                                <!--</a>-->
                                                                                                        <button type="button" class="btn btn-primary position-relative"
                                                                                                                onclick="cargarTicketConversacion('<?= $row['id_ticket']; ?>', '<?= $row['id_tecnico']; ?>', '<?= $row['id_usuario']; ?>', <?= $row['id_estado']; ?>)" data-bs-toggle="popover">
                                                                                                                <i class="bi bi-chat-dots"></i>
                                                                                                                  <?php if ($row['cantidadMensajes'] > 0): ?>
                                                                                        
                                                                                                                <span class="position-absolute top-0 start-90 translate-middle badge rounded-pill bg-danger" >
                                                                                                                    <?= $row['cantidadMensajes']; ?>
                                                                                                                <span class="visually-hidden"></span>
                                                                                                                </span>
                                                                                                                  <?php endif; ?>
                                                                                        
                                                                                                            </button>          
                                                                                                                              
                                                                                                    
                                                                                                        <?php if ($row['cantidadArchivos'] > 0): ?>
                                                                                                            <button type="button" class="btn btn-primary position-relative me-1" onclick="archivosAdjuntos('<?= $row['id_ticket']; ?>')">
                                                                                                                <i class="bi bi-paperclip"></i>
                                                                                                                <span class="position-absolute top-0 start-110 translate-middle badge rounded-pill bg-danger">
                                                                                                                    <?= $row['cantidadArchivos']; ?>
                                                                                                                    <span class="visually-hidden">Archivos adjuntos</span>
                                                                                                                </span>
                                                                                                            </button>
                                                                                                        <?php endif; ?>
                                                                                                    
                                                                                                  <?php if ($row['id_estado'] == 5): ?>
                                                                                                        <!--<a href="#" class="btn btn-success btn-icon-split ms-2" onclick="descargarTicket('<?= $row['id_ticket']; ?>')" -->
                                                                                                        <!--    data-bs-toggle="popover" -->
                                                                                                        <!--    data-bs-placement="top" -->
                                                                                                        <!--   >-->
                                                                                                        <!--    <i class="bi bi-download"></i>-->
                                                                                                        <!--</a>-->
                                                                                                    
                                                                                                        <!--<a href="#" class="btn btn-primary btn-icon-split ms-2"  onclick="enviarTicketPorCorreo(<?= $row['id_ticket']; ?>)">-->
                                                                                                        <!--    <i class="bi bi-envelope"></i>-->
                                                                                                        <!--</a>-->
                                                                                                    
                                                                                                        <?php
                                                                                                        // Esto es lo mÃ¡s importante: validar que tieneCalificacion venga con algÃºn valor
                                                                                                        $tieneCalificacion = isset($row['tieneCalificacion']) && !empty($row['tieneCalificacion']) ? $row['tieneCalificacion'] : null;
                                                                                                        if ($tieneCalificacion !== null):
                                                                                                    ?>
                                                                                                <a href="#" class="btn btn-secondary btn-icon-split me-2 " id="idverConversacion" 
                                                                                                onclick="calificarTicket(<?= $row['id_ticket']; ?>)" 
                                                                                                    data-bs-toggle="popover" 
                                                                                                    data-bs-placement="top">
                                                                                                    <i class="fas fa-star text-warning"></i>
                                                                                                </a>
                                                                                            <?php endif; ?>
                                                                                        <?php endif; ?>
                                                            </div>
                                                        </td>
    
                                                    </tr>
                                                    <?php 
                                                        }
                                                    } else { 
                                                    ?>
                                                    <!--<tr>-->
                                                    <!--    <td colspan="9">No hay tickets disponibles.</td>-->
                                                    <!--</tr>-->
                                                    <?php }?>
                                                </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            

<script>
    
function mostrarEstrellas(id_ticket, contenedorID) {
  const el = document.getElementById(contenedorID);
  if (!el) return;

  fetch("modelos/rescatar/calificacionUsuario.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
    body: new URLSearchParams({ id_ticket })
  })
  .then(async (res) => {
    if (!res.ok) throw new Error("HTTP " + res.status);
    const ct = res.headers.get("content-type") || "";
    if (!ct.includes("application/json")) {
      // Si el backend devolvió HTML/texto, lo tratamos como "sin datos"
      console.warn("Respuesta no JSON de calificación");
      return { success: true, data: {} };
    }
    return res.json();
  })
  .then((data) => {
    const estrellas = parseInt(data?.data?.id_calificacion ?? 0, 10) || 0;

    if (estrellas > 0) {
      let html = "";
      for (let i = 1; i <= 4; i++) {
        html += `<i class="${i <= estrellas ? 'fas text-warning' : 'far text-muted'} fa-star"></i>`;
      }
      el.innerHTML = html;
    } else {
      el.innerHTML = `
        <span class="estado-badge estado-azul">
          <i class="fas fa-ban me-1"></i>Sin calificacion
        </span>`;
    }
  })
  .catch((err) => {
    console.error("Calificación – error:", err);
    // En error también mostrar "Sin calificación" (no "Error al cargar")
    el.innerHTML = `
      <span class="estado-badge estado-azul">
        <i class="fas fa-ban me-1"></i>Sin calificacion
      </span>`;
  });
}

    
</script>
    