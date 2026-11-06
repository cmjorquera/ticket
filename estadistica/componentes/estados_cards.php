<?php /** Tarjetas por estado con barra de porcentaje. Requiere $estados. */ ?>
<div class="est-estado-grid mb-4">
    <?php foreach ($estados as $e): ?>
        <div class="est-estado">
            <div class="est-estado__top">
                <div class="est-estado__icon" style="background:<?= est_h($e['color']) ?>"><i class="bi <?= est_h($e['icono']) ?>"></i></div>
                <span class="est-estado__nombre"><?= est_h($e['etiqueta']) ?><?php if ($e['etiqueta'] !== $e['nombre']): ?><span class="est-sub"><?= est_h($e['nombre']) ?></span><?php endif; ?></span>
            </div>
            <div class="est-estado__total"><?= est_n($e['total']) ?></div>
            <div class="est-estado__pct"><?= est_d($e['pct']) ?>% del total</div>
            <div class="est-bar"><span style="width:<?= min(100, (float) $e['pct']) ?>%;background:<?= est_h($e['color']) ?>"></span></div>
        </div>
    <?php endforeach; ?>
</div>
