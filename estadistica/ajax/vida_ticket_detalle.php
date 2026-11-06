<?php
/** Detalle expandible de un ticket (HTML). Solo tickets dentro del alcance del usuario. */
require_once dirname(__DIR__) . '/componentes/boot.php';
require_once dirname(__DIR__) . '/componentes/vida_etapas.php';

header('Content-Type: text/html; charset=utf-8');
try {
    $t = $estadistica->detalleTicket((int) ($_GET['id_ticket'] ?? 0));
} catch (Throwable $e) {
    error_log('[estadistica] detalle: ' . $e->getMessage());
    est_error('No fue posible cargar el detalle del ticket.');
}
if (!$t) {
    est_error('No se encontró el ticket o no tienes acceso a él.', 404);
}
$vida = est_etapas_ticket($t);
?>
<div class="est-vida">
    <div class="est-vida__resumen">
        <div class="est-vida__dato"><span>Estado</span><strong><span class="est-dot" style="background:<?= est_h($t['estado_color'] ?: '#e5e7eb') ?>"></span><?= est_h($t['estado_nombre'] ?: 'Sin estado') ?></strong></div>
        <div class="est-vida__dato"><span>Técnico</span><strong><?= est_h($t['tecnico_nombre'] ?: 'Sin técnico') ?></strong></div>
        <div class="est-vida__dato"><span>Solicitante</span><strong><?= est_h($t['usuario_nombre'] ?: 'Sin solicitante') ?></strong></div>
        <div class="est-vida__dato"><span><?= $vida['abierto'] ? 'Tiempo abierto' : 'Tiempo total' ?></span><strong><?= est_h($vida['total']) ?></strong></div>
    </div>
    <div class="est-pasos">
        <?php foreach ($vida['etapas'] as $e): ?>
            <div class="est-paso <?= est_h($e['estado']) ?>">
                <i class="bi <?= est_h($e['icono']) ?>"></i>
                <div class="est-paso__titulo"><?= est_h($e['titulo']) ?></div>
                <div class="est-paso__fecha"><?= est_h(est_formato_fecha($e['fecha'])) ?></div>
                <div class="est-paso__dur"><?= $e['titulo'] === 'Cierre' ? est_h($e['duracion']) : 'Siguiente etapa: ' . est_h($e['duracion']) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="est-vida__cuerpo">
        <div class="est-vida__caja">
            <h6>Descripción</h6>
            <p><?= est_h(trim((string) $t['descripcion_ticket']) ?: 'Sin descripción.') ?></p>
            <?php if (trim((string) $t['comentario_final']) !== ''): ?>
                <h6 class="mt-3">Comentario final</h6>
                <p><?= est_h($t['comentario_final']) ?></p>
            <?php endif; ?>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="vida_ticket_pdf.php?id_ticket=<?= (int) $t['id_ticket'] ?>" class="module-btn module-btn--secondary" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf"></i>PDF</a>
                <a href="../ticket/ticket_detalle.php?id=<?= (int) $t['id_ticket'] ?>" class="module-btn module-btn--secondary"><i class="bi bi-box-arrow-up-right"></i>Abrir ticket</a>
            </div>
        </div>
        <div class="est-vida__caja">
            <h6>Actividad registrada (<?= count($t['actividad']) ?>)</h6>
            <?php if (!$t['actividad']): ?>
                <p class="text-muted">Sin mensajes ni avances registrados.</p>
            <?php else: ?>
                <div class="est-actividad">
                    <?php foreach ($t['actividad'] as $a): ?>
                        <article class="<?= est_h($a['tipo']) ?>">
                            <strong><?= est_h($a['autor']) ?></strong>
                            <small><?= $a['tipo'] === 'avance' ? 'Avance técnico' : 'Mensaje' ?> · <?= est_h(est_formato_fecha(est_fecha_hora(substr($a['fecha'], 0, 10), substr($a['fecha'], 11)), '—')) ?></small>
                            <p><?= est_h($a['texto']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
