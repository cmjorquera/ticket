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
    <?php if (empty($pagina_sin_componentes)): /* módulos con Bootstrap propio (inventario) no cargan componentes */ ?>
    <link rel="stylesheet" href="<?= $depth ?>css/components.css">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= $depth ?>css/tema_institucional.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <?php foreach (($pagina_estilos ?? []) as $estilo): ?>
    <link rel="stylesheet" href="<?= (preg_match('#^(https?:)?//#', (string) $estilo) ? '' : $depth) . htmlspecialchars((string) $estilo, ENT_QUOTES, 'UTF-8') ?>">
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

        <!-- Topbar: mismo diseño que componentes/topbar.php -->
        <?php
        $topbar_nombre = trim((string) $usuario_nombre . ' ' . (string) $usuario_apellido);
        $topbar_iniciales = '';
        foreach (preg_split('/\s+/u', $topbar_nombre, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $parte) {
            $topbar_iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
            if (mb_strlen($topbar_iniciales) >= 2) {
                break;
            }
        }
        ?>
        <header id="topbar">
            <button id="mobile-menu-btn" onclick="toggleMobileSidebar()" aria-label="Abrir menú">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="breadcrumb">
                <span>SEDUC</span>
                <span class="sep">›</span>
                <span class="current"><?= htmlspecialchars((string) $breadcrumb_label) ?></span>
            </div>
            <div class="search-wrap">
                <svg class="icon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" placeholder="Buscar usuarios, colegios, módulos..."/>
            </div>
            <div class="topbar-right">
                <button class="notif-btn" type="button" aria-label="Notificaciones">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    <span class="notif-badge">3</span>
                </button>
                <div class="dropdown">
                    <button class="user-chip" type="button" onclick="toggleDropdown(this)">
                        <div class="avatar"><?= htmlspecialchars($topbar_iniciales !== '' ? $topbar_iniciales : 'U') ?></div>
                        <div class="user-info">
                            <strong><?= htmlspecialchars($topbar_nombre) ?></strong>
                            <span><?= htmlspecialchars((string) $usuario_rol) ?></span>
                        </div>
                    </button>
                    <div class="dropdown-menu" style="right:0;min-width:180px">
                        <a class="dropdown-item" href="<?= $depth ?>pages/perfil.php">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                            Perfil
                        </a>
                        <div style="height:1px;background:var(--border);margin:4px 8px"></div>
                        <a class="dropdown-item danger" href="<?= $depth ?>cerrar_sesion.php">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                            Salir
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Zona de contenido -->
        <main id="content" class="content">
