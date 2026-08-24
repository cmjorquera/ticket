<?php
require_once __DIR__ . '/componentes/boot.php';

function inv_dash_money($valor): string
{
    return '$' . number_format((int)$valor, 0, ',', '.');
}

function inv_dash_int($valor): string
{
    return number_format((int)$valor, 0, ',', '.');
}

function inv_dash_section_menu(): string
{
    $html = '<div class="dropdown">';
    $html .= '<button type="button" class="btn inv-section-menu-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones del gráfico">';
    $html .= '<i class="bi bi-three-dots-vertical"></i>';
    $html .= '</button>';
    $html .= '<ul class="dropdown-menu dropdown-menu-end inv-kpi-menu">';
    $html .= '<li><span class="dropdown-item-text text-muted">Ver detalle</span></li>';
    $html .= '</ul></div>';
    return $html;
}

try {
    $tituloPagina = 'Dashboard Inventario';
    $alcanceInventario = $inventario->obtenerAlcanceInventario($idUsuarioSession);
    $perfilInventario = (int)($alcanceInventario['id_perfil'] ?? 1);

    if ($perfilInventario < 2) {
        header('Location: index.php');
        exit;
    }

    $filtrosDashboard = $inventario->normalizarFiltrosDashboard([
        'id_colegio' => (int)($_GET['id_colegio'] ?? 0),
        'tipo_equipo' => trim((string)($_GET['tipo_equipo'] ?? 'pc')),
    ], $alcanceInventario);

    $dashboard = $inventario->obtenerDashboardInventario($filtrosDashboard);
    $coloresColegio = $inventario->obtenerColoresColegio($idUsuarioSession);
    $colegios = $alcanceInventario['colegios'] ?? [];
    $estadosInventario = $inventario->obtenerEstados();
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar el dashboard de inventario: ' . $e->getMessage());
}

$kpis = $dashboard['kpis'] ?? [];
$valor = $dashboard['valor'] ?? [];
$valorPorColegio = $dashboard['valor_por_colegio'] ?? [];
$resumenPorColegio = $dashboard['resumen_por_colegio'] ?? [];
$alertas = $dashboard['alertas'] ?? [];
$ultimos = $dashboard['ultimos'] ?? [];
$detalleValor = $dashboard['detalle_valor'] ?? [];
$hardware = $dashboard['hardware'] ?? [];
$idColegioFiltro = (int)($filtrosDashboard['id_colegio'] ?? 0);
$tipoMeta = [
    'pc' => ['singular' => 'PC', 'plural' => 'PC', 'genero' => 'm', 'activos' => 'PC ACTIVOS', 'baja' => 'PC DADOS DE BAJA'],
    'tablet' => ['singular' => 'Tablet', 'plural' => 'Tablet', 'genero' => 'f', 'activos' => 'TABLET ACTIVAS', 'baja' => 'TABLET DADAS DE BAJA'],
    'monitor' => ['singular' => 'Monitor', 'plural' => 'Monitores', 'genero' => 'm', 'activos' => 'MONITORES ACTIVOS', 'baja' => 'MONITORES DADOS DE BAJA'],
    'impresora' => ['singular' => 'Impresora', 'plural' => 'Impresoras', 'genero' => 'f', 'activos' => 'IMPRESORAS ACTIVAS', 'baja' => 'IMPRESORAS DADAS DE BAJA'],
];
$tipoEquipoFiltro = (string)($filtrosDashboard['tipo_equipo'] ?? 'pc');
$tipoSeleccionado = $tipoMeta[$tipoEquipoFiltro] ?? $tipoMeta['pc'];
$tipoPluralUpper = mb_strtoupper($tipoSeleccionado['plural'], 'UTF-8');
$tipoSingularUpper = mb_strtoupper($tipoSeleccionado['singular'], 'UTF-8');
$tipoEnPreparacion = $tipoEquipoFiltro === 'impresora';

$totalGeneral = [
    'total_equipos' => 0,
    'activos' => 0,
    'sin_ubicacion' => 0,
    'sin_responsable' => 0,
    'en_reparacion' => 0,
    'dados_baja' => 0,
    'valor_total' => 0,
];
foreach ($resumenPorColegio as $filaResumen) {
    foreach ($totalGeneral as $clave => $valorInicial) {
        $totalGeneral[$clave] += (int)($filaResumen[$clave] ?? 0);
    }
}

$colegioSeleccionado = 'Todos los colegios visibles';
foreach ($colegios as $colegio) {
    if ((int)$colegio['id_colegio'] === (int)($filtrosDashboard['id_colegio'] ?? 0)) {
        $colegioSeleccionado = $colegio['nom_colegio'];
        break;
    }
}

// -----------------------------------------------------------------------
// BRANDING: colores + foto del colegio SELECCIONADO EN EL FILTRO.
// obtenerColoresColegio($idUsuarioSession) (arriba) trae el branding del
// colegio del USUARIO EN SESION -- fijo, no cambia al filtrar. Aqui se
// busca el branding del colegio filtrado (puede ser otro) y, si existe,
// tiene prioridad; si no hay colegio filtrado o no tiene colores propios,
// se usa el del usuario en sesion como respaldo.
// -----------------------------------------------------------------------
$coloresColegioFiltro = $idColegioFiltro > 0 ? $inventario->obtenerColoresPorColegio($idColegioFiltro) : [];
$coloresActivos = !empty($coloresColegioFiltro['color_principal']) ? $coloresColegioFiltro : $coloresColegio;

