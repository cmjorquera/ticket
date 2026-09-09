<?php

require_once __DIR__ . '/../clases/Session.php';
require_once __DIR__ . '/../clases/Conexion.php';
Session::iniciar();

// Nombre/rol para el topbar. El login guarda $_SESSION['nombre'] y $_SESSION['perfil'];
// se dejan las claves antiguas como respaldo.
$usuario_nombre   = $_SESSION['nombre'] ?? Session::get('usuario_nombre', 'Usuario');
$usuario_apellido = Session::get('usuario_apellido', '');
$usuario_rol      = $_SESSION['perfil'] ?? Session::get('usuario_rol', 'usuario');
$pagina_actual    = basename($_SERVER['PHP_SELF'], '.php');

$script_path = trim((string) (parse_url($_SERVER['PHP_SELF'] ?? '/', PHP_URL_PATH) ?: ''), '/');
$base_path = defined('BASE_URL')
    ? trim((string) (parse_url((string) BASE_URL, PHP_URL_PATH) ?: ''), '/')
    : '';

if ($base_path !== ''
    && ($script_path === $base_path || str_starts_with($script_path, $base_path . '/'))
) {
    $script_path = ltrim(substr($script_path, strlen($base_path)), '/');
}

$script_directory = str_replace('\\', '/', dirname($script_path));
$depth = ($script_directory === '.' || $script_directory === '')
    ? ''
    : str_repeat('../', count(array_filter(explode('/', trim($script_directory, '/')))));

// El menú lateral es dinámico: se arma desde la BD en clases/menu_lateral.php.
$breadcrumb_grupo = 'Sección';
$breadcrumb_label = $pagina_titulo ?? 'Inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pagina_titulo ?? 'Sistema') ?></title>
    <?php if (!empty($pagina_bootstrap)): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <?php endif; ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= $depth ?>css/tokens.css">
    <link rel="stylesheet" href="<?= $depth ?>css/layout.css">
    <link rel="stylesheet" href="<?= $depth ?>css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php foreach (($pagina_estilos ?? []) as $estilo): ?>
    <link rel="stylesheet" href="<?= $depth . htmlspecialchars((string) $estilo, ENT_QUOTES, 'UTF-8') ?>">
    <?php endforeach; ?>
    <?php foreach (($pagina_scripts_head ?? []) as $scriptHead): ?>
    <script src="<?= htmlspecialchars((string) $scriptHead, ENT_QUOTES, 'UTF-8') ?>" defer></script>
    <?php endforeach; ?>
</head>
<body>

<div id="app">
    <?php
    // El componente conserva la estructura visual y menu_lateral() aporta los datos de BD.
    if (isset($_SESSION['id'])) {
        $db = Conexion::getInstance('sistema_panel_central');
        $sidebar_component = __DIR__ . '/../componentes/sidebar.php';

        if (is_file($sidebar_component)) {
            require $sidebar_component;
        } else {
            require_once __DIR__ . '/../clases/menu_lateral.php';
            echo '<aside id="sidebar">';
            menu_lateral((int) $_SESSION['id'], $db, $pagina_actual);
            echo '</aside>';
        }
    }
    ?>

    <div id="mobile-overlay" onclick="closeMobileSidebar()"></div>

    <!-- Contenido principal -->
    <div id="main">

        <!-- Topbar -->
        <header id="topbar" class="topbar">
            <div class="topbar-left">
                <button id="mobile-menu-btn" class="topbar-toggle" onclick="toggleMobileSidebar()">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <span class="mobile-brand"><i class="fa-solid fa-graduation-cap"></i> SEDUC</span>
                <span class="breadcrumb">
                    SEDUC <i class="fa-solid fa-chevron-right"></i>
                    <?= htmlspecialchars($breadcrumb_grupo) ?> <i class="fa-solid fa-chevron-right"></i>
                    <strong><?= htmlspecialchars($breadcrumb_label) ?></strong>
                </span>
            </div>
            <div class="topbar-right">
                <button class="topbar-icon" onclick="toggleTheme()" title="Modo claro/oscuro">
                    <i class="fa-solid fa-moon" id="theme-icon"></i>
                </button>
                <div class="topbar-dd">
                    <button class="topbar-icon notification-btn" onclick="toggleDD(this)" title="Notificaciones">
                        <i class="fa-solid fa-bell"></i>
                        <span class="notification-dot" id="notification-dot"></span>
                    </button>
                    <div class="dropdown-menu notification-menu">
                        <div class="notification-head">
                            <strong>Notificaciones</strong>
                            <button type="button" onclick="markAllRead(event)">Marcar todas como leídas</button>
                        </div>
                        <div id="notification-list"></div>
                    </div>
                </div>
                <div class="topbar-dd">
                    <button class="topbar-user" onclick="toggleDD(this)">
                        <i class="fa-solid fa-user-circle"></i>
                        <span><?= htmlspecialchars($usuario_nombre . ' ' . $usuario_apellido) ?></span>
                        <i class="fa-solid fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?= $depth ?>pages/perfil.php"><i class="fa-solid fa-user"></i> Perfil</a>
                        <a href="<?= $depth ?>cerrar_sesion.php"><i class="fa-solid fa-right-from-bracket"></i> Salir</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Zona de contenido -->
        <main id="content" class="content">
