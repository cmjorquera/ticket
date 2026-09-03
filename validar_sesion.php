<?php
/**
 * VALIDAR_SESION.PHP - VERSIÓN CORREGIDA
 * 
 * Middleware para validar sesión activa
 * Usa estructura REAL de BD
 */

session_start();

// 1. Verificar que hay sesión con id de usuario
if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit;
}

// 2. Verificar que el usuario existe en BD y está activo
try {
    require_once __DIR__ . '/clases/Conexion.php';
    
    $db = Conexion::getInstance('sistema_panel_central');
    
    // Consultar usuario (columnas REALES)
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
    
    // ✅ Usuario validado correctamente
    
} catch (Exception $e) {
    // Error de conexión
    die('❌ Error validando sesión: ' . $e->getMessage());
}

?>