// Los colores vienen de una columna varchar editable por un admin, no de
// una paleta validada: solo se valida el FORMATO hex (mismo criterio que ya
// usaba el hero de index.php/carga_masiva.php), no legibilidad ni contraste.
// Si un color no es un hex valido, se cae al respaldo de graficos.css.
$_hex = static function (?string $valor): string {
    return preg_match('/^#[0-9a-fA-F]{3,8}$/', (string)$valor) ? (string)$valor : '';
};
$_c1 = $_hex($coloresActivos['color_principal'] ?? null);
$_c2 = $_hex($coloresActivos['color_secundario'] ?? null);
$_c3 = $_hex($coloresActivos['color_terciario'] ?? null);
$_c4 = $_hex($coloresActivos['color_cuaternario'] ?? null);

// Respaldo para graficos cuando el colegio no tiene 4 colores propios
// cargados (muy comun: terciario/cuaternario suelen estar vacios). Es el
// mismo orden azul/naranja/aqua/amarillo validado por el skill "dataviz"
// para 4 series adyacentes (dona/barra) -- ver css/graficos.css.
$_coloresChartFallback = ['#2a78d6', '#eb6834', '#1baf7a', '#eda100'];
$coloresChartColegio = [
    'principal'   => $_c1 ?: $_coloresChartFallback[0],
    'secundario'  => $_c2 ?: $_coloresChartFallback[1],
    'terciario'   => $_c3 ?: $_coloresChartFallback[2],
    'cuaternario' => $_c4 ?: $_coloresChartFallback[3],
];

$_heroBranded = $_c1 !== '';
$_heroStyle = $_heroBranded ? ' style="background: linear-gradient(135deg, ' . $_c1 . ' 0%, ' . ($_c2 ?: $_c1) . ' 100%)"' : '';

// Tarjetas KPI con degradado del colegio: solo cuando hay UN colegio puntual
// filtrado (con "Todos los colegios" no hay un solo colegio al que pintar
// las tarjetas, y usar igual el color del usuario en sesion confundiria).
$_kpiBranded = $_heroBranded && $idColegioFiltro > 0;
$_kpiBrandClass = $_kpiBranded ? ' inv-kpi-card--branded' : '';
$_kpiBrandStyle = $_kpiBranded
    ? ' style="background: linear-gradient(135deg, ' . $_c1 . ' 0%, ' . ($_c2 ?: $_c1) . ' 100%);"'
    : '';

// Foto del colegio: archivo fisico img/colegios/colegio_{id}.png (prefijo
// "colegio_" real segun exportar_plantilla.php e inventario.js -- NO es
// "{id}.png" solo). Se valida existencia en disco antes de mostrar el <img>
// para no romper el layout con un icono de imagen rota.
$fotoColegioExiste = false;
$fotoColegioUrl = '';
if ($idColegioFiltro > 0) {
    $fotoColegioRuta = dirname(__DIR__) . '/img/colegios/colegio_' . $idColegioFiltro . '.png';
    if (is_file($fotoColegioRuta)) {
        $fotoColegioExiste = true;
        $fotoColegioUrl = inventario_sistema_url('img/colegios/colegio_' . $idColegioFiltro . '.png');
    }
}

$colegioMayor = $valor['colegio_mayor_valor'] ?? null;
$alertaMeta = [
    'sin_ubicacion' => [
        'titulo' => $tipoSeleccionado['plural'] . ' sin ubicación',
        'asunto' => 'Alerta inventario - ' . $tipoSeleccionado['plural'] . ' sin ubicación',
        'mensaje' => 'El colegio seleccionado tiene {total} ' . $tipoSeleccionado['plural'] . ' sin ubicación asignada.',
    ],
    'sin_responsable' => [
        'titulo' => $tipoSeleccionado['plural'] . ' sin responsable',
        'asunto' => 'Alerta inventario - ' . $tipoSeleccionado['plural'] . ' sin responsable',
        'mensaje' => 'El colegio seleccionado tiene {total} ' . $tipoSeleccionado['plural'] . ' sin responsable asignado.',
    ],
    'en_reparacion' => [
        'titulo' => $tipoSeleccionado['plural'] . ' en reparación',
        'asunto' => 'Alerta inventario - ' . $tipoSeleccionado['plural'] . ' en reparación',
        'mensaje' => 'El colegio seleccionado tiene {total} ' . $tipoSeleccionado['plural'] . ' en reparación.',
    ],
    'dados_baja' => [
        'titulo' => $tipoSeleccionado['plural'] . ' dados de baja',
        'asunto' => 'Alerta inventario - ' . $tipoSeleccionado['plural'] . ' dados de baja',
        'mensaje' => 'El colegio seleccionado tiene {total} ' . $tipoSeleccionado['plural'] . ' dados de baja.',
    ],
    'sin_valor' => [
        'titulo' => $tipoSeleccionado['plural'] . ' sin valor registrado',
        'asunto' => 'Alerta inventario - ' . $tipoSeleccionado['plural'] . ' sin valor registrado',
        'mensaje' => 'El colegio seleccionado tiene {total} ' . $tipoSeleccionado['plural'] . ' sin valor de compra registrado.',
    ],
];
$chartEstados = [
    'labels' => array_map(static fn($f) => (string)$f['etiqueta'], $dashboard['estados'] ?? []),
    'values' => array_map(static fn($f) => (int)$f['total'], $dashboard['estados'] ?? []),
];
$chartTipos = [
    'labels' => array_map(static fn($f) => (string)$f['etiqueta'], $dashboard['tipos'] ?? []),
    'values' => array_map(static fn($f) => (int)$f['total'], $dashboard['tipos'] ?? []),
];
$chartRam = [
    'labels' => array_map(static fn($f) => (string)$f['etiqueta'], $hardware['ram'] ?? []),
    'values' => array_map(static fn($f) => (int)$f['total'], $hardware['ram'] ?? []),
];
$chartSistemas = [
    'labels' => array_map(static fn($f) => (string)$f['etiqueta'], $hardware['sistemas'] ?? []),
    'values' => array_map(static fn($f) => (int)$f['total'], $hardware['sistemas'] ?? []),
];
$maxValorColegio = max(1, ...array_map(static fn($f) => (int)($f['valor_total'] ?? 0), $valorPorColegio ?: [['valor_total' => 1]]));
$valorTotalVisible = array_sum(array_map(static fn($f) => (int)($f['valor_total'] ?? 0), $valorPorColegio));

