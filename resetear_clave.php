<?php
/**
 * RESETEAR_CONTRASEÑA.PHP
 * 
 * Script para generar y guardar contraseña en la BD automáticamente
 * SOLO PARA DESARROLLO - BORRAR DESPUÉS DE USAR
 * 
 * Uso:
 * 1. Abre este archivo en el navegador
 * 2. Ingresa el email y contraseña
 * 3. Click en "Resetear"
 * 4. Listo, ya puedes login
 * 5. BORRA ESTE ARCHIVO
 */

require_once 'clases/Conexion.php';

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $nueva_password = trim($_POST['password'] ?? '');
    
    if (empty($email) || empty($nueva_password)) {
        $error = '❌ Completa email y contraseña';
    } else {
        try {
            $db = Conexion::getInstance('sistema_panel_central');
            
            // Generar hash
            $hash = password_hash($nueva_password, PASSWORD_BCRYPT);
            
            // Actualizar BD
            $db->execute(
                "UPDATE usuarios SET clave = ?, intentos_fallidos = 0 WHERE email = ?",
                [$hash, $email]
            );
            
            $mensaje = "✅ Contraseña actualizada. Email: $email | Contraseña: $nueva_password";
        } catch (Exception $e) {
            $error = '❌ Error: ' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resetear Contraseña</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h1 { margin-bottom: 20px; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
        button { width: 100%; padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background: #45a049; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .alert-error { background: #ffdddd; color: #d32f2f; }
        .alert-success { background: #ddffdd; color: #388e3c; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 4px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>⚠️ Resetear Contraseña</h1>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email del Usuario:</label>
                <input type="email" name="email" placeholder="ejemplo@seduc.cl" required>
            </div>
            
            <div class="form-group">
                <label>Nueva Contraseña:</label>
                <input type="text" name="password" placeholder="112233" required>
            </div>
            
            <button type="submit">Resetear Contraseña</button>
        </form>
        
        <div class="warning">
            <strong>⚠️ IMPORTANTE:</strong><br>
            - Este archivo es SOLO para desarrollo<br>
            - Úsalo para resetear contraseña de prueba<br>
            - <strong>BORRA ESTE ARCHIVO DESPUÉS</strong><br>
            - NO lo dejes en producción
        </div>
    </div>
</body>
</html>