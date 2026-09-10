<?php
declare(strict_types=1);

$depth = '';
require_once __DIR__ . '/configuracion/_inicio.php';
require_once __DIR__ . '/clases/DashboardTickets.php';

$con_charts = true;
$pagina_estilos = ['css/dashboard.css'];
$pagina_scripts_head = [
    'https://cdn.jsdelivr.net/npm/apexcharts@3.54.1/dist/apexcharts.min.js',
];
$pagina_scripts = ['js/datatables.js', 'js/funciones.js', 'js/dashboard.js'];

$usuarioId = (int) ($_SESSION['id'] ?? 0);
$perfiles = [['clave' => 'usuario', 'nombre' => 'Usuario']];
$perfilActual = 'usuario';
$errorInicial = '';

try {
    $dashboard = new DashboardTickets($db, $usuarioId);
    $perfiles = $dashboard->perfilesDisponibles();
    $perfilSolicitado = (string) ($_GET['vista_perfil'] ?? ($_SESSION['dashboard_perfil'] ?? ''));
    $perfilActual = $dashboard->resolverPerfil($perfilSolicitado);
    $_SESSION['dashboard_perfil'] = $perfilActual;
} catch (Throwable $ex) {
    error_log('No fue posible iniciar el dashboard: ' . $ex->getMessage());
    $errorInicial = 'No fue posible preparar el dashboard. Intenta recargar la página.';
}

$csrfDashboard = ticket_csrf_token();
iniciar_layout_configuracion('Dashboard', 'Dashboard', 'dashboard');
?>

