<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../clases/DashboardTickets.php';

Sesion::requerir();

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $dashboard = new DashboardTickets($db, (int) $_SESSION['id']);
    $perfil = $dashboard->resolverPerfil((string) ($_GET['perfil'] ?? ($_SESSION['dashboard_perfil'] ?? '')));
    responder_json(['ok' => true, 'data' => $dashboard->obtenerGraficoEstados($perfil)]);
} catch (Throwable $ex) {
    responder_json(['ok' => false, 'mensaje' => 'No fue posible cargar el gráfico de estados.'], 500);
}
