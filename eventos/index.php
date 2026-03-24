<?php
require_once __DIR__ . '/bootstrap.php';

$usuario = eventos_requiere_login();
$contexto = eventos_contexto_dashboard();
$tituloPagina = 'Eventos';
$idUsuarioSession = (int) ($usuario['id'] ?? 0);
$idPagActual = '13';
$funciones = new Funciones();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= eventos_h($tituloPagina) ?></title>
    <link rel="icon" type="image/x-icon" href="<?= eventos_h(eventos_sistema_url('imagenes/logo_seduc.png')) ?>">
    <link href="<?= eventos_h(eventos_sistema_url('vendor/fontawesome-free/css/all.min.css')) ?>" rel="stylesheet" type="text/css">
    <link href="<?= eventos_h(eventos_sistema_url('css/sb-admin-2.min.css')) ?>" rel="stylesheet">
    <link href="<?= eventos_h(eventos_sistema_url('css/modalesTicket.css')) ?>" rel="stylesheet">
    <link href="<?= eventos_h(eventos_sistema_url('css/estilo.css')) ?>" rel="stylesheet">
    <link href="<?= eventos_h(eventos_sistema_url('css/bitacora.css')) ?>" rel="stylesheet">
    <link href="<?= eventos_h(eventos_sistema_url('css/contenedor.css')) ?>" rel="stylesheet">
    <link href="<?= eventos_h(eventos_sistema_url('css/principal.css')) ?>" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css" rel="stylesheet">
    <link href="css/eventos.css?v=<?= eventos_h(eventos_asset_version('css/eventos.css')) ?>" rel="stylesheet">
