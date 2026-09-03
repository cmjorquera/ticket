<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/funciones.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (($_SESSION['tipo'] ?? '') !== 'Administrador') {
    $_SESSION['mensaje_error'] = 'Acceso restringido solo a administradores.';
    redirect('../dashboard.php');
}

