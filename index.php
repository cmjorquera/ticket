<?php
// Mostrar errores solo en desarrollo
// ini_set('display_errors', 1); error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SEDUC SPA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="icon" type="image/x-icon" href="imagenes/logo_seduc.png" />
    <link href="css/index.css" rel="stylesheet">

   
</head>

<body>
    <?php if (isset($_GET['error']) || isset($_GET['mensaje'])): ?>
    <div class="position-absolute top-0 start-50 translate-middle-x w-100 px-4 mt-3" style="z-index: 9999;">
        <?php
    $alertType = 'danger';
    $message = '';
    $icon = 'bi-exclamation-triangle-fill';

    if (isset($_GET['error'])) {
        switch ($_GET['error']) {
            case 'nuevas_credenciales':
                $alertType = 'success';
                $icon = 'bi-check-circle-fill';
                $message = 'Perfecto! Ingresa tu nueva clave.';
                break;
            case 'cuenta_activada':
                $alertType = 'success';
                $icon = 'bi-check-circle-fill';
                $message = 'Tu cuenta fue activada correctamente. Ya puedes iniciar sesion.';
                break;
            case 'cuenta_pendiente':
                $message = 'Tu cuenta aun no esta activada. Revisa el correo de bienvenida para definir tu clave.';
                break;
            case 'formato_incorrecto':
                $message = 'El formato del Email es incorrecto.';
                break;
            case 'usuario_no_existe':
                $message = 'El usuario no existe en la base de datos.';
                break;
            case 'credenciales_incorrectas':
                $message = 'La contraseña no coincide.';
                break;
            case 'usuario_vacio':
                $message = 'Debe ingresar un usuario.';
                break;
            case 'clave_vacio':
                $message = 'Debe ingresar una clave.';
                break;
            case 'correo_enviado':
                $alertType = 'success';
                $icon = 'bi-check-circle-fill';
                $message = 'Correo enviado.';
                break;
            case 'bloqueado':
                $message = '<strong>BLOQUEADO!</strong> Tu cuenta ha sido bloqueada por intentos fallidos. <a href="recuperar_clave.php" class="alert-link">Recuperar clave</a>';
                break;
            default:
                $message = 'Ha ocurrido un error desconocido.';
                break;
        }
    } elseif (isset($_GET['mensaje']) && $_GET['mensaje'] == 'correo_enviado') {
        $alertType = 'success';
        $icon = 'bi-check-circle-fill';
        $message = 'Clave enviada al correo corporativo.';
    }
    ?>

        <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show d-flex align-items-center shadow"
            role="alert">
            <i class="bi <?php echo $icon; ?> me-2 fs-5"></i>
            <div><?php echo $message; ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    </div>
    <?php endif; ?>

    <div class="left-section">
        <div class="text-center mb-3">
            <h4 class="fw-bold">Bienvenido a <span style="color:#007bff;"><br>Sistema Tickets</span></h4>
            <img src="img/logo_seduc.png" alt="Logo SEDUC" height="60" class="my-3" />
        </div>
        <!-- MENSAJE DE USUARUIOS NUEVOS  -->
