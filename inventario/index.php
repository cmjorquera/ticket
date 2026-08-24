<?php
require_once __DIR__ . '/componentes/boot.php';

$modoDiagnostico = isset($_GET['diag']) && (string)$_GET['diag'] === '1';

if ($modoDiagnostico) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Diagnostico inventario\n";
    echo "======================\n";
    echo "boot.php cargado correctamente\n";

    try {
        $tituloPagina = 'Inventario de computadores';
        echo "titulo ok\n";

        $colegios = $inventario->obtenerColegios();
        echo "obtenerColegios ok: " . count($colegios) . " registros\n";

        $usuarios = $inventario->obtenerUsuarios();
        echo "obtenerUsuarios ok: " . count($usuarios) . " registros\n";

        $estados = $inventario->obtenerEstados();
        echo "obtenerEstados ok: " . count($estados) . " registros\n";

        $tiposPc = $inventario->obtenerTiposPc();
        echo "obtenerTiposPc ok: " . count($tiposPc) . " registros\n";

        $filtros = [
            'id_colegio' => (int)($_GET['id_colegio'] ?? 0),
            'id_estado' => (int)($_GET['id_estado'] ?? 0),
            'tipo_pc' => trim((string)($_GET['tipo_pc'] ?? '')),
            'id_usuario_asignado' => (int)($_GET['id_usuario_asignado'] ?? 0),
            'busqueda' => trim((string)($_GET['busqueda'] ?? '')),
        ];
        echo "filtros ok\n";

        $resumen = $inventario->obtenerResumen($filtros);
        echo "obtenerResumen ok\n";
        echo "diagnostico completado sin errores\n";
    } catch (Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "ARCHIVO: " . $e->getFile() . "\n";
        echo "LINEA: " . $e->getLine() . "\n";
    }

    exit;
}

try {
    $tituloPagina = 'Inventario';
    $alcanceInventario = $inventario->obtenerAlcanceInventario($idUsuarioSession);
    $colegios = $alcanceInventario['colegios'];
    $estados = $inventario->obtenerEstados();
    $tiposPc = $inventario->obtenerTiposPc();
    $filtros = [
        'id_colegio' => (int)($_GET['id_colegio'] ?? 0),
        'id_estado' => (int)($_GET['id_estado'] ?? 0),
        'tipo_pc' => '',
        'id_usuario_asignado' => (int)($_GET['id_usuario_asignado'] ?? 0),
        'busqueda' => trim((string)($_GET['busqueda'] ?? '')),
    ];
    $filtros = $inventario->normalizarFiltrosPorAlcance($filtros, $alcanceInventario);
    $resumen = $inventario->obtenerResumen($filtros);
    $coloresColegio    = $inventario->obtenerColoresColegio($idUsuarioSession);
    $idColegioRestringido = (int)($filtros['id_colegio'] ?: ($alcanceInventario['id_colegio_predeterminado'] ?? 0));
    $mostrarFiltroColegio = (bool)($alcanceInventario['mostrar_filtro_colegio'] ?? false);
    $mostrarColumnaColegio = (bool)($alcanceInventario['mostrar_columna_colegio'] ?? false);
    $usuarios = $inventario->obtenerUsuariosAsignablesPorColegios($alcanceInventario['ids_colegio'] ?? []);
    $usuariosPorColegio = [];
    foreach (($alcanceInventario['ids_colegio'] ?? []) as $idColegioPermitido) {
        $usuariosPorColegio[(int)$idColegioPermitido] = $inventario->obtenerUsuariosAsignablesPorColegio((int)$idColegioPermitido);
    }
    $usuariosFiltro = $idColegioRestringido > 0 ? ($usuariosPorColegio[$idColegioRestringido] ?? []) : $usuarios;
    $todasUbicaciones = $inventario->obtenerTodasUbicaciones();
    $ubicacionesPermitidas = [];
    foreach (($alcanceInventario['ids_colegio'] ?? []) as $idColegioPermitido) {
        if (isset($todasUbicaciones[$idColegioPermitido])) {
            $ubicacionesPermitidas[$idColegioPermitido] = $todasUbicaciones[$idColegioPermitido];
        }
    }
    $ubicacionesPc = $idColegioRestringido > 0 ? ($ubicacionesPermitidas[$idColegioRestringido] ?? []) : [];
    $tabActiva = in_array($_GET['tab'] ?? '', ['pc', 'monitores', 'tablet', 'impresoras'], true) ? $_GET['tab'] : 'pc';
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar la portada del inventario: ' . $e->getMessage());
}