<div class="dashboard-page" id="dashboard-app" aria-busy="true">
    <header class="page-header dashboard-heading">
        <div>
            <h1>Dashboard</h1>
            <p>Estado operativo de tus solicitudes y carga de atención.</p>
        </div>
        <?php if (count($perfiles) > 1): ?>
            <nav class="dashboard-profiles" aria-label="Vista del dashboard" style="--profile-count:<?= count($perfiles) ?>">
                <span>Ver como</span>
                <div class="dashboard-profile-group" role="group">
                    <?php foreach ($perfiles as $perfil): ?>
                        <button type="button"
                                class="btn dashboard-profile-btn<?= $perfil['clave'] === $perfilActual ? ' active' : '' ?>"
                                data-dashboard-profile="<?= htmlspecialchars($perfil['clave'], ENT_QUOTES, 'UTF-8') ?>"
                                aria-pressed="<?= $perfil['clave'] === $perfilActual ? 'true' : 'false' ?>">
                            <?= htmlspecialchars($perfil['nombre'], ENT_QUOTES, 'UTF-8') ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            </nav>
        <?php endif; ?>
    </header>

    <div class="dashboard-alert" id="dashboard-alert" role="alert"<?= $errorInicial === '' ? ' hidden' : '' ?>>
        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
        <span><?= htmlspecialchars($errorInicial, ENT_QUOTES, 'UTF-8') ?></span>
        <button type="button" id="dashboard-retry">Reintentar</button>
    </div>

    <section aria-labelledby="dashboard-kpi-title">
        <div class="dashboard-section-heading">
            <div>
                <h2 id="dashboard-kpi-title">Resumen de atención</h2>
                <p>Selecciona una tarjeta para filtrar el listado.</p>
            </div>
            <p class="dashboard-filter-status" id="dashboard-filter-status" role="status" aria-live="polite" hidden></p>
        </div>
        <div class="contenedor-tickets dashboard-kpi-grid" id="dashboard-kpis">
            <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="dashboard-kpi-cell"><div class="dashboard-skeleton dashboard-skeleton-kpi"></div></div>
            <?php endfor; ?>
        </div>
    </section>

    <section class="chart-grid dashboard-chart-grid" aria-label="Gráficos de tickets">
        <div>
            <article class="card dashboard-panel">
                <div class="card-header dashboard-panel-header">
                    <div><h2>Gráfico de tickets</h2><p>Distribución actual de la bandeja.</p></div>
                    <?php if (count($perfiles) > 1): ?>
                        <label class="dashboard-chart-profile-label">Perfil
                            <select class="dashboard-chart-profile" data-chart-profile aria-label="Perfil del gráfico de tickets">
                                <?php foreach ($perfiles as $perfil): ?><option value="<?= htmlspecialchars($perfil['clave'], ENT_QUOTES, 'UTF-8') ?>"<?= $perfil['clave'] === $perfilActual ? ' selected' : '' ?>><?= htmlspecialchars($perfil['nombre'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                            </select>
                        </label>
                    <?php else: ?><span class="dashboard-profile-label" data-profile-label></span><?php endif; ?>
                </div>
                <div class="card-body dashboard-chart-body">
                    <div class="dashboard-chart-loader" id="loadingGraficoTicket" role="status"><span class="dashboard-loader-dot" aria-hidden="true"></span><span>Cargando gráfico</span></div>
                    <canvas id="graficoTicket" role="img" aria-label="Cantidad de tickets por estado"></canvas>
                </div>
            </article>
        </div>
        <div>
            <article class="card dashboard-panel">
                <div class="card-header dashboard-panel-header">
                    <div><h2>Estados por categoría</h2><p>Comparación de carga entre tipos de solicitud.</p></div>
                    <?php if (count($perfiles) > 1): ?>
                        <label class="dashboard-chart-profile-label">Perfil
                            <select class="dashboard-chart-profile" data-chart-profile aria-label="Perfil del gráfico por categoría">
                                <?php foreach ($perfiles as $perfil): ?><option value="<?= htmlspecialchars($perfil['clave'], ENT_QUOTES, 'UTF-8') ?>"<?= $perfil['clave'] === $perfilActual ? ' selected' : '' ?>><?= htmlspecialchars($perfil['nombre'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                            </select>
                        </label>
                    <?php else: ?><span class="dashboard-profile-label" data-profile-label></span><?php endif; ?>
                </div>
                <div class="card-body dashboard-chart-body">
                    <div class="dashboard-chart-loader" id="loadingGraficoCategorias" role="status"><span class="dashboard-loader-dot" aria-hidden="true"></span><span>Cargando gráfico</span></div>
                    <div id="graficoCategorias" aria-label="Estados de tickets por categoría"></div>
                </div>
            </article>
        </div>
    </section>

    <section class="card dashboard-panel dashboard-table-panel" aria-labelledby="dashboard-table-title">
        <div class="card-header dashboard-panel-header">
            <div><h2 id="dashboard-table-title">Últimos tickets</h2><p id="dashboard-table-description">Los casos más recientes disponibles para el perfil activo.</p></div>
            <span class="dashboard-list-count" id="dashboard-list-count">—</span>
        </div>
        <div class="dashboard-table-filters" aria-label="Filtros del listado">
            <label class="dashboard-filter-search">Buscar<input class="form-input" id="dashboard-filter-search" type="search" placeholder="Folio, asunto, usuario, técnico o categoría"></label>
            <label>Estado<select class="form-input" id="dashboard-filter-state"><option value="">Todos los estados</option></select></label>
            <label>Fecha de creación<input class="form-input" id="dashboard-filter-created" type="date"></label>
            <label>Fecha de respuesta<input class="form-input" id="dashboard-filter-response" type="date"></label>
            <button class="btn btn-outline btn-sm" type="button" id="dashboard-clear-filters"><i class="bi bi-x-circle"></i> Limpiar</button>
        </div>
        <div id="dashboard-ticket-table"><div class="dashboard-table-loading"><span class="dashboard-loader-dot" aria-hidden="true"></span> Cargando tickets</div></div>
    </section>

    <noscript><div class="dashboard-alert"><i class="bi bi-exclamation-circle"></i><span>Activa JavaScript para consultar los indicadores y gráficos.</span></div></noscript>
</div>

<script>
window.SEDUC_DASHBOARD = <?= json_encode([
    'perfil' => $perfilActual,
    'csrf' => $csrfDashboard,
    'endpoints' => [
        'datos' => 'ajax/dashboard_datos.php',
        'cambiarPerfil' => 'ajax/cambiarPerfilDashboard.php',
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
</script>

<?php finalizar_layout_configuracion(); ?>
