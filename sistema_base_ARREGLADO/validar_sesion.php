<?php
/**
 * VALIDAR_SESION.PHP
 * 
 * Middleware para validar que:
 * - La sesión existe
 * - El usuario existe en BD
 * - El usuario NO está bloqueado/inactivo
 * 
 * Incluir al inicio de CUALQUIER página protegida:
 * require_once 'includes/validar_sesion.php';
 */

session_start();

// 1. Verificar que hay sesión con id de usuario
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

// 2. Verificar que el usuario existe en BD y está activo
try {
    require_once __DIR__ . '/../clases/Conexion.php';
    
    $db = Conexion::getInstance('sistema_panel_central');
    
    $sql = "SELECT id, nombre, email, estado FROM usuarios WHERE id = ?";
    $usuario = $db->fetchOne($sql, [(int)$_SESSION['id']]);
    
    // 2a. Si el usuario no existe, destruir sesión
    if (!$usuario) {
        session_destroy();
        header('Location: login.php');
        exit;
    }
    
    // 2b. Si el usuario está bloqueado, destruir sesión
    if ($usuario['estado'] === 'bloqueado') {
        session_destroy();
        header('Location: login.php?error=bloqueado');
        exit;
    }
    
    // 2c. Si el usuario está inactivo, destruir sesión
    if ($usuario['estado'] === 'inactivo') {
        session_destroy();
        header('Location: login.php?error=inactivo');
        exit;
    }
    
    // 2d. Si el usuario está pendiente (sin activar), podría bloquearse según lógica
    // Por ahora permitimos pendientes
    
} catch (Exception $e) {
    // Error de conexión
    die('❌ Error validando sesión: ' . $e->getMessage());
}

// ✅ Usuario validado correctamente
// Puedes continuar con el resto de la página
?>