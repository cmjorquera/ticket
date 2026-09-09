<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../clases/DashboardTickets.php';
require_once __DIR__ . '/../helpers/tickets.php';

Sesion::requerir();

try {
    $entrada = entrada_ajax();
    if (!ticket_csrf_valido((string) ($entrada['csrf'] ?? ''))) {
        responder_json(['ok' => false, 'mensaje' => 'La sesión de seguridad expiró.'], 419);
    }

    $db = Conexion::getInstance('sistema_panel_central');
    $dashboard = new DashboardTickets($db, (int) $_SESSION['id']);
    $solicitado = (string) ($entrada['perfil'] ?? '');
    if (!in_array($solicitado, ['usuario', 'tecnico', 'administrador'], true)) {
        responder_json(['ok' => false, 'mensaje' => 'El perfil seleccionado no es válido.'], 422);
    }
    $perfil = $dashboard->resolverPerfil($solicitado);
    if ($perfil !== DashboardTickets::normalizarPerfil($solicitado)) {
        responder_json(['ok' => false, 'mensaje' => 'El perfil seleccionado no está disponible.'], 403);
    }

    $_SESSION['dashboard_perfil'] = $perfil;
    responder_json(['ok' => true, 'perfil' => $perfil]);
} catch (Throwable $ex) {
    error_log('Error al cambiar perfil del dashboard: ' . $ex->getMessage());
    responder_json(['ok' => false, 'mensaje' => 'No fue posible cambiar el perfil.'], 500);
}
