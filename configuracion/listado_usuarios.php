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

// CSS propio de la página (lo carga componentes/head.php vía $configuracionCss)
$configuracionCss = ['configuracion/css/listado_usuarios.css'];

// Componentes reutilizables
require_once __DIR__ . '/componentes/avatar-usuario.php';
require_once __DIR__ . '/componentes/badge-estado.php';

// ── Cargar datos para los filtros ────────────────────────────────────────────
$bdato = new MySQL('', '', '');

$resAreas    = $bdato->consulta("SELECT id_area, nombre_area FROM area_trabajo ORDER BY nombre_area ASC");
$resColegios = $bdato->consulta("SELECT id_colegio, nom_colegio FROM colegio WHERE estado = 1 ORDER BY nom_colegio ASC");

$areas    = [];
$colegios = [];
while ($r = $bdato->fetch_assoc($resAreas))    { $areas[]    = $r; }
while ($r = $bdato->fetch_assoc($resColegios)) { $colegios[] = $r; }

// ── ¿El usuario en sesión es Super Admin? (id_perfil = 3) ────────────────────
$perfilesUsuario = [];
$resPerfilesUsuario = $bdato->consulta(
    "SELECT id_perfil FROM usuario_perfil WHERE id_usuario = " . (int) $idUsuarioSession
);
while ($rp = $bdato->fetch_assoc($resPerfilesUsuario)) { $perfilesUsuario[] = (int) $rp['id_perfil']; }
$esSuperAdmin = in_array(3, $perfilesUsuario, true);

