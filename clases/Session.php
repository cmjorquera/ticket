<?php

class Session
{
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $savePath = '/var/cpanel/php/sessions/ea-php83';
            if (!is_dir($savePath) || !is_writable($savePath)) {
                $fallback = sys_get_temp_dir();
                if (is_dir($fallback) && is_writable($fallback)) {
                    session_save_path($fallback);
                } else {
                    error_log('[session] No se encontró un directorio de sesiones escribible.');
                }
            }
            if (!session_start()) {
                error_log('[session] No fue posible iniciar la sesión PHP.');
                throw new RuntimeException('No fue posible iniciar la sesión.');
            }
        }
    }

    public static function destruir(): void
    {
        self::iniciar();
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();
    }

    public static function verificar(): bool
    {
        self::iniciar();
        return (int) ($_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0) > 0;
    }

    public static function set(string $key, mixed $value): void
    {
        self::iniciar();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::iniciar();
        return $_SESSION[$key] ?? $default;
    }

    public static function eliminar(string $key): void
    {
        self::iniciar();
        unset($_SESSION[$key]);
    }
}