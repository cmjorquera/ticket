<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $tickets = $estadistica->listadoTickets();
} catch (Throwable $e) {
    error_log('[estadistica] vida_ticket: ' . $e->getMessage());
    est_error('No fue posible cargar los tickets.');
}

$colegios = $categorias = $estados = [];
$abiertos = 0;
foreach ($tickets as $t) {
    $colegios[$t['colegio_nombre']] = true;
    if ($t['nombre_categoria']) { $categorias[$t['nombre_categoria']] = true; }
    if ($t['estado_nombre']) { $estados[$t['estado_nombre']] = true; }
    if (!in_array((int) $t['id_estado'], [5, 6], true)) { $abiertos++; }
}
ksort($colegios); ksort($categorias); ksort($estados);

$tituloPagina = 'Vida del ticket';
$usaDataTables = true;
$scriptsModulo = ['vida_ticket.js'];
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="inv-hero mb-4">
    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
        <div>
            <span class="inv-kicker">Módulo Estadística</span>
            <h1 class="inv-title mb-2">Vida del ticket</h1>
            <p class="inv-subtitle mb-0">Recorre cada ticket desde su ingreso hasta el cierre: etapas, tiempos y actividad registrada.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <?php require __DIR__ . '/componentes/selector_perfil.php'; ?>
            <a href="index.php" class="module-btn module-btn--secondary"><i class="bi bi-graph-up-arrow"></i>Estadística general</a>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4"><div class="inv-stat-card inv-stat-primary"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Tickets visibles</span><strong><?= est_n(count($tickets)) ?></strong><small>Según tu perfil</small></div><div class="inv-stat-icon"><i class="bi bi-ticket-perforated"></i></div></div></div></div>
    <div class="col-12 col-md-4"><div class="inv-stat-card inv-stat-warning"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Abiertos</span><strong><?= est_n($abiertos) ?></strong><small>Sin terminar ni cerrar</small></div><div class="inv-stat-icon"><i class="bi bi-hourglass-split"></i></div></div></div></div>
    <div class="col-12 col-md-4"><div class="inv-stat-card inv-stat-success"><div class="d-flex justify-content-between align-items-start"><div><span class="inv-stat-label">Resueltos</span><strong><?= est_n(count($tickets) - $abiertos) ?></strong><small>Terminados o cerrados</small></div><div class="inv-stat-icon"><i class="bi bi-check2-circle"></i></div></div></div></div>
</div>

<div class="card shadow-sm border-0 inv-list-card">
    <div class="card-body">
        <div class="inv-card-head">
            <div>
                <h5 class="mb-1">Tickets</h5>
                <p class="text-muted mb-0 small">Usa “Ver vida” para desplegar las etapas de cada ticket.</p>
            </div>
        </div>
        <div class="inv-toolbar row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="filtroColegio">Colegio</label>
                <select id="filtroColegio" class="form-select"><option value="">Todos</option>
                    <?php foreach (array_keys($colegios) as $c): ?><option value="<?= est_h($c) ?>"><?= est_h($c) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="filtroCategoria">Categoría</label>
                <select id="filtroCategoria" class="form-select"><option value="">Todas</option>
                    <?php foreach (array_keys($categorias) as $c): ?><option value="<?= est_h($c) ?>"><?= est_h($c) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="filtroEstado">Estado</label>
                <select id="filtroEstado" class="form-select">
                    <option value="__abiertos">Abiertos</option>
                    <option value="">Todos</option>
                    <?php foreach (array_keys($estados) as $c): ?><option value="<?= est_h($c) ?>"><?= est_h($c) ?></option><?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="filtroBusqueda">Buscar</label>
                <input type="text" id="filtroBusqueda" class="form-control" placeholder="N° de ticket, asunto, solicitante o técnico...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle" id="tablaVida">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Ingreso</th>
                        <th>Asunto</th>
                        <th>Solicitante</th>
                        <th>Colegio</th>
                        <th>Estado</th>
                        <th>Prioridad</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($tickets as $t):
                    $ingreso = est_fecha_hora($t['fecha_creacion_inicio'], $t['hora_creacion_inicio']);
                    $abierto = !in_array((int) $t['id_estado'], [5, 6], true); ?>
                    <tr data-id="<?= (int) $t['id_ticket'] ?>"
                        data-colegio="<?= est_h($t['colegio_nombre']) ?>"
                        data-categoria="<?= est_h($t['nombre_categoria'] ?? '') ?>"
                        data-estado="<?= est_h($t['estado_nombre'] ?? '') ?>"
                        data-abierto="<?= $abierto ? '1' : '0' ?>">
                        <td><strong>#<?= (int) $t['id_ticket'] ?></strong></td>
                        <td data-order="<?= $ingreso ? $ingreso->format('YmdHi') : '0' ?>"><?= $ingreso ? $ingreso->format('d-m-Y') : 'Sin fecha' ?><span class="est-sub"><?= $ingreso ? $ingreso->format('H:i') : '' ?></span></td>
                        <td><?= est_h($t['asunto'] ?: 'Sin asunto') ?><span class="est-sub"><?= est_h(($t['tecnico_nombre'] ?: 'Sin técnico') . ' · ' . ($t['nombre_categoria'] ?: 'Sin categoría')) ?></span></td>
                        <td><?= est_h($t['usuario_nombre'] ?: '—') ?></td>
                        <td><?= est_h($t['colegio_nombre']) ?></td>
                        <td><span class="est-dot" style="background:<?= est_h($t['estado_color'] ?: '#e5e7eb') ?>"></span><?= est_h($t['estado_nombre'] ?: 'Sin estado') ?></td>
                        <td><?= est_h($t['prioridad_nombre'] ?: '—') ?></td>
                        <td>
                            <div class="d-flex gap-2">
                                <button type="button" class="module-btn module-btn--primary btnVerVida" data-id="<?= (int) $t['id_ticket'] ?>"><i class="bi bi-diagram-3"></i>Ver vida</button>
                                <a href="vida_ticket_pdf.php?id_ticket=<?= (int) $t['id_ticket'] ?>" class="module-btn module-btn--secondary" target="_blank" rel="noopener" title="PDF"><i class="bi bi-file-earmark-pdf"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
