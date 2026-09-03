<?php
/**
 * ============================================================================
 *  INDEX  ·  Punto de entrada + LOGIN integrado  ·  SEDUC Chile - Panel Central
 * ============================================================================
 *  - session_start() propio. NO incluye validar_sesion.php (evita loop).
 *  - Procesa el login por POST.
 *  - Si ya hay sesión, redirige a dashboard.php.
 *
 *  Esquema REAL de `usuarios` (verificado):
 *    id, nombre, email, clave (hash bcrypt),
 *    estado ENUM('activo','bloqueado','inactivo','Pendiente'),
 *    intentos_fallidos INT
 *  (NO existe `password`, NO existe `intento_fallidos`, NO existe `ultima_conexion`)
 *
 *  Reglas:
 *    - email inexistente   -> "Usuario no encontrado"
 *    - estado 'bloqueado'  -> rechaza
 *    - estado 'inactivo'   -> rechaza
 *    - clave incorrecta    -> intentos_fallidos += 1; al 3º -> estado = 'bloqueado'
 *    - clave correcta      -> intentos_fallidos = 0, crea sesión, redirige
 * ============================================================================
 */

session_start();

require_once __DIR__ . '/clases/Conexion.php';

const MAX_INTENTOS = 3;

$error = '';

// Ya autenticado -> al panel
if (!empty($_SESSION['id'])) {
    header('Location: dashboard.php');
    exit;
}

// Token CSRF
if (empty($_SESSION['csrf_login'])) {
    $_SESSION['csrf_login'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $token = (string) ($_POST['csrf'] ?? '');
    $email = trim((string) ($_POST['email'] ?? ''));
    $clave = (string) ($_POST['password'] ?? '');   // el input del form se llama "password"

    if (!hash_equals($_SESSION['csrf_login'], $token)) {
        $error = 'La sesión del formulario expiró. Recarga la página e intenta de nuevo.';

    } elseif ($email === '' || $clave === '') {
        $error = 'Por favor completa todos los campos.';

    } else {
        try {
            $db = Conexion::getInstance('sistema_panel_central');

            $usuario = $db->fetchOne(
                "SELECT id, nombre, email, clave, estado, intentos_fallidos
                   FROM usuarios
                  WHERE email = ?
                  LIMIT 1",
                [$email]
            );

            if ($usuario === false) {
                $error = 'Usuario no encontrado.';

            } elseif ($usuario['estado'] === 'bloqueado') {
                $error = 'Tu cuenta está bloqueada. Contacta al administrador.';

            } elseif ($usuario['estado'] === 'inactivo') {
                $error = 'Tu cuenta está inactiva. Contacta al administrador.';

            } elseif (!password_verify($clave, (string) $usuario['clave'])) {
                $intentos = (int) $usuario['intentos_fallidos'] + 1;

                if ($intentos >= MAX_INTENTOS) {
                    $db->execute(
                        "UPDATE usuarios SET estado = 'bloqueado', intentos_fallidos = ? WHERE id = ?",
                        [$intentos, (int) $usuario['id']]
                    );
                    $error = 'Cuenta bloqueada por exceso de intentos fallidos. Contacta al administrador.';
                } else {
                    $db->execute(
                        "UPDATE usuarios SET intentos_fallidos = ? WHERE id = ?",
                        [$intentos, (int) $usuario['id']]
                    );
                    $restantes = MAX_INTENTOS - $intentos;
                    $error = "Contraseña incorrecta. Te queda"
                           . ($restantes === 1 ? '' : 'n') . " {$restantes} intento"
                           . ($restantes === 1 ? '' : 's') . '.';
                }

            } else {
                // Acceso correcto
                $db->execute(
                    "UPDATE usuarios SET intentos_fallidos = 0 WHERE id = ?",
                    [(int) $usuario['id']]
                );

                // Perfiles del usuario (opcional; el primero como principal)
                $perfiles = $db->fetchAll(
                    "SELECT pf.id_perfil, pf.nombre
                       FROM usuario_perfil up
                       INNER JOIN perfiles pf ON pf.id_perfil = up.id_perfil
                      WHERE up.id_usuario = ?
                        AND pf.estado = 'activo'
                      ORDER BY pf.id_perfil ASC",
                    [(int) $usuario['id']]
                );

                session_regenerate_id(true);

                $_SESSION['id']       = (int) $usuario['id'];
                $_SESSION['nombre']   = $usuario['nombre'];
                $_SESSION['email']    = $usuario['email'];
                $_SESSION['perfil']   = $perfiles[0]['nombre'] ?? 'Sin perfil';
                $_SESSION['perfiles'] = array_column($perfiles, 'nombre');

                unset($_SESSION['csrf_login']);

                header('Location: dashboard.php');
                exit;
            }

        } catch (\Throwable $e) {
            $error = 'Error en el servidor. Intenta más tarde.';
        }
    }
}

$csrf        = $_SESSION['csrf_login'] ?? '';
$email_valor = htmlspecialchars((string) ($_POST['email'] ?? ''), ENT_QUOTES);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SEDUC Chile</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

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

        .login-header { text-align: center; margin-bottom: 30px; }
        .login-logo { font-size: 48px; margin-bottom: 12px; }
        .login-title { font-size: 24px; font-weight: 600; color: #1e1b4b; margin-bottom: 8px; }
        .login-subtitle { font-size: 14px; color: #6b7280; }

        .form-group { margin-bottom: 20px; }

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

        .btn-login:active { transform: translateY(0); }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #6b7280;
        }

        .login-footer a { color: #667eea; text-decoration: none; font-weight: 600; }
        .login-footer a:hover { text-decoration: underline; }

        @media (max-width: 480px) {
            .login-container { padding: 30px 20px; }
            .login-title { font-size: 20px; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">🎓</div>
            <h1 class="login-title">SEDUC Chile</h1>
            <p class="login-subtitle">Panel Central - Inicia sesión</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf" value="<?php echo htmlspecialchars((string) $csrf, ENT_QUOTES); ?>">

            <div class="form-group">
                <label for="email">Email o Usuario</label>
                <input type="email" id="email" name="email"
                       placeholder="tu@correo.com" required autofocus
                       value="<?php echo $email_valor; ?>">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>

        <div class="login-footer">
            <p>¿Problemas? <a href="#">Contacta al administrador</a></p>
        </div>
    </div>
</body>
</html>
