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

/** Renderiza los indicadores resumidos de una bandeja técnica. */
final class FuncionesTicket
{
    private Conexion $db;
    private array $cache = [];

    public function __construct(Conexion $db)
    {
        $this->db = $db;
    }

    private function totales(int $usuarioId): array
    {
        if (isset($this->cache[$usuarioId])) {
            return $this->cache[$usuarioId];
        }

        $totales = ['nuevo' => 0, 'en_proceso' => 0, 'resuelto' => 0, 'atrasado' => 0];
        try {
            $filas = $this->db->fetchAll(
                'SELECT estado, COUNT(*) AS total FROM tickets WHERE id_tecnico_asignado = ? GROUP BY estado',
                [$usuarioId]
            );
            foreach ($filas as $fila) {
                $estado = strtolower((string) ($fila['estado'] ?? ''));
                if (array_key_exists($estado, $totales)) {
                    $totales[$estado] = (int) ($fila['total'] ?? 0);
                }
            }
        } catch (Throwable $ex) {
            error_log('Error al calcular contenedores de tickets: ' . $ex->getMessage());
        }

        return $this->cache[$usuarioId] = $totales;
    }

    private function renderizar(int $usuarioId, string $estado, string $titulo, string $icono, string $clase): void
    {
        $totales = $this->totales($usuarioId);
        $cantidad = (int) ($totales[$estado] ?? 0);
        $total = array_sum($totales);
        $porcentaje = $total > 0 ? (int) round(($cantidad / $total) * 100) : 0;

        echo '<article class="contenedor-ticket ' . $clase . '">';
        echo '<div class="contenedor-ticket-body"><h2 class="contenedor-ticket-titulo">' . htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . '</h2>';
        echo '<div class="contenedor-ticket-numero">' . $cantidad . '</div><small class="contenedor-ticket-porcentaje">' . $porcentaje . '% de la bandeja</small></div>';
        echo '<div class="contenedor-ticket-icono" aria-hidden="true"><i class="bi ' . htmlspecialchars($icono, ENT_QUOTES, 'UTF-8') . '"></i></div>';
        echo '</article>';
    }

    public function contenedorTicketNuevos(int $usuarioId): void
    {
        $this->renderizar($usuarioId, 'nuevo', 'Nuevos', 'bi-inbox', 'nuevos');
    }

    public function contenedorTicketEnProceso(int $usuarioId): void
    {
        $this->renderizar($usuarioId, 'en_proceso', 'En proceso', 'bi-hourglass-split', 'en-proceso');
    }

    public function contenedorTicketResueltos(int $usuarioId): void
    {
        $this->renderizar($usuarioId, 'resuelto', 'Resueltos', 'bi-check-circle', 'resueltos');
    }

    public function contenedorTicketAtrasados(int $usuarioId): void
    {
        $this->renderizar($usuarioId, 'atrasado', 'Atrasados', 'bi-exclamation-triangle', 'atrasados');
    }
}
