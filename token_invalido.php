<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Token Inválido | Sistema Tickets</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="stylesheet" href="css/estilos_login.css">
                <link rel="icon" type="image/x-icon" href="imagenes/logo_seduc.png" />

</head>
<style>
    body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background-color: #f4f6f9;
  margin: 0;
  padding: 0;
}

.login-box {
  background-color: #ffffff;
  padding: 40px 30px;
  border-radius: 20px;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
  text-align: center;
  min-width: 360px;
}

.logo-login {
  width: 50px;
  margin-bottom: 15px;
}

.login-box h4 {
  font-weight: bold;
  margin-bottom: 10px;
  color: #111;
}

.login-box p {
  color: #666;
  font-size: 15px;
  margin-bottom: 10px;
}

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

</style>
<body>

<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="login-box">
    <img src="../img/logo_seduc.png" alt="Logo" class="logo-login">
    
    <div class="mb-3 text-warning fs-1">
      <i class="bi bi-exclamation-triangle-fill"></i>
    </div>

    <h4>Enlace inválido o expirado</h4>
    <p>El enlace que has utilizado ya no es válido. Puede que ya haya sido usado o que haya expirado.</p>
    <p>Por favor, solicita un nuevo reinicio de contraseña desde la página de inicio.</p>

    <a href="index.php" class="btn btn-primary w-100 mt-3">
      <i class="bi bi-house-door-fill me-2"></i>Volver al Sistema de Tickets
    </a>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
