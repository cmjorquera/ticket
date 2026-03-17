<?php
ob_start();
session_start();
require_once 'class/conexion.php'; 
require_once 'class/funciones.php';
$funciones = new Funciones();
$token = isset($_GET['token']) ? trim($_GET['token']) : ''; 

if (empty($token)) {
    include 'token_invalido.php';
    exit;
}

$bdato = new MySQL("", "", "");
$sql = "SELECT * FROM usuarios WHERE token_reinicio = '$token'";
$resultado = $bdato->consulta($sql);
if ($bdato->num_rows($resultado) > 0) {
    $row = $bdato->fetch_array($resultado);
    $nombreUsuario       = htmlspecialchars($row['nombre'] . " " . $row['apellido_paterno']. " " . $row['apellido_materno']);
    $emailUsuario        = htmlspecialchars($row['email']);
    $currentPasswordHash = $row['clave']; 
    $token               = $row['token_reinicio'];
} else {
    include 'token_invalido.php';
    exit;
}

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $usuario = $_POST['usuario_oculto'] ?? '';

    if (empty($new_password) || empty($confirm_password)) {
        $mensaje = 'pass_vacia'; 
    } elseif ($new_password !== $confirm_password) {
        $mensaje = 'contrasenas_no_coinciden';
    } elseif (strlen($new_password) < 5) { 
        $mensaje = 'pass_corta';
    } elseif (password_verify($new_password, $currentPasswordHash)) {
        $mensaje = 'misma_clave';
    }

    if ($mensaje) {
        header("Location: reiniciar_clave.php?token=$token&mensaje=$mensaje");
        exit;
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $sql = "UPDATE `usuarios` SET `clave`='$hashed_password', `token_reinicio`='' WHERE email ='$usuario'";
        $bdato->consulta($sql);
        
        if ($bdato->getTotalConsultas() > 0) {
            header("Location: index.php?error=nuevas_credenciales&email=" . urlencode($emailUsuario));
            exit;
        } else {
            header("Location: reiniciar_clave.php?token=$token&mensaje=error_actualizacion");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <?php $funciones->header(); ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Restablecer Clave | Sistema Tickets</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f4f6f8;
      font-family: Arial, Helvetica, sans-serif;
      color: #1f2937;
      margin: 0;
      padding: 0;
    }

    .reset-container {
      max-width: 420px;
      width: 100%;
      background: #ffffff;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      padding: 32px 28px;
      text-align: center;
    }

    .reset-logo {
      width: 180px;
      margin-bottom: 12px;
    }

    .reset-container h4 {
      font-weight: 700;
      color: #111827;
    }

    .reset-container p {
      font-size: 15px;
      color: #374151;
      margin-bottom: 24px;
    }

    .form-control {
      border-radius: 8px;
    }

    .btn-primary {
      background-color: #2563eb;
      border-color: #2563eb;
      border-radius: 8px;
      font-weight: 600;
    }

    .btn-primary:hover {
      background-color: #1e40af;
      border-color: #1e40af;
    }

    .footer-link {
      font-size: 14px;
      color: #6b7280;
      text-decoration: none;
    }

    .footer-link:hover {
      color: #111827;
      text-decoration: underline;
    }

    @media (max-width: 620px) {
      .reset-container {
        margin: 0 12px;
        padding: 24px 16px;
      }
    }
  </style>
</head>
<body>

<?php if (isset($_GET['mensaje'])): ?>
  <div class="alert alert-danger alert-dismissible fade show m-3 shadow-sm text-start" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>
    <?php
      switch ($_GET['mensaje']) {
        case 'pass_vacia':
          echo "Debe ingresar una nueva contraseña.";
          break;
        case 'pass_corta':
          echo "La contraseña debe tener al menos 5 caracteres.";
          break;
        case 'misma_clave':
          echo "Estás intentando usar la misma clave anterior.";
          break;
        case 'contrasenas_no_coinciden':
          echo "Las contraseñas no coinciden.";
          break;
        case 'error_actualizacion':
          echo "Hubo un error al actualizar la contraseña.";
          break;
        default:
          echo "Ha ocurrido un error.";
      }
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
  </div>
<?php endif; ?>

<div class="d-flex justify-content-center align-items-center vh-100">
  <div class="reset-container">
    <img src="../img/logo_seduc.png" alt="Logo SEDUC" class="reset-logo">
    <h4>Restablecer Contraseña</h4>
    <p>Bienvenido nuevamente, <strong><?php echo $nombreUsuario; ?></strong></p>

    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?token=<?php echo htmlspecialchars($token); ?>" method="POST">
      <input type="hidden" name="usuario_oculto" value="<?php echo htmlspecialchars($emailUsuario); ?>">

      <div class="mb-3 input-group">
        <input type="password" name="new_password" id="newPassword" placeholder="Nueva contraseña" required
               class="form-control <?php if (isset($_GET['mensaje']) && in_array($_GET['mensaje'], ['pass_vacia', 'pass_corta', 'misma_clave'])) echo 'is-invalid'; ?>">
        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('newPassword', 'iconNew')">
          <i class="bi bi-eye-fill" id="iconNew"></i>
        </button>
      </div>

      <div class="mb-3 input-group">
        <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirmar contraseña" required
               class="form-control <?php if (isset($_GET['mensaje']) && in_array($_GET['mensaje'], ['contrasenas_no_coinciden', 'pass_vacia'])) echo 'is-invalid'; ?>">
        <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirmPassword', 'iconConfirm')">
          <i class="bi bi-eye-fill" id="iconConfirm"></i>
        </button>
      </div>

      <button type="submit" class="btn btn-primary w-100 mb-2">
        <i class="bi bi-shield-lock-fill me-1"></i> Cambiar Contraseña
      </button>
    </form>

    <a href="index.php" class="footer-link d-block mt-3">
      <i class="bi bi-arrow-left-circle me-1"></i>Volver al inicio de sesión
    </a>
  </div>
</div>

<script>
function togglePassword(inputId, iconId) {
  const input = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  if (input.type === "password") {
    input.type = "text";
    icon.classList.replace("bi-eye-fill", "bi-eye-slash-fill");
  } else {
    input.type = "password";
    icon.classList.replace("bi-eye-slash-fill", "bi-eye-fill");
  }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

