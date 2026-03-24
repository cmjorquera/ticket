<?php
session_start();

require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';

$funciones = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id'] ?? '');
$nombre = trim((htmlspecialchars($_SESSION['nombre'] ?? '') . ' ' . htmlspecialchars($_SESSION['apellido_paterno'] ?? '')));
$areaTrabajo = htmlspecialchars($_SESSION['id_area_trabajo'] ?? '');
$idPagActual = '11';

$datosEstados = json_decode($funciones->contarTicketsPorEstado(), true);
$datosCategoriasPayload = json_decode($funciones->contarTicketsPorCategoria(), true);
$datosPorcentajes = json_decode($funciones->obtenerPorcentajeEstadosPorCategoria(), true);
$promediosEstados = json_decode($funciones->obtenerPromedioTiempoEstados(), true);
$usuarios = $funciones->obtenerEstadisticasUsuarios();
$datosMensuales = json_decode($funciones->obtenerDatosGraficoColegios2(), true);

$estados = $datosEstados['estados'] ?? [];
$cantidades = array_map('intval', $datosEstados['cantidades'] ?? []);
$totalTickets = array_sum($cantidades);
$resumenEstados = [];
foreach ($estados as $indice => $estado) {
    $resumenEstados[$estado] = (int) ($cantidades[$indice] ?? 0);
}

$obtenerCuentaEstado = static function (array $resumen, array $terminos): int {
    foreach ($resumen as $nombreEstado => $cantidad) {
        $nombreNormalizado = strtolower((string) $nombreEstado);
        foreach ($terminos as $termino) {
            if (strpos($nombreNormalizado, $termino) !== false) {
                return (int) $cantidad;
            }
        }
    }

    return 0;
};

$ticketsRecibidos = $obtenerCuentaEstado($resumenEstados, ['recib']);
$ticketsAsignados = $obtenerCuentaEstado($resumenEstados, ['asign']);
$ticketsEnProceso = $obtenerCuentaEstado($resumenEstados, ['proceso']);
$ticketsTerminados = $obtenerCuentaEstado($resumenEstados, ['termin']);
$ticketsCerrados = $obtenerCuentaEstado($resumenEstados, ['cerr']);
$ticketsResueltos = $ticketsCerrados > 0 ? $ticketsCerrados : $ticketsTerminados;
$ticketsActivos = max($totalTickets - $ticketsResueltos, 0);
$porcentajeResolucion = $totalTickets > 0 ? round(($ticketsResueltos / $totalTickets) * 100, 1) : 0;

$categorias = $datosCategoriasPayload['datos'] ?? [];
$topCategorias = [];
foreach ($categorias as $nombreCategoria => $estadosCategoria) {
    $totalCategoria = array_sum(array_map('intval', $estadosCategoria));
    $topCategorias[] = [
        'nombre' => $nombreCategoria,
        'total' => $totalCategoria,
        'estados' => $estadosCategoria,
        'porcentajes' => $datosPorcentajes[$nombreCategoria]['estados'] ?? [],
    ];
}
usort($topCategorias, static function (array $a, array $b): int {
    return $b['total'] <=> $a['total'];
});
$topCategorias = array_slice($topCategorias, 0, 6);

$usuariosResumen = [];
foreach ($usuarios as $usuario) {
    $entradas = is_array($usuario['ultimas_entradas'] ?? null) ? count($usuario['ultimas_entradas']) : 0;
    $usuariosResumen[] = [
        'nombre' => $usuario['nombre_completo_usuario'] ?? 'Sin nombre',
        'activo' => !empty($usuario['activo']),
        'tickets_terminados' => (int) ($usuario['tickets_terminados'] ?? 0),
        'tickets_ingresados' => (int) ($usuario['tickets_ingresados'] ?? 0),
        'mensajes' => (int) ($usuario['mensajes'] ?? 0),
        'entradas' => $entradas,
    ];
}
usort($usuariosResumen, static function (array $a, array $b): int {
    $scoreA = $a['tickets_terminados'] + $a['tickets_ingresados'];
    $scoreB = $b['tickets_terminados'] + $b['tickets_ingresados'];

    return $scoreB <=> $scoreA;
});
$usuariosResumen = array_slice($usuariosResumen, 0, 6);