// -----------------------------------------------------------------------
// SCATTER + COMPARATIVA: reutilizan obtenerResumenPorColegioDashboard()
// (misma fuente que $resumenPorColegio) pero forzando id_colegio=0, para
// obtener SIEMPRE todos los colegios visibles al usuario -- $resumenPorColegio
// colapsa a 1 fila cuando hay un colegio filtrado, y un scatter/promedio
// con un solo punto no aporta nada.
// -----------------------------------------------------------------------
$filtrosSistema = $inventario->normalizarFiltrosDashboard(
    ['id_colegio' => 0, 'tipo_equipo' => $tipoEquipoFiltro],
    $alcanceInventario
);
$resumenSistema = $inventario->obtenerResumenPorColegioDashboard($filtrosSistema);

// Scatter "equipos vs valor por colegio": un punto por colegio, con el
// logo y los colores propios de CADA colegio (no los del colegio filtrado
// como en $coloresChartColegio -- aca cada punto necesita SU PROPIO color,
// para poder distinguirse entre si en el mismo grafico).
// X = total_equipos, Y = valor_total (misma tabla/consulta que la tabla
// "Resumen por colegio" de más abajo, solo que sin filtrar por colegio).
$chartScatterColegios = array_map(function ($fila) use ($idColegioFiltro, $inventario) {
    $idColegioFila = (int)($fila['id_colegio'] ?? 0);

    // Colores propios del colegio de ESTA fila (obtenerColoresPorColegio,
    // por id_colegio -- NO obtenerColoresColegio, que espera un id_usuario
    // y devolveria datos incorrectos si se le pasa un id_colegio).
    $coloresFila = $idColegioFila > 0 ? $inventario->obtenerColoresPorColegio($idColegioFila) : [];
    $colorPrincipalFila = preg_match('/^#[0-9a-fA-F]{3,8}$/', $coloresFila['color_principal'] ?? '')
        ? $coloresFila['color_principal'] : '#2a78d6';
    $colorSecundarioFila = preg_match('/^#[0-9a-fA-F]{3,8}$/', $coloresFila['color_secundario'] ?? '')
        ? $coloresFila['color_secundario'] : '#008300';

    // Logo: img/colegios/colegio_{id}.png (prefijo real "colegio_", ver
    // exportar_plantilla.php / inventario.js). Se valida en disco antes de
    // mandar la URL al frontend -- si no existe, 'imagen' queda null y el
    // plugin de dashboard.js cae al circulo de color solo.
    $imagenFila = null;
    if ($idColegioFila > 0) {
        $rutaImagenFila = dirname(__DIR__) . '/img/colegios/colegio_' . $idColegioFila . '.png';
        if (is_file($rutaImagenFila)) {
            $imagenFila = inventario_sistema_url('img/colegios/colegio_' . $idColegioFila . '.png');
        }
    }

    return [
        'id_colegio'       => $idColegioFila,
        'colegio'          => (string)($fila['nom_colegio'] ?? ''),
        'x'                => (int)($fila['total_equipos'] ?? 0),
        'y'                => (int)($fila['valor_total'] ?? 0),
        'imagen'           => $imagenFila,
        'color_principal'  => $colorPrincipalFila,
        'color_secundario' => $colorSecundarioFila,
        'seleccionado'     => $idColegioFiltro > 0 && $idColegioFila === $idColegioFiltro,
    ];
}, $resumenSistema);

// Comparativa "colegio vs promedio del sistema": solo aplica si hay un
// colegio puntual seleccionado en el filtro (si no, no hay "un" colegio
// que comparar contra el promedio).
$comparativaColegio = null;
if ($idColegioFiltro > 0 && !empty($resumenSistema)) {
    $filaColegioComparativa = null;
    foreach ($resumenSistema as $filaSistema) {
        if ((int)($filaSistema['id_colegio'] ?? 0) === $idColegioFiltro) {
            $filaColegioComparativa = $filaSistema;
            break;
        }
    }
    if ($filaColegioComparativa !== null) {
        $totalColegiosSistema = count($resumenSistema);
        $sumaMetricas = ['total_equipos' => 0, 'activos' => 0, 'sin_ubicacion' => 0, 'en_reparacion' => 0, 'dados_baja' => 0, 'valor_total' => 0];
        foreach ($resumenSistema as $filaSistema) {
            foreach ($sumaMetricas as $clave => $acumulado) {
                $sumaMetricas[$clave] += (int)($filaSistema[$clave] ?? 0);
            }
        }
        $promedioMetricas = [];
        foreach ($sumaMetricas as $clave => $suma) {
            $promedioMetricas[$clave] = $totalColegiosSistema > 0 ? (int)round($suma / $totalColegiosSistema) : 0;
        }
        $comparativaColegio = [
            'nombre' => (string)($filaColegioComparativa['nom_colegio'] ?? ''),
            // Categorias en cantidad de equipos (mismo eje/unidad). El valor
            // monetario NO entra aca: se muestra aparte en .inv-card-info
            // para no mezclar dos escalas distintas en un mismo eje Y.
            'categorias' => ['Total', 'Activos', 'Sin ubicación', 'En reparación', 'Dados de baja'],
            'colegio' => [
                (int)($filaColegioComparativa['total_equipos'] ?? 0),
                (int)($filaColegioComparativa['activos'] ?? 0),
                (int)($filaColegioComparativa['sin_ubicacion'] ?? 0),
                (int)($filaColegioComparativa['en_reparacion'] ?? 0),
                (int)($filaColegioComparativa['dados_baja'] ?? 0),
            ],
            'promedio' => [
                $promedioMetricas['total_equipos'],
                $promedioMetricas['activos'],
                $promedioMetricas['sin_ubicacion'],
                $promedioMetricas['en_reparacion'],
                $promedioMetricas['dados_baja'],
            ],
            'valor_colegio'  => (int)($filaColegioComparativa['valor_total'] ?? 0),
            'valor_promedio' => $promedioMetricas['valor_total'],
        ];
    }
}

$cssExtraInventario = ['css/dashboard.css', inventario_sistema_url('css/graficos.css'), inventario_sistema_url('css/cards.css')];
$jsExtraInventario = ['js/dashboard.js'];
$idPagActual = '9';
require __DIR__ . '/componentes/layout_top.php';
?>
<div class="row mx-1 mx-md-3">
    <div class="col-12">
        <div class="card shadow mb-4 px-0 border-0 inv-panel">
            <div class="card-body p-4 p-lg-5">

                <div class="inv-hero mb-4<?= $_heroBranded ? ' inv-hero--branded' : '' ?>"<?= $_heroStyle ?>>
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="inv-kicker">Módulo Inventario</span>
                            <h1 class="inv-title mb-2">Dashboard de Inventario</h1>
                            <p class="inv-subtitle mb-0">Resumen general, distribución, valor y estado del equipamiento tecnológico.</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="index.php" class="btn btn-outline-primary">
                                <i class="bi bi-arrow-left me-1"></i>Volver al Inventario
                            </a>
                        </div>
                    </div>
                </div>

                <div class="inv-dashboard-filters mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-12 col-md-6 col-xl-5">
                            <label for="id_colegio" class="form-label">Colegio</label>
                            <select name="id_colegio" id="id_colegio" class="form-select" data-dashboard-filter>
                                <option value="0">Todos los colegios</option>
                                <?php foreach ($colegios as $colegio): ?>
                                    <option value="<?= (int)$colegio['id_colegio'] ?>" <?= (int)($filtrosDashboard['id_colegio'] ?? 0) === (int)$colegio['id_colegio'] ? 'selected' : '' ?>>
                                        <?= inventario_h($colegio['nom_colegio']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <?php
                // Pestañas de tipo de equipo: reemplazan el <select> anterior.
                // Son enlaces reales (no data-bs-toggle="tab"): esta pagina NO
                // tiene un panel de contenido por tipo ya cargado en el DOM
                // (serian 4 dashboards completos a la vez, con 4x las consultas).
                // Cada click recarga dashboard.php con el nuevo tipo_equipo -- el
                // mismo comportamiento que el <select> con data-dashboard-filter
                // tenia antes, solo que ahora es un <a> normal (funciona sin JS).
                $tabsTipoEquipo = [
                    'pc' => ['label' => 'PC', 'icono' => 'bi-display'],
                    'tablet' => ['label' => 'Tablet', 'icono' => 'bi-tablet'],
                    'monitor' => ['label' => 'Monitor', 'icono' => 'bi-tv'],
                    'impresora' => ['label' => 'Impresora', 'icono' => 'bi-printer'],
                ];
                ?>
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <?php foreach ($tabsTipoEquipo as $tabValor => $tabMeta): ?>
                        <?php $tabActivo = $tipoEquipoFiltro === $tabValor; ?>
                        <li class="nav-item" role="presentation">
                            <a class="nav-link<?= $tabActivo ? ' active' : '' ?>"
                               href="dashboard.php?id_colegio=<?= $idColegioFiltro ?>&amp;tipo_equipo=<?= inventario_h($tabValor) ?>"
                               role="tab"
                               <?= $tabActivo ? 'aria-current="page"' : '' ?>>
                                <i class="bi <?= inventario_h($tabMeta['icono']) ?> me-1"></i><?= inventario_h($tabMeta['label']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="row g-4">
                    <!-- Info + branding del colegio filtrado -->
                    <!-- Fuente: $coloresActivos / $fotoColegioUrl / $colegioSeleccionado (calculados arriba) -->
                    <div class="col-12 col-lg-3">
                        <div class="inv-card text-center h-100">
                            <?php include __DIR__ . '/../include/logo_3d_seduc.php'; ?>

                            <?php if ($fotoColegioExiste): ?>
                                <img src="<?= inventario_h($fotoColegioUrl) ?>"
                                     alt="Foto de <?= inventario_h($colegioSeleccionado) ?>"
                                     class="img-fluid rounded mt-3"
                                     style="max-width: 160px; height: auto;">
                            <?php endif; ?>

                            <h5 class="mt-3 mb-0"><?= inventario_h($colegioSeleccionado) ?></h5>
                            <?php if ($idColegioFiltro <= 0): ?>
                                <small class="text-muted d-block mt-1">Seleccione un colegio para ver su logo y colores</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12 col-lg-9">

                <div class="inv-dashboard-grid inv-dashboard-grid--kpis mb-4">
                    <?php
                    $cardsKpi = [
                        ['label' => 'TOTAL DE ' . $tipoPluralUpper, 'valor' => $kpis['total_pcs'] ?? 0, 'descripcion' => $tipoSeleccionado['plural'] . ' visibles con filtros', 'icono' => 'bi-pc-display'],
                        ['label' => $tipoSeleccionado['activos'], 'valor' => $kpis['activos'] ?? 0, 'descripcion' => 'Operativos actualmente', 'icono' => 'bi-check-circle'],
                        ['label' => $tipoPluralUpper . ' SIN UBICACIÓN', 'valor' => $kpis['sin_ubicacion'] ?? 0, 'descripcion' => 'Pendientes de corregir', 'icono' => 'bi-geo-alt', 'tipo_alerta' => 'sin_ubicacion'],
                        ['label' => $tipoPluralUpper . ' SIN RESPONSABLE', 'valor' => $kpis['sin_responsable'] ?? 0, 'descripcion' => 'Sin usuario asignado', 'icono' => 'bi-person-x', 'tipo_alerta' => 'sin_responsable'],
                        ['label' => $tipoPluralUpper . ' EN REPARACIÓN', 'valor' => $kpis['en_reparacion'] ?? 0, 'descripcion' => $tipoSeleccionado['plural'] . ' en revisión', 'icono' => 'bi-tools', 'tipo_alerta' => 'en_reparacion'],
                        ['label' => $tipoSeleccionado['baja'], 'valor' => $kpis['dados_baja'] ?? 0, 'descripcion' => 'Fuera de operación', 'icono' => 'bi-x-octagon', 'tipo_alerta' => 'dados_baja'],
                    ];
                    foreach ($cardsKpi as $card):
                        $totalCard = (int)($card['valor'] ?? 0);
                        $tipoAlerta = (string)($card['tipo_alerta'] ?? '');
                        $puedeCorreo = $tipoAlerta !== '' && $totalCard > 0 && $idColegioFiltro > 0;
                        $sinColegio = $tipoAlerta !== '' && $totalCard > 0 && $idColegioFiltro <= 0;
                    ?>
                        <div class="inv-kpi-card<?= $_kpiBrandClass ?>"<?= $_kpiBrandStyle ?>>
                            <div class="inv-kpi-card__top">
                                <span><?= inventario_h($card['label']) ?></span>
                                <div class="dropdown">
                                    <button type="button" class="btn inv-kpi-menu-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones de <?= inventario_h($card['label']) ?>">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end inv-kpi-menu">
                                        <?php if ($puedeCorreo): ?>
                                            <li>
                                                <button type="button"
                                                        class="dropdown-item inv-dashboard-alert-action"
                                                        data-alert-type="<?= inventario_h($tipoAlerta) ?>"
                                                        data-alert-total="<?= $totalCard ?>">
                                                    <i class="bi bi-envelope me-2"></i>Mandar correo
                                                </button>
                                            </li>
                                        <?php elseif ($sinColegio): ?>
                                            <li><span class="dropdown-item-text text-muted">Seleccione un colegio específico para enviar correos a sus responsables.</span></li>
                                        <?php else: ?>
                                            <li><span class="dropdown-item-text text-muted">Sin acciones disponibles</span></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                            <strong><?= inv_dash_int($totalCard) ?></strong>
                            <small><?= inventario_h($card['descripcion']) ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="inv-dashboard-grid inv-dashboard-grid--value mb-4">
                    <div class="inv-kpi-card inv-kpi-card--money">
                        <div class="inv-kpi-card__top">
                            <span>VALOR TOTAL DEL INVENTARIO DE <?= inventario_h($tipoPluralUpper) ?></span>
                            <button type="button" class="btn inv-kpi-menu-btn" disabled aria-label="Sin acciones disponibles">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                        </div>
                        <strong><?= inv_dash_money($valor['valor_total'] ?? 0) ?></strong>
                        <small>Consolidado de colegios activos visibles</small>
                    </div>
                    <div class="inv-kpi-card inv-kpi-card--money">
                        <div class="inv-kpi-card__top">
                            <span>COLEGIO CON MAYOR VALOR EN <?= inventario_h($tipoPluralUpper) ?></span>
                            <button type="button" class="btn inv-kpi-menu-btn" disabled aria-label="Sin acciones disponibles">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                        </div>
                        <strong><?= inventario_h($colegioMayor['nom_colegio'] ?? 'Sin datos') ?></strong>
                        <small><?= inv_dash_money($colegioMayor['valor_total'] ?? 0) ?></small>
                    </div>
                    <div class="inv-kpi-card inv-kpi-card--money">
                        <?php
                        $totalSinValor = (int)($valor['sin_valor'] ?? 0);
                        $puedeCorreoSinValor = $totalSinValor > 0 && $idColegioFiltro > 0;
                        ?>
                        <div class="inv-kpi-card__top">
                            <span>EQUIPOS SIN VALOR REGISTRADO</span>
                            <div class="dropdown">
                                <button type="button" class="btn inv-kpi-menu-btn" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones de equipos sin valor">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end inv-kpi-menu">
                                    <?php if ($puedeCorreoSinValor): ?>
                                        <li>
                                            <button type="button"
                                                    class="dropdown-item inv-dashboard-alert-action"
                                                    data-alert-type="sin_valor"
                                                    data-alert-total="<?= $totalSinValor ?>">
                                                <i class="bi bi-envelope me-2"></i>Mandar correo
                                            </button>
                                        </li>
                                    <?php elseif ($totalSinValor > 0 && $idColegioFiltro <= 0): ?>
                                        <li><span class="dropdown-item-text text-muted">Seleccione un colegio específico para enviar correos a sus responsables.</span></li>
                                    <?php else: ?>
                                        <li><span class="dropdown-item-text text-muted">Sin acciones disponibles</span></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <strong><?= inv_dash_int($totalSinValor) ?></strong>
                        <small>Sin compra o con valor cero</small>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-xl-7">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <div class="inv-section-title">
                                    <h2>Valor del inventario <?= inventario_h($tipoSeleccionado['plural']) ?> por colegio</h2>
                                    <span>Valores de compra registrados</span>
                                </div>
                                <?= inv_dash_section_menu() ?>
                            </div>
                            <div class="inv-value-bars">
                                <?php if ($tipoEnPreparacion): ?>
                                    <div class="inv-empty-state">Módulo en preparación. Sin información disponible para el dashboard.</div>
                                <?php endif; ?>
                                <?php if (empty($valorPorColegio)): ?>
                                    <div class="inv-empty-state">No hay equipos para los filtros seleccionados.</div>
                                <?php elseif ($valorTotalVisible <= 0): ?>
                                    <div class="inv-empty-state">No hay valores de compra registrados para los filtros seleccionados.</div>
                                <?php endif; ?>
                                <?php foreach ($valorPorColegio as $fila): ?>
                                    <?php $porcentaje = ((int)$fila['valor_total'] / $maxValorColegio) * 100; ?>
                                    <div class="inv-value-bar">
                                        <div class="inv-value-bar__label">
                                            <span><?= inventario_h($fila['nom_colegio']) ?></span>
                                            <strong><?= inv_dash_money($fila['valor_total']) ?></strong>
                                        </div>
                                        <div class="inv-value-bar__track">
                                            <div class="inv-value-bar__fill" style="width: <?= max(3, (int)$porcentaje) ?>%"></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                    <div class="col-12 col-xl-5">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <div class="inv-section-title">
                                    <h2>Estado del inventario <?= inventario_h($tipoSeleccionado['plural']) ?></h2>
                                    <span><?= inventario_h($tipoSeleccionado['plural']) ?> por estado actual</span>
                                </div>
                                <?= inv_dash_section_menu() ?>
                            </div>
                            <div class="inv-chart-box">
                                <canvas id="chartEstados"></canvas>
                                <?php if (empty($chartEstados['labels'])): ?>
                                    <div class="inv-chart-empty">Sin información disponible para el tipo seleccionado.</div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($dashboard['estados'])): ?>
                            <!-- Fuente: $dashboard['estados'] (obtenerDistribucionEstadosDashboard) -->
                            <div class="inv-card-row inv-card-row--estados">
                                <?php foreach ($dashboard['estados'] as $estadoFila): ?>
                                    <div class="inv-card inv-card-estado inv-card-estado--<?= inventario_h($estadoFila['color_badge'] ?? 'secondary') ?>">
                                        <strong><?= inv_dash_int($estadoFila['total'] ?? 0) ?></strong>
                                        <span><?= inventario_h($estadoFila['etiqueta'] ?? 'Sin estado') ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </section>
                    </div>
                </div>

                <!-- ===== SCATTER: equipos vs valor por colegio | BAR: colegio vs promedio del sistema ===== -->
                <!-- Fuente PHP: $chartScatterColegios / $comparativaColegio, calculados a partir de
                     $inventario->obtenerResumenPorColegioDashboard() sin filtro de colegio (ver arriba) -->
                <div class="row g-4 mb-4">
                    <div class="col-12 col-xl-7">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <div class="inv-section-title">
                                    <h2>Equipos vs. valor por colegio</h2>
                                    <span>Cada punto es un colegio · eje X cantidad de equipos, eje Y valor</span>
                                </div>
                                <?= inv_dash_section_menu() ?>
                            </div>
                            <div class="chart-container">
                                <canvas id="chartScatterColegios"></canvas>
                                <?php if (empty($chartScatterColegios)): ?>
                                    <div class="chart-empty-state">Sin información disponible para el tipo seleccionado.</div>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($chartScatterColegios)): ?>
                            <!-- Leyenda manual con logo/colores reales de cada colegio (cada
                                 punto del scatter tiene SU PROPIO color, ya no hay un unico
                                 "seleccionado vs resto"). Tambien sirve de alternativa clickeable
                                 al punto del grafico (mas facil de tocar en movil). -->
                            <div class="chart-legend chart-legend--colegios">
                                <?php foreach ($chartScatterColegios as $puntoColegio): ?>
                                    <a class="chart-legend__item chart-legend__item--colegio<?= $puntoColegio['seleccionado'] ? ' is-active' : '' ?>"
                                       href="dashboard.php?id_colegio=<?= (int)$puntoColegio['id_colegio'] ?>&amp;tipo_equipo=<?= inventario_h($tipoEquipoFiltro) ?>"
                                       title="Filtrar por <?= inventario_h($puntoColegio['colegio']) ?>">
                                        <?php if ($puntoColegio['imagen']): ?>
                                            <img src="<?= inventario_h($puntoColegio['imagen']) ?>"
                                                 alt=""
                                                 class="chart-legend__logo"
                                                 style="border-color: <?= inventario_h($puntoColegio['color_secundario']) ?>; background: <?= inventario_h($puntoColegio['color_principal']) ?>;">
                                        <?php else: ?>
                                            <span class="chart-legend__swatch" style="background: <?= inventario_h($puntoColegio['color_principal']) ?>; border-color: <?= inventario_h($puntoColegio['color_secundario']) ?>;"></span>
                                        <?php endif; ?>
                                        <?= inventario_h($puntoColegio['colegio']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </section>
                    </div>
                    <div class="col-12 col-xl-5">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <div class="inv-section-title">
                                    <h2>Colegio vs. promedio del sistema</h2>
                                    <span><?= $comparativaColegio ? inventario_h($comparativaColegio['nombre']) : 'Seleccione un colegio' ?></span>
                                </div>
                                <?= inv_dash_section_menu() ?>
                            </div>
                            <?php if ($comparativaColegio): ?>
                                <div class="chart-container chart-container--sm">
                                    <canvas id="chartComparativaColegio"></canvas>
                                </div>
                                <div class="inv-card-row">
                                    <div class="inv-card inv-card-info">
                                        <span>Valor <?= inventario_h($comparativaColegio['nombre']) ?></span>
                                        <strong><?= inv_dash_money($comparativaColegio['valor_colegio']) ?></strong>
                                    </div>
                                    <div class="inv-card inv-card-info">
                                        <span>Valor promedio del sistema</span>
                                        <strong><?= inv_dash_money($comparativaColegio['valor_promedio']) ?></strong>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="inv-empty-state m-3">Seleccione un colegio específico en el filtro para comparar sus métricas contra el promedio del sistema.</div>
                            <?php endif; ?>
                        </section>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-xl-8">
                        <section class="inv-dashboard-section">
                            <div class="inv-dashboard-section__head">
                                <h2>Resumen por colegio</h2>
                            </div>
                            <div class="table-responsive">
                                <table class="table inv-dashboard-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Colegio</th>
                                            <th>Total <?= inventario_h($tipoSeleccionado['plural']) ?></th>
                                            <th>Activos</th>
                                            <th>Sin ubicación</th>
                                            <th>Sin responsable</th>
                                            <th>En reparación</th>
                                            <th>Dados de baja</th>
                                            <th>Valor total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($resumenPorColegio as $fila): ?>
                                        <tr>
                                            <td><?= inventario_h($fila['nom_colegio']) ?></td>
                                            <td><?= inv_dash_int($fila['total_equipos']) ?></td>
                                            <td><?= inv_dash_int($fila['activos']) ?></td>
                                            <td><?= inv_dash_int($fila['sin_ubicacion']) ?></td>
                                            <td><?= inv_dash_int($fila['sin_responsable']) ?></td>
                                            <td><?= inv_dash_int($fila['en_reparacion']) ?></td>
                                            <td><?= inv_dash_int($fila['dados_baja']) ?></td>
                                            <td><?= inv_dash_money($fila['valor_total']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>TOTAL GENERAL</th>
                                            <th><?= inv_dash_int($totalGeneral['total_equipos']) ?></th>
                                            <th><?= inv_dash_int($totalGeneral['activos']) ?></th>
                                            <th><?= inv_dash_int($totalGeneral['sin_ubicacion']) ?></th>
                                            <th><?= inv_dash_int($totalGeneral['sin_responsable']) ?></th>
                                            <th><?= inv_dash_int($totalGeneral['en_reparacion']) ?></th>
                                            <th><?= inv_dash_int($totalGeneral['dados_baja']) ?></th>
                                            <th><?= inv_dash_money($totalGeneral['valor_total']) ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </section>
                    </div>
                    <div class="col-12 col-xl-4">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <div class="inv-section-title">
                                    <h2>Distribución de <?= inventario_h($tipoSeleccionado['plural']) ?></h2>
                                    <span>Clasificación registrada</span>
                                </div>
                                <?= inv_dash_section_menu() ?>
                            </div>
                            <div class="inv-chart-box">
                                <canvas id="chartTipos"></canvas>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-xl-5">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <h2>Alertas del inventario</h2>
                            </div>
                            <div class="inv-alert-list">
                                <?php
                                $itemsAlerta = [
                                    ['bi-geo-alt', $alertas['sin_ubicacion'] ?? 0, $tipoSeleccionado['plural'] . ' sin ubicación asignada'],
                                    ['bi-person-x', $alertas['sin_responsable'] ?? 0, $tipoSeleccionado['plural'] . ' sin responsable'],
                                    ['bi-qr-code', $alertas['sin_qr'] ?? 0, $tipoSeleccionado['plural'] . ' sin código QR'],
                                    ['bi-image', $alertas['sin_fotografia'] ?? 0, $tipoSeleccionado['plural'] . ' sin fotografía'],
                                    ['bi-cash-coin', $alertas['sin_valor'] ?? 0, $tipoSeleccionado['plural'] . ' sin valor de compra'],
                                    ['bi-upc-scan', $alertas['sin_serie'] ?? 0, $tipoSeleccionado['plural'] . ' sin número de serie'],
                                ];
                                foreach ($itemsAlerta as $alerta): ?>
                                    <div class="inv-alert-item">
                                        <span class="inv-alert-item__icon"><i class="bi <?= inventario_h($alerta[0]) ?>"></i></span>
                                        <div>
                                            <strong><?= inv_dash_int($alerta[1]) ?></strong>
                                            <span><?= inventario_h($alerta[2]) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    </div>
                    <div class="col-12 col-xl-7">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <h2>Últimos <?= inventario_h($tipoSeleccionado['plural']) ?> ingresados</h2>
                            </div>
                            <div class="table-responsive">
                                <table class="table inv-dashboard-table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Equipo</th>
                                            <th>Identificador técnico</th>
                                            <th>Tipo</th>
                                            <th>Colegio</th>
                                            <th>Fecha de registro</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ultimos as $equipo): ?>
                                        <?php $nombreVisible = trim((string)($equipo['nombre_personalizado'] ?? '')); ?>
                                        <tr>
                                            <td><?= inventario_h($nombreVisible !== '' ? $nombreVisible : ($equipo['nombre_equipo'] ?? 'Sin nombre')) ?></td>
                                            <td><?= inventario_h($equipo['nombre_equipo'] ?? '-') ?></td>
                                            <td><?= inventario_h($equipo['tipo_pc'] ?? '-') ?></td>
                                            <td><?= inventario_h($equipo['nom_colegio'] ?? '-') ?></td>
                                            <td><?= inventario_h($equipo['fecha_registro'] ?? '-') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
                </div>

                <div class="row g-4 mb-4">
                    <div class="col-12 col-xl-4">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <div class="inv-section-title">
                                    <h2>Distribución de RAM</h2>
                                    <span><?= inv_dash_int($hardware['ram_baja'] ?? 0) ?> con menos de 8 GB</span>
                                </div>
                                <?= inv_dash_section_menu() ?>
                            </div>
                            <div class="inv-chart-box inv-chart-box--sm">
                                <canvas id="chartRam"></canvas>
                            </div>
                        </section>
                    </div>
                    <div class="col-12 col-xl-4">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <div class="inv-section-title">
                                    <h2>Sistemas operativos</h2>
                                    <span>Versiones detectadas</span>
                                </div>
                                <?= inv_dash_section_menu() ?>
                            </div>
                            <div class="inv-chart-box inv-chart-box--sm">
                                <canvas id="chartSistemas"></canvas>
                            </div>
                        </section>
                    </div>
                    <div class="col-12 col-xl-4">
                        <section class="inv-dashboard-section h-100">
                            <div class="inv-dashboard-section__head">
                                <h2>Detalle de valor</h2>
                            </div>
                            <div class="inv-detail-value">
                                <span><?= inventario_h($colegioSeleccionado) ?></span>
                                <strong><?= inv_dash_int($totalGeneral['total_equipos']) ?> <?= inventario_h($tipoSeleccionado['plural']) ?></strong>
                                <strong><?= inv_dash_money($valor['valor_total'] ?? 0) ?></strong>
                                <small>Promedio: <?= inv_dash_money($valor['valor_promedio'] ?? 0) ?></small>
                                <small>Sin valor: <?= inv_dash_int($valor['sin_valor'] ?? 0) ?></small>
                            </div>
                        </section>
                    </div>
                </div>

                <section class="inv-dashboard-section">
                    <div class="inv-dashboard-section__head">
                        <h2><?= inventario_h($tipoSeleccionado['plural']) ?> valorizados</h2>
                        <span><?= inventario_h($colegioSeleccionado) ?></span>
                    </div>
                    <div class="table-responsive">
                        <table class="table inv-dashboard-table align-middle">
                            <thead>
                                <tr>
                                    <th>Nombre equipo</th>
                                    <th>Identificador técnico</th>
                                    <th>Serie</th>
                                    <th>Ubicación</th>
                                    <th>Responsable</th>
                                    <th>Estado</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalleValor as $equipo): ?>
                                <?php $nombreVisible = trim((string)($equipo['nombre_personalizado'] ?? '')); ?>
                                <tr>
                                    <td><?= inventario_h($nombreVisible !== '' ? $nombreVisible : ($equipo['nombre_equipo'] ?? 'Sin nombre')) ?></td>
                                    <td><?= inventario_h($equipo['nombre_equipo'] ?? '-') ?></td>
                                    <td><?= inventario_h($equipo['numero_serie'] ?? '-') ?></td>
                                    <td><?= inventario_h(trim((string)($equipo['nombre_ubicacion'] ?? '')) !== '' ? $equipo['nombre_ubicacion'] : 'Pendiente') ?></td>
                                    <td><?= inventario_h(trim((string)($equipo['usuario_asignado'] ?? '')) !== '' ? $equipo['usuario_asignado'] : 'Sin asignar') ?></td>
                                    <td><?= inventario_h($equipo['nombre_estado'] ?? 'Sin estado') ?></td>
                                    <td><?= inv_dash_money($equipo['valor_equipo'] ?? 0) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                    </div><!-- /.col-lg-9 -->
                </div><!-- /.row (sidebar colegio + contenido) -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalAlertaInventario" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content inv-dashboard-mail-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAlertaInventarioLabel">Enviar alerta de inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="invMailAlertBox" class="alert alert-info d-none mb-3"></div>
                <div class="mb-3">
                    <label class="form-label">Responsables del colegio</label>
                    <div class="inv-mail-recipients">
                        <label class="form-check inv-mail-select-all">
                            <input class="form-check-input" type="checkbox" id="invMailSelectAll">
                            <span class="form-check-label">Seleccionar todos</span>
                        </label>
                        <div id="invMailRecipientsList" class="inv-mail-recipients-list">
                            <div class="text-muted small">Cargando responsables...</div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="invMailSubject" class="form-label">Asunto</label>
                    <input type="text" id="invMailSubject" class="form-control">
                </div>
                <div>
                    <label for="invMailMessage" class="form-label">Mensaje</label>
                    <textarea id="invMailMessage" class="form-control" rows="5"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnEnviarAlertaInventario">
                    <span class="spinner-border spinner-border-sm me-2 d-none" id="invMailSpinner" role="status" aria-hidden="true"></span>
                    Enviar correo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
window.inventarioDashboardCharts = {
    estados: <?= json_encode($chartEstados, JSON_UNESCAPED_UNICODE) ?>,
    tipos: <?= json_encode($chartTipos, JSON_UNESCAPED_UNICODE) ?>,
    ram: <?= json_encode($chartRam, JSON_UNESCAPED_UNICODE) ?>,
    sistemas: <?= json_encode($chartSistemas, JSON_UNESCAPED_UNICODE) ?>,
    scatterColegios: <?= json_encode($chartScatterColegios, JSON_UNESCAPED_UNICODE) ?>,
    comparativa: <?= json_encode($comparativaColegio, JSON_UNESCAPED_UNICODE) ?>
};
window.inventarioDashboardConfig = {
    idColegio: <?= $idColegioFiltro ?>,
    tipoEquipo: <?= json_encode($tipoEquipoFiltro, JSON_UNESCAPED_UNICODE) ?>,
    tipoLabel: <?= json_encode($tipoSeleccionado['plural'], JSON_UNESCAPED_UNICODE) ?>,
    endpointAlertas: 'ajax/enviar_alerta_dashboard.php',
    alertas: <?= json_encode($alertaMeta, JSON_UNESCAPED_UNICODE) ?>,
    // Colores del colegio filtrado, con respaldo cuando no tiene los 4
    // cargados (ver $coloresChartColegio arriba). Usados en dashboard.js
    // para pintar chartEstados y reforzar la serie "colegio" del scatter.
    colores: <?= json_encode($coloresChartColegio, JSON_UNESCAPED_UNICODE) ?>
};
</script>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
