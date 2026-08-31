<?php
/**
 * avatar-usuario.php
 * Componente reutilizable: avatar circular con las iniciales del usuario.
 *
 * Uso:
 *   require_once __DIR__ . '/componentes/avatar-usuario.php';
 *   renderAvatarUsuario($nombre, $apellido);
 *
 * El color se deriva de un hash del nombre+apellido, por lo que un mismo
 * usuario siempre obtiene el mismo color.
 */

if (!function_exists('renderAvatarUsuario')) {

    function avatarUsuarioColor(string $semilla): string
    {
        $paleta = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#6f42c1', '#fd7e14', '#20c997', '#e83e8c', '#5a5c69',
        ];
        $hash = crc32($semilla);
        return $paleta[$hash % count($paleta)];
    }

    function renderAvatarUsuario($nombre, $apellido = ''): void
    {
        $nombre   = trim((string) $nombre);
        $apellido = trim((string) $apellido);

        $ini = mb_strtoupper(
            mb_substr($nombre, 0, 1, 'UTF-8') . mb_substr($apellido, 0, 1, 'UTF-8'),
            'UTF-8'
        );
        if ($ini === '') {
            $ini = '?';
        }

        $color = avatarUsuarioColor($nombre . '|' . $apellido);
        $label = htmlspecialchars(trim($nombre . ' ' . $apellido), ENT_QUOTES, 'UTF-8');

        echo '<span class="lu-avatar-initials" style="background:' . $color . ';" title="' . $label . '">'
           . htmlspecialchars($ini, ENT_QUOTES, 'UTF-8')
           . '</span>';
    }
}
