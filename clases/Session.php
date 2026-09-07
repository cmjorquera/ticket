<?php

class Session
{
    public static function iniciar(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            $savePath = '/var/cpanel/php/sessions/ea-php83';
            if (!is_dir($savePath)) {
                session_save_path(sys_get_temp_dir());
            }
            session_start();
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
        return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
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