$promediosTarjetas = [
    [
        'clave' => 'recibido_a_asignado',
        'titulo' => 'Recepcion a asignacion',
        'descripcion' => 'Tiempo promedio hasta asignar tecnico',
        'icono' => 'bi-inbox',
        'valor' => (float) ($promediosEstados['recibido_a_asignado'] ?? 0),
    ],
    [
        'clave' => 'asignado_a_en_proceso',
        'titulo' => 'Asignacion a trabajo',
        'descripcion' => 'Inicio operativo del ticket',
        'icono' => 'bi-person-workspace',
        'valor' => (float) ($promediosEstados['asignado_a_en_proceso'] ?? 0),
    ],
    [
        'clave' => 'en_proceso_a_terminado',
        'titulo' => 'Trabajo a termino',
        'descripcion' => 'Resolucion tecnica promedio',
        'icono' => 'bi-tools',
        'valor' => (float) ($promediosEstados['en_proceso_a_terminado'] ?? 0),
    ],
    [
        'clave' => 'terminado_a_cerrado',
        'titulo' => 'Termino a cierre',
        'descripcion' => 'Cierre administrativo del ticket',
        'icono' => 'bi-check2-circle',
        'valor' => (float) ($promediosEstados['terminado_a_cerrado'] ?? 0),
    ],
];

$versionCss = @filemtime(__DIR__ . '/css/estadistica.css') ?: time();
$versionJs = @filemtime(__DIR__ . '/js/estadistica.js') ?: time();

