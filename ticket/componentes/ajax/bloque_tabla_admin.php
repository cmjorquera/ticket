<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../configuracion/_inicio.php';
require_once __DIR__ . '/../../../clases/Tickets/TicketAdministrador.php';
header('Content-Type: text/html; charset=UTF-8');

$usuarioId = (int) Sesion::get('id', 0);
$esGlobal = false;
$autorizado = false;
$tickets = [];
$colegios = [];
$tecnicos = [];
$errorCarga = null;
$estadoFiltro = ticket_estado_id($_GET['estado'] ?? 0);
$resumenFiltro = ticket_estado_grupo_filtro($_GET['resumen'] ?? '');
$colegioFiltro = (int) ($_GET['colegio'] ?? 0);
$tecnicoFiltro = (int) ($_GET['tecnico'] ?? 0);

try {
    $esGlobal = es_administrador_global($usuarioId, $db);
    $autorizado = $esGlobal || es_admin_colegio($usuarioId, null, $db);
    if (!$autorizado) {
        http_response_code(403);
        echo '<div class="ticket-empty"><i class="bi bi-shield-lock"></i>Acceso restringido.</div>';
        exit;
    }

    if ($esGlobal) {
        $colegios = $db->fetchAll('SELECT id_colegio, nom_colegio FROM colegio WHERE estado = 1 ORDER BY nom_colegio');
    } else {
        $colegios = $db->fetchAll(
            "SELECT DISTINCT c.id_colegio, c.nom_colegio
               FROM usuario_colegio uc
               JOIN colegio c ON c.id_colegio = uc.id_colegio
          LEFT JOIN perfiles p ON p.id_perfil = uc.id_perfil
              WHERE uc.id_usuario = ? AND uc.estado = 1 AND c.estado = 1
                AND (uc.es_admin_colegio = 1 OR LOWER(p.nombre) IN ('admin colegio','admin_colegio','administrador colegio'))
           ORDER BY c.nom_colegio",
            [$usuarioId]
        );
    }

    $idsColegio = array_map('intval', array_column($colegios, 'id_colegio'));
    if ($colegioFiltro > 0 && !in_array($colegioFiltro, $idsColegio, true)) {
        $colegioFiltro = 0;
    }
    $tecnicos = $db->fetchAll("SELECT id, CONCAT_WS(' ', nombre, apellido_paterno) AS nombre FROM usuarios WHERE id_area_trabajo = 1 AND LOWER(estado) = 'activo' ORDER BY nombre, apellido_paterno");
    $idsTecnico = array_map('intval', array_column($tecnicos, 'id'));
    if ($tecnicoFiltro > 0 && !in_array($tecnicoFiltro, $idsTecnico, true)) {
        $tecnicoFiltro = 0;
    }

    $ticketData = new TicketAdministrador($db, $usuarioId);
    $tickets = $ticketData->traer(
        $estadoFiltro > 0 ? $estadoFiltro : null,
        null,
        $esGlobal ? null : $idsColegio,
        $colegioFiltro > 0 ? $colegioFiltro : null,
        $tecnicoFiltro > 0 ? $tecnicoFiltro : null
    );
    $tickets = array_map('ticket_normalizar_fila', $tickets);
    if ($resumenFiltro !== '') {
        $tickets = array_values(array_filter(
            $tickets,
            static fn (array $ticket): bool => ticket_estado_grupo((int) $ticket['id_estado']) === $resumenFiltro
        ));
    }
} catch (Throwable $ex) {
    error_log('Error AJAX en administración de tickets: ' . $ex->getMessage());
    $errorCarga = 'No fue posible consultar los tickets.';
}

$ticketsPorPagina = 6;
$totalTickets = count($tickets);
$totalPaginas = (int) ceil($totalTickets / $ticketsPorPagina);
$paginaActual = max(1, (int) ($_GET['pagina'] ?? 1));
$paginaActual = min($paginaActual, max(1, $totalPaginas));
$offsetTickets = ($paginaActual - 1) * $ticketsPorPagina;
$ticketsPagina = array_slice($tickets, $offsetTickets, $ticketsPorPagina);

require __DIR__ . '/../bloque_tabla_admin.php';
