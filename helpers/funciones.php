<?php
declare(strict_types=1);

// Redirige a una ruta interna o absoluta y detiene la ejecución.
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

// Responde JSON con estructura uniforme para APIs.
function jsonResponse(bool $success, string $mensaje, $data = null): void
{
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'mensaje' => $mensaje,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Limpia entradas simples o arreglos recursivamente.
function sanitize($input)
{
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }

    return htmlspecialchars(trim((string) $input), ENT_QUOTES, 'UTF-8');
}

// Formatea una fecha en formato chileno dd-mm-YYYY.
function formatFecha($fecha): string
{
    if (empty($fecha)) {
        return '';
    }

    $timestamp = strtotime((string) $fecha);
    return $timestamp ? date('d-m-Y', $timestamp) : '';
}

// Formatea un número como peso chileno.
function formatPeso($numero): string
{
    return '$' . number_format((float) $numero, 0, ',', '.');
}

// Verifica acceso de administrador y redirige si no corresponde.
function soloAdmin(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        $sp = '/var/cpanel/php/sessions/ea-php83';
        if (!is_dir($sp)) {
            session_save_path(sys_get_temp_dir());
        }
        session_start();
    }

    if (($_SESSION['tipo'] ?? '') !== 'Administrador') {
        $_SESSION['mensaje_error'] = 'No tienes permisos para acceder a esta sección.';
        redirect('../dashboard.php');
    }
}

// Guarda errores controlados en un archivo de log compatible con hosting compartido.
function log_error(string $mensaje, string $archivo = 'app.log'): void
{
    $dir = __DIR__ . '/../logica/logs';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $linea = '[' . date('Y-m-d H:i:s') . '] ' . $mensaje . PHP_EOL;
    error_log($linea, 3, $dir . '/' . basename($archivo));
}