$dashboardData = [
    'estados' => [
        'labels' => array_values($estados),
        'values' => array_values($cantidades),
    ],
    'categorias' => array_map(static function (array $categoria): array {
        return [
            'label' => $categoria['nombre'],
            'total' => $categoria['total'],
        ];
    }, $topCategorias),
    'mensual' => $datosMensuales ?: new stdClass(),
    'ciclo' => array_map(static function (array $item): array {
        return [
            'label' => $item['titulo'],
            'value' => $item['valor'],
        ];
    }, $promediosTarjetas),
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="../">
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <link href="estadistica/css/estadistica.css?v=<?php echo $versionCss; ?>" rel="stylesheet">
</head>
<body id="page-top">
    <input type="hidden" id="idUsuario" value="<?php echo htmlspecialchars((string) $idUsuarioSession, ENT_QUOTES, 'UTF-8'); ?>">
    <div id="wrapper">
        <?php $funciones->menuLateral2($idUsuarioSession, $idPagActual); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>

                <div class="container-fluid">
                    <section class="inv-hero shadow-sm mb-4">
                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                            <div>
                                <span class="inv-kicker">Dashboard general</span>
                                <h1 class="inv-title mb-2">Estadistica y ciclo de vida de tickets</h1>
                                <p class="inv-subtitle mb-0">
                                    Vista ejecutiva para seguimiento operacional, tiempos de atencion y rendimiento general del sistema.
                                </p>
                            </div>
                            <div class="stats-summary-badge">
                                <span>Vista institucional</span>
                                <strong><?php echo number_format($totalTickets); ?> tickets monitoreados</strong>
                            </div>
                        </div>
                    </section>

                    <section class="row g-3 mb-4">
                        <div class="col-12 col-md-6 col-xl-3">
                            <article class="inv-stat-card inv-stat-primary d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="inv-stat-label">Tickets totales</span>
                                    <strong><?php echo number_format($totalTickets); ?></strong>
                                    <small>Base total considerada en el dashboard</small>
                                </div>
                                <div class="inv-stat-icon"><i class="bi bi-ticket-perforated"></i></div>
                            </article>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <article class="inv-stat-card inv-stat-success d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="inv-stat-label">Tickets resueltos</span>
                                    <strong><?php echo number_format($ticketsResueltos); ?></strong>
                                    <small><?php echo number_format($porcentajeResolucion, 1); ?>% del universo total</small>
                                </div>
                                <div class="inv-stat-icon"><i class="bi bi-patch-check"></i></div>
                            </article>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <article class="inv-stat-card inv-stat-warning d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="inv-stat-label">Tickets activos</span>
                                    <strong><?php echo number_format($ticketsActivos); ?></strong>
                                    <small>Pendientes de cierre o resolucion final</small>
                                </div>
                                <div class="inv-stat-icon"><i class="bi bi-hourglass-split"></i></div>
                            </article>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <article class="inv-stat-card inv-stat-info d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="inv-stat-label">Estados clave</span>
                                    <strong><?php echo number_format($ticketsEnProceso + $ticketsAsignados + $ticketsRecibidos); ?></strong>
                                    <small>Recibidos, asignados y en proceso</small>
                                </div>
                                <div class="inv-stat-icon"><i class="bi bi-arrow-repeat"></i></div>
                            </article>
                        </div>
                    </section>

                    <section class="row g-4 mb-4">
                        <div class="col-12 col-xl-8">
                            <div class="panel-card h-100">
                                <div class="panel-card__header">
                                    <div>
                                        <span class="panel-card__eyebrow">Distribucion operacional</span>
                                        <h2 class="panel-card__title">Estado actual de tickets</h2>
                                    </div>
                                    <span class="panel-chip"><?php echo count($resumenEstados); ?> estados</span>
                                </div>
                                <div class="row g-4 align-items-center">
                                    <div class="col-12 col-lg-6">
                                        <div class="chart-shell">
                                            <canvas id="chartEstados"></canvas>
                                        </div>
                                    </div>
                                    <div class="col-12 col-lg-6">
                                        <div class="state-list">
                                            <?php foreach ($resumenEstados as $estadoNombre => $cantidadEstado): ?>
                                                <?php $porcentajeEstado = $totalTickets > 0 ? round(($cantidadEstado / $totalTickets) * 100, 1) : 0; ?>
                                                <div class="state-list__item">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars((string) $estadoNombre, ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <span><?php echo $porcentajeEstado; ?>% del total</span>
                                                    </div>
                                                    <div class="state-list__metric">
                                                        <strong><?php echo number_format($cantidadEstado); ?></strong>
                                                        <div class="state-list__bar">
                                                            <span style="width: <?php echo max(4, min(100, $porcentajeEstado)); ?>%;"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-4">
                            <div class="panel-card h-100">
                                <div class="panel-card__header">
                                    <div>
                                        <span class="panel-card__eyebrow">Semaforo operativo</span>
                                        <h2 class="panel-card__title">Lectura rapida</h2>
                                    </div>
                                </div>
                                <div class="signal-grid">
                                    <div class="signal-card">
                                        <span class="signal-card__label">Recibidos</span>
                                        <strong><?php echo number_format($ticketsRecibidos); ?></strong>
                                    </div>
                                    <div class="signal-card">
                                        <span class="signal-card__label">Asignados</span>
                                        <strong><?php echo number_format($ticketsAsignados); ?></strong>
                                    </div>
                                    <div class="signal-card">
                                        <span class="signal-card__label">En proceso</span>
                                        <strong><?php echo number_format($ticketsEnProceso); ?></strong>
                                    </div>
                                    <div class="signal-card">
                                        <span class="signal-card__label">Terminados</span>
                                        <strong><?php echo number_format($ticketsTerminados); ?></strong>
                                    </div>
                                </div>

                                <div class="resolution-meter">
                                    <div class="resolution-meter__head">
                                        <span>Tasa de resolucion</span>
                                        <strong><?php echo number_format($porcentajeResolucion, 1); ?>%</strong>
                                    </div>
                                    <div class="resolution-meter__bar">
                                        <span style="width: <?php echo max(6, min(100, $porcentajeResolucion)); ?>%;"></span>
                                    </div>
                                    <p class="resolution-meter__caption">
                                        Indicador basado en tickets terminados o cerrados respecto del total registrado.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="panel-card mb-4">
                        <div class="panel-card__header">
                            <div>
                                <span class="panel-card__eyebrow">Ciclo de vida</span>
                                <h2 class="panel-card__title">Promedio entre etapas del ticket</h2>
                            </div>
                            <span class="panel-chip">Horas promedio</span>
                        </div>
                        <div class="lifecycle-grid">
                            <?php foreach ($promediosTarjetas as $item): ?>
                                <article class="lifecycle-card">
                                    <div class="lifecycle-card__icon"><i class="bi <?php echo htmlspecialchars($item['icono'], ENT_QUOTES, 'UTF-8'); ?>"></i></div>
                                    <span class="lifecycle-card__title"><?php echo htmlspecialchars($item['titulo'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <strong class="lifecycle-card__value"><?php echo number_format((float) $item['valor'], 1); ?> h</strong>
                                    <p class="lifecycle-card__text"><?php echo htmlspecialchars($item['descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="row g-4 mb-4">
                        <div class="col-12 col-xl-7">
                            <div class="panel-card h-100">
                                <div class="panel-card__header">
                                    <div>
                                        <span class="panel-card__eyebrow">Carga por categoria</span>
                                        <h2 class="panel-card__title">Categorias con mayor volumen</h2>
                                    </div>
                                    <span class="panel-chip">Top 6</span>
                                </div>
                                <div class="chart-shell chart-shell--compact">
                                    <canvas id="chartCategorias"></canvas>
                                </div>
                                <div class="category-list">
                                    <?php foreach ($topCategorias as $categoria): ?>
                                        <article class="category-list__item">
                                            <div class="category-list__title-row">
                                                <strong><?php echo htmlspecialchars((string) $categoria['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                <span><?php echo number_format((int) $categoria['total']); ?> tickets</span>
                                            </div>
                                            <div class="category-list__progress">
                                                <?php
                                                $porcentajesCategoria = $categoria['porcentajes'];
                                                $segmentos = [
                                                    ['nombre' => 'Recibido', 'clase' => 'is-blue'],
                                                    ['nombre' => 'Asignado', 'clase' => 'is-slate'],
                                                    ['nombre' => 'En Proceso', 'clase' => 'is-amber'],
                                                    ['nombre' => 'Terminado', 'clase' => 'is-green'],
                                                    ['nombre' => 'Cerrado', 'clase' => 'is-dark'],
                                                ];
                                                foreach ($segmentos as $segmento):
                                                    $valorSegmento = 0;
                                                    foreach ($porcentajesCategoria as $nombreSegmento => $valor) {
                                                        if (stripos($nombreSegmento, $segmento['nombre']) !== false) {
                                                            $valorSegmento = (float) $valor;
                                                            break;
                                                        }
                                                    }
                                                    if ($valorSegmento <= 0) {
                                                        continue;
                                                    }
                                                ?>
                                                    <span class="<?php echo $segmento['clase']; ?>" style="width: <?php echo min(100, $valorSegmento); ?>%;"></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-5">
                            <div class="panel-card h-100">
                                <div class="panel-card__header">
                                    <div>
                                        <span class="panel-card__eyebrow">Actividad mensual</span>
                                        <h2 class="panel-card__title">Comportamiento por mes</h2>
                                    </div>
                                </div>
                                <div class="chart-shell chart-shell--compact">
                                    <canvas id="chartMensual"></canvas>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="row g-4">
                        <div class="col-12 col-xl-7">
                            <div class="panel-card h-100">
                                <div class="panel-card__header">
                                    <div>
                                        <span class="panel-card__eyebrow">Rendimiento de usuarios</span>
                                        <h2 class="panel-card__title">Resumen de actividad destacada</h2>
                                    </div>
                                    <span class="panel-chip">Top 6</span>
                                </div>
                                <div class="team-list">
                                    <?php foreach ($usuariosResumen as $usuario): ?>
                                        <article class="team-list__item">
                                            <div class="team-list__identity">
                                                <div class="team-list__avatar">
                                                    <?php echo strtoupper(substr($usuario['nombre'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <strong><?php echo htmlspecialchars($usuario['nombre'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                    <span class="<?php echo $usuario['activo'] ? 'is-online' : 'is-offline'; ?>">
                                                        <?php echo $usuario['activo'] ? 'Sesion activa' : 'Sin sesion activa'; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="team-list__stats">
                                                <span><strong><?php echo number_format($usuario['tickets_ingresados']); ?></strong> ingresados</span>
                                                <span><strong><?php echo number_format($usuario['tickets_terminados']); ?></strong> terminados</span>
                                                <span><strong><?php echo number_format($usuario['mensajes']); ?></strong> mensajes</span>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xl-5">
                            <div class="panel-card h-100">
                                <div class="panel-card__header">
                                    <div>
                                        <span class="panel-card__eyebrow">Analitica de tiempos</span>
                                        <h2 class="panel-card__title">Comparativa del ciclo</h2>
                                    </div>
                                </div>
                                <div class="chart-shell chart-shell--compact">
                                    <canvas id="chartCiclo"></canvas>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.estadisticaDashboardData = <?php echo json_encode($dashboardData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    </script>
    <script src="js/funciones.js"></script>
    <script src="js/mensajes.js"></script>
    <script src="js/ticket.js"></script>
    <script src="js/buscadores.js"></script>
    <script src="js/validacionTicket.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="estadistica/js/estadistica.js?v=<?php echo $versionJs; ?>"></script>
</body>
</html>
