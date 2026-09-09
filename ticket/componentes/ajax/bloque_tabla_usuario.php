<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../configuracion/_inicio.php';
require_once __DIR__ . '/../../../clases/Tickets/TicketUsuario.php';
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo '<tbody></tbody>';
    exit;
}

$usuarioId = (int) Sesion::get('id', 0);
$misTickets = [];
$errorListado = null;
$etiquetasEstado = [
    'nuevo' => 'Nuevo',
    'asignado' => 'Asignado',
    'en_proceso' => 'En proceso',
    'borrador' => 'Borrador',
    'atrasado' => 'Demorado',
    'resuelto' => 'Terminado',
];

try {
    $ticketData = new TicketUsuario($db, $usuarioId);
    $misTickets = $ticketData->traer();
    $misTickets = array_map('ticket_normalizar_fila', $misTickets);
} catch (Throwable $ex) {
    error_log('Error AJAX al listar tickets del usuario: ' . $ex->getMessage());
    $ultimoDebug = (int) ($_SESSION['ticket_debug_polling'] ?? 0);
    if ($ultimoDebug < time() - 60) {
        $_SESSION['ticket_debug_polling'] = time();
        error_log('Detalle polling de tickets: ' . $ex->getMessage());
    }
    $errorListado = 'No fue posible consultar tus solicitudes en este momento.';
}

header('X-Ticket-Count: ' . count($misTickets));

$ticketsPorPagina = 6;
$totalTickets = count($misTickets);
$totalPaginas = (int) ceil($totalTickets / $ticketsPorPagina);
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));
$paginaActual = min($paginaActual, max(1, $totalPaginas));
$offsetTickets = ($paginaActual - 1) * $ticketsPorPagina;
$ticketsPagina = array_slice($misTickets, $offsetTickets, $ticketsPorPagina);

if ($errorListado) {
    http_response_code(500);
    echo '<tbody></tbody>';
    exit;
}

if (!$misTickets) {
    echo '<tbody><tr class="ticket-poll-empty"><td colspan="7"><div class="ticket-empty"><i class="bi bi-inbox"></i>No hay solicitudes registradas.</div></td></tr></tbody>';
    exit;
}

ob_start();
require __DIR__ . '/../bloque_tabla_usuario.php';
$html = (string) ob_get_clean();

if (preg_match('/<tbody>(.*?)<\/tbody>/si', $html, $coincidencia) === 1) {
    echo '<tbody>' . $coincidencia[1] . '</tbody>';
    exit;
}

http_response_code(500);
echo '<tbody></tbody>';
