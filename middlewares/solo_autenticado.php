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

if (empty($_SESSION['usuario_id'])) {
    $_SESSION['mensaje_error'] = 'Debes iniciar sesión para continuar.';
    redirect('../index.php');
}