$_c1 = preg_match('/^#[0-9a-fA-F]{3,8}$/', $coloresColegio['color_principal'] ?? '') ? $coloresColegio['color_principal'] : '';
$_c2 = preg_match('/^#[0-9a-fA-F]{3,8}$/', $coloresColegio['color_secundario'] ?? '') ? $coloresColegio['color_secundario'] : '';
$_heroBranded = $_c1 !== '';
$_heroStyle   = $_heroBranded ? ' style="background: linear-gradient(135deg, ' . $_c1 . ' 0%, ' . ($_c2 ?: $_c1) . ' 100%)"' : '';

$idPagActual = '9';
require __DIR__ . '/componentes/layout_top.php';

// Pantalla de carga fullscreen (overlay). Se muestra/oculta desde inventario.js
// en filtros, AJAX de tabla, descarga de PDF y detalle de equipo.
$textoCarga = 'Cargando inventario...';
$loaderVisibleInicialmente = false; // la pagina ya viene renderizada por PHP, no se muestra al cargar
$loaderFallbackMs = 8000; // seguridad: se oculta solo si algo no llama a ocultarPantallaCarga()
require __DIR__ . '/../include/pantallaCargando.php';
?>
<div class="row mx-1 mx-md-3">
    <div class="col-12">
        <div class="card shadow mb-4 px-0 border-0 inv-panel">
            <div class="card-body p-4 p-lg-5">

                <!-- Hero -->
                <div class="inv-hero mb-4<?= $_heroBranded ? ' inv-hero--branded' : '' ?>"<?= $_heroStyle ?>>
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="inv-kicker">Módulo Inventario</span>
                            <h1 class="inv-title mb-2">Gestión de Inventario</h1>
                            <p class="inv-subtitle mb-0">Registra, organiza y da seguimiento al equipamiento tecnológico por colegio desde una sola vista operativa.</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <?php if ((int)($alcanceInventario['id_perfil'] ?? 1) >= 2): ?>
                            <a href="dashboard.php" class="module-btn module-btn--secondary">
                                <i class="bi bi-bar-chart-line"></i>Ir al Dashboard
                            </a>
                            <?php endif; ?>
                            <a href="carga_masiva.php" class="module-btn module-btn--secondary inv-btn-carga-masiva">
                                <i class="bi bi-cloud-upload"></i>Carga masiva
                            </a>
                            <a href="carga_masiva_monitores.php" class="module-btn module-btn--secondary inv-btn-carga-masiva-mon d-none">
                                <i class="bi bi-cloud-upload"></i>Carga masiva monitores
                            </a>
                            <button type="button" class="module-btn module-btn--secondary inv-btn-pdf-equipos" id="btnDescargarPdfInventario">
                                <i class="bi bi-file-earmark-pdf"></i>Descargar PDF
                            </button>
                            <a href="registrar_equipo.php" class="module-btn module-btn--primary" id="btnAgregarPrincipal"
                               data-href-pc="registrar_equipo.php"
                               data-href-mon="registrar_monitor.php"
                               data-label-pc="Agregar PC"
                               data-label-mon="Agregar monitor">
                                <i class="bi bi-plus-circle"></i><span id="btnAgregarLabel">Agregar PC</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tabs -->
                <ul class="nav nav-tabs mb-4" id="inventarioTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $tabActiva === 'pc' ? 'active' : '' ?>"
                                id="tab-pc-btn" data-bs-toggle="tab" data-bs-target="#tab-pc"
                                type="button" role="tab" data-tab="pc">
                            <i class="bi bi-pc-display me-1"></i>PC
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $tabActiva === 'monitores' ? 'active' : '' ?>"
                                id="tab-monitores-btn" data-bs-toggle="tab" data-bs-target="#tab-monitores"
                                type="button" role="tab" data-tab="monitores">
                            <i class="bi bi-display me-1"></i>Monitores
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $tabActiva === 'tablet' ? 'active' : '' ?>"
                                id="tab-tablet-btn" data-bs-toggle="tab" data-bs-target="#tab-tablet"
                                type="button" role="tab" data-tab="tablet">
                            <i class="bi bi-tablet me-1"></i>Tablet
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $tabActiva === 'impresoras' ? 'active' : '' ?>"
                                id="tab-impresoras-btn" data-bs-toggle="tab" data-bs-target="#tab-impresoras"
                                type="button" role="tab" data-tab="impresoras">
                            <i class="bi bi-printer me-1"></i>Impresoras
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="inventarioTabsContent">

                    <!-- ===== TAB PC ===== -->
                    <div class="tab-pane fade <?= $tabActiva === 'pc' ? 'show active' : '' ?>" id="tab-pc" role="tabpanel">

                        <div id="contenedorResumen">
                            <?php require __DIR__ . '/componentes/resumen.php'; ?>
                        </div>

                        <div class="card shadow-sm border-0 inv-panel">
                            <div class="card-body">
                                <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-3">
                                    <div>
                                        <h5 class="mb-1">Equipos inventariados</h5>
                                        <p class="text-muted mb-0 small">Listado operativo segun filtros y permisos actuales.</p>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary" id="btnImprimirQrEquipos">
                                        <i class="bi bi-qr-code me-1"></i>Imprimir todos los códigos
                                    </button>
                                </div>
                                <div class="row g-3 align-items-end mb-4">
                                    <?php if ($mostrarFiltroColegio): ?>
                                    <div class="col-md-3">
                                        <label class="form-label">Colegio</label>
                                        <select id="filtroColegio" class="form-select">
                                            <option value="">Todos</option>
                                            <?php foreach ($colegios as $colegio): ?>
                                                <option value="<?= (int)$colegio['id_colegio'] ?>" <?= $filtros['id_colegio'] === (int)$colegio['id_colegio'] ? 'selected' : '' ?>>
                                                    <?= inventario_h($colegio['nom_colegio']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-md-2">
                                        <label class="form-label">Estado</label>
                                        <select id="filtroEstado" class="form-select">
                                            <option value="">Todos</option>
                                            <?php foreach ($estados as $estado): ?>
                                                <option value="<?= (int)$estado['id_estado'] ?>" <?= $filtros['id_estado'] === (int)$estado['id_estado'] ? 'selected' : '' ?>>
                                                    <?= inventario_h($estado['nombre_estado']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Ubicación</label>
                                        <select id="filtroUbicacion" class="form-select">
                                            <option value="">Todas</option>
                                            <?php foreach ($ubicacionesPc as $ub): ?>
                                                <option value="<?= (int)$ub['id_ubicacion'] ?>">
                                                    <?= inventario_h($ub['nombre_ubicacion']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Usuario asignado</label>
                                        <select id="filtroUsuario" class="form-select">
                                            <option value="">Todos</option>
                                            <?php foreach ($usuariosFiltro as $usuario): ?>
                                                <option value="<?= (int)$usuario['id'] ?>" <?= $filtros['id_usuario_asignado'] === (int)$usuario['id'] ? 'selected' : '' ?>>
                                                    <?= inventario_h($usuario['nombre_completo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Buscar</label>
                                        <input type="text" id="filtroBusqueda" class="form-control" placeholder="Nombre, serie, QR..." value="<?= inventario_h($filtros['busqueda']) ?>">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle" id="tablaInventario">
                                        <thead>
                                            <tr>
                                                <th>N°</th>
                                                <th>Equipo</th>
                                                <?php if ($mostrarColumnaColegio): ?>
                                                <th>Colegio</th>
                                                <?php endif; ?>
                                                <th>Tipo</th>
                                                <th>Serie</th>
                                                <th>Ubicación</th>
                                                <th>Asignado</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ===== TAB MONITORES ===== -->
                    <div class="tab-pane fade <?= $tabActiva === 'monitores' ? 'show active' : '' ?>" id="tab-monitores" role="tabpanel">

                        <div id="contenedorResumenMonitores">
                            <?php
                            $resumen = $inventario->obtenerResumenMonitores($filtros);
                            require __DIR__ . '/componentes/resumen_monitores.php';
                            ?>
                        </div>

                        <div class="card shadow-sm border-0 inv-panel">
                            <div class="card-body">
                                <div class="row g-3 align-items-end mb-4">
                                    <?php if ($mostrarFiltroColegio): ?>
                                    <div class="col-md-3">
                                        <label class="form-label">Colegio</label>
                                        <select id="filtroColegioMon" class="form-select">
                                            <option value="">Todos</option>
                                            <?php foreach ($colegios as $colegio): ?>
                                                <option value="<?= (int)$colegio['id_colegio'] ?>">
                                                    <?= inventario_h($colegio['nom_colegio']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-md-2">
                                        <label class="form-label">Estado</label>
                                        <select id="filtroEstadoMon" class="form-select">
                                            <option value="">Todos</option>
                                            <?php foreach ($estados as $estado): ?>
                                                <option value="<?= (int)$estado['id_estado'] ?>">
                                                    <?= inventario_h($estado['nombre_estado']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label">Usuario asignado</label>
                                        <select id="filtroUsuarioMon" class="form-select">
                                            <option value="">Todos</option>
                                            <?php foreach ($usuariosFiltro as $usuario): ?>
                                                <option value="<?= (int)$usuario['id'] ?>">
                                                    <?= inventario_h($usuario['nombre_completo']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Buscar</label>
                                        <input type="text" id="filtroBusquedaMon" class="form-control" placeholder="Nombre, serie, marca...">
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle" id="tablaMonitores">
                                        <thead>
                                            <tr>
                                                <th>N°</th>
                                                <th>Monitor</th>
                                                <?php if ($mostrarColumnaColegio): ?>
                                                <th>Colegio</th>
                                                <?php endif; ?>
                                                <th>Ubicación</th>
                                                <th>Serie</th>
                                                <th>Asignado</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade <?= $tabActiva === 'tablet' ? 'show active' : '' ?>" id="tab-tablet" role="tabpanel">
                        <div class="alert alert-info border-0 shadow-sm p-4 mb-0">
                            <h5 class="mb-1">Tablet</h5>
                            <p class="mb-0">Disculpe las molestias, estamos trabajando en esta pantalla.</p>
                        </div>
                    </div>

                    <div class="tab-pane fade <?= $tabActiva === 'impresoras' ? 'show active' : '' ?>" id="tab-impresoras" role="tabpanel">
                        <div class="alert alert-info border-0 shadow-sm p-4 mb-0">
                            <h5 class="mb-1">Impresoras</h5>
                            <p class="mb-0">Disculpe las molestias, estamos trabajando en esta pantalla.</p>
                        </div>
                    </div>

                </div><!-- /tab-content -->
            </div>
        </div>
    </div>
</div>

<!-- Modal QR equipo -->
<div class="modal fade" id="modalQrEquipo" tabindex="-1" aria-labelledby="modalQrEquipoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg inv-qr-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalQrEquipoLabel">Código QR del equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center">
                <div class="inv-qr-card mx-auto">
                    <div id="qrEquipoCanvas" class="inv-qr-canvas"></div>
                    <h6 class="mb-1 mt-3" id="qrEquipoNombre">Equipo</h6>
                    <p class="text-muted small mb-2" id="qrEquipoSerie"></p>
                </div>
            </div>
            <div class="modal-footer justify-content-between">
                <a href="#" target="_blank" rel="noopener" class="btn btn-outline-primary" id="btnAbrirFichaQr">
                    <i class="bi bi-box-arrow-up-right me-1"></i>Abrir ficha QR
                </a>
                <button type="button" class="btn btn-primary" id="btnImprimirQrIndividual" onclick="InventarioFunciones.imprimirQrIndividual(this)">
                    <i class="bi bi-printer me-1"></i>Imprimir QR
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Offcanvas detalle inventario -->
<div class="offcanvas offcanvas-end inv-detail-offcanvas" tabindex="-1" id="offcanvasDetalleInventario" aria-labelledby="offcanvasDetalleInventarioLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="offcanvasDetalleInventarioLabel">Detalle</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body" id="offcanvasDetalleInventarioBody">
        <div class="text-center py-5 text-muted">Selecciona un registro para ver el detalle.</div>
    </div>
</div>

<!-- Modal galería equipos PC -->
<div class="modal fade" id="modalGaleriaEquipo" tabindex="-1" aria-labelledby="modalGaleriaEquipoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGaleriaEquipoLabel">Imágenes del equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalGaleriaBody">
                <div class="text-center py-5 text-muted">Cargando galería...</div>
            </div>
        </div>
    </div>
</div>

<!-- Modal galería monitores -->
<div class="modal fade" id="modalGaleriaMonitor" tabindex="-1" aria-labelledby="modalGaleriaMonitorLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGaleriaMonitorLabel">Imágenes del monitor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalGaleriaMonitorBody">
                <div class="text-center py-5 text-muted">Cargando galería...</div>
            </div>
        </div>
    </div>
</div>

<script>
window.INVENTARIO_CONFIG = {
    tabActiva: '<?= $tabActiva ?>',
    idColegioRestringido: <?= $idColegioRestringido ?>,
    mostrarColumnaColegio: <?= $mostrarColumnaColegio ? 'true' : 'false' ?>,
    qrEquipoBaseUrl: <?= json_encode(inventario_url_absoluta('equipoQRinformacion.php')) ?>,
    colegioLogoBaseUrl: '../img/colegios/',
    endpoints: {
        listar:          'ajax/listar_equipos.php',
        detalle:         'ajax/obtener_detalle_equipo.php',
        cambiarEstado:   'ajax/cambiar_estado_equipo.php',
        eliminar:        'ajax/eliminar_equipo.php',
        liberarEquipo:   'ajax/liberar_equipo.php',
        listarMonitores: 'ajax/listar_monitores.php',
        detalleMonitor:  'ajax/obtener_detalle_monitor.php',
        fotosMonitor:    'ajax/obtener_fotos_monitor.php',
        eliminarMonitor: 'ajax/eliminar_monitor.php',
        liberarMonitor:  'ajax/liberar_monitor.php'
    },
    estadosEquipo: <?= json_encode(array_map(static function ($estado) {
        return [
            'id_estado'    => (int)$estado['id_estado'],
            'nombre_estado'=> (string)$estado['nombre_estado'],
            'color_badge'  => (string)($estado['color_badge'] ?? 'secondary'),
        ];
    }, $estados), JSON_UNESCAPED_UNICODE) ?>,
    ubicaciones: <?= json_encode($ubicacionesPermitidas, JSON_UNESCAPED_UNICODE) ?>,
    usuariosPorColegio: <?= json_encode($usuariosPorColegio, JSON_UNESCAPED_UNICODE) ?>,
    usuariosPermitidos: <?= json_encode($usuarios, JSON_UNESCAPED_UNICODE) ?>
};
</script>

<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
