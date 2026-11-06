<?php
/**
 * Vida del ticket en PDF. Con mPDF (vendor/) descarga el archivo; si no
 * está instalado, muestra una página lista para imprimir / guardar como PDF.
 */
require_once __DIR__ . '/componentes/boot.php';
require_once __DIR__ . '/componentes/vida_etapas.php';

try {
    $t = $estadistica->detalleTicket((int) ($_GET['id_ticket'] ?? 0));
} catch (Throwable $e) {
    error_log('[estadistica] pdf: ' . $e->getMessage());
    est_error('No fue posible generar el documento.');
}
if (!$t) {
    est_error('No se encontró el ticket o no tienes acceso a él.', 404);
}
$vida = est_etapas_ticket($t);

ob_start(); ?>
<style>
    body { font-family: sans-serif; color: #1f2937; font-size: 11px; }
    h1 { color: #1e3a5f; font-size: 20px; margin: 0 0 4px; }
    h2 { color: #1e3a5f; font-size: 14px; margin: 18px 0 8px; }
    .muted { color: #64748b; }
    .header { border-bottom: 2px solid #d8e4ef; padding-bottom: 10px; margin-bottom: 12px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #d8e4ef; padding: 7px 8px; vertical-align: top; text-align: left; }
    th { background: #eef4fa; color: #1e3a5f; font-size: 10px; text-transform: uppercase; }
    .box { border: 1px solid #d8e4ef; border-radius: 6px; padding: 10px; white-space: pre-line; }
</style>
<div class="header">
    <h1>Vida del ticket #<?= (int) $t['id_ticket'] ?></h1>
    <div class="muted"><?= est_h($t['asunto'] ?: 'Sin asunto') ?> · Generado el <?= date('d-m-Y H:i') ?></div>
</div>
<table>
    <tr><th>Estado</th><th>Prioridad</th><th>Categoría</th><th>Colegio</th></tr>
    <tr><td><?= est_h($t['estado_nombre'] ?: '—') ?></td><td><?= est_h($t['prioridad_nombre'] ?: '—') ?></td><td><?= est_h($t['nombre_categoria'] ?: '—') ?></td><td><?= est_h($t['colegio_nombre']) ?></td></tr>
    <tr><th>Solicitante</th><th>Técnico</th><th>Ingreso</th><th><?= $vida['abierto'] ? 'Tiempo abierto' : 'Tiempo total' ?></th></tr>
    <tr><td><?= est_h($t['usuario_nombre'] ?: '—') ?></td><td><?= est_h($t['tecnico_nombre'] ?: 'Sin técnico') ?></td><td><?= est_h(est_formato_fecha($vida['creacion'], '—')) ?></td><td><?= est_h($vida['total']) ?></td></tr>
</table>
<h2>Descripción</h2>
<div class="box"><?= est_h(trim((string) $t['descripcion_ticket']) ?: 'Sin descripción.') ?></div>
<?php if (trim((string) $t['comentario_final']) !== ''): ?>
    <h2>Comentario final</h2>
    <div class="box"><?= est_h($t['comentario_final']) ?></div>
<?php endif; ?>
<h2>Línea de tiempo</h2>
<table>
    <tr><th>Etapa</th><th>Fecha</th><th>Detalle</th><th>Hasta la siguiente etapa</th></tr>
    <?php foreach ($vida['etapas'] as $e): ?>
        <tr><td><strong><?= est_h($e['titulo']) ?></strong></td><td><?= est_h(est_formato_fecha($e['fecha'])) ?></td><td><?= est_h($e['descripcion']) ?></td><td><?= est_h($e['duracion']) ?></td></tr>
    <?php endforeach; ?>
</table>
<h2>Actividad registrada</h2>
<?php if (!$t['actividad']): ?>
    <div class="box muted">Sin mensajes ni avances registrados.</div>
<?php else: ?>
    <table>
        <tr><th style="width:18%">Fecha</th><th style="width:22%">Autor</th><th>Detalle</th></tr>
        <?php foreach ($t['actividad'] as $a): ?>
            <tr><td><?= est_h(est_formato_fecha(est_fecha_hora(substr($a['fecha'], 0, 10), substr($a['fecha'], 11)), '—')) ?></td><td><?= est_h($a['autor']) ?> <span class="muted">(<?= $a['tipo'] === 'avance' ? 'avance' : 'mensaje' ?>)</span></td><td><?= est_h($a['texto']) ?></td></tr>
        <?php endforeach; ?>
    </table>
<?php endif;
$html = ob_get_clean();
$nombre = 'vida_ticket_' . (int) $t['id_ticket'];

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}
if (class_exists('\\Mpdf\\Mpdf')) {
    $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4', 'margin_left' => 12, 'margin_right' => 12, 'margin_top' => 12, 'margin_bottom' => 12, 'tempDir' => sys_get_temp_dir()]);
    $mpdf->SetTitle('Vida del ticket #' . (int) $t['id_ticket']);
    $mpdf->WriteHTML($html);
    $mpdf->Output($nombre . '.pdf', 'D');
    exit;
}
header('Content-Type: text/html; charset=UTF-8');
?><!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= est_h($nombre) ?></title>
<style>@page{size:A4;margin:12mm}body{margin:24px;max-width:960px}.no-print{margin-bottom:16px;display:flex;gap:8px}.no-print button{font:600 13px sans-serif;padding:8px 14px;border-radius:8px;border:1px solid #d8e4ef;background:#1e4e8c;color:#fff;cursor:pointer}.no-print button.sec{background:#fff;color:#1f2937}@media print{.no-print{display:none}body{margin:0}}</style>
</head><body>
<div class="no-print"><button type="button" onclick="window.print()">Imprimir / Guardar como PDF</button><button type="button" class="sec" onclick="window.close()">Cerrar</button></div>
<?= $html ?>
<script>window.addEventListener('load',function(){setTimeout(function(){window.print();},300);});</script>
</body></html>
