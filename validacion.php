<?php
session_start();
include_once("class/conexion.php");

// Sanitizar y validar los inputs
$usu  = isset($_POST['usuario']) ? filter_var(trim($_POST['usuario']), FILTER_SANITIZE_EMAIL) : '';
$pass = isset($_POST['pass']) ? trim($_POST['pass']) : '';

if (empty($usu)) {
    header("Location: index.php?error=usuario_vacio");
    exit;
}

if (empty($pass)) {
    header("Location: index.php?error=clave_vacio");
    exit;
}

// Validar que el email tenga un formato correcto
if (!filter_var($usu, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.php?error=formato_incorrecto");
    exit;
}

// Conectar a la base de datos
// $db = new MySQL("ticket", "root", "seduc2024");
$db = new MySQL("", "", "");


// Proteger contra SQL Injection escapando las variables
$usu = $db->escape_string($usu);

// Consultar el usuario por email
$consulta = $db->consulta("SELECT * FROM usuarios WHERE email = '$usu'");

if ($db->num_rows($consulta) == 1) {
    $usuario = $db->fetch_array($consulta);

    if (strcasecmp($usuario['estado'], 'Pendiente') === 0) {
        header("Location: index.php?error=cuenta_pendiente&email=" . urlencode($usu));
        exit;
    }

    // Verificar si el usuario está bloqueado o ha superado los intentos fallidos
    if ($usuario['estado'] === 'bloqueado' || $usuario['intentos_fallidos'] >= 3) {
        header("Location: index.php?error=bloqueado");
        exit;
    }

    // Verificar la contraseña
    if (password_verify($pass, $usuario['clave'])) {
        // Resetear intentos fallidos
        $db->guardar("UPDATE usuarios SET intentos_fallidos = 0 WHERE email = '$usu'");

        // Establecer datos de sesión
        $_SESSION['nombre']              = $usuario['nombre'];
        $_SESSION['id']                  = $usuario['id'];
        $_SESSION['apellido_paterno']    = $usuario['apellido_paterno'];
        $_SESSION['apellido_materno']    = $usuario['apellido_materno'];
        $_SESSION['email']               = $usuario['email'];
        $_SESSION['id_area_trabajo']     = $usuario['id_area_trabajo'];
        $_SESSION['primera_vez']         = 0;

        // Redirigir a la página principal
        header("Location: principal.php");
        exit;
    } else {
        // Incrementar intentos fallidos
        $nuevosIntentos = $usuario['intentos_fallidos'] + 1;
        $db->guardar("UPDATE usuarios SET intentos_fallidos = $nuevosIntentos WHERE email = '$usu'");

        // Si los intentos son menores a 3, redirigir con el correo ingresado
        if ($nuevosIntentos < 3) {
            header("Location: index.php?error=credenciales_incorrectas&email=" . urlencode($usu));
            exit;
        } else {
            // Si se ha alcanzado el límite, bloquear al usuario y mantener el email
            $db->guardar("UPDATE usuarios SET estado = 'bloqueado' WHERE email = '$usu'");
            header("Location: index.php?error=bloqueado&email=" . urlencode($usu));
            exit;
        }
        
        exit;
    }
} else {
    header("Location: index.php?error=usuario_no_existe");
    exit;
}
?>
