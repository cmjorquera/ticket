<?php
function mostrarOffcanvasActividad() {
    ?>
     <div class="offcanvas offcanvas-end" data-bs-backdrop="false" tabindex="-1" id="offcanvasActividad"
                    aria-labelledby="offcanvasActividadLabel">

                    <div class="offcanvas-body">
                        <div class="mb-4 p-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title m-0 text-primary fw-bold">Actividad reciente <span
                                        class="text-muted fw-normal">| Hoy</span></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                                    aria-label="Cerrar"></button>
                            </div>

                            <div class="position-relative ps-4 timeline-container">
                                <!-- Línea vertical -->
                                <div class="timeline-line"></div>

                                <ul class="list-unstyled mb-0">
                                    <li class="mb-4 d-flex position-relative">
                                        <span class="timeline-dot bg-success"></span>
                                        <div class="ms-3">
                                            <small class="text-muted">32 minutos</small><br>
                                            Porque os voy <b>a explicar los deberes</b> de los bienaventurados.
                                        </div>
                                    </li>
                                    <li class="mb-4 d-flex position-relative">
                                        <span class="timeline-dot bg-danger"></span>
                                        <div class="ms-3">
                                            <small class="text-muted">56 minutos</small><br>
                                            El placer vendrá de la adulación y la lisonja.
                                        </div>
                                    </li>
                                    <li class="mb-4 d-flex position-relative">
                                        <span class="timeline-dot bg-primary"></span>
                                        <div class="ms-3">
                                            <small class="text-muted">2 horas</small><br>
                                            Los placeres corrompen los problemas, el placer.
                                        </div>
                                    </li>
                                    <li class="mb-4 d-flex position-relative">
                                        <span class="timeline-dot bg-info"></span>
                                        <div class="ms-3">
                                            <small class="text-muted">1 día</small><br>
                                            Pero con el tiempo, <b>el placer a menudo queda cegado</b> por el tiempo.
                                        </div>
                                    </li>
                                    <li class="mb-4 d-flex position-relative">
                                        <span class="timeline-dot bg-warning"></span>
                                        <div class="ms-3">
                                            <small class="text-muted">2 días</small><br>
                                            Es una práctica rechazarlo.
                                        </div>
                                    </li>
                                    <li class="d-flex position-relative">
                                        <span class="timeline-dot bg-dark"></span>
                                        <div class="ms-3">
                                            <small class="text-muted">4 semanas</small><br>
                                            Las palabras de estos dolores no son suyas. Que de hecho, de hecho, puede
                                            ser
                                            que
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
    <?php
}




function mostrarOffcanvasConversacion() {
    session_start();
    $idUsuarioSession = $_SESSION['id'];

    include_once("class/conexion.php");
    $bdato = new MySQL("", "", "");

    $sql = "SELECT id, nombre, apellido_paterno, telefono, email 
            FROM usuarios 
            WHERE id != $idUsuarioSession
            ORDER BY nombre ASC";
    $resultado = $bdato->consulta($sql);
    ?>

    <style>
        .offcanvas-elegante {
            background-color: #f5f7fa;
            color: #1f2937;
        }

        .usuario-card {
            background-color: #ffffff;
            border-left: 5px solid #3b82f6;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .usuario-card:hover {
            background-color: #f0f4f8;
        }

        .usuario-card span {
            font-weight: 500;
        }

        .offcanvas-body {
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }
    </style>

    <div class="offcanvas offcanvas-end" data-bs-backdrop="false" tabindex="-1" id="offcanvasRight"
        aria-labelledby="offcanvasRightLabel">
        
        <div class="offcanvas-header border-bottom">
            <h5 class="offcanvas-title" id="offcanvasRightLabel">
                <i class="bi bi-people-fill me-2"></i>Contactar trabajadores
            </h5>
            <button class="btn-close" data-bs-dismiss="offcanvas" onclick="detenerActualizacionMensajes()" aria-label="Cerrar"></button>
        </div>

        <div class="offcanvas-body offcanvas-elegante">
            <input type="text" class="form-control mb-3" placeholder="Buscar trabajador..." onkeyup="filtrarUsuarios(this.value)">

            <div class="list-group">
                <?php while ($row = $bdato->fetch_array($resultado)):
                    $id = $row['id'];
                    $nombre = $row['nombre'] . ' ' . $row['apellido_paterno'];
                    $telefono = $row['telefono'];
                    $email = $row['email'];
                ?>
                <div class="usuario-card border rounded p-3 mb-3" id="usuarioCard<?= $id ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><?= htmlspecialchars($nombre) ?></span>
                        <div class="d-flex gap-2">
                            <a href="mailto:<?= $email ?>" class="btn btn-outline-primary btn-sm rounded-circle" title="Correo">
                                <i class="bi bi-envelope"></i>
                            </a>
                            <a href="https://wa.me/56<?= preg_replace('/[^0-9]/', '', $telefono) ?>" target="_blank"
                               class="btn btn-outline-success btn-sm rounded-circle" title="WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            <button class="btn btn-outline-secondary btn-sm rounded-circle" title="Chat" onclick="abrirConversacion(<?= $id ?>, <?= $idUsuarioSession ?>)">
                                <i class="bi bi-chat-dots"></i>
                            </button>
                        </div>
                    </div>
                    <div id="acordeonChat<?= $id ?>" class="mt-2" style="display: none;"></div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <?php
}