</head>
<body id="page-top" class="bg-light">
    <input type="hidden" id="idUsuario" value="<?= (int) $idUsuarioSession ?>">
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
                <div class="container-fluid pb-4">
                    <div class="row">
                        <div class="col-12">
                            <div class="eventos-hero card shadow-sm border-0 mb-4">
                                <div class="card-body p-4 p-lg-5">
                                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                        <div>
                                            <span class="eventos-kicker">Modulo institucional</span>
                                            <h1 class="eventos-title mb-2">Gestión de Eventos</h1>
                                            <p class="eventos-subtitle mb-0">Planifica, coordina y reporta actividades desde un solo calendario operativo.</p>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <button type="button" class="btn btn-primary" data-eventos-action="nuevo">
                                                <i class="bi bi-plus-circle me-1"></i> Nuevo evento
                                            </button>
                                            <a href="calendario.php" class="btn btn-outline-primary">
                                                <i class="bi bi-calendar3 me-1"></i> Vista calendario
                                            </a>
                                            <a href="reporte.php" class="btn btn-outline-dark">
                                                <i class="bi bi-bar-chart-line me-1"></i> Reportes
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        $cards = [
                            ['label' => 'Eventos hoy', 'value' => $contexto['resumen']['hoy'], 'icon' => 'bi-calendar-day', 'tone' => 'primary'],
                            ['label' => 'Esta semana', 'value' => $contexto['resumen']['semana'], 'icon' => 'bi-calendar-week', 'tone' => 'success'],
                            ['label' => 'Este mes', 'value' => $contexto['resumen']['mes'], 'icon' => 'bi-calendar3', 'tone' => 'info'],
                            ['label' => 'Cancelados', 'value' => $contexto['resumen']['cancelados'], 'icon' => 'bi-x-octagon', 'tone' => 'danger'],
                        ];
                        foreach ($cards as $card):
                        ?>
                            <div class="col-xl-3 col-md-6 mb-4">
                                <div class="eventos-stat eventos-stat-<?= eventos_h($card['tone']) ?> card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="eventos-stat-label"><?= eventos_h($card['label']) ?></div>
                                                <div class="eventos-stat-value"><?= (int) $card['value'] ?></div>
                                            </div>
                                            <div class="eventos-stat-icon">
                                                <i class="bi <?= eventos_h($card['icon']) ?>"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="col-xl-8 mb-4">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                                    <div>
                                        <h2 class="eventos-section-title mb-1">Calendario operativo</h2>
                                        <p class="text-muted mb-0">Haz clic sobre un día para crear o sobre un evento para editar.</p>
                                    </div>
                                    <span class="badge bg-light text-dark">FullCalendar</span>
                                </div>
                                <div class="card-body px-3 px-lg-4 pb-4">
                                    <div id="eventos-calendar" class="eventos-calendar"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-4 mb-4">
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white border-0 pt-4 px-4">
                                    <h2 class="eventos-section-title mb-1">Eventos de hoy</h2>
                                    <p class="text-muted mb-0">Seguimiento rápido de la jornada.</p>
                                </div>
                                <div class="card-body px-4 pt-3">
                                    <div id="lista-eventos-hoy" class="eventos-side-list">
                                        <?php if (!$contexto['eventos_hoy']): ?>
                                            <div class="eventos-empty">No hay eventos programados para hoy.</div>
                                        <?php endif; ?>
                                        <?php foreach ($contexto['eventos_hoy'] as $evento): ?>
                                            <article class="eventos-side-item">
                                                <span class="eventos-side-dot" style="background: <?= eventos_h($evento['color_evento']) ?>"></span>
                                                <div>
                                                    <h3><?= eventos_h($evento['titulo']) ?></h3>
                                                    <p><?= eventos_h(substr($evento['hora_inicio'], 0, 5)) ?> · <?= eventos_h($evento['ubicacion']) ?></p>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-header bg-white border-0 pt-4 px-4">
                                    <h2 class="eventos-section-title mb-1">Próximos eventos</h2>
                                    <p class="text-muted mb-0">Próximas actividades registradas.</p>
                                </div>
                                <div class="card-body px-4 pt-3">
                                    <div id="lista-proximos-eventos" class="eventos-side-list">
                                        <?php foreach ($contexto['proximos'] as $evento): ?>
                                            <article class="eventos-side-item">
                                                <span class="eventos-side-dot" style="background: <?= eventos_h($evento['color_evento']) ?>"></span>
                                                <div>
                                                    <h3><?= eventos_h($evento['titulo']) ?></h3>
                                                    <p><?= eventos_h($evento['fecha_inicio']) ?> · <?= eventos_h(substr($evento['hora_inicio'], 0, 5)) ?></p>
                                                </div>
                                            </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="card shadow-sm border-0">
                                <div class="card-header bg-white border-0 pt-4 px-4">
                                    <h2 class="eventos-section-title mb-1">Requerimientos técnicos</h2>
                                    <p class="text-muted mb-0">Resumen acumulado del módulo.</p>
                                </div>
                                <div class="card-body px-4 pt-3">
                                    <div class="eventos-tech-grid">
                                        <div class="eventos-tech-box"><span>Audio</span><strong><?= (int) $contexto['requerimientos']['audio'] ?></strong></div>
                                        <div class="eventos-tech-box"><span>Música</span><strong><?= (int) $contexto['requerimientos']['musica_ambiental'] ?></strong></div>
                                        <div class="eventos-tech-box"><span>Presentación</span><strong><?= (int) $contexto['requerimientos']['solo_presentacion'] ?></strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
    <?php include __DIR__ . '/modals/modal_evento.php'; ?>
    <?php include __DIR__ . '/modals/modal_detalle_evento.php'; ?>
    <script>
    window.eventosResponsables = <?= json_encode($contexto['responsables'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.eventosTipoColores = <?= json_encode($contexto['tipo_colores'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.eventosUsuarioActual = <?= json_encode($usuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.idUsuarioSession = <?= json_encode($idUsuarioSession) ?>;
    window.EventosConfig = {
        page: "dashboard",
        urls: {
            eventos: "ajax/obtener_eventos.php",
            guardar: "ajax/guardar_evento_api.php",
            editar: "ajax/editar_evento.php",
            eliminar: "ajax/eliminar_evento.php",
            reporte: "ajax/obtener_reporte_eventos.php"
        }
    };
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/locales/es.js"></script>
    <script src="<?= eventos_h(eventos_sistema_url('vendor/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
    <script src="<?= eventos_h(eventos_sistema_url('vendor/jquery-easing/jquery.easing.min.js')) ?>"></script>
    <script src="<?= eventos_h(eventos_sistema_url('js/sb-admin-2.min.js')) ?>"></script>
    <script src="<?= eventos_h(eventos_sistema_url('vendor/chart.js/Chart.min.js')) ?>"></script>
    <script src="<?= eventos_h(eventos_sistema_url('js/demo/chart-area-demo.js')) ?>"></script>
    <script src="<?= eventos_h(eventos_sistema_url('js/demo/chart-pie-demo.js')) ?>"></script>
    <script src="<?= eventos_h(eventos_sistema_url('js/funciones.js')) ?>"></script>
    <script src="<?= eventos_h(eventos_sistema_url('js/mensajes.js')) ?>"></script>
    <script src="<?= eventos_h(eventos_sistema_url('js/ticket.js')) ?>"></script>
    <script src="<?= eventos_h(eventos_sistema_url('js/buscadores.js')) ?>"></script>
    <script src="<?= eventos_h(eventos_sistema_url('js/validacionTicket.js')) ?>"></script>
    <script src="<?= eventos_h(eventos_sistema_url('js/toast.js')) ?>"></script>
    <script src="js/eventos.js?v=<?= eventos_h(eventos_asset_version('js/eventos.js')) ?>"></script>
    <script src="js/calendario.js?v=<?= eventos_h(eventos_asset_version('js/calendario.js')) ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.EventosModulo) {
            window.EventosModulo.initDashboard();
        }
    });
    </script>
    <?php $funciones->script(); ?>
</body>
</html>

