<?php
/**
 * Página de bandeja compartida. Antes de incluirla se define $carpeta:
 * 'recibidos' | 'enviados' | 'archivados'.
 */
require_once __DIR__ . '/boot.php';

$carpetas = [
    'recibidos'  => ['Recibidos', 'Conversaciones en las que te escribieron.', 'index.php', 'bi-inbox'],
    'enviados'   => ['Enviados', 'Conversaciones en las que escribiste.', 'enviados.php', 'bi-send'],
    'archivados' => ['Archivados', 'Conversaciones que guardaste fuera de la bandeja.', 'archivados.php', 'bi-archive'],
];
$carpeta = isset($carpetas[$carpeta ?? '']) ? $carpeta : 'recibidos';
[$tituloCarpeta, $descripcionCarpeta] = $carpetas[$carpeta];

$conversaciones = [];
$resumen = ['no_leidos' => 0, 'recibidos' => 0, 'enviados' => 0, 'archivados' => 0];
$errorCarga = '';
if (!$mensajeriaInstalada) {
    $errorCarga = 'La mensajería todavía no está instalada en esta base de datos. Ejecuta sql/mensajes_instalacion.sql.';
} else {
    try {
        $conversaciones = $mensajeria->listar($carpeta);
        $resumen = $mensajeria->resumen();
    } catch (Throwable $e) {
        error_log('[mensajes] listar: ' . $e->getMessage());
        $errorCarga = 'No fue posible cargar los mensajes. Intenta nuevamente.';
    }
}

$pagina_titulo = 'Mensajes · ' . $tituloCarpeta;
$pagina_bootstrap = true;
$pagina_bootstrap_js = true;
$pagina_sin_componentes = true;
$pagina_estilos = [
    'https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css',
    'mensajes/css/inventario.css?v=' . msg_asset_version('css/inventario.css'),
    'mensajes/css/estilo_sistema.css?v=' . msg_asset_version('css/estilo_sistema.css'),
    'mensajes/css/mensajes.css?v=' . msg_asset_version('css/mensajes.css'),
];
require dirname(__DIR__, 2) . '/includes/header.php';
?>
<div class="inventario-body msg-body">
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="inv-kicker">Mensajes</span>
            <h1 class="inv-title mb-2">Mensajes · <?= msg_h($tituloCarpeta) ?></h1>
            <p class="inv-subtitle mb-0"><?= msg_h($descripcionCarpeta) ?> Archivar o eliminar solo afecta tu bandeja.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="../colaboradores.php" class="module-btn module-btn--secondary"><i class="bi bi-people"></i>Colaboradores</a>
            <button type="button" class="module-btn module-btn--primary" id="btnNuevoMensaje"<?= $mensajeriaInstalada ? '' : ' disabled' ?>><i class="bi bi-pencil-square"></i>Nuevo mensaje</button>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php foreach ([
        ['index.php', 'Sin leer', $resumen['no_leidos'], 'Mensajes pendientes', 'bi-envelope-exclamation', 'danger'],
        ['index.php', 'Recibidos', $resumen['recibidos'], 'Conversaciones', 'bi-inbox', 'primary'],
        ['enviados.php', 'Enviados', $resumen['enviados'], 'Conversaciones', 'bi-send', 'success'],
        ['archivados.php', 'Archivados', $resumen['archivados'], 'Fuera de la bandeja', 'bi-archive', 'secondary'],
    ] as [$href, $label, $valor, $detalle, $icono, $tono]): ?>
        <div class="col-6 col-xl-3">
            <a href="<?= $href ?>" class="msg-stat-link">
                <div class="inv-stat-card inv-stat-<?= $tono ?>"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label"><?= msg_h($label) ?></span><strong><?= number_format((int) $valor, 0, ',', '.') ?></strong><small><?= msg_h($detalle) ?></small></div><div class="inv-stat-icon"><i class="bi <?= $icono ?>"></i></div></div></div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm border-0 inv-list-card">
    <div class="card-body">
        <div class="inv-card-head">
            <div>
                <ul class="msg-tabs">
                    <?php foreach ($carpetas as $clave => [$titulo, , $href, $icono]): ?>
                        <li><a href="<?= $href ?>" class="<?= $clave === $carpeta ? 'active' : '' ?>"><i class="bi <?= $icono ?>"></i><?= msg_h($titulo) ?><?php if ($clave === 'recibidos' && $resumen['no_leidos'] > 0): ?> <span class="msg-badge"><?= (int) $resumen['no_leidos'] ?></span><?php endif; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="inv-toolbar row g-3 align-items-end">
            <div class="col-md-8">
                <label class="form-label" for="filtroBusqueda">Buscar</label>
                <input type="text" id="filtroBusqueda" class="form-control" placeholder="Persona o texto del mensaje...">
            </div>
            <div class="col-md-4">
                <label class="form-label" for="filtroLectura">Mostrar</label>
                <select id="filtroLectura" class="form-select">
                    <option value="">Todas</option>
                    <option value="no_leidas">Solo sin leer</option>
                    <option value="urgentes">Solo urgentes</option>
                </select>
            </div>
        </div>
        <?php if ($errorCarga !== ''): ?>
            <div class="alert alert-warning m-3"><?= msg_h($errorCarga) ?></div>
        <?php endif; ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle" id="tablaMensajes">
                <thead>
                    <tr>
                        <th><?= $carpeta === 'enviados' ? 'Para' : 'Conversación con' ?></th>
                        <th>Último mensaje</th>
                        <th>Fecha</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($conversaciones as $c): ?>
                    <tr class="msg-fila<?= $c['no_leidos'] > 0 ? ' msg-no-leida' : '' ?>" data-id="<?= $c['id'] ?>" data-no-leida="<?= $c['no_leidos'] > 0 ? '1' : '0' ?>" data-urgente="<?= $c['urgente'] ? '1' : '0' ?>">
                        <td>
                            <div class="msg-persona">
                                <span class="msg-avatar"><?= msg_h(mb_strtoupper(mb_substr($c['otro'], 0, 1))) ?></span>
                                <div>
                                    <strong><?= msg_h($c['otro']) ?></strong>
                                    <span class="msg-sub"><?= $c['total'] ?> mensaje<?= $c['total'] === 1 ? '' : 's' ?><?= $c['urgente'] ? ' · <span class="msg-urgente">Urgente</span>' : '' ?></span>
                                </div>
                                <?php if ($c['no_leidos'] > 0): ?><span class="msg-badge" title="Sin leer"><?= $c['no_leidos'] ?></span><?php endif; ?>
                            </div>
                        </td>
                        <td class="msg-extracto"><?= $c['ultimo_mio'] ? '<span class="msg-sub d-inline">Tú: </span>' : '' ?><?= msg_h(mb_strimwidth($c['ultimo'], 0, 140, '…')) ?></td>
                        <td data-order="<?= msg_h($c['fecha']) ?>" class="text-nowrap"><?= msg_h(msg_fecha($c['fecha'])) ?></td>
                        <td class="text-end text-nowrap">
                            <button type="button" class="module-btn module-btn--primary btnAbrir" data-id="<?= $c['id'] ?>"><i class="bi bi-chat-dots"></i>Abrir</button>
                            <?php if ($carpeta === 'archivados'): ?>
                                <button type="button" class="module-btn module-btn--secondary btnAccion" data-accion="desarchivar" data-id="<?= $c['id'] ?>" title="Devolver a la bandeja"><i class="bi bi-inbox"></i></button>
                            <?php else: ?>
                                <button type="button" class="module-btn module-btn--secondary btnAccion" data-accion="archivar" data-id="<?= $c['id'] ?>" title="Archivar"><i class="bi bi-archive"></i></button>
                            <?php endif; ?>
                            <button type="button" class="module-btn module-btn--secondary btnAccion" data-accion="eliminar" data-id="<?= $c['id'] ?>" title="Eliminar"><i class="bi bi-trash3"></i></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Conversación -->
