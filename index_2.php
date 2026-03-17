<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="css/login.css" rel="stylesheet">
</head>
<style>
    .alert {
    margin-top: 10px;
    margin-bottom: 10px; 
    padding: 8px 12px;  /* Reducir el padding interno */
    font-size: 20px;    /* Reducir el tamaño de la fuente */
    border-radius: 6px; /* Bordes redondeados suaves */
    max-width: 350px;   /* Limitar el ancho */
    text-align: center; /* Centrar el texto */
    display: inline-block; /* Evitar que ocupe todo el ancho */
}

</style>
<body>
    <div class="login-container">
        <!-- Panel izquierdo -->
        <div class="login-left">
            <h1>Bienvenido</h1>
<img src="img/logo_seduc.png" height="50" class="me-2"> <strong>sisteMA Tickets</strong>
        </div>
        <div class="login-right">

            <!-- Mostrar alertas de error -->
            <?php if (isset($_GET['error'])): ?>
                <?php
                switch ($_GET['error']) {
                    case 'nuevas_credenciales':
                        echo "<div class='alert alert-success' role='alert'>Perfecto! Ingresa tu nueva clave.</div>";
                        break;
                    case 'formato_incorrecto':
                        echo "<div class='alert alert-danger' role='alert'>El formato del Email es incorrecto.</div>";
                        break;
                    case 'usuario_no_existe':
                        echo "<div class='alert alert-danger' role='alert'>El usuario no existe en la base de datos.</div>";
                        break;
                    case 'credenciales_incorrectas':
                        echo "<div class='alert alert-danger' role='alert'>La contraseña no coincide.</div>";
                        break;
                    case 'usuario_vacio':
                        echo "<div class='alert alert-danger' role='alert'>Debe ingresar un usuario.</div>";
                        break;
                    case 'clave_vacio':
                        echo "<div class='alert alert-danger' role='alert'>Debe ingresar una clave.</div>";
                        break;
                    case 'correo_enviado':
                        echo "<div class='alert alert-success' role='alert'>Correo enviado.</div>";
                        break;
                    case 'bloqueado':
                        echo "<div class='alert alert-danger' role='alert'>
                                <h4 class='alert-heading'>BLOQUEADO!</h4>
                                <p>Tu cuenta ha sido bloqueada debido a múltiples intentos fallidos.</p>
                                <hr>
                                <p class='mb-0'><a href='recuperar_clave.php'>¿Olvidaste tu contraseña?</a>

                                <a href='https://wa.me/569883027' target='_blank'><i class='fas fa-mobile-alt'></i></a></p>
                              </div>";
                        break;
                    default:
                        echo "<div class='alert alert-danger' role='alert'>Ha ocurrido un error desconocido.</div>";
                        break;
                }
                ?>
            <?php endif; ?>

            <!-- Mostrar alertas de éxito -->
            <?php if (isset($_GET['mensaje'])): ?>
            <div id="" class="alert alert-success" role="aleeert" colspan="7">
                <?php
                switch ($_GET['mensaje']) {
                    case 'correo_enviado':
                        echo "Clave enviada al Correo Cooporativo.";
                        break;
                    default:
                        echo "Operación completada con éxito.";
                        break;
                }
                ?>
            </div>
            <?php endif; ?>

            <!-- Panel derecho -->
            <form class="user needs-validation" novalidate action="validacion.php" method="post" id="loginForm">
                <div class="form-group">
                    <!-- <label for="usuario">Correo</label> -->
                    <input type="email" name="usuario" id="usuario" class="form-control"
                        placeholder="Ingrese su correo..." required>
                    <div id="emailError" class="invalid-feedback"></div>
                </div>

                <div class="form-group">
                    <!-- <label for="pass">Clave</label> -->
                    <div class="input-group">
                        <input type="password" name="pass" id="pass" class="form-control" placeholder="Clave" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                            <i class="bi bi-eye-slash-fill" id="toggleIcon" onclick="mostrarOcultarClave()"></i>
                            <!-- Ojo cerrado por defecto -->
                        </button>
                        <div class="invalid-feedback">Por favor, ingrese su contraseña.</div>
                    </div>

                </div>

                <button type="submit" class="login-btn">Entrar</button>

            </form>

            <div class="links">
                <a href="recuperar_clave.php">¿Olvidaste tu contraseña?</a>
                <!-- <a href="registro.php">Crear cuenta</a> -->
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
    // Validación en tiempo real
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('usuario');
        const emailError = document.getElementById('emailError');

        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();

                if (emailInput.value === '') {
                    emailError.textContent = 'Debe ingresar un usuario.';
                    emailInput.classList.add('is-invalid');
                } else if (!validateEmail(emailInput.value)) {
                    emailError.textContent = 'El formato del Email es incorrecto.';
                    emailInput.classList.add('is-invalid');
                } else {
                    emailInput.classList.remove('is-invalid');
                }
            }

            form.classList.add('was-validated');
        }, false);

        emailInput.addEventListener('input', function() {
            if (emailInput.classList.contains('is-invalid')) {
                emailInput.classList.remove('is-invalid');
            }
        });
    });
    </script>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/comunes.js"></script>
</body>

</html>
