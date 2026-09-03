<?php
/**
 * Raíz del sitio. El login vive ahora en login.php.
 * Este archivo solo redirige para que la URL raíz siga funcionando.
 */

session_start();

if (!empty($_SESSION['id'])) {
    header('Location: dashboard.php');
    exit;
}

header('Location: login.php');
exit;
