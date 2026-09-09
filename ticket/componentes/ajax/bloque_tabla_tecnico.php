<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../configuracion/_inicio.php';
require_once __DIR__ . '/../../../clases/Tickets/TicketTecnico.php';
header('Content-Type: text/html; charset=UTF-8');

$usuarioId = (int) Sesion::get('id', 0);
$resumenFiltro = ticket_estado_grupo_filtro($_GET['resumen'] ?? '');
$tickets = [];
$errorCarga = null;

try {
    $ticketData = new TicketTecnico($db, $usuarioId);
    $tickets = $ticketData->traer();
    $tickets = array_map('ticket_normalizar_fila', $tickets);
    if ($resumenFiltro !== '') {
        $tickets = array_values(array_filter(
            $tickets,
            static fn (array $ticket): bool => ticket_estado_grupo((int) $ticket['id_estado']) === $resumenFiltro
        ));
    }
} catch (Throwable $ex) {
    error_log('Error AJAX al listar tickets del técnico: ' . $ex->getMessage());
    $errorCarga = 'No fue posible consultar los tickets asignados.';
}

$ticketsPorPagina = 6;
$totalTickets = count($tickets);
$totalPaginas = (int) ceil($totalTickets / $ticketsPorPagina);
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));
$paginaActual = min($paginaActual, max(1, $totalPaginas));
$offsetTickets = ($paginaActual - 1) * $ticketsPorPagina;
$ticketsPagina = array_slice($tickets, $offsetTickets, $ticketsPorPagina);

require __DIR__ . '/../bloque_tabla_tecnico.php';
