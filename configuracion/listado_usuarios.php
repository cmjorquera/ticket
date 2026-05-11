<?php
session_start();
require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';
require_once __DIR__ . '/../class/personas.php';

$funciones  = new Funciones();
$personas   = new persona();
$idUsuarioSession = htmlspecialchars($_SESSION['id']);
$idPagActual = 7;

$assetPrefix = (strpos(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''), '/configuracion/') !== false) ? '../' : '';
$tituloPagina = 'Listado de Usuarios';

// ── Cargar datos para los filtros ────────────────────────────────────────────
$bdato = new MySQL('', '', '');

$resAreas    = $bdato->consulta("SELECT id_area, nombre_area FROM area_trabajo ORDER BY nombre_area ASC");
$resColegios = $bdato->consulta("SELECT id_colegio, nom_colegio FROM colegio WHERE estado = 1 ORDER BY nom_colegio ASC");

$areas    = [];
$colegios = [];
while ($r = $bdato->fetch_assoc($resAreas))    { $areas[]    = $r; }
while ($r = $bdato->fetch_assoc($resColegios)) { $colegios[] = $r; }
?>
<?php require __DIR__ . '/componentes/head.php'; ?>
    <link  href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link  href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css"     rel="stylesheet">
    <link  href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link  href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css"  rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script src="<?php echo $assetPrefix; ?>js/buscadores.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/funciones.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/permisos.js"></script>
    <script>window.CONFIG_RELATIVE_ROOT = '<?php echo $assetPrefix; ?>';</script>
    <script src="<?php echo $assetPrefix; ?>configuracion/js/comun.js"></script>
    <script src="<?php echo $assetPrefix; ?>js/comunes.js"></script>

    <style>
        /* ── Filtros ── */
        .lu-filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
            align-items: flex-end;
            padding: 1rem 1.25rem .75rem;
            background: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }
        .lu-filter-bar .lu-filter-group {
            display: flex;
            flex-direction: column;
            gap: .22rem;
            min-width: 160px;
            flex: 1 1 160px;
        }
        .lu-filter-bar label {
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            color: #6c757d;
            margin: 0;
        }
        .lu-filter-bar select,
        .lu-filter-bar input[type="text"] {
            height: 36px;
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            padding: 0 .65rem;
            font-size: .85rem;
            color: #3d3f52;
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
        }
        .lu-filter-bar select:focus,
        .lu-filter-bar input[type="text"]:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 .18rem rgba(78,115,223,.18);
            outline: none;
        }
        .lu-filter-bar .lu-btn-reset {
            height: 36px;
            padding: 0 1rem;
            border-radius: 8px;
            border: 1px solid #d1d3e2;
            background: #fff;
            color: #6c757d;
            font-size: .82rem;
            font-weight: 600;
            cursor: pointer;
            transition: background .15s, color .15s;
            align-self: flex-end;
            white-space: nowrap;
        }
        .lu-filter-bar .lu-btn-reset:hover {
            background: #e2e6ea;
            color: #3d3f52;
        }

        /* ── Contador de resultados ── */
        .lu-results-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: .5rem 1.25rem;
            font-size: .82rem;
            color: #6c757d;
            border-bottom: 1px solid #e3e6f0;
            background: #fff;
        }
        .lu-results-bar strong { color: #3d3f52; }

        /* ── Tabla ── */
        #dataTableUsuarios th,
        #dataTableUsuarios td { vertical-align: middle; }
        #dataTableUsuarios thead th { white-space: nowrap; }
        .usuarios-table-wrap { overflow-x: auto; }

        /* ── Celdas de área y estado (heredadas del sistema) ── */
        .ticket-resumen-estado--config { display: inline-flex; align-items: center; gap: .5rem; }
        .ticket-resumen-estado__dot    { width: 10px; height: 10px; border-radius: 50%; flex: 0 0 10px; }
        .ticket-resumen-estado__titulo  { font-weight: 700; font-size: .85rem; line-height: 1.2; }
        .ticket-resumen-estado__detalle { font-size: .75rem; color: #6c757d; line-height: 1.2; }

        /* ── Botones de acción elegantes ── */
        .lu-btn-group { display: flex; gap: .3rem; flex-wrap: nowrap; }

        .lu-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 9px;
            border: none;
            cursor: pointer;
            font-size: .9rem;
            transition: transform .12s, box-shadow .12s, filter .12s;
            text-decoration: none;
            position: relative;
        }
        .lu-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,.18);
            text-decoration: none;
        }
        .lu-btn:active { transform: translateY(0); box-shadow: none; }

        /* Editar — azul */
        .lu-btn--edit   { background: #4e73df; color: #fff; }
        .lu-btn--edit:hover { background: #3756c0; color: #fff; }

        /* Permisos — esmeralda */
        .lu-btn--perms  { background: #1cc88a; color: #fff; }
        .lu-btn--perms:hover { background: #17a673; color: #fff; }

        /* Estado — gris neutro */
        .lu-btn--state  { background: #858796; color: #fff; }
        .lu-btn--state:hover { background: #606270; color: #fff; }

        /* Estado activo → rojo suave */
        .lu-btn--state.is-active  { background: #e74a3b; }
        .lu-btn--state.is-active:hover { background: #c0392b; }

        /* Tooltip nativo */
        .lu-btn [data-tooltip] { pointer-events: none; }

        /* Badge área técnica */
        .badge-tecnico {
            display: inline-block;
            margin-top: 3px;
            padding: 2px 7px;
            border-radius: 20px;
            font-size: .68rem;
            font-weight: 700;
            background: #e8effd;
            color: #2c56b8;
            border: 1px solid #b8d0f7;
        }

        /* Colegio chip */
        .lu-colegio-item { display: flex; align-items: center; gap: .5rem; margin-bottom: .25rem; }
        .lu-colegio-logo {
            width: 36px; height: 36px;
            object-fit: contain;
            border: 1px solid #e3e6f0;
            border-radius: 6px;
            background: #fff;
            padding: 2px;
        }
        .lu-colegio-fallback {
            width: 36px; height: 36px;
            display: inline-flex; align-items: center; justify-content: center;
            border: 1px solid #d1d3e2;
            border-radius: 6px;
            background: #f0f2f8;
            font-size: .7rem; font-weight: 700; color: #5a5c69;
        }

        /* DataTables overrides */
        div.dataTables_wrapper div.dataTables_filter { display: none; } /* usamos nuestro buscador */
        div.dataTables_wrapper div.dataTables_length label { font-size: .82rem; color: #6c757d; }
        div.dataTables_wrapper div.dataTables_info  { font-size: .82rem; color: #6c757d; }
    </style>
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

                                <!-- Card header -->
                                <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                                    <div>
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="bi bi-people-fill me-2"></i>Listado de Usuarios
                                        </h6>
                                        <p class="mb-0 text-muted" style="font-size:.82rem;">
                                            Visualiza, filtra y gestiona todas las cuentas del sistema.
                                        </p>
                                    </div>
                                    <a href="#" class="btn btn-primary btn-sm px-3" onclick="agregarUsuario()">
                                        <i class="bi bi-person-plus-fill me-1"></i>Agregar usuario
                                    </a>
                                </div>

                                <!-- ── Barra de filtros ── -->
                                <div class="lu-filter-bar">

                                    <div class="lu-filter-group" style="flex: 2 1 220px;">
                                        <label for="lu-buscar"><i class="bi bi-search me-1"></i>Buscar</label>
                                        <input type="text" id="lu-buscar"
                                               placeholder="Nombre, correo…">
                                    </div>

                                    <div class="lu-filter-group">
                                        <label for="lu-colegio"><i class="bi bi-building me-1"></i>Colegio</label>
                                        <select id="lu-colegio">
                                            <option value="">Todos</option>
                                            <?php foreach ($colegios as $c): ?>
                                            <option value="<?php echo (int)$c['id_colegio']; ?>">
                                                <?php echo htmlspecialchars($c['nom_colegio']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="lu-filter-group">
                                        <label for="lu-area"><i class="bi bi-diagram-3 me-1"></i>Área</label>
                                        <select id="lu-area">
                                            <option value="">Todas</option>
                                            <?php foreach ($areas as $a): ?>
                                            <option value="<?php echo (int)$a['id_area']; ?>">
                                                <?php echo htmlspecialchars($a['nombre_area']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="lu-filter-group">
                                        <label for="lu-estado"><i class="bi bi-toggle-on me-1"></i>Estado</label>
                                        <select id="lu-estado">
                                            <option value="">Todos</option>
                                            <option value="Activo">Activo</option>
                                            <option value="Inactivo">Inactivo</option>
                                        </select>
                                    </div>

                                    <button class="lu-btn-reset" id="lu-reset" title="Limpiar filtros">
                                        <i class="bi bi-x-circle me-1"></i>Limpiar
                                    </button>
                                </div>

                                <!-- ── Contador ── -->
                                <div class="lu-results-bar">
                                    <span>Mostrando <strong id="lu-count">–</strong> usuarios</span>
                                    <span id="lu-filter-tags" class="d-flex gap-1 flex-wrap"></span>
                                </div>

                                <!-- ── Tabla ── -->
                                <div class="card-body p-0">
                                    <div class="usuarios-table-wrap">
                                        <table id="dataTableUsuarios"
                                               class="table table-bordered table-hover table-striped w-100 mb-0"
                                               data-asset-prefix="<?php echo htmlspecialchars($assetPrefix); ?>">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th style="width:50px">#</th>
                                                    <th>Colegio</th>
                                                    <th>Nombre</th>
                                                    <th>Email</th>
                                                    <th>Área</th>
                                                    <th>Estado</th>
                                                    <th class="text-center" style="width:120px">Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            // ── Query principal ──────────────────────────────────
                                            $consulta = "
                                                SELECT
                                                    u.id,
                                                    u.nombre,
                                                    u.apellido_paterno,
                                                    u.apellido_materno,
                                                    CONCAT(u.nombre,' ',u.apellido_paterno,' ',u.apellido_materno) AS nombre_completo,
                                                    u.email,
                                                    u.clave,
                                                    u.token_reinicio,
                                                    u.estado,
                                                    u.id_area_trabajo,
                                                    at.nombre_area,
                                                    GROUP_CONCAT(DISTINCT uc.id_colegio   ORDER BY uc.id_colegio   ASC SEPARATOR ',') AS colegios_ids,
                                                    GROUP_CONCAT(DISTINCT c.nom_colegio   ORDER BY c.nom_colegio   ASC SEPARATOR '||') AS colegios_nombres
                                                FROM usuarios u
                                                LEFT JOIN area_trabajo  at ON at.id_area    = u.id_area_trabajo
                                                LEFT JOIN usuario_colegio uc ON uc.id_usuario = u.id AND uc.estado = 1
                                                LEFT JOIN colegio       c  ON c.id_colegio  = uc.id_colegio
                                                GROUP BY u.id, u.nombre, u.apellido_paterno, u.apellido_materno,
                                                         u.email, u.clave, u.estado, at.nombre_area, u.id_area_trabajo
                                                ORDER BY u.nombre ASC
                                            ";
                                            $resultado = $bdato->consulta($consulta);
                                            $indice    = 1;

                                            while ($row = mysqli_fetch_assoc($resultado)):
                                                $userId        = (int) $row['id'];
                                                $fullName      = htmlspecialchars($row['nombre_completo']);
                                                $email         = htmlspecialchars($row['email']);
                                                $area          = htmlspecialchars($row['nombre_area'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $estado        = htmlspecialchars($row['estado']);
                                                $idArea        = (int) $row['id_area_trabajo'];
                                                $tieneClave    = !empty($row['clave']);
                                                $esActivo      = ($estado === 'Activo');
                                                $esTecnico     = ($idArea === 1);

                                                // Colores / labels
                                                $stateColor  = $esActivo ? '#b8f4bb' : '#d9e2ec';
                                                $stateLabel  = $esActivo ? 'Activo'  : 'Inactivo';
                                                $stateDetail = $esActivo ? 'Cuenta habilitada' : 'Revisión requerida';
                                                $areaDot     = $esTecnico ? '#4e73df' : '#b8f4bb';
                                                $areaDetalle = $esTecnico ? 'Técnico de soporte' : 'Usuario del sistema';
                                                $areaBadge   = $esTecnico
                                                    ? '<span class="badge-tecnico">Informática</span>'
                                                    : '';

                                                // Colegios
                                                $colegiosIds    = array_filter(array_map('trim', explode(',',  (string)($row['colegios_ids']    ?? ''))));
                                                $colegiosNombres= array_filter(array_map('trim', explode('||', (string)($row['colegios_nombres'] ?? ''))));
                                                $colegiosIdsJson = htmlspecialchars(json_encode(array_values($colegiosIds)));

                                                $colegiosHtml = '<span class="text-muted" style="font-size:.78rem;">Sin colegio</span>';
                                                if (!empty($colegiosIds)) {
                                                    $items = [];
                                                    foreach ($colegiosIds as $ci => $idC) {
                                                        $idC  = (int)$idC;
                                                        $nom  = htmlspecialchars($colegiosNombres[$ci] ?? "Colegio $idC", ENT_QUOTES, 'UTF-8');
                                                        $logo = '../img/colegios/colegio_' . $idC . '.png';
                                                        $items[] = '
                                                            <div class="lu-colegio-item" title="' . $nom . '">
                                                                <img src="' . $logo . '" alt="' . $nom . '" class="lu-colegio-logo" loading="lazy"
                                                                     onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'inline-flex\';">
                                                                <span class="lu-colegio-fallback" style="display:none;">' . $idC . '</span>
                                                                <small style="font-size:.78rem;color:#3d3f52;">' . $nom . '</small>
                                                            </div>';
                                                    }
                                                    $colegiosHtml = implode('', $items);
                                                }

                                                // Email con botón reenviar
                                                $puedeReenviar = (!$esActivo && !$tieneClave);
                                                $emailHtml = '<div class="d-flex align-items-center gap-1 flex-wrap">'
                                                    . '<span style="word-break:break-word;font-size:.83rem;">' . $email . '</span>'
                                                    . ($puedeReenviar
                                                        ? '<button type="button" class="btn btn-outline-secondary btn-sm p-0" style="width:26px;height:26px;"
                                                               onclick="confirmarReenvioActivacion(' . $userId . ',\'' . addslashes($email) . '\')"
                                                               title="Reenviar correo de activación">
                                                               <i class="bi bi-envelope-arrow-up" style="font-size:.75rem;"></i></button>'
                                                        : '')
                                                    . '</div>';

                                                // data-* para filtros JS
                                                $dataColegio = $colegiosIdsJson;
                                                $dataArea    = $idArea;
                                                $dataEstado  = $estado;
                                            ?>
                                            <tr data-colegio='<?php echo $dataColegio; ?>'
                                                data-area="<?php echo $dataArea; ?>"
                                                data-estado="<?php echo htmlspecialchars($dataEstado); ?>">
                                                <td><?php echo $indice; ?></td>
                                                <td><?php echo $colegiosHtml; ?></td>
                                                <td><?php echo $fullName; ?></td>
                                                <td><?php echo $emailHtml; ?></td>

                                                <!-- Área -->
                                                <td>
                                                    <div class="ticket-resumen-estado ticket-resumen-estado--config">
                                                        <span class="ticket-resumen-estado__dot"
                                                              style="background:<?php echo $areaDot; ?>;"></span>
                                                        <div>
                                                            <div class="ticket-resumen-estado__titulo"><?php echo $area ?: 'Sin área'; ?></div>
                                                            <div class="ticket-resumen-estado__detalle"><?php echo $areaDetalle; ?></div>
                                                            <?php echo $areaBadge; ?>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Estado -->
                                                <td>
                                                    <div class="ticket-resumen-estado ticket-resumen-estado--config">
                                                        <span class="ticket-resumen-estado__dot"
                                                              style="background:<?php echo $stateColor; ?>;"></span>
                                                        <div>
                                                            <div class="ticket-resumen-estado__titulo"><?php echo $stateLabel; ?></div>
                                                            <div class="ticket-resumen-estado__detalle"><?php echo $stateDetail; ?></div>
                                                        </div>
                                                    </div>
                                                </td>

                                                <!-- Acciones -->
                                                <td class="text-center">
                                                    <div class="lu-btn-group justify-content-center">

                                                        <!-- Editar -->
                                                        <a href="#"
                                                           class="lu-btn lu-btn--edit"
                                                           onclick="modificarUsuario(<?php echo $userId; ?>)"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           title="Editar usuario">
                                                            <i class="bi bi-pencil-fill"></i>
                                                        </a>

                                                        <!-- Permisos -->
                                                        <a href="#"
                                                           class="lu-btn lu-btn--perms"
                                                           onclick="mostrarPermisos(<?php echo $userId; ?>)"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           title="Gestionar permisos">
                                                            <i class="bi bi-shield-lock-fill"></i>
                                                        </a>

                                                        <!-- Activar / Bloquear -->
                                                        <a href="#"
                                                           class="lu-btn lu-btn--state <?php echo $esActivo ? 'is-active' : ''; ?>"
                                                           onclick="estadoUsuario(<?php echo $userId; ?>, '<?php echo $estado; ?>')"
                                                           data-bs-toggle="tooltip"
                                                           data-bs-placement="top"
                                                           title="<?php echo $esActivo ? 'Bloquear usuario' : 'Activar usuario'; ?>">
                                                            <i class="bi <?php echo $esActivo ? 'bi-person-fill-slash' : 'bi-person-fill-check'; ?>"></i>
                                                        </a>

                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                                $indice++;
                                            endwhile;
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <!-- fin card-body -->

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="<?php echo $assetPrefix; ?>template_01/js/funciones.js"></script>
    <!-- index.js NO se carga aquí — es exclusivo de configuracion/index.php
         y causaría un doble init de DataTable (#dataTableUsuarios) -->

    <script>
    // ── Tooltips Bootstrap ───────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            new bootstrap.Tooltip(el, { trigger: 'hover' });
        });
    });

    // ── DataTable ────────────────────────────────────────────────────────────
    var table;
    $(function () {
        // Guard: si ya fue inicializado por algún script externo, destruirlo primero
        if ($.fn.DataTable.isDataTable('#dataTableUsuarios')) {
            $('#dataTableUsuarios').DataTable().destroy();
        }

        table = $('#dataTableUsuarios').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[2, 'asc']],
            autoWidth: false,
            responsive: false,
            columnDefs: [
                { orderable: false, targets: [0, 1, 6] }
            ],
            drawCallback: actualizarContador
        });

        // búsqueda global (sobre las columnas visibles)
        $('#lu-buscar').on('keyup', function () {
            table.search(this.value).draw();
        });

        actualizarContador();
    });

    // ── Filtros por columna con data-* ───────────────────────────────────────
    function filtrarTabla() {
        var colegio = $('#lu-colegio').val();
        var area    = $('#lu-area').val();
        var estado  = $('#lu-estado').val();

        $('#dataTableUsuarios tbody tr').each(function () {
            var $tr         = $(this);
            var trColegios  = JSON.parse($tr.attr('data-colegio') || '[]');
            var trArea      = String($tr.attr('data-area') || '');
            var trEstado    = $tr.attr('data-estado') || '';

            var okColegio = !colegio || trColegios.includes(colegio);
            var okArea    = !area    || trArea === area;
            var okEstado  = !estado  || trEstado === estado;

            $tr.toggle(okColegio && okArea && okEstado);
        });

        actualizarContador();
        actualizarTags(colegio, area, estado);
    }

    $('#lu-colegio, #lu-area, #lu-estado').on('change', filtrarTabla);

    // ── Reset ────────────────────────────────────────────────────────────────
    $('#lu-reset').on('click', function () {
        $('#lu-colegio, #lu-area, #lu-estado').val('');
        $('#lu-buscar').val('');
        if (table) table.search('').draw();
        $('#dataTableUsuarios tbody tr').show();
        actualizarContador();
        actualizarTags('', '', '');
    });

    // ── Contador visible ─────────────────────────────────────────────────────
    function actualizarContador() {
        var visibles = $('#dataTableUsuarios tbody tr:visible').length;
        $('#lu-count').text(visibles);
    }

    // ── Tags de filtros activos ──────────────────────────────────────────────
    function actualizarTags(colegio, area, estado) {
        var $tags = $('#lu-filter-tags');
        $tags.empty();

        if (colegio) {
            var nom = $('#lu-colegio option:selected').text();
            $tags.append(tag('bi-building', nom, function () {
                $('#lu-colegio').val(''); filtrarTabla();
            }));
        }
        if (area) {
            var nomA = $('#lu-area option:selected').text();
            $tags.append(tag('bi-diagram-3', nomA, function () {
                $('#lu-area').val(''); filtrarTabla();
            }));
        }
        if (estado) {
            $tags.append(tag('bi-toggle-on', estado, function () {
                $('#lu-estado').val(''); filtrarTabla();
            }));
        }
    }

    function tag(icon, label, onRemove) {
        var $t = $('<span>')
            .css({
                display: 'inline-flex', alignItems: 'center', gap: '4px',
                padding: '2px 8px', borderRadius: '20px',
                background: '#e8effd', color: '#2c56b8',
                border: '1px solid #b8d0f7', fontSize: '.75rem', fontWeight: '600',
                cursor: 'pointer'
            })
            .html('<i class="bi ' + icon + '"></i> ' + label + ' <i class="bi bi-x" style="font-size:.8rem;"></i>')
            .on('click', onRemove);
        return $t;
    }
    </script>
</body>
</html>