<!-- 
        <div id="firstTimeHint"
            class="first-time-hint alert alert-info alert-dismissible fade show d-flex align-items-start gap-2"
            role="alert" aria-live="polite">
            <i class="bi bi-info-circle-fill fs-5 flex-shrink-0"></i>

            <div>
                <strong>¿Primera vez aquí?</strong><br>
                Recibiras un correo para activar tu cuenta y crear tu clave de acceso.
            </div>

            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div> -->



        <form class="login-container" method="POST" action="validacion.php">
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Correo</label>
                <input type="email" class="form-control" id="usuario" name="usuario" placeholder="usuario@correo.com">
            </div>

            <div class="input-group">
                <input type="password" name="pass" id="pass" class="form-control" placeholder="Clave"><br><br>
                <button class="btn btn-outline-secondary toggle-password" type="button">
                    <i class="bi bi-eye-slash-fill" id="toggleIcon" onclick="mostrarOcultarClave()"></i>
                </button><br>
                <div class="invalid-feedback">Por favor, ingrese su contraseña.</div>
            </div>

            <button type="submit" class="btn btn-login w-100 text-white">Iniciar Sesión</button>
            <div class="text-end mt-2">
                <a href="recuperar_clave.php" class="text-muted small">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>















    <div class="right-section">
        <div id="carouselInfo" class="carousel slide content w-100 px-4" data-bs-ride="carousel">
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <i class="bi bi-bar-chart-line mb-3"></i>
                    <h5><strong>Estadísticas en Tiempo Real</strong></h5>
                    <p>Visualiza el estado actual de los tickets con gráficos dinámicos y seguimiento de solicitudes.
                    </p>
                    <div class="img-gallery">
                        <img src="img/grafico_login_estadisticas.png" alt="Estadísticas 1">
                        <img src="img/grafico_login_estadisticas_2.png" alt="Estadísticas 2">
                    </div>
                </div>
                <div class="carousel-item">
                    <i class="bi bi-person-badge mb-3"></i>
                    <h5><strong>Seguimiento de Estados</strong></h5>
                    <p>
                        Revisa el estado de tus tickets en tiempo real: desde que se crean hasta su cierre. <br>
                        Podrás ver en qué etapa del proceso están, cuánto tiempo estimado queda para su resolución y si
                        están próximos a finalizar.
                    </p>
                    <div class="img-gallery">
                        <img src="img/estadostiecket.png" alt="Estado de Ticket">
                    </div>
                </div>

                <div class="carousel-item">
                    <i class="bi bi-person-badge mb-3"></i>
                    <h5><strong>Roles y Permisos</strong></h5>
                    <p>Vista personalizada para Administrador, Técnico o Usuario. Flujo adaptado a cada perfil con
                        gestión de tickets, adjuntos y privilegios únicos.</p>
                    <div class="img-gallery">
                        <img src="img/login_usuario.png" alt="Rol Usuario">
                        <img src="img/login_tecnico.png" alt="Rol Técnico">
                        <img src="img/login_administrador.png" alt="Rol Administrador">
                    </div>
                </div>
                <div class="carousel-item">
                    <i class="bi bi-tools mb-3"></i>
                    <h5><strong>Gestión de Tickets</strong></h5>
                    <p>Crea, asigna y gestiona tickets. Comunicación en tiempo real, adjunta documentos e imágenes.</p>
                    <div class="img-gallery">
                        <img src="img/adjuntar_chatear.png" alt="Adjuntar y Chatear">
                    </div>
                </div>
                <div class="carousel-item">
                    <i class="bi bi-people mb-3"></i>
                    <h5><strong>Colaboración Eficiente</strong></h5>
                    <p>Conversa en tiempo real con colaboradores. Mejora coordinación y velocidad de resolución.</p>
                    <div class="img-gallery">
                        <img src="img/chat_ticket.png" alt="Chat Ticket">
                    </div>
                </div>
            </div>
            <div class="custom-indicators d-flex justify-content-center gap-2 mt-4">
                <div class="bar active"></div>
                <div class="bar"></div>
                <div class="bar"></div>
                <div class="bar"></div>
            </div>
            <button class="btn-nav prev" type="button" data-bs-target="#carouselInfo" data-bs-slide="prev">
                <i class="bi bi-chevron-left"></i>
            </button>
            <button class="btn-nav next" type="button" data-bs-target="#carouselInfo" data-bs-slide="next">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>













    <script>
    const carousel = document.getElementById('carouselInfo');
    const bars = document.querySelectorAll('.custom-indicators .bar');

    function updateIndicators(index) {
        bars.forEach((bar, i) => bar.classList.toggle('active', i === index));
    }

    updateIndicators(0);

    carousel.addEventListener('slid.bs.carousel', function(e) {
        updateIndicators(e.to);
    });

    function mostrarOcultarClave() {
        const input = document.getElementById("pass");
        const icon = document.getElementById("toggleIcon");

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("bi-eye-slash-fill");
            icon.classList.add("bi-eye-fill");
        } else {
            input.type = "password";
            icon.classList.remove("bi-eye-fill");
            icon.classList.add("bi-eye-slash-fill");
        }
    }
    </script>

</body>

</html>
