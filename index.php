<?php
/**
 * LOGIN.PHP
 * 
 * Formulario de login con validación de intentos fallidos
 * - Máximo 3 intentos fallidos
 * - Al 3er intento: bloquea cuenta (estado = 'bloqueado')
 * - Si cuenta está bloqueada: rechaza login
 */

session_start();

require_once 'clases/Conexion.php';

$error = '';
$success = '';

// Si ya está autenticado, redirigir a dashboard
if (isset($_SESSION['id']) && isset($_SESSION['nombre'])) {
    header('Location: dashboard.php');
    exit;
}

// Procesar login si viene formulario POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validar campos
    if (empty($email) || empty($password)) {
        $error = '❌ Por favor completa todos los campos';
    } else {
        try {
            $db = Conexion::getInstance('sistema_panel_central');

            // 1. Verificar que el usuario existe
            $sql = "SELECT id, nombre, email, clave, estado, intentos_fallidos FROM usuarios WHERE email = ?";
            $usuario = $db->fetchOne($sql, [$email]);

            if (!$usuario) {
                $error = '❌ Usuario no encontrado';
            } else {
                // 2. Verificar si está bloqueado
                if ($usuario['estado'] === 'bloqueado') {
                    $error = '❌ Tu cuenta está bloqueada. Contacta al administrador.';
                }
                // 3. Verificar contraseña
                elseif (!password_verify($password, $usuario['clave'])) {
                    // Contraseña incorrecta - incrementar intentos fallidos
                    $intentos = $usuario['intentos_fallidos'] + 1;

                    if ($intentos >= 3) {
                        // Bloquear la cuenta en el 3er intento
                        $db->execute(
                            "UPDATE usuarios SET estado = 'bloqueado', intentos_fallidos = ? WHERE id = ?",
                            [$intentos, $usuario['id']]
                        );
                        $error = '❌ Cuenta bloqueada por exceso de intentos fallidos. Contacta al administrador.';
                    } else {
                        // Actualizar intentos fallidos
                        $db->execute(
                            "UPDATE usuarios SET intentos_fallidos = ? WHERE id = ?",
                            [$intentos, $usuario['id']]
                        );
                        $intentos_restantes = 3 - $intentos;
                        $error = "❌ Contraseña incorrecta. Te quedan $intentos_restantes intentos.";
                    }
                }
                // 4. Login exitoso
                else {
                    // Resetear intentos fallidos
                    $db->execute(
                        "UPDATE usuarios SET intentos_fallidos = 0 WHERE id = ?",
                        [$usuario['id']]
                    );

                    // Crear sesión
                    $_SESSION['id'] = $usuario['id'];
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['email'] = $usuario['email'];

                    // Redirigir al dashboard
                    header('Location: dashboard.php');
                    exit;
                }
            }
        } catch (Exception $e) {
            $error = '❌ Error en el servidor: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SEDUC Chile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-logo {
            font-size: 48px;
            margin-bottom: 12px;
        }

        .login-title {
            font-size: 24px;
            font-weight: 600;
            color: #1e1b4b;
            margin-bottom: 8px;
        }

        .login-subtitle {
            font-size: 14px;
            color: #6b7280;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.2s ease;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #6b7280;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
            }

            .login-title {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Header -->
        <div class="login-header">
            <div class="login-logo">🎓</div>
            <h1 class="login-title">SEDUC Chile</h1>
            <p class="login-subtitle">Panel Central - Inicia sesión</p>
        </div>

        <!-- Alertas -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Formulario -->
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email o Usuario</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="tu@correo.com" 
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="••••••••" 
                    required
                >
            </div>

            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>

        <!-- Footer -->
        <div class="login-footer">
            <p>¿Problemas? <a href="#">Contacta al administrador</a></p>
        </div>
    </div>
</body>
</html>