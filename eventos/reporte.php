<?php
require_once __DIR__ . '/bootstrap.php';

$usuario = eventos_requiere_login();
$tituloPagina = 'Reporte de Eventos';
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
    <link href="<?= eventos_h(eventos_sistema_url('vendor/fontawesome-free/css/all.min.css')) ?>" rel="stylesheet" type="text/css">
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
                            <div class="card shadow-sm border-0 mb-4">
                                <div class="card-body p-4">
                                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                        <div>
                                            <span class="eventos-kicker">Analítica del módulo</span>
                                            <h1 class="eventos-title mb-2">Reportes de eventos</h1>
                                            <p class="eventos-subtitle mb-0">Indicadores visuales para planificación, estados y demanda técnica.</p>
                                        </div>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="index.php" class="btn btn-outline-dark">Volver al dashboard</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white border-0 pt-4 px-4"><h2 class="eventos-section-title mb-0">Eventos por mes</h2></div><div class="card-body"><canvas id="chart-eventos-mes"></canvas></div></div></div>
                        <div class="col-lg-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white border-0 pt-4 px-4"><h2 class="eventos-section-title mb-0">Eventos por tipo</h2></div><div class="card-body"><canvas id="chart-eventos-tipo"></canvas></div></div></div>
                        <div class="col-lg-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white border-0 pt-4 px-4"><h2 class="eventos-section-title mb-0">Eventos por estado</h2></div><div class="card-body"><canvas id="chart-eventos-estado"></canvas></div></div></div>
                        <div class="col-lg-6 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white border-0 pt-4 px-4"><h2 class="eventos-section-title mb-0">Top responsables</h2></div><div class="card-body"><canvas id="chart-top-responsables"></canvas></div></div></div>
                        <div class="col-12 mb-4"><div class="card shadow-sm border-0 h-100"><div class="card-header bg-white border-0 pt-4 px-4"><h2 class="eventos-section-title mb-0">Requerimientos técnicos</h2></div><div class="card-body"><canvas id="chart-requerimientos"></canvas></div></div></div>
                    </div>
                </div>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>
    <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
    <script>
    window.eventosUsuarioActual = <?= json_encode($usuario, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.idUsuarioSession = <?= json_encode($idUsuarioSession) ?>;
    window.EventosConfig = {
        page: "reports",
        urls: {
            reporte: "ajax/obtener_reporte_eventos.php"
        }
    };
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <script src="js/reporte_eventos.js?v=<?= eventos_h(eventos_asset_version('js/reporte_eventos.js')) ?>"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.EventosReportes) {
            window.EventosReportes.init();
        }
    });
    </script>
    <?php $funciones->script(); ?>
</body>
</html>