<div class="offcanvas offcanvas-end msg-panel" tabindex="-1" id="panelConversacion" aria-labelledby="panelTitulo">
    <div class="offcanvas-header">
        <div>
            <h5 class="offcanvas-title" id="panelTitulo">Conversación</h5>
            <small class="text-muted" id="panelSub"></small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column p-0">
        <div class="msg-hilo" id="panelHilo"></div>
        <form class="msg-responder" id="formResponder">
            <textarea id="txtRespuesta" class="form-control" rows="2" maxlength="<?= MensajesChat::MAX_MENSAJE ?>" placeholder="Escribe una respuesta… (Ctrl + Enter para enviar)" required></textarea>
            <button type="submit" class="module-btn module-btn--primary" title="Enviar"><i class="bi bi-send"></i></button>
        </form>
    </div>
</div>

<!-- Mensaje nuevo -->
<div class="modal fade" id="modalNuevo" tabindex="-1" aria-labelledby="modalNuevoTitulo" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form class="modal-content" id="formNuevo">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoTitulo">Nuevo mensaje</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <label class="form-label" for="buscarDestino">Para</label>
                <input type="text" id="buscarDestino" class="form-control mb-2" placeholder="Buscar colaborador...">
                <div class="msg-destinos" id="listaDestinos"><div class="text-muted small p-2">Cargando colaboradores…</div></div>
                <div class="small text-muted mt-1" id="contadorDestinos">Ningún destinatario seleccionado.</div>
                <label class="form-label mt-3" for="txtNuevo">Mensaje</label>
                <textarea id="txtNuevo" class="form-control" rows="4" maxlength="<?= MensajesChat::MAX_MENSAJE ?>" required></textarea>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" id="chkUrgente">
                    <label class="form-check-label" for="chkUrgente">Marcar como urgente</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="module-btn module-btn--secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="module-btn module-btn--primary"><i class="bi bi-send"></i>Enviar</button>
            </div>
        </form>
    </div>
</div>
</div><!-- /.msg-body -->
<script>window.MSG_CONFIG = <?= json_encode(['csrf' => $_SESSION['csrf_mensajes'], 'carpeta' => $carpeta, 'max' => MensajesChat::MAX_MENSAJE]) ?>;</script>
<?php
$pagina_scripts = [
    'https://code.jquery.com/jquery-3.7.1.min.js',
    'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js',
    'https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js',
    'https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js',
    'mensajes/js/mensajes.js?v=' . msg_asset_version('js/mensajes.js'),
];
require dirname(__DIR__, 2) . '/includes/footer.php';
