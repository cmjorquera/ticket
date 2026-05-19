<?php
session_start();
require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';
require_once __DIR__ . '/../class/personas.php';

$funciones        = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$idPagActual      = 7;

$assetPrefix = (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/configuracion/') !== false) ? '../' : '';
$tituloPagina = 'Categorías y Técnicos';
$configuracionCss = ['configuracion/css/admin_categorias.css'];

// ── Datos para la tabla ───────────────────────────────────────────────────────
$bdato = new MySQL('', '', '');

// Categorías activas (sin "Otros" id=10)
$resCat = $bdato->consulta("
    SELECT id_categoria, nombre_categoria, icono, orden
    FROM categoria_de_ticket
    WHERE estado = 1 AND id_categoria != 10
    ORDER BY orden ASC, id_categoria ASC
");
$categorias = [];
while ($r = $bdato->fetch_assoc($resCat)) {
    $categorias[(int)$r['id_categoria']] = [
        'nombre' => htmlspecialchars($r['nombre_categoria'], ENT_QUOTES, 'UTF-8'),
        'icono'  => htmlspecialchars($r['icono'],             ENT_QUOTES, 'UTF-8'),
    ];
}

// Técnicos (solo id_area_trabajo = 1, excluye id 27 = sistema)
$resTec = $bdato->consulta("
    SELECT u.id, u.nombre, u.apellido_paterno, u.foto,
           GROUP_CONCAT(ct.id_categoria      ORDER BY ct.id_categoria SEPARATOR ',') AS cat_ids,
           GROUP_CONCAT(ct.id_categoria_tecnico ORDER BY ct.id_categoria SEPARATOR ',') AS cat_tec_ids
    FROM usuarios u
    LEFT JOIN categoria_tecnico ct ON ct.id_tecnico = u.id
    WHERE u.id_area_trabajo = 1 AND u.id != 27 AND u.estado = 'Activo'
    GROUP BY u.id, u.nombre, u.apellido_paterno, u.foto
    ORDER BY u.nombre ASC
");
$tecnicos = [];
while ($r = $bdato->fetch_assoc($resTec)) {
    $catIds    = $r['cat_ids']     ? array_map('intval', explode(',', $r['cat_ids']))     : [];
    $catTecIds = $r['cat_tec_ids'] ? array_map('intval', explode(',', $r['cat_tec_ids'])) : [];
    $tecnicos[] = [
        'id'       => (int)$r['id'],
        'nombre'   => htmlspecialchars($r['nombre'],          ENT_QUOTES, 'UTF-8'),
        'apellido' => htmlspecialchars($r['apellido_paterno'],ENT_QUOTES, 'UTF-8'),
        'foto'     => $r['foto'] ?: '',
        'cat_ids'  => array_combine($catIds, $catTecIds) ?: [], // [id_cat => id_cat_tec]
    ];
}

$resTecNuevaCategoria = $bdato->consulta("
    SELECT id, nombre, apellido_paterno
    FROM usuarios
    WHERE id_area_trabajo = 1 AND id != 27
    ORDER BY nombre ASC, apellido_paterno ASC
");
$tecnicosParaNuevaCategoria = [];
while ($r = $bdato->fetch_assoc($resTecNuevaCategoria)) {
    $tecnicosParaNuevaCategoria[] = [
        'id' => (int)$r['id'],
        'nombre' => trim($r['nombre'] . ' ' . $r['apellido_paterno']),
    ];
}

// Contar cuántas categorías tiene cada técnico asignadas
// (para el resumen del header)
?>
<?php require __DIR__ . '/componentes/head.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/buscadores.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/funciones.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/permisos.js"></script>
    <script>window.CONFIG_RELATIVE_ROOT = '<?php echo $assetPrefix; ?>';</script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/comun.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/comunes.js"></script>
</head>

<body id="page-top">
    <input type="hidden" id="idUsuario" value="<?php echo (int)$idUsuarioSession; ?>">

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

                                <!-- Header de la card -->
                                <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <div>
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="bi bi-person-gear me-2"></i>Asignación de Categorías a Técnicos
                                        </h6>
                                        <p class="mb-0 text-muted ac-page-subtitle">
                                            Haz clic en un chip para activar o desactivar la categoría.
                                            Solo técnicos de <strong>Informática</strong> aparecen aquí.
                                        </p>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="agregarCategoria()">
                                            <i class="bi bi-plus-circle me-1"></i>Agregar categoría
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="abrirModalAdministrarCategorias()">
                                            <i class="bi bi-gear-fill me-1"></i>Administrar categorías
                                        </button>
                                    </div>
                                </div>

                                <!-- Leyenda -->
                                <div class="ac-legend">
                                    <span>
                                        <span class="cat-chip cat-chip--on cat-chip--legend">
                                            <i class="bi bi-check-circle-fill"></i> Asignada
                                        </span>
                                        El técnico recibe tickets de esta categoría
                                    </span>
                                    <span>
                                        <span class="cat-chip cat-chip--off cat-chip--legend">
                                            <i class="bi bi-circle"></i> Sin asignar
                                        </span>
                                        No recibe tickets de esta categoría
                                    </span>
                                    <span class="ms-auto text-muted">
                                        <i class="bi bi-info-circle me-1"></i>
                                        <?php echo count($tecnicos); ?> técnico(s) · <?php echo count($categorias); ?> categoría(s)
                                    </span>
                                </div>

                                <!-- Grid de técnicos -->
                                <div class="ac-grid" id="acGrid">
                                    <?php foreach ($tecnicos as $tec): ?>
                                    <?php
                                        $asignadas = count($tec['cat_ids']);
                                        $total     = count($categorias);
                                        $fotoSrc   = !empty($tec['foto'])
                                            ? $assetPrefix . htmlspecialchars($tec['foto'], ENT_QUOTES, 'UTF-8')
                                            : $assetPrefix . 'img/undraw_profile.svg';
                                    ?>
                                    <div class="ac-card" data-tecnico-id="<?php echo $tec['id']; ?>">

                                        <!-- Cabecera del card -->
                                        <div class="ac-card__header">
                                            <img src="<?php echo $fotoSrc; ?>"
                                                 alt="<?php echo $tec['nombre']; ?>"
                                                 class="ac-card__avatar"
                                                 onerror="this.src='<?php echo $assetPrefix; ?>img/undraw_profile.svg'">
                                            <div>
                                                <div class="ac-card__name">
                                                    <?php echo $tec['nombre'] . ' ' . $tec['apellido']; ?>
                                                </div>
                                                <div class="ac-card__meta">Técnico · INFORMÁTICA</div>
                                            </div>
                                            <span class="ac-badge-count">
                                                <span class="cat-count"><?php echo $asignadas; ?></span>/<?php echo $total; ?>
                                            </span>
                                        </div>

                                        <!-- Chips de categorías -->
                                        <div class="ac-card__body">
                                            <div class="cat-chips">
                                                <?php foreach ($categorias as $idCat => $catData): ?>
                                                <?php
                                                    $asignada    = isset($tec['cat_ids'][$idCat]);
                                                    $idCatTec    = $asignada ? $tec['cat_ids'][$idCat] : '';
                                                    $chipClass   = $asignada ? 'cat-chip--on' : 'cat-chip--off';
                                                    $chipIcon    = $asignada ? 'bi-check-circle-fill' : 'bi-circle';
                                                ?>
                                                <span class="cat-chip <?php echo $chipClass; ?>"
                                                      data-id-categoria="<?php echo $idCat; ?>"
                                                      data-id-categoria-tecnico="<?php echo $idCatTec; ?>"
                                                      onclick="toggleChip(this)">
                                                    <i class="bi <?php echo $catData['icono']; ?>"></i>
                                                    <?php echo $catData['nombre']; ?>
                                                </span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>

                                    </div>
                                    <?php endforeach; ?>

                                    <?php if (empty($tecnicos)): ?>
                                    <div class="col-12">
                                        <div class="alert alert-warning m-3">
                                            <i class="bi bi-exclamation-triangle me-2"></i>
                                            No hay técnicos activos en el área de Informática.
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Barra de acciones pegajosa -->
                                <div class="ac-action-bar">
                                    <span class="ac-hint">
                                        <i class="bi bi-mouse2 me-1"></i>
                                        Haz clic en las categorías y luego guarda los cambios.
                                    </span>
                                    <button type="button" class="btn btn-primary px-4" onclick="guardarCambiosCards()">
                                        <i class="bi bi-floppy-fill me-1"></i>Guardar cambios
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>

    <?php include __DIR__ . '/../modal_salir.php'; ?>

    <!-- ── Modal: Administrar Categorías ─────────────────────────────────── -->
    <div class="modal fade" id="modalAdministrarCategorias" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable config-modal-dialog config-modal-dialog--xl">
            <div class="modal-content config-modal">
                <div class="modal-header config-modal__header">
                    <h5 class="modal-title config-modal__title">
                        <i class="bi bi-tags-fill me-2"></i>Administrar Categorías
                    </h5>
                    <button type="button" class="btn-close config-modal__close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body config-modal__body">
                    <div id="contenedorAdministrarCategorias">
                        <div class="d-flex justify-content-center py-5">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Modal: Editar Categoría ──────────────────────────────────────── -->
    <div class="modal fade" id="modalEditarCategoria" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg swal-categoria-popup">
                <form id="formEditarCategoria">
                    <div class="modal-body p-0">
                        <input type="hidden" id="editar_id_categoria" name="id_categoria">
                        <div class="swal-categoria">
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>
                            <h2 class="swal-categoria__title">Editar categoría</h2>
                            <div class="swal-categoria__grid">
                                <div class="swal-categoria__field swal-categoria__field--full">
                                    <label class="swal-categoria__label" for="editar_id_tecnico">Técnico asignado</label>
                                    <select id="editar_id_tecnico" name="id_tecnico" class="swal-categoria__select" required></select>
                                </div>
                                <div class="swal-categoria__field">
                                    <label class="swal-categoria__label" for="editar_nombre_categoria">Nombre</label>
                                    <input type="text" class="swal-categoria__input" id="editar_nombre_categoria" name="nombre_categoria" placeholder="Nombre de la categoría" required>
                                </div>
                                <div class="swal-categoria__field">
                                    <label class="swal-categoria__label" for="editar_abreviacion">Abreviación</label>
                                    <input type="text" class="swal-categoria__input" id="editar_abreviacion" name="abreviacion" placeholder="Abreviación corta">
                                </div>
                                <div class="swal-categoria__field swal-categoria__field--full">
                                    <label class="swal-categoria__label" for="editar_icono">Ícono Bootstrap</label>
                                    <div class="swal-categoria__icon-row">
                                        <span class="swal-categoria__icon-preview" id="iconoPreview"><i class="bi bi-tag"></i></span>
                                        <input type="text" class="swal-categoria__input" id="editar_icono" name="icono" placeholder="bi-tag">
                                    </div>
                                    <div class="swal-categoria__hint">
                                        Copia el nombre del ícono desde
                                        <a href="https://icons.getbootstrap.com/" target="_blank" rel="noopener noreferrer">Bootstrap Icons</a>.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0 justify-content-center pt-4 pb-0">
                        <button type="submit" class="btn btn-primary px-4">Guardar</button>
                        <button type="button" class="btn btn-config-soft px-4" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="<?php echo $assetPrefix; ?>template_01/js/funciones.js"></script>

    <script>
    window.ADMIN_CATEGORIAS_CONFIG = {
        root: '<?php echo $assetPrefix; ?>',
        tecnicos: <?php echo json_encode($tecnicosParaNuevaCategoria, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
    };
    </script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/admin_categorias.js"></script>
</body>
</html>

