<?php
session_start();
require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';

$funciones = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$idPagActual = 7;
$assetPrefix = (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/configuracion/') !== false) ? '../' : '';
$tituloPagina = 'Mantenimiento Tickets | Fusionar';
?>
<?php require __DIR__ . '/componentes/head.php'; ?>
    <script type="text/javascript" src="<?php echo $assetPrefix; ?>js/buscadores.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/funciones.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/permisos.js"></script>
    <script>
        window.CONFIG_RELATIVE_ROOT = '<?php echo $assetPrefix; ?>';
    </script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/comun.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/comunes.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body id="page-top">
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
                    <div class="ticket-maint-page">
                        <div class="ticket-maint-page__hero">
                            <div>
                                <div class="ticket-maint-page__eyebrow">Mantenimiento de tickets</div>
                                <h1 class="ticket-maint-page__title">Fusionar tickets</h1>
                                <p class="ticket-maint-page__text">
                                    Dejé esta pagina lista como base separada para construir el flujo de fusion sin tocar modulos compartidos.
                                </p>
                            </div>
                            <div class="ticket-maint-page__actions">
                                <a href="index.php" class="btn btn-outline-primary ticket-maint-pill">
                                    <i class="bi bi-arrow-left me-1"></i>Volver a permisos
                                </a>
                            </div>
                        </div>

                        <div class="ticket-maint-card">
                            <div class="ticket-maint-empty">
                                <div class="ticket-maint-empty__icon"><i class="bi bi-shuffle"></i></div>
                                <h2>Modulo de fusion en preparacion</h2>
                                <p>
                                    El siguiente paso aqui sera mostrar dos buscadores de tickets, una vista comparativa
                                    y la previsualizacion del impacto antes de unificar ambos casos.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>
    <script type='text/javascript' src='<?php echo $assetPrefix; ?>template_01/js/funciones.js'></script>
</body>
</html>
