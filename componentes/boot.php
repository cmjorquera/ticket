<?php

// Usar la misma lógica de sesiones que validar_sesion.php
$_vs_sp = '/var/cpanel/php/sessions/ea-php83';
if (!is_dir($_vs_sp)) {
    session_save_path(sys_get_temp_dir());
}
session_start();

// Configuración de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Registrar errores fatales
register_shutdown_function(function () {
    $error = error_get_last();
    if (!$error || !in_array($error['type'], array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR), true)) {
        return;
    }

    $log = '[' . date('Y-m-d H:i:s') . '] ' . $error['message'] . ' en ' . $error['file'] . ':' . $error['line'] . PHP_EOL;
    @file_put_contents(dirname(__DIR__) . '/error.log', $log, FILE_APPEND);
});

// Cargar clases necesarias desde la ruta correcta
$raiz = dirname(__DIR__);
require_once $raiz . '/clases/Conexion.php';
if (file_exists($raiz . '/clases/Funciones.php')) {
    require_once $raiz . '/clases/Funciones.php';
}

// Conexión compartida para todo el sistema
try {
    $bdato = Conexion::getInstance('sistema_panel_central');
} catch (Exception $e) {
    die('Error de conexión a BD: ' . $e->getMessage());
}

// Inicializar variables de sesión
$idUsuarioSession = (int)($_SESSION['id'] ?? 0);
$nombreUsuarioSession = trim((string)($_SESSION['nombre'] ?? '') . ' ' . (string)($_SESSION['apellido_paterno'] ?? ''));

// Función para escapar HTML
if (!function_exists('h')) {
    function h($valor) {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

?>
