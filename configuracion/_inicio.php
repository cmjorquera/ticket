<?php

require_once __DIR__ . '/../clases/Session.php';
require_once __DIR__ . '/../clases/Conexion.php';
require_once __DIR__ . '/../helpers/tickets.php';
require_once __DIR__ . '/../helpers/funciones.php';
require_once __DIR__ . '/../helpers/funciones_tickets.php';

if (!class_exists('Sesion', false)) {
    final class Sesion extends Session
    {
        public static function requerir(): void
        {
            self::iniciar();
            if (!isset($_SESSION['id']) || (int) $_SESSION['id'] <= 0) {
                header('Location: ../index.php');
                exit;
            }
        }

        public static function iniciales(): string
        {
            $nombre = trim((string) ($_SESSION['nombre'] ?? $_SESSION['usuario_nombre'] ?? 'Usuario'));
            $partes = preg_split('/\s+/', $nombre, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $iniciales = '';
            foreach (array_slice($partes, 0, 2) as $parte) {
                $iniciales .= strtoupper(substr($parte, 0, 1));
            }
            return $iniciales !== '' ? $iniciales : 'U';
        }

        public static function get(string $key, mixed $default = null): mixed
        {
            self::iniciar();
            $alias = ['usuario_nombre' => 'nombre', 'usuario_perfil' => 'perfil', 'usuario_id' => 'id'];
            return $_SESSION[$key] ?? (isset($alias[$key]) ? ($_SESSION[$alias[$key]] ?? $default) : $default);
        }
    }
}

Sesion::requerir();
$db = Conexion::getInstance('sistema_panel_central');

function iniciar_layout_configuracion(string $titulo, string $breadcrumb, string $pagina): void
{
    global $db, $titulo_pagina, $breadcrumb_actual, $pagina_actual, $depth;
    $titulo_pagina = $titulo;
    $breadcrumb_actual = $breadcrumb;
    $pagina_actual = $pagina;

    require __DIR__ . '/../componentes/head.php';
    require __DIR__ . '/../componentes/sidebar.php';
    echo '<div id="mobile-overlay" onclick="closeMobileSidebar()"></div><div id="main">';
    require __DIR__ . '/../componentes/topbar.php';
    echo '<main id="content" class="content">';
}

function finalizar_layout_configuracion(): void
{
    global $depth;
    echo '</main></div>';
    require __DIR__ . '/../componentes/scripts.php';
    echo '</body></html>';
}

function e(mixed $valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
