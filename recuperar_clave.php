<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Recuperar Clave | Sistema Tickets</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/estilos_login.css">
    <link rel="icon" type="image/x-icon" href="imagenes/logo_seduc.png" />

</head>
<style>
/* Reset */
body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f4f6f9;
  margin: 0;
  padding: 0;
}

/* Contenedor general */
.login-box {
  background-color: #ffffff;
  padding: 40px 30px;
  border-radius: 20px;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
  text-align: center;
  min-width: 360px;
}

/* Logo */
.logo-login {
  width: 50px;
  margin-bottom: 15px;
}

/* Títulos */
.login-box h4 {
  font-weight: bold;
  margin-bottom: 10px;
  color: #111;
}

.login-box p {
  color: #666;
  font-size: 15px;
  margin-bottom: 25px;
}

/* Inputs */
.login-box input[type="email"],
.login-box input[type="password"] {
  text-align: center;
  border-radius: 8px;
  font-size: 15px;
  height: 45px;
}

.login-box input:focus {
  box-shadow: 0 0 0 2px rgba(13, 110, 253, 0.25);
}

/* Botón principal */
.login-box .btn-primary {
  background-color: #006aff;
  font-weight: 600;
  font-size: 15px;
  padding: 12px;
  border-radius: 8px;
  transition: background-color 0.2s;
}

.login-box .btn-primary:hover {
  background-color: #0056d2;
}

/* Link de abajo */
.login-box a.text-secondary {
  display: inline-block;
  margin-top: 10px;
  font-size: 14px;
}

.login-box a.text-secondary:hover {
  text-decoration: underline;
  color: #0056b3;
}

/* Alertas */
.alert {
  /*max-width: 500px;*/
  margin: 15px auto;
  font-size: 15px;
}

</style>
<body>

<?php if (isset($_GET['error'])): ?>
  <div class="alert alert-danger alert-dismissible fade show m-3 shadow-sm" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?php
      switch ($_GET['error']) {
        case 'correo_vacio':
          echo "Debe ingresar un correo.";
          break;
        case 'formato_incorrecto':
          echo "El formato del correo electrónico es incorrecto.";
          break;
        case 'usuario_no_existe':
          echo "Usuario no encontrado en la base de datos.";
          break;
        case 'error_envio':
          echo "Hubo un error al enviar el correo. Por favor, inténtelo nuevamente.";
          break;
        default:
          echo "Error desconocido.";
      }
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
  </div>
<?php endif; ?>

<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="login-box">
    <img src="img/logo_seduc.png" alt="Logo" class="logo-login">
    <h4>¿Olvidaste tu clave?</h4>
    <p>Ingresa tu correo y te enviaremos un enlace para restablecerla.</p>

    <form action="validacion_recuperar_clave.php" method="POST">
      <div class="mb-3">
        <input type="email" class="form-control" name="email" placeholder="usuario@correo.com" >
      </div>
      <button type="submit" class="btn btn-primary w-100">Enviar Enlace</button>
      <a href="index.php" class="text-secondary d-block mt-3">
        <i class="bi bi-arrow-left-circle me-1"></i>Volver al inicio de sesión
      </a>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