// ── Colegios activos sin administrador asignado (solo para el aviso) ─────────
$colegiosSinAdmin = [];
if ($esSuperAdmin) {
    $resSinAdmin = $bdato->consulta("
        SELECT c.nom_colegio
        FROM colegio c
        WHERE c.estado = 1
          AND NOT EXISTS (
              SELECT 1 FROM usuario_colegio uc
              WHERE uc.id_colegio = c.id_colegio
                AND uc.estado = 1
                AND uc.es_admin_colegio = 1
          )
        ORDER BY c.nom_colegio ASC
    ");
    while ($cs = $bdato->fetch_assoc($resSinAdmin)) { $colegiosSinAdmin[] = $cs['nom_colegio']; }
}
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

                                <!-- Card header: título + buscador arriba a la derecha -->
                                <div class="card-header lu-card-header">
                                    <div class="lu-card-header__title">
                                        <h6 class="m-0 font-weight-bold text-primary">
                                            <i class="bi bi-people-fill me-2"></i>Listado de Usuarios
                                        </h6>
                                        <p class="mb-0 text-muted lu-subtitle">
                                            Visualiza, filtra y gestiona todas las cuentas del sistema.
                                        </p>
                                    </div>
                                    <div class="lu-card-header__actions">
                                        <div class="lu-search-top-right">
                                            <i class="bi bi-search"></i>
                                            <input type="text" id="lu-buscar" placeholder="Buscar nombre o correo…">
                                        </div>
                                        <a href="#" class="btn btn-primary btn-sm px-3" onclick="agregarUsuario()">
                                            <i class="bi bi-person-plus-fill me-1"></i>Agregar usuario
                                        </a>
                                    </div>
                                </div>

                                <?php if ($esSuperAdmin && !empty($colegiosSinAdmin)): ?>
                                <!-- ── Aviso: colegios sin administrador ── -->
                                <div id="lu-alert-sinadmin" class="lu-alert-sinadmin" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    <div>
                                        <strong>Colegios sin administrador asignado (<span data-count><?php echo count($colegiosSinAdmin); ?></span>):</strong>
                                        <span id="lu-sinadmin-lista"><?php echo htmlspecialchars(implode(', ', $colegiosSinAdmin), ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>
                                    <button type="button" class="lu-alert-close" aria-label="Cerrar"
                                            onclick="document.getElementById('lu-alert-sinadmin').remove()">&times;</button>
                                </div>
                                <?php endif; ?>

                                <!-- ── Toolbar: tabs de estado + filtros compactos ── -->
                                <div class="lu-toolbar">
                                    <div class="lu-filter-tabs">
                                        <button type="button" class="lu-tab-btn is-active" data-estado="">Todos</button>
                                        <button type="button" class="lu-tab-btn" data-estado="Activo">Activo</button>
                                        <button type="button" class="lu-tab-btn" data-estado="Pendiente">Pendiente</button>
                                        <button type="button" class="lu-tab-btn" data-estado="Bloqueado">Bloqueado</button>
                                    </div>

                                    <div class="lu-filter-bar-compact">
                                        <select id="lu-colegio" class="lu-select" title="Filtrar por colegio">
                                            <option value="">Todos los colegios</option>
                                            <?php foreach ($colegios as $c): ?>
                                            <option value="<?php echo (int)$c['id_colegio']; ?>">
                                                <?php echo htmlspecialchars($c['nom_colegio']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <select id="lu-area" class="lu-select" title="Filtrar por área">
                                            <option value="">Todas las áreas</option>
                                            <?php foreach ($areas as $a): ?>
                                            <option value="<?php echo (int)$a['id_area']; ?>">
                                                <?php echo htmlspecialchars($a['nombre_area']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button class="lu-btn-reset" id="lu-reset" title="Limpiar filtros">
                                            <i class="bi bi-x-circle me-1"></i>Limpiar
                                        </button>
                                    </div>

                                    <!-- valor de estado gobernado por los tabs -->
                                    <select id="lu-estado" class="d-none">
                                        <option value=""></option>
                                        <option value="Activo">Activo</option>
                                        <option value="Pendiente">Pendiente</option>
                                        <option value="Bloqueado">Bloqueado</option>
                                    </select>
                                </div>

                                <!-- ── Contador + chips de filtros activos ── -->
                                <div class="lu-results-bar">
                                    <span id="lu-count">–</span>
                                    <span id="lu-filter-tags" class="lu-filter-tags"></span>
                                </div>

                                <!-- ── Tabla ── -->
                                <div class="card-body p-0">
                                    <div class="usuarios-table-wrap">
                                        <table id="dataTableUsuarios"
                                               class="table table-bordered table-hover table-striped w-100 mb-0"
                                               data-asset-prefix="<?php echo htmlspecialchars($assetPrefix); ?>">
                                            <thead>
                                                <tr>
                                                    <th class="lu-col-num">#</th>
                                                    <th class="lu-col-avatar"></th>
                                                    <th>Usuario</th>
                                                    <th>Área</th>
                                                    <th>Estado</th>
                                                    <th class="lu-col-acciones">Acciones</th>
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
                                                    GROUP_CONCAT(uc.id_colegio                    ORDER BY uc.id_colegio ASC SEPARATOR ',')  AS colegios_ids,
                                                    GROUP_CONCAT(uc.id                            ORDER BY uc.id_colegio ASC SEPARATOR ',')  AS colegios_rel_ids,
                                                    GROUP_CONCAT(COALESCE(uc.es_admin_colegio, 0) ORDER BY uc.id_colegio ASC SEPARATOR ',')  AS colegios_admin_flags,
                                                    GROUP_CONCAT(c.nom_colegio                    ORDER BY uc.id_colegio ASC SEPARATOR '||') AS colegios_nombres
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
                                                $userId      = (int) $row['id'];
                                                $fullName    = htmlspecialchars($row['nombre_completo']);
                                                $email       = htmlspecialchars($row['email']);
                                                $area        = htmlspecialchars($row['nombre_area'] ?? '', ENT_QUOTES, 'UTF-8');
                                                $estadoRaw   = trim((string) $row['estado']);
                                                $estadoTipo  = strtolower($estadoRaw);            // activo | pendiente | bloqueado
                                                $estadoLabel = $estadoRaw !== '' ? ucfirst($estadoTipo) : 'Sin estado';
                                                $idArea      = (int) $row['id_area_trabajo'];
                                                $tieneClave  = !empty($row['clave']);
                                                $esActivo    = ($estadoTipo === 'activo');
                                                $esTecnico   = ($idArea === 1);

                                                // Colegios → chips dentro de la celda Usuario (arrays alineados por índice)
                                                $rawColegiosIds = trim((string)($row['colegios_ids'] ?? ''));
                                                if ($rawColegiosIds === '') {
                                                    $colegiosIds = $colegiosRelIds = $colegiosNombres = $colegiosAdminFlags = [];
                                                } else {
                                                    $colegiosIds        = explode(',',  $rawColegiosIds);
                                                    $colegiosRelIds     = explode(',',  (string) $row['colegios_rel_ids']);
                                                    $colegiosAdminFlags = explode(',',  (string) $row['colegios_admin_flags']);
                                                    $colegiosNombres    = explode('||', (string) $row['colegios_nombres']);
                                                }
                                                $colegiosIdsJson = htmlspecialchars(json_encode(array_map('strval', $colegiosIds)));

                                                $colegiosChips = '';
                                                foreach ($colegiosIds as $ci => $idC) {
                                                    $idC     = (int) $idC;
                                                    $relId   = (int) ($colegiosRelIds[$ci] ?? 0);
                                                    $nom     = htmlspecialchars($colegiosNombres[$ci] ?? "Colegio $idC", ENT_QUOTES, 'UTF-8');
                                                    $logo    = '../img/colegios/colegio_' . $idC . '.png';
                                                    $adminC  = (isset($colegiosAdminFlags[$ci]) && (int) $colegiosAdminFlags[$ci] === 1);

                                                    if ($esSuperAdmin) {
                                                        $ctrl = '<button type="button" class="lu-admin-toggle' . ($adminC ? ' is-admin' : '') . '"'
                                                            . ' data-rel-id="' . $relId . '"'
                                                            . ' data-usuario-id="' . $userId . '"'
                                                            . ' data-colegio-id="' . $idC . '"'
                                                            . ' data-colegio-nom="' . $nom . '"'
                                                            . ' aria-pressed="' . ($adminC ? 'true' : 'false') . '"'
                                                            . ' title="' . ($adminC ? 'Quitar como admin de ' : 'Marcar como admin de ') . $nom . '"'
                                                            . ' onclick="toggleAdminColegio(this)">'
                                                            . '<i class="bi ' . ($adminC ? 'bi-star-fill' : 'bi-star') . '"></i></button>';
                                                    } elseif ($adminC) {
                                                        $ctrl = '<span class="lu-admin-badge" title="Administrador de ' . $nom . '"><i class="bi bi-star-fill"></i></span>';
                                                    } else {
                                                        $ctrl = '';
                                                    }

                                                    $colegiosChips .= '<span class="lu-colegio-chip' . ($adminC ? ' is-admin' : '') . '" title="' . $nom . '">'
                                                        . '<img src="' . $logo . '" alt="" loading="lazy" onerror="this.remove();">'
                                                        . '<span>' . $nom . '</span>'
                                                        . $ctrl
                                                        . '</span>';
                                                }

                                                // ¿Puede reenviarse el correo de activación?
                                                $puedeReenviar = (!$esActivo && !$tieneClave);
                                            ?>
                                            <tr data-colegio='<?php echo $colegiosIdsJson; ?>'
                                                data-area="<?php echo $idArea; ?>"
                                                data-estado="<?php echo htmlspecialchars($estadoTipo); ?>">
                                                <td class="lu-col-num"><?php echo $indice; ?></td>
                                                <td class="lu-col-avatar">
                                                    <?php renderAvatarUsuario($row['nombre'], $row['apellido_paterno']); ?>
                                                </td>

                                                <!-- Usuario -->
                                                <td>
                                                    <div class="lu-user">
                                                        <span class="lu-user__name"><?php echo $fullName; ?></span>
                                                        <span class="lu-user__mail">
                                                            <?php echo $email; ?>
                                                            <?php if ($puedeReenviar): ?>
                                                            <button type="button" class="lu-btn-icon lu-btn-icon--ghost"
                                                                    onclick="confirmarReenvioActivacion(<?php echo $userId; ?>, '<?php echo addslashes($row['email']); ?>')"
                                                                    title="Reenviar correo de activación">
                                                                <i class="bi bi-envelope-arrow-up"></i>
                                                            </button>
                                                            <?php endif; ?>
                                                        </span>
                                                        <?php if ($colegiosChips !== ''): ?>
                                                        <span class="lu-user__colegios"><?php echo $colegiosChips; ?></span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>

                                                <!-- Área -->
                                                <td>
                                                    <div class="lu-area">
                                                        <span class="lu-area__name"><?php echo $area ?: 'Sin área'; ?></span>
                                                        <?php if ($esTecnico): ?>
                                                        <span class="lu-badge-tecnico">Informática</span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>

                                                <!-- Estado -->
                                                <td><?php renderBadgeEstado($estadoTipo, $estadoLabel); ?></td>

                                                <!-- Acciones -->
                                                <td class="lu-col-acciones">
                                                    <div class="lu-actions">
                                                        <a href="#" class="lu-btn-icon lu-btn-icon--edit"
                                                           onclick="modificarUsuario(<?php echo $userId; ?>)"
                                                           data-bs-toggle="tooltip" data-bs-placement="top" title="Editar usuario">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <a href="#" class="lu-btn-icon lu-btn-icon--perms"
                                                           onclick="mostrarPermisos(<?php echo $userId; ?>)"
                                                           data-bs-toggle="tooltip" data-bs-placement="top" title="Gestionar permisos">
                                                            <i class="bi bi-shield-lock"></i>
                                                        </a>
                                                        <a href="#" class="lu-btn-icon <?php echo $esActivo ? 'lu-btn-icon--danger' : 'lu-btn-icon--ok'; ?>"
                                                           onclick="estadoUsuario(<?php echo $userId; ?>, '<?php echo htmlspecialchars($estadoRaw, ENT_QUOTES); ?>')"
                                                           data-bs-toggle="tooltip" data-bs-placement="top"
                                                           title="<?php echo $esActivo ? 'Bloquear usuario' : 'Activar usuario'; ?>">
                                                            <i class="bi <?php echo $esActivo ? 'bi-person-slash' : 'bi-person-check'; ?>"></i>
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
    var table;

    // ── Tooltips Bootstrap (se reinicializan tras cada redibujo de la tabla) ──
    function initTooltips() {
        document.querySelectorAll('#dataTableUsuarios [data-bs-toggle="tooltip"]').forEach(function (el) {
            var prev = bootstrap.Tooltip.getInstance(el);
            if (prev) { prev.dispose(); }
            new bootstrap.Tooltip(el, { trigger: 'hover' });
        });
    }

    // ── Filtro personalizado: colegio / área / estado vía atributos data-* ───
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'dataTableUsuarios') { return true; }

        var tr = settings.aoData[dataIndex].nTr;
        if (!tr) { return true; }

        var colegio = $('#lu-colegio').val();
        var area    = $('#lu-area').val();
        var estado  = ($('#lu-estado').val() || '').toLowerCase();

        var trColegios = (JSON.parse(tr.getAttribute('data-colegio') || '[]')).map(String);
        var trArea     = String(tr.getAttribute('data-area') || '');
        var trEstado   = (tr.getAttribute('data-estado') || '').toLowerCase();

        var okColegio = !colegio || trColegios.indexOf(String(colegio)) !== -1;
        var okArea    = !area    || trArea === String(area);
        var okEstado  = !estado  || trEstado === estado;

        return okColegio && okArea && okEstado;
    });

    // ── DataTable ────────────────────────────────────────────────────────────
    $(function () {
        // Guard: si ya fue inicializado por algún script externo, destruirlo primero
        if ($.fn.DataTable.isDataTable('#dataTableUsuarios')) {
            $('#dataTableUsuarios').DataTable().destroy();
        }

        table = $('#dataTableUsuarios').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 10,
            lengthMenu: [10, 25, 50, 100],
            order: [[2, 'asc']],
            autoWidth: false,
            responsive: false,
            columnDefs: [
                { orderable: false, targets: [0, 1, 5] }
            ],
            drawCallback: function () {
                actualizarContador();
                initTooltips();
            }
        });

        // Buscador (esquina superior derecha del card)
        $('#lu-buscar').on('keyup', function () {
            table.search(this.value).draw();
        });

        // Selects de colegio / área
        $('#lu-colegio, #lu-area').on('change', filtrarTabla);

        // Tabs de estado
        $('.lu-tab-btn').on('click', function () {
            $('.lu-tab-btn').removeClass('is-active');
            $(this).addClass('is-active');
            $('#lu-estado').val($(this).data('estado') || '');
            filtrarTabla();
        });

        // Limpiar
        $('#lu-reset').on('click', function () {
            $('#lu-colegio, #lu-area').val('');
            $('#lu-estado').val('');
            $('#lu-buscar').val('');
            $('.lu-tab-btn').removeClass('is-active').filter('[data-estado=""]').addClass('is-active');
            table.search('').draw();
            actualizarTags();
        });

        actualizarContador();
        actualizarTags();
    });

    // ── Aplica los filtros y refresca contador + chips ──────────────────────
    function filtrarTabla() {
        table.draw();
        actualizarTags();
    }

    // ── Contador: "Mostrando X–Y de Z" ─────────────────────────────────────
    function actualizarContador() {
        if (!table) { return; }
        var info  = table.page.info();
        var desde = info.recordsDisplay === 0 ? 0 : info.start + 1;
        var texto = 'Mostrando ' + desde + '–' + info.end + ' de ' + info.recordsDisplay;
        if (info.recordsDisplay !== info.recordsTotal) {
            texto += ' (de ' + info.recordsTotal + ' en total)';
        }
        $('#lu-count').text(texto);
    }

    // ── Chips de filtros activos (colegio / área) ──────────────────────────
    function actualizarTags() {
        var $tags = $('#lu-filter-tags').empty();
        var colegio = $('#lu-colegio').val();
        var area    = $('#lu-area').val();

        if (colegio) {
            $tags.append(chip('bi-building', $('#lu-colegio option:selected').text(), function () {
                $('#lu-colegio').val(''); filtrarTabla();
            }));
        }
        if (area) {
            $tags.append(chip('bi-diagram-3', $('#lu-area option:selected').text(), function () {
                $('#lu-area').val(''); filtrarTabla();
            }));
        }
    }

    function chip(icon, label, onRemove) {
        return $('<span class="lu-chip"></span>')
            .html('<i class="bi ' + icon + '"></i>' + label + ' <i class="bi bi-x"></i>')
            .on('click', onRemove);
    }

    // ── Admin de colegio (solo Super Admin) ───────────────────────────────
    function toggleAdminColegio(btn) {
        if (btn.dataset.busy === '1') { return; }

        var relId      = btn.getAttribute('data-rel-id');
        var usuarioId  = btn.getAttribute('data-usuario-id');
        var colegioId  = btn.getAttribute('data-colegio-id');
        var colegioNom = btn.getAttribute('data-colegio-nom') || 'este colegio';
        var activar    = !btn.classList.contains('is-admin');

        Swal.fire({
            title: '<div class="alert alert-dark" role="alert">CONFIRMACIÓN</div>',
            html: activar
                ? '¿Marcar a este usuario como <b>administrador</b> de <b>' + colegioNom + '</b>?'
                  + '<br><small>Si otro usuario ya es admin de ese colegio, será reemplazado.</small>'
                : '¿Quitar a este usuario como administrador de <b>' + colegioNom + '</b>?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: activar ? 'Sí, asignar' : 'Sí, quitar',
            cancelButtonText: 'Cancelar'
        }).then(function (res) {
            if (!res.isConfirmed) { return; }

            btn.dataset.busy = '1';
            $.ajax({
                url: configuracionUrl('modelos/guardar/guardar_admin_colegio.php'),
                type: 'POST',
                dataType: 'json',
                data: {
                    id_relacion:      relId,
                    id_usuario:       usuarioId,
                    id_colegio:       colegioId,
                    es_admin_colegio: activar ? 1 : 0
                },
                success: function (data) {
                    if (data && data.success) {
                        aplicarEstadoAdminColegio(colegioId, activar ? usuarioId : null);
                        if (data.colegios_sin_admin !== undefined) {
                            actualizarAvisoSinAdmin(data.colegios_sin_admin);
                        }
                        Swal.fire({
                            icon: 'success', title: 'Listo',
                            text: data.message || 'Cambio guardado',
                            timer: 1600, showConfirmButton: false
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Error', text: (data && data.message) || 'No se pudo guardar.' });
                    }
                },
                error: function () {
                    Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Intenta nuevamente.' });
                },
                complete: function () { btn.dataset.busy = '0'; }
            });
        });
    }

    // Deja a <usuarioIdAdmin> como único admin de <colegioId> en la tabla (o ninguno si es null).
    // Recorre todas las filas de DataTables (incluidas las de otras páginas) para que el
    // cambio persista al paginar / filtrar / ordenar.
    function aplicarEstadoAdminColegio(colegioId, usuarioIdAdmin) {
        var filas = table ? table.rows().nodes().toArray() : [document];
        filas.forEach(function (tr) {
            tr.querySelectorAll('.lu-admin-toggle[data-colegio-id="' + colegioId + '"]').forEach(function (b) {
                var esEste = usuarioIdAdmin !== null && b.getAttribute('data-usuario-id') === String(usuarioIdAdmin);
                b.classList.toggle('is-admin', esEste);
                b.setAttribute('aria-pressed', esEste ? 'true' : 'false');
                var chipEl = b.closest('.lu-colegio-chip');
                if (chipEl) { chipEl.classList.toggle('is-admin', esEste); }
                var icon = b.querySelector('i');
                if (icon) { icon.className = 'bi ' + (esEste ? 'bi-star-fill' : 'bi-star'); }
                var nom = b.getAttribute('data-colegio-nom') || '';
                b.title = (esEste ? 'Quitar como admin de ' : 'Marcar como admin de ') + nom;
            });
        });
    }

    // Refresca (o crea/elimina) el aviso de colegios sin administrador
    function actualizarAvisoSinAdmin(lista) {
        var box = document.getElementById('lu-alert-sinadmin');

        if (!Array.isArray(lista) || lista.length === 0) {
            if (box) { box.remove(); }
            return;
        }

        if (!box) {
            box = document.createElement('div');
            box.id = 'lu-alert-sinadmin';
            box.className = 'lu-alert-sinadmin';
            box.setAttribute('role', 'alert');
            box.innerHTML =
                '<i class="bi bi-exclamation-triangle-fill"></i>'
                + '<div><strong>Colegios sin administrador asignado (<span data-count></span>):</strong> '
                + '<span id="lu-sinadmin-lista"></span></div>'
                + '<button type="button" class="lu-alert-close" aria-label="Cerrar">&times;</button>';
            box.querySelector('.lu-alert-close').addEventListener('click', function () { box.remove(); });
            var anchor = document.querySelector('.lu-toolbar');
            anchor.parentNode.insertBefore(box, anchor);
        }

        box.querySelector('[data-count]').textContent = lista.length;
        box.querySelector('#lu-sinadmin-lista').textContent = lista.join(', ');
    }
    </script>
</body>
</html>