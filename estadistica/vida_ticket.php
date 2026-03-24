<?php
session_start();

require_once __DIR__ . '/../class/conexion.php';
require_once __DIR__ . '/../class/funciones.php';

$db = new MySQL('', '', '');
$db->set_charset('utf8mb4');

$normalizarFecha = static function (?string $fecha, ?string $hora): ?DateTime {
    $fecha = trim((string) $fecha);
    if ($fecha === '' || $fecha === '0000-00-00') {
        return null;
    }

    $hora = trim((string) $hora);
    if ($hora === '') {
        $hora = '00:00:00';
    }

    try {
        return new DateTime($fecha . ' ' . $hora);
    } catch (Throwable $e) {
        return null;
    }
};

$formatearFecha = static function (?DateTime $fecha): string {
    return $fecha ? $fecha->format('d-m-Y H:i') : 'Pendiente';
};

$duracion = static function (?DateTime $inicio, ?DateTime $fin): string {
    if (!$inicio || !$fin) {
        return 'Pendiente';
    }

    $diff = $inicio->diff($fin);
    $partes = [];
    if ($diff->d > 0) {
        $partes[] = $diff->d . ' d';
    }
    if ($diff->h > 0) {
        $partes[] = $diff->h . ' h';
    }
    if ($diff->i > 0 && count($partes) < 2) {
        $partes[] = $diff->i . ' min';
    }

    return empty($partes) ? 'Menos de 1 hora' : implode(' ', $partes);
};

$renderizarDetalleTicket = static function (array $ticket, array $acciones) use ($normalizarFecha, $formatearFecha, $duracion): string {
    $creacion = $normalizarFecha($ticket['fecha_creacion_inicio'] ?? '', $ticket['hora_creacion_inicio'] ?? '');
    $asignacion = $normalizarFecha($ticket['fecha_asignacion_tecnico'] ?? '', $ticket['hora_asignacion_tecnico'] ?? '');
    $comienzo = $normalizarFecha($ticket['fecha_comienzo_ticket'] ?? '', $ticket['hora_comienzo_ticket'] ?? '');
    $termino = $normalizarFecha($ticket['fecha_termino_ticket'] ?? '', $ticket['hora_termino_ticket'] ?? '');
    $cierre = $normalizarFecha($ticket['fecha_cierre_ticket'] ?? '', $ticket['hora_cierre_ticket'] ?? '');

    $etapas = [
        [
            'titulo' => 'Ingreso del ticket',
            'descripcion' => 'Solicitud registrada por el usuario en mesa de ayuda.',
            'fecha' => $creacion,
            'duracion' => $duracion($creacion, $asignacion),
            'estado' => $creacion ? 'is-done' : 'is-pending',
            'icono' => 'bi bi-inbox',
        ],
        [
            'titulo' => 'Asignacion tecnica',
            'descripcion' => 'El ticket fue derivado a un responsable tecnico.',
            'fecha' => $asignacion,
            'duracion' => $duracion($asignacion, $comienzo),
            'estado' => $asignacion ? 'is-done' : 'is-pending',
            'icono' => 'bi bi-person-badge',
        ],
        [
            'titulo' => 'Inicio de atencion',
            'descripcion' => 'Comienzo formal del trabajo sobre el requerimiento.',
            'fecha' => $comienzo,
            'duracion' => $duracion($comienzo, $termino),
            'estado' => $comienzo ? 'is-done' : 'is-pending',
            'icono' => 'bi bi-play-circle',
        ],
        [
            'titulo' => 'Termino tecnico',
            'descripcion' => 'Resolucion tecnica o entrega del trabajo comprometido.',
            'fecha' => $termino,
            'duracion' => $duracion($termino, $cierre),
            'estado' => $termino ? 'is-done' : 'is-pending',
            'icono' => 'bi bi-tools',
        ],
        [
            'titulo' => 'Cierre administrativo',
            'descripcion' => 'Confirmacion final y cierre del ticket.',
            'fecha' => $cierre,
            'duracion' => $cierre ? 'Completado' : 'Pendiente',
            'estado' => $cierre ? 'is-done' : 'is-pending',
            'icono' => 'bi bi-check2-circle',
        ],
    ];

    $resumenEtapas = [
        [
            'titulo' => 'Creado',
            'fecha' => $creacion ? $creacion->format('d/m/Y H:i') : 'Pendiente',
            'detalle' => 'Ingreso',
            'estado' => $creacion ? 'is-done' : 'is-pending',
            'icono' => 'bi bi-1-circle-fill',
        ],
        [
            'titulo' => 'Asignado',
            'fecha' => $asignacion ? $asignacion->format('H:i') : '--:--',
            'detalle' => trim((string) ($ticket['tecnico_nombre'] ?? 'Sin tecnico')),
            'estado' => $asignacion ? 'is-done' : 'is-pending',
            'icono' => 'bi bi-check-circle-fill',
        ],
        [
            'titulo' => 'Inicio',
            'fecha' => $comienzo ? $comienzo->format('H:i') : '--:--',
            'detalle' => $duracion($asignacion, $comienzo),
            'estado' => $comienzo ? 'is-done' : 'is-pending',
            'icono' => 'bi bi-person-workspace',
        ],
        [
            'titulo' => 'En proceso',
            'fecha' => $termino ? $termino->format('H:i') : '--:--',
            'detalle' => $termino ? 'Termino tecnico' : 'Sigue en proceso',
            'estado' => $termino ? 'is-done' : ($comienzo ? 'is-current' : 'is-pending'),
            'icono' => 'bi bi-arrow-right-circle-fill',
        ],
    ];

    ob_start();
    ?>
    <div class="ticket-life-detail">
        <div class="ticket-life-detail__summary">
            <div class="ticket-life-summary-card">
                <span>Estado actual</span>
                <strong><?= htmlspecialchars((string) ($ticket['estado_nombre'] ?? 'Sin estado'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="ticket-life-summary-card">
                <span>Prioridad</span>
                <strong><?= htmlspecialchars((string) ($ticket['prioridad_nombre'] ?? 'Sin prioridad'), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="ticket-life-summary-card">
                <span>Usuario</span>
                <strong><?= htmlspecialchars(trim((string) ($ticket['usuario_nombre'] ?? 'Sin solicitante')), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
            <div class="ticket-life-summary-card">
                <span>Tecnico</span>
                <strong><?= htmlspecialchars(trim((string) ($ticket['tecnico_nombre'] ?? 'Sin tecnico')), ENT_QUOTES, 'UTF-8') ?></strong>
            </div>
        </div>

        <div class="ticket-life-progress">
            <div class="ticket-life-progress__line"></div>
            <div class="ticket-life-progress__grid">
                <?php foreach ($resumenEtapas as $etapaResumen): ?>
                    <article class="ticket-life-progress__step <?= htmlspecialchars($etapaResumen['estado'], ENT_QUOTES, 'UTF-8') ?>">
                        <div class="ticket-life-progress__icon">
                            <i class="<?= htmlspecialchars($etapaResumen['icono'], ENT_QUOTES, 'UTF-8') ?>"></i>
                        </div>
                        <div class="ticket-life-progress__time"><?= htmlspecialchars($etapaResumen['fecha'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="ticket-life-progress__label"><?= htmlspecialchars($etapaResumen['titulo'], ENT_QUOTES, 'UTF-8') ?></div>
                        <div class="ticket-life-progress__detail"><?= htmlspecialchars($etapaResumen['detalle'] !== '' ? $etapaResumen['detalle'] : 'Pendiente', ENT_QUOTES, 'UTF-8') ?></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ticket-life-detail__body">
            <div class="ticket-life-detail__main">
                <div class="ticket-life-description">
                    <h3>Resumen del ticket</h3>
                    <p><?= nl2br(htmlspecialchars(trim((string) ($ticket['descripcion_ticket'] ?? 'Sin descripcion disponible')), ENT_QUOTES, 'UTF-8')) ?></p>
                    <?php if (!empty($ticket['comentario_final'])): ?>
                        <div class="ticket-life-final-note">
                            <strong>Comentario final</strong>
                            <p><?= nl2br(htmlspecialchars(trim((string) $ticket['comentario_final']), ENT_QUOTES, 'UTF-8')) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="ticket-life-timeline">
                    <?php foreach ($etapas as $etapa): ?>
                        <article class="ticket-life-step <?= $etapa['estado'] ?>">
                            <div class="ticket-life-step__marker">
                                <i class="<?= htmlspecialchars($etapa['icono'], ENT_QUOTES, 'UTF-8') ?>"></i>
                            </div>
                            <div class="ticket-life-step__content">
                                <div class="ticket-life-step__top">
                                    <strong><?= htmlspecialchars($etapa['titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars($formatearFecha($etapa['fecha']), ENT_QUOTES, 'UTF-8') ?></span>
                                </div>
                                <p><?= htmlspecialchars($etapa['descripcion'], ENT_QUOTES, 'UTF-8') ?></p>
                                <small>Duracion hacia la siguiente etapa: <?= htmlspecialchars($etapa['duracion'], ENT_QUOTES, 'UTF-8') ?></small>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <aside class="ticket-life-detail__side">
                <div class="ticket-life-side-card">
                    <h3>Ficha general</h3>
                    <ul class="ticket-life-data-list">
                        <li><span>Ticket</span><strong>#<?= (int) $ticket['id_ticket'] ?></strong></li>
                        <li><span>Categoria</span><strong><?= htmlspecialchars((string) ($ticket['nombre_categoria'] ?? 'Sin categoria'), ENT_QUOTES, 'UTF-8') ?></strong></li>
                        <li><span>Ingreso</span><strong><?= htmlspecialchars($formatearFecha($creacion), ENT_QUOTES, 'UTF-8') ?></strong></li>
                        <li><span>Estado</span><strong><?= htmlspecialchars((string) ($ticket['estado_nombre'] ?? 'Sin estado'), ENT_QUOTES, 'UTF-8') ?></strong></li>
                    </ul>
                </div>

                <div class="ticket-life-side-card">
                    <h3>Avances registrados</h3>
                    <?php if (empty($acciones)): ?>
                        <div class="ticket-life-side-empty">No hay avances tecnicos registrados.</div>
                    <?php else: ?>
                        <div class="ticket-life-actions">
                            <?php foreach ($acciones as $accion): ?>
                                <?php $fechaAccion = $normalizarFecha($accion['fecha_avance'] ?? '', $accion['hora_avance'] ?? ''); ?>
                                <article class="ticket-life-action">
                                    <strong><?= htmlspecialchars((string) ($accion['accion'] ?? 'Accion sin detalle'), ENT_QUOTES, 'UTF-8') ?></strong>
                                    <span><?= htmlspecialchars($formatearFecha($fechaAccion), ENT_QUOTES, 'UTF-8') ?></span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </aside>
        </div>
    </div>
    <?php
    return ob_get_clean();
};

$resolverTicket = static function (MySQL $db, int $idTicket): array {
    $ticketQuery = $db->consulta("
        SELECT
            t.id_ticket,
            t.asunto,
            t.descripcion_ticket,
            t.comentario_final,
            et.nombre AS estado_nombre,
            et.color AS estado_color,
            pr.nombre AS prioridad_nombre,
            ct.nombre_categoria,
            CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido_paterno, ''), ' ', COALESCE(u.apellido_materno, '')) AS usuario_nombre,
            CONCAT(COALESCE(tec.nombre, ''), ' ', COALESCE(tec.apellido_paterno, ''), ' ', COALESCE(tec.apellido_materno, '')) AS tecnico_nombre,
            pt.fecha_creacion_inicio,
            pt.hora_creacion_inicio,
            pt.fecha_asignacion_tecnico,
            pt.hora_asignacion_tecnico,
            pt.fecha_comienzo_ticket,
            pt.hora_comienzo_ticket,
            pt.fecha_termino_ticket,
            pt.hora_termino_ticket,
            pt.fecha_cierre_ticket,
            pt.hora_cierre_ticket
        FROM tickets t
        LEFT JOIN estados_ticket et ON et.id = t.id_estado
        LEFT JOIN prioridad pr ON pr.id = t.id_prioridad
        LEFT JOIN categoria_de_ticket ct ON ct.id_categoria = t.id_categoria_ticket
        LEFT JOIN usuarios u ON u.id = t.id_usuario
        LEFT JOIN usuarios tec ON tec.id = t.id_tecnico
        LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
        WHERE t.id_ticket = {$idTicket}
        LIMIT 1
    ");

    if (!$ticketQuery || $db->num_rows($ticketQuery) === 0) {
        return [null, []];
    }

    $ticket = $db->fetch_assoc($ticketQuery);
    $acciones = [];
    $accionesQuery = $db->consulta("
        SELECT accion, fecha_avance, hora_avance
        FROM avance_tecnicos
        WHERE id_ticket = {$idTicket}
        ORDER BY fecha_avance DESC, hora_avance DESC
    ");
    if ($accionesQuery) {
        while ($accion = $db->fetch_assoc($accionesQuery)) {
            $acciones[] = $accion;
        }
    }

    return [$ticket, $acciones];
};

$idTicketAjax = isset($_GET['id_ticket']) ? (int) $_GET['id_ticket'] : 0;
$esSolicitudDetalle = $idTicketAjax > 0;

if ($esSolicitudDetalle) {
    header('Content-Type: text/html; charset=utf-8');
    [$ticketDetalle, $accionesDetalle] = $resolverTicket($db, $idTicketAjax);
    if (!$ticketDetalle) {
        http_response_code(404);
        echo '<div class="ticket-life-error">No se encontro informacion para este ticket.</div>';
        exit;
    }

    echo $renderizarDetalleTicket($ticketDetalle, $accionesDetalle);
    exit;
}

$funciones = new Funciones();
$idUsuarioSession = htmlspecialchars($_SESSION['id'] ?? '');
$idPagActual = '13';
$versionCss = @filemtime(__DIR__ . '/css/estadistica.css') ?: time();
$versionJs = @filemtime(__DIR__ . '/js/estadistica.js') ?: time();
$colegiosVisibles = $funciones->obtenerColegios((int) $idUsuarioSession);

$resultadoTickets = $db->consulta("
    SELECT
        t.id_ticket,
        t.id_estado,
        t.asunto,
        et.nombre AS estado_nombre,
        et.color AS estado_color,
        pr.nombre AS prioridad_nombre,
        ct.nombre_categoria,
        CONCAT(COALESCE(u.nombre, ''), ' ', COALESCE(u.apellido_paterno, '')) AS usuario_nombre,
        CONCAT(COALESCE(tec.nombre, ''), ' ', COALESCE(tec.apellido_paterno, '')) AS tecnico_nombre,
        COALESCE(col.nom_colegio, 'Sin colegio') AS colegio_nombre,
        pt.fecha_creacion_inicio,
        pt.hora_creacion_inicio
    FROM tickets t
    LEFT JOIN estados_ticket et ON et.id = t.id_estado
    LEFT JOIN prioridad pr ON pr.id = t.id_prioridad
    LEFT JOIN categoria_de_ticket ct ON ct.id_categoria = t.id_categoria_ticket
    LEFT JOIN usuarios u ON u.id = t.id_usuario
    LEFT JOIN usuarios tec ON tec.id = t.id_tecnico
    LEFT JOIN (
        SELECT uc.id_usuario, MIN(c.nom_colegio) AS nom_colegio
        FROM usuario_colegio uc
        INNER JOIN colegio c ON c.id_colegio = uc.id_colegio
        WHERE uc.estado = 1 AND c.estado = 1
        GROUP BY uc.id_usuario
    ) col ON col.id_usuario = t.id_usuario
    LEFT JOIN proceso_tickets pt ON pt.id_ticket = t.id_ticket
    ORDER BY pt.fecha_creacion_inicio DESC, pt.hora_creacion_inicio DESC, t.id_ticket DESC
");
$ticketsRecientes = [];
if ($resultadoTickets) {
    while ($ticket = $db->fetch_assoc($resultadoTickets)) {
        $ticketsRecientes[] = $ticket;
    }
}
$categoriasDisponibles = [];
$estadosDisponibles = [];
foreach ($ticketsRecientes as $ticketItem) {
    $categoria = trim((string) ($ticketItem['nombre_categoria'] ?? ''));
    $estado = trim((string) ($ticketItem['estado_nombre'] ?? ''));
    if ($categoria !== '') {
        $categoriasDisponibles[$categoria] = $categoria;
    }
    if ($estado !== '') {
        $estadosDisponibles[$estado] = $estado;
    }
}
ksort($categoriasDisponibles);
ksort($estadosDisponibles);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <base href="../">
    <?php $funciones->header(); ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <link href="css/estilo.css" rel="stylesheet">
    <link href="css/bitacora.css" rel="stylesheet">
    <link href="css/contenedor.css" rel="stylesheet">
    <link href="css/tour.css" rel="stylesheet">
    <link href="css/principal.css" rel="stylesheet">
    <link href="css/ticket_admin.css" rel="stylesheet">
    <link href="estadistica/css/estadistica.css?v=<?php echo $versionCss; ?>" rel="stylesheet">
</head>
<body id="page-top">
    <input type="hidden" id="idUsuario" value="<?php echo htmlspecialchars((string) $idUsuarioSession, ENT_QUOTES, 'UTF-8'); ?>">
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
                    <div class="row mx-1 mx-md-3">
                        <div class="col-12">
                            <div class="card shadow mb-4 px-0 border-0 inv-panel">
                                <div class="card-body p-4 p-lg-5">
                                    <div class="inv-hero mb-4">
                                        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                                            <div>
                                                <span class="inv-kicker">Mesa de ayuda</span>
                                                <h1 class="inv-title mb-2">Vida del ticket</h1>
                                                <p class="inv-subtitle mb-0">
                                                    Explora el listado de tickets y despliega el ciclo completo de cada caso con sus hitos, tiempos y avances registrados.
                                                </p>
                                            </div>
                                            <div class="stats-summary-badge">
                                                <span>Tickets cargados</span>
                                                <strong><?php echo number_format(count($ticketsRecientes)); ?> visibles en pantalla</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <section class="panel-card">
                                        <div class="panel-card__header">
                                            <div>
                                                <span class="panel-card__eyebrow">Listado operacional</span>
                                                <h2 class="panel-card__title">Tickets con detalle expandible</h2>
                                            </div>
                                            <span class="panel-chip"><?php echo count($ticketsRecientes); ?> recientes</span>
                                        </div>

                                        <div class="ticket-admin-filtros ticket-life-filtros px-0 pt-0">
                                            <div class="row g-3">
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold text-muted mb-1">Colegio</label>
                                                    <select id="ticketLifeFilterColegio" class="form-select">
                                                        <option value="">Todos</option>
                                                        <?php foreach ($colegiosVisibles as $colegio): ?>
                                                            <option value="<?php echo htmlspecialchars((string) $colegio['nom_colegio'], ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?php echo htmlspecialchars((string) $colegio['nom_colegio'], ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold text-muted mb-1">Categoria</label>
                                                    <select id="ticketLifeFilterCategoria" class="form-select">
                                                        <option value="">Todas</option>
                                                        <?php foreach ($categoriasDisponibles as $categoria): ?>
                                                            <option value="<?php echo htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?php echo htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold text-muted mb-1">Estado</label>
                                                    <select id="ticketLifeFilterEstado" class="form-select">
                                                        <option value="activos">Activos</option>
                                                        <option value="">Todos</option>
                                                        <?php foreach ($estadosDisponibles as $estado): ?>
                                                            <option value="<?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>">
                                                                <?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-semibold text-muted mb-1">Buscar</label>
                                                    <input type="text" id="ticketLifeSearch" class="form-control" placeholder="Ticket, asunto, solicitante o tecnico...">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="ticket-table-wrap">
                                            <?php if (empty($ticketsRecientes)): ?>
                                                <div class="ticket-life-empty">No hay tickets disponibles para mostrar en este momento.</div>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-hover align-middle ticket-life-table" id="ticketLifeTable">
                                                        <thead class="table-dark">
                                                            <tr>
                                                                <th class="col-id">ID</th>
                                                                <th class="col-fecha-hora">FECHA / HORA</th>
                                                                <th class="col-de">DE</th>
                                                                <th class="col-asunto">ASUNTO</th>
                                                                <th class="col-de">COLEGIO</th>
                                                                <th class="col-fecha-respuesta">ESTADO</th>
                                                                <th class="col-fecha-respuesta">PRIORIDAD</th>
                                                                <th class="col-opciones">OPCIONES</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $contadorFila = 1; ?>
                                                            <?php foreach ($ticketsRecientes as $ticket): ?>
                                                                <?php
                                                                $ticketId = (int) ($ticket['id_ticket'] ?? 0);
                                                                $estadoId = (int) ($ticket['id_estado'] ?? 0);
                                                                $estadoColor = trim((string) ($ticket['estado_color'] ?? '#0f4c81'));
                                                                $usuarioNombre = trim((string) ($ticket['usuario_nombre'] ?? 'Sin solicitante'));
                                                                $tecnicoNombre = trim((string) ($ticket['tecnico_nombre'] ?? 'Sin tecnico'));
                                                                $asuntoTicket = trim((string) ($ticket['asunto'] ?? 'Sin asunto'));
                                                                $categoriaNombre = trim((string) ($ticket['nombre_categoria'] ?? 'Sin categoria'));
                                                                $colegioNombre = trim((string) ($ticket['colegio_nombre'] ?? 'Sin colegio'));
                                                                $estadoNombre = trim((string) ($ticket['estado_nombre'] ?? 'Sin estado'));
                                                                $prioridadNombre = trim((string) ($ticket['prioridad_nombre'] ?? 'Sin prioridad'));
                                                                $fechaCreacionLabel = 'Sin fecha';
                                                                if (!empty($ticket['fecha_creacion_inicio']) && $ticket['fecha_creacion_inicio'] !== '0000-00-00') {
                                                                    $fechaCreacionLabel = date('d-m-Y', strtotime((string) $ticket['fecha_creacion_inicio']));
                                                                    if (!empty($ticket['hora_creacion_inicio'])) {
                                                                        $fechaCreacionLabel .= ' ' . substr((string) $ticket['hora_creacion_inicio'], 0, 5);
                                                                    }
                                                                }
                                                                $buscarTexto = strtolower(implode(' ', [
                                                                    $ticketId,
                                                                    $asuntoTicket,
                                                                    $usuarioNombre,
                                                                    $tecnicoNombre,
                                                                    $categoriaNombre,
                                                                    $colegioNombre,
                                                                    $estadoNombre,
                                                                    $prioridadNombre,
                                                                ]));
                                                                ?>
                                                                <tr class="ticket-life-row fila-ticket-admin-compacta estado-ticket-<?php echo $estadoId; ?>"
                                                                    data-ticket-id="<?php echo $ticketId; ?>"
                                                                    data-ticket-search="<?php echo htmlspecialchars($buscarTexto, ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-colegio="<?php echo htmlspecialchars(strtolower($colegioNombre), ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-categoria="<?php echo htmlspecialchars(strtolower($categoriaNombre), ENT_QUOTES, 'UTF-8'); ?>"
                                                                    data-estado="<?php echo htmlspecialchars(strtolower($estadoNombre), ENT_QUOTES, 'UTF-8'); ?>">
                                                                    <td class="celda-id celda-id-con-indicador">
                                                                        <span class="estado-indicador-dot estado-indicador-dot--inline" style="background-color: <?php echo htmlspecialchars($estadoColor, ENT_QUOTES, 'UTF-8'); ?>;"></span>
                                                                        <span class="celda-id-numero"><?php echo $contadorFila++; ?></span>
                                                                    </td>
                                                                    <td class="celda-fecha-hora">
                                                                        <div class="ticket-fecha-hora">
                                                                            <div class="ticket-fecha-hora__fecha"><?php echo htmlspecialchars(explode(' ', $fechaCreacionLabel)[0] ?? 'Sin fecha', ENT_QUOTES, 'UTF-8'); ?></div>
                                                                            <div class="ticket-fecha-hora__hora"><?php echo htmlspecialchars(explode(' ', $fechaCreacionLabel)[1] ?? '--', ENT_QUOTES, 'UTF-8'); ?></div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="celda-de"><?php echo htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                    <td class="celda-asunto">
                                                                        <div class="ticket-life-table__title"><?php echo htmlspecialchars($asuntoTicket, ENT_QUOTES, 'UTF-8'); ?></div>
                                                                        <div class="ticket-life-table__subtitle">
                                                                            <?php echo htmlspecialchars($tecnicoNombre, ENT_QUOTES, 'UTF-8'); ?> | <?php echo htmlspecialchars($categoriaNombre, ENT_QUOTES, 'UTF-8'); ?>
                                                                        </div>
                                                                    </td>
                                                                    <td class="celda-de"><?php echo htmlspecialchars($colegioNombre, ENT_QUOTES, 'UTF-8'); ?></td>
                                                                    <td class="celda-fecha-respuesta">
                                                                        <div class="ticket-resumen-estado">
                                                                            <span class="ticket-resumen-estado__dot" style="background-color: <?php echo htmlspecialchars($estadoColor, ENT_QUOTES, 'UTF-8'); ?>;"></span>
                                                                            <div class="ticket-resumen-estado__body">
                                                                                <div class="ticket-resumen-estado__titulo"><?php echo htmlspecialchars($estadoNombre, ENT_QUOTES, 'UTF-8'); ?></div>
                                                                                <div class="ticket-resumen-estado__detalle">Estado actual</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="celda-fecha-respuesta">
                                                                        <div class="ticket-resumen-estado">
                                                                            <span class="ticket-resumen-estado__dot" style="background-color: #93c5fd;"></span>
                                                                            <div class="ticket-resumen-estado__body">
                                                                                <div class="ticket-resumen-estado__titulo"><?php echo htmlspecialchars($prioridadNombre, ENT_QUOTES, 'UTF-8'); ?></div>
                                                                                <div class="ticket-resumen-estado__detalle">Prioridad</div>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td class="celda-opciones">
                                                                        <button type="button"
                                                                                class="btn btn-sm btn-primary btn-tecnico-accion ticket-life-row__toggle"
                                                                                data-ticket-id="<?php echo $ticketId; ?>">
                                                                            Ver vida
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div class="ticket-life-empty d-none" id="ticketLifeNoResults">
                                                    No hay tickets que coincidan con los filtros aplicados.
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>window.estadisticaDashboardData = window.estadisticaDashboardData || {};</script>
    <script src="js/funciones.js"></script>
    <script src="js/mensajes.js"></script>
    <script src="js/ticket.js"></script>
    <script src="js/buscadores.js"></script>
    <script src="js/validacionTicket.js"></script>
    <script src="js/sb-admin-2.min.js"></script>
    <script src="estadistica/js/estadistica.js?v=<?php echo $versionJs; ?>"></script>
</body>
</html>
