<?php
/**
 * Arranque del módulo Estadística dentro del sistema nuevo.
 * - Sesión obligatoria (clases/Session.php).
 * - Conexión PDO con credenciales de config_privada (clases/Conexion.php).
 * - Alcance de tickets igual al Dashboard (usuario / técnico / administrador).
 */
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once dirname(__DIR__, 2) . '/clases/Session.php';
require_once dirname(__DIR__, 2) . '/clases/Conexion.php';
require_once dirname(__DIR__, 2) . '/helpers/tickets.php';
require_once __DIR__ . '/../class/EstadisticaTickets.php';
Session::iniciar();

function est_es_ajax(): bool
{
    return strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/estadistica/ajax/') !== false;
}

function est_error(string $mensaje, int $codigo = 500): void
{
    http_response_code($codigo);
    if (est_es_ajax()) {
        header('Content-Type: text/html; charset=utf-8');
        echo '<div class="est-empty">' . est_h($mensaje) . '</div>';
        exit;
    }
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Estadística</title>'
        . '<style>body{font-family:Arial,sans-serif;background:#f4f7fb;color:#17324d;padding:32px}.box{max-width:900px;margin:0 auto;background:#fff;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.08)}a{color:#1d4675}</style>'
        . '</head><body><div class="box"><h1>Estadística no pudo cargar</h1><p>' . est_h($mensaje) . '</p><p><a href="../dashboard.php">Volver al inicio</a></p></div></body></html>';
    exit;
}

function est_h($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/** Entero con separador de miles chileno. */
function est_n($valor): string
{
    return number_format((float) $valor, 0, ',', '.');
}

/** Decimal chileno (1 decimal, sin ",0" sobrante). */
function est_d($valor): string
{
    if ($valor === null) {
        return '—';
    }
    $texto = number_format((float) $valor, 1, ',', '.');
    return substr($texto, -2) === ',0' ? substr($texto, 0, -2) : $texto;
}

/** Fecha + hora de proceso_tickets → DateTime (o null si está vacía). */
function est_fecha_hora($fecha, $hora = ''): ?DateTime
{
    $fecha = trim((string) $fecha);
    if ($fecha === '' || strpos($fecha, '0000-00-00') === 0) {
        return null;
    }
    $hora = trim((string) $hora);
    try {
        return new DateTime($fecha . ' ' . ($hora !== '' ? $hora : '00:00:00'));
    } catch (Throwable $e) {
        return null;
    }
}

function est_formato_fecha(?DateTime $f, string $vacio = 'Pendiente'): string
{
    return $f ? $f->format('d-m-Y H:i') : $vacio;
}

/** Duración legible entre dos momentos. */
function est_duracion(?DateTime $inicio, ?DateTime $fin): string
{
    if (!$inicio || !$fin) {
        return 'Pendiente';
    }
    if ($fin < $inicio) {
        return 'Fechas inconsistentes';
    }
    $diff = $inicio->diff($fin);
    $partes = [];
    if ($diff->days > 0) {
        $partes[] = $diff->days . ' d';
    }
    if ($diff->h > 0) {
        $partes[] = $diff->h . ' h';
    }
    if ($diff->i > 0 && count($partes) < 2) {
        $partes[] = $diff->i . ' min';
    }
    return $partes ? implode(' ', $partes) : 'Menos de 1 min';
}

function est_asset_version(string $ruta): string
{
    $fisica = dirname(__DIR__) . '/' . ltrim($ruta, '/');
    return is_file($fisica) ? (string) filemtime($fisica) : (string) time();
}

$idUsuarioSession = (int) ($_SESSION['id'] ?? 0);
if ($idUsuarioSession <= 0) {
    if (est_es_ajax()) {
        est_error('Sesión expirada. Vuelve a iniciar sesión.', 401);
    }
    header('Location: ../index.php');
    exit;
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    // "Ver como": misma elección que el Dashboard (se recuerda en la sesión)
    $perfilSolicitado = (string) ($_GET['vista_perfil'] ?? ($_SESSION['dashboard_perfil'] ?? ''));
    $estadistica = new EstadisticaTickets($db, $idUsuarioSession, $perfilSolicitado);
    $_SESSION['dashboard_perfil'] = $estadistica->perfil();
} catch (Throwable $e) {
    error_log('[estadistica] inicio: ' . $e->getMessage());
    est_error('No fue posible iniciar el módulo de estadística. Intenta nuevamente o contacta al administrador.');
}
