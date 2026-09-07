<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/funciones.php';

if (session_status() === PHP_SESSION_NONE) {
    $sp = '/var/cpanel/php/sessions/ea-php83';
    if (!is_dir($sp)) {
        session_save_path(sys_get_temp_dir());
    }
    session_start();
}

if (($_SESSION['tipo'] ?? '') !== 'Administrador') {
    $_SESSION['mensaje_error'] = 'Acceso restringido solo a administradores.';
    redirect('../dashboard.php');
}
