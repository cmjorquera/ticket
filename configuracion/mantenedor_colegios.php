<?php
// ============================================================================
// MANTENEDOR DE COLEGIOS  ·  configuracion/mantenedor_colegios.php
// Dashboard de establecimientos (shell clásico: menuLateral2 + sb-admin-2).
// ============================================================================
session_start();
require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';

$funciones        = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id'] ?? '');
$idPagActual      = 7; // sección Configuración (no hay ítem de menú propio aún)

$assetPrefix  = (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/configuracion/') !== false) ? '../' : '';
$tituloPagina = 'Administrar Colegios';
$configuracionCss = ['configuracion/css/mantenedor_colegios.css'];

$colegios = $funciones->obtenerColegiosActivos();
?>
<?php require __DIR__ . '/componentes/head_legacy.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/buscadores.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/funciones.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/permisos.js"></script>
    <script>window.CONFIG_RELATIVE_ROOT = '<?php echo $assetPrefix; ?>';</script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/comun.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/comunes.js"></script>
</head>

<body id="page-top">
    <input type="hidden" id="idUsuario" value="<?php echo (int) $idUsuarioSession; ?>">

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
                    <div class="row">
                        <div class="col-xl-12">
                            <div class="card shadow mb-4">

                                <div class="card-header py-3">
                                    <h6 class="m-0 font-weight-bold text-primary">
                                        <i class="bi bi-building me-2"></i>Administrar Colegios
                                    </h6>
                                    <p class="mb-0 text-muted mc-subtitle">
                                        Establecimientos educacionales bajo dependencia SEDUC.
                                    </p>
                                </div>

                                <div class="card-body">
                                    <?php if (!empty($colegios)): ?>
                                    <div class="mc-grid">
                                        <?php foreach ($colegios as $c):
                                            $idC  = (int) $c['id_colegio'];
                                            $nom  = htmlspecialchars($c['nom_colegio'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $rza  = htmlspecialchars($c['rza_colegio'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $dir  = htmlspecialchars($c['dir_colegio'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $tel  = htmlspecialchars($c['tel_colegio'] ?? '', ENT_QUOTES, 'UTF-8');
                                            $mail = htmlspecialchars($c['email_comunicaciones'] ?? '', ENT_QUOTES, 'UTF-8');

                                            $usuarios = $funciones->contarUsuariosPorColegio($idC);
                                            $modulos  = $funciones->contarModulosPorColegio($idC);
                                            $eventos  = $funciones->contarEventosPorColegio($idC);

                                            $palabras = preg_split('/\s+/', trim((string) ($c['nom_colegio'] ?? '')));
                                            $ini = mb_strtoupper(
                                                mb_substr($palabras[0] ?? '', 0, 1, 'UTF-8')
                                                . (isset($palabras[1]) ? mb_substr($palabras[1], 0, 1, 'UTF-8') : ''),
                                                'UTF-8'
                                            );
                                            if ($ini === '') { $ini = '?'; }
                                            $iniAttr = htmlspecialchars($ini, ENT_QUOTES, 'UTF-8');

                                            $logo = $assetPrefix . 'img/colegios/colegio_' . $idC . '.png';
                                        ?>
                                        <article class="mc-card">
                                            <div class="mc-card__head">
                                                <span class="mc-avatar">
                                                    <img src="<?php echo htmlspecialchars($logo, ENT_QUOTES, 'UTF-8'); ?>"
                                                         alt="" loading="lazy" onerror="this.remove()">
                                                    <span class="mc-avatar__ini"><?php echo $iniAttr; ?></span>
                                                </span>
                                                <div class="mc-card__id">
                                                    <h3 class="mc-card__name"><?php echo $nom; ?></h3>
                                                    <?php if ($rza !== ''): ?>
                                                    <p class="mc-card__rza"><?php echo $rza; ?></p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <span class="mc-badge"><i class="bi bi-check-circle-fill"></i> Activo</span>

                                            <?php if ($dir !== '' || $tel !== '' || $mail !== ''): ?>
                                            <ul class="mc-contact">
                                                <?php if ($dir !== ''): ?>
                                                <li><i class="bi bi-geo-alt"></i><span><?php echo $dir; ?></span></li>
                                                <?php endif; ?>
                                                <?php if ($tel !== ''): ?>
                                                <li><i class="bi bi-telephone"></i><span><?php echo $tel; ?></span></li>
                                                <?php endif; ?>
                                                <?php if ($mail !== ''): ?>
                                                <li><i class="bi bi-envelope"></i><span><?php echo $mail; ?></span></li>
                                                <?php endif; ?>
                                            </ul>
                                            <?php endif; ?>

                                            <div class="mc-stats">
                                                <div class="mc-stat">
                                                    <span class="mc-stat__num"><?php echo $usuarios; ?></span>
                                                    <span class="mc-stat__lbl">Usuarios</span>
                                                </div>
                                                <div class="mc-stat">
                                                    <span class="mc-stat__num"><?php echo $modulos; ?></span>
                                                    <span class="mc-stat__lbl">Módulos</span>
                                                </div>
                                                <div class="mc-stat">
                                                    <span class="mc-stat__num"><?php echo $eventos; ?></span>
                                                    <span class="mc-stat__lbl">Eventos</span>
                                                </div>
                                            </div>
                                        </article>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php else: ?>
                                    <div class="mc-empty">
                                        <i class="bi bi-building"></i>
                                        <p>No hay colegios activos.</p>
                                    </div>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <a class="scroll-to-top rounded" href="#page-top"><i class="fas fa-angle-up"></i></a>
            </div>

            <?php $funciones->footer(); ?>
        </div>
    </div>

    <?php include __DIR__ . '/../modal_salir.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="<?php echo $assetPrefix; ?>template_01/js/funciones.js"></script>
</body>
</html>
