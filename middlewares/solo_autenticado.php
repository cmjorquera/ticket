<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/funciones.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['usuario_id'])) {
    $_SESSION['mensaje_error'] = 'Debes iniciar sesión para continuar.';
    redirect('../index.php');
}

