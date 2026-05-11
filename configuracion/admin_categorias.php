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

// Contar cuántas categorías tiene cada técnico asignadas
// (para el resumen del header)
?>
<?php require __DIR__ . '/componentes/head.php'; ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/buscadores.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/funciones.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/permisos.js"></script>
    <script>window.CONFIG_RELATIVE_ROOT = '<?php echo $assetPrefix; ?>';</script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/comun.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/comunes.js"></script>

    <style>
        /* ── Layout ── */
        .ac-card { border-radius: 14px; border: 1px solid #e3e6f0; background: #fff; }
        .ac-card__header {
            padding: .85rem 1.2rem;
            background: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            border-radius: 14px 14px 0 0;
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .ac-card__avatar {
            width: 44px; height: 44px; border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e3e6f0;
            background: #eef0f5;
            flex-shrink: 0;
        }
        .ac-card__name    { font-size: .95rem; font-weight: 700; color: #1f2a44; line-height: 1.2; }
        .ac-card__meta    { font-size: .75rem; color: #6c757d; }
        .ac-card__body    { padding: .85rem 1.2rem 1rem; }

        /* Contador de categorías asignadas */
        .ac-badge-count {
            margin-left: auto;
            background: #4e73df;
            color: #fff;
            border-radius: 999px;
            padding: 3px 10px;
            font-size: .72rem;
            font-weight: 700;
            white-space: nowrap;
        }

        /* ── Chips de categoría ── */
        .cat-chips { display: flex; flex-wrap: wrap; gap: .4rem; }

        .cat-chip {
            display: inline-flex;
            align-items: center;
            gap: .3rem;
            padding: .32rem .75rem;
            border-radius: 999px;
            font-size: .78rem;
            font-weight: 600;
            cursor: pointer;
            border: 2px solid transparent;
            transition: transform .1s, box-shadow .1s, background .15s, border-color .15s;
            user-select: none;
        }
        .cat-chip:hover   { transform: translateY(-1px); box-shadow: 0 3px 8px rgba(0,0,0,.13); }
        .cat-chip:active  { transform: none; }

        /* Asignada = verde */
        .cat-chip--on  {
            background: #d4edda;
            color: #155724;
            border-color: #a8d5b5;
        }
        .cat-chip--on:hover  { background: #c2e8cc; }

        /* No asignada = salmón */
        .cat-chip--off {
            background: #fde8e4;
            color: #7d2d1f;
            border-color: #f5bdb5;
        }
        .cat-chip--off:hover { background: #fad5cf; }

        /* ── Barra de acciones ── */
        .ac-action-bar {
            position: sticky;
            bottom: 0;
            z-index: 10;
            background: #fff;
            border-top: 1px solid #e3e6f0;
            padding: .75rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .75rem;
            flex-wrap: wrap;
        }
        .ac-action-bar .ac-hint {
            font-size: .78rem;
            color: #6c757d;
            flex: 1 1 auto;
        }

        /* ── Grid de cards ── */
        .ac-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1rem;
            padding: 1.25rem;
        }

        /* ── Leyenda rápida ── */
        .ac-legend {
            display: flex;
            gap: 1rem;
            align-items: center;
            padding: .5rem 1.25rem;
            background: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            font-size: .78rem;
            color: #6c757d;
            flex-wrap: wrap;
        }
        .ac-legend span { display: inline-flex; align-items: center; gap: .3rem; }

        /* ── Modal Administrar Categorías ── */
        .cat-admin-row td { vertical-align: middle; }
    </style>
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
                                        <p class="mb-0 text-muted" style="font-size:.8rem;">
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
                                        <span class="cat-chip cat-chip--on" style="pointer-events:none;font-size:.72rem;padding:.2rem .6rem;">
                                            <i class="bi bi-check-circle-fill"></i> Asignada
                                        </span>
                                        El técnico recibe tickets de esta categoría
                                    </span>
                                    <span>
                                        <span class="cat-chip cat-chip--off" style="pointer-events:none;font-size:.72rem;padding:.2rem .6rem;">
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
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="bi bi-tags-fill me-2"></i>Administrar Categorías
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light">
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
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2 text-primary"></i>Editar Categoría
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarCategoria">
                    <div class="modal-body">
                        <input type="hidden" id="editar_id_categoria" name="id_categoria">
                        <div class="mb-3">
                            <label for="editar_nombre_categoria" class="form-label fw-bold">Nombre</label>
                            <input type="text" class="form-control" id="editar_nombre_categoria" name="nombre_categoria" required>
                        </div>
                        <div class="mb-3">
                            <label for="editar_abreviacion" class="form-label fw-bold">Abreviación</label>
                            <input type="text" class="form-control" id="editar_abreviacion" name="abreviacion">
                        </div>
                        <div class="mb-3">
                            <label for="editar_icono" class="form-label fw-bold">Ícono Bootstrap</label>
                            <div class="input-group">
                                <span class="input-group-text" id="iconoPreview"><i class="bi bi-tag-fill"></i></span>
                                <input type="text" class="form-control" id="editar_icono" name="icono"
                                       placeholder="bi-tag-fill"
                                       oninput="document.getElementById('iconoPreview').innerHTML='<i class=\'bi \'+this.value+\'\'></i>'">
                            </div>
                            <div class="form-text">Clases de Bootstrap Icons, ej: <code>bi-tools</code></div>
                        </div>
                        <div class="mb-0">
                            <label for="editar_orden" class="form-label fw-bold">Orden</label>
                            <input type="number" class="form-control" id="editar_orden" name="orden" min="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check2-circle me-1"></i>Guardar cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="<?php echo $assetPrefix; ?>template_01/js/funciones.js"></script>

    <script>
    // ── Prefijo para rutas AJAX (siempre apunta a la raíz del proyecto) ─────
    const ROOT = '<?php echo $assetPrefix; ?>';

    // ── Toggle chip ──────────────────────────────────────────────────────────
    function toggleChip(chip) {
        const esOn  = chip.classList.contains('cat-chip--on');
        const idCat = chip.dataset.idCategoria;
        const card  = chip.closest('.ac-card');

        // Si vamos a activar, verificar que ningún otro técnico tenga esa cat activa
        // (regla de negocio: una categoría solo puede tener un técnico asignado)
        if (!esOn) {
            const yaAsignado = document.querySelector(
                `.ac-card:not([data-tecnico-id="${card.dataset.tecnicoId}"]) .cat-chip--on[data-id-categoria="${idCat}"]`
            );
            if (yaAsignado) {
                const otroCard  = yaAsignado.closest('.ac-card');
                const otroNombre = otroCard.querySelector('.ac-card__name').textContent.trim();
                Swal.fire({
                    icon: 'warning',
                    title: 'Categoría ya asignada',
                    html: `<b>${chip.textContent.trim()}</b> ya está asignada a <b>${otroNombre}</b>.<br>¿Quieres reasignarla a este técnico?`,
                    showCancelButton: true,
                    confirmButtonText: 'Sí, reasignar',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#4e73df',
                });
                // No hacemos nada hasta que el usuario confirme en un flujo real
                // Por ahora solo avisamos
                return;
            }
        }

        // Toggle visual
        chip.classList.toggle('cat-chip--on',  !esOn);
        chip.classList.toggle('cat-chip--off',  esOn);
        chip.querySelector('i:first-child').className =
            'bi ' + (!esOn ? 'bi-check-circle-fill' : 'bi-circle');

        // Actualizar contador
        const chips    = card.querySelectorAll('.cat-chip--on').length;
        const total    = card.querySelectorAll('.cat-chip').length;
        card.querySelector('.cat-count').textContent = chips;
    }

    // ── Guardar cambios (versión cards, apunta a la ruta correcta) ───────────
    function guardarCambiosCards() {
        Swal.fire({
            title: 'Guardar cambios',
            text: '¿Confirmas los cambios de asignación de categorías?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4e73df',
            cancelButtonColor: '#858796',
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
        }).then(result => {
            if (!result.isConfirmed) return;

            const peticiones = [];

            document.querySelectorAll('.ac-card').forEach(card => {
                const idTecnico = parseInt(card.dataset.tecnicoId, 10);
                const idsCategorias = [];

                card.querySelectorAll('.cat-chip--on').forEach(chip => {
                    const idCat = parseInt(chip.dataset.idCategoria, 10);
                    if (!isNaN(idCat)) idsCategorias.push(idCat);
                });

                peticiones.push(
                    fetch(ROOT + 'modelos/guardar/guardar_permisos_categoria.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id_usuario: idTecnico, ids_categorias: idsCategorias })
                    }).then(r => r.json())
                );
            });

            Promise.all(peticiones)
                .then(respuestas => {
                    const conError = respuestas.some(r => !r || r.status !== 'ok');
                    if (conError) {
                        Swal.fire('Error', 'Ocurrió un error al guardar una o más asignaciones.', 'error');
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Guardado',
                            text: 'Las asignaciones se guardaron correctamente.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(() => Swal.fire('Error', 'No se pudo conectar al servidor.', 'error'));
        });
    }

    // ── Administrar Categorías — carga con ruta correcta ────────────────────
    function abrirModalAdministrarCategorias() {
        const modalEl = document.getElementById('modalAdministrarCategorias');
        if (!modalEl) return;
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        $('#contenedorAdministrarCategorias').html(`
            <div class="d-flex justify-content-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>
        `);

        $.ajax({
            url: ROOT + 'modelos/rescatar/administrar_categorias.php',
            type: 'GET',
            success: function(html) {
                $('#contenedorAdministrarCategorias').html(html);
            },
            error: function() {
                $('#contenedorAdministrarCategorias').html(
                    '<div class="alert alert-danger mb-0"><i class="bi bi-exclamation-circle me-2"></i>No se pudo cargar el listado de categorías.</div>'
                );
            }
        });
    }

    // ── Editar categoría (formulario inline en el modal) ─────────────────────
    $(document).on('click', '.js-editar-categoria', function () {
        $('#editar_id_categoria').val($(this).data('id_categoria'));
        $('#editar_nombre_categoria').val($(this).data('nombre_categoria') || '');
        $('#editar_abreviacion').val($(this).data('abreviacion') || '');
        const icono = $(this).data('icono') || '';
        $('#editar_icono').val(icono);
        $('#iconoPreview').html('<i class="bi ' + icono + '"></i>');
        $('#editar_orden').val($(this).data('orden') || 0);

        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAdministrarCategorias')).hide();
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarCategoria')).show();
    });

    $('#formEditarCategoria').on('submit', function(e) {
        e.preventDefault();
        const data = $(this).serialize();
        $.post(ROOT + 'modelos/editar/editar_categoria_ticket.php', data, function(resp) {
            let ok = false;
            try { ok = (typeof resp === 'object' ? resp : JSON.parse(resp)).success; } catch(ex) {}
            if (ok) {
                Swal.fire({ icon:'success', title:'Guardado', timer:1500, showConfirmButton:false })
                    .then(() => abrirModalAdministrarCategorias());
                bootstrap.Modal.getOrCreateInstance(document.getElementById('modalEditarCategoria')).hide();
            } else {
                Swal.fire('Error', 'No se pudo guardar la categoría.', 'error');
            }
        });
    });

    // ── Toggle estado categoría ──────────────────────────────────────────────
    $(document).on('click', '.js-toggle-estado-categoria', function () {
        const idCat     = $(this).data('id_categoria');
        const estado    = parseInt($(this).data('estado'), 10);
        const accion    = estado === 1 ? 'desactivar' : 'activar';

        Swal.fire({
            title: accion.charAt(0).toUpperCase() + accion.slice(1) + ' categoría',
            text: '¿Confirmas esta acción?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: estado === 1 ? '#e74a3b' : '#1cc88a',
        }).then(result => {
            if (!result.isConfirmed) return;
            $.post(ROOT + 'modelos/editar/cambiar_estado_categoria_ticket.php',
                { id_categoria: idCat, estado: estado === 1 ? 0 : 1 },
                function() {
                    Swal.fire({ icon:'success', title:'Listo', timer:1200, showConfirmButton:false })
                        .then(() => {
                            // Recargar tabla dentro del modal
                            $.ajax({
                                url: ROOT + 'modelos/rescatar/administrar_categorias.php',
                                success: html => $('#contenedorAdministrarCategorias').html(html)
                            });
                        });
                }
            );
        });
    });

    // ── Agregar categoría ────────────────────────────────────────────────────
    function agregarCategoria() {
        Swal.fire({
            title: 'Nueva categoría',
            html: `
                <div style="text-align:left;display:grid;gap:.6rem;">
                    <div>
                        <label style="font-size:.82rem;font-weight:700;">Nombre</label>
                        <input id="nc_nombre" class="swal2-input" placeholder="Nombre de la categoría" style="margin:0;height:38px;">
                    </div>
                    <div>
                        <label style="font-size:.82rem;font-weight:700;">Abreviación</label>
                        <input id="nc_abrev" class="swal2-input" placeholder="Abreviación corta" style="margin:0;height:38px;">
                    </div>
                    <div>
                        <label style="font-size:.82rem;font-weight:700;">Ícono Bootstrap (ej: bi-tools)</label>
                        <input id="nc_icono" class="swal2-input" placeholder="bi-tag" style="margin:0;height:38px;">
                    </div>
                    <div>
                        <label style="font-size:.82rem;font-weight:700;">Orden</label>
                        <input id="nc_orden" type="number" class="swal2-input" value="99" style="margin:0;height:38px;">
                    </div>
                </div>`,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4e73df',
            focusConfirm: false,
            preConfirm: () => {
                const nombre = document.getElementById('nc_nombre').value.trim();
                if (!nombre) { Swal.showValidationMessage('El nombre es obligatorio'); return false; }
                return {
                    nombre_categoria: nombre,
                    abreviacion: document.getElementById('nc_abrev').value.trim(),
                    icono: document.getElementById('nc_icono').value.trim() || 'bi-tag',
                    orden: document.getElementById('nc_orden').value || 99,
                };
            }
        }).then(result => {
            if (!result.isConfirmed) return;
            $.post(ROOT + 'modelos/guardar/crear_categorias.php', result.value, function(resp) {
                let ok = false;
                try { ok = (typeof resp === 'object' ? resp : JSON.parse(resp)).success; } catch(ex) { ok = !!resp; }
                if (ok) {
                    Swal.fire({ icon:'success', title:'Categoría creada', timer:1500, showConfirmButton:false })
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', 'No se pudo crear la categoría.', 'error');
                }
            });
        });
    }
    </script>
</body>
</html>