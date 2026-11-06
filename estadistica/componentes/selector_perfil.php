<?php
/** Botones "Ver como" (solo si el usuario tiene más de un perfil). */
$perfilesEst = $estadistica->perfiles();
if (count($perfilesEst) > 1): ?>
    <div class="est-perfiles" role="group" aria-label="Ver como">
        <span>Ver como</span>
        <?php foreach ($perfilesEst as $p): ?>
            <a href="?vista_perfil=<?= est_h($p['clave']) ?>" class="est-perfil-btn<?= $p['clave'] === $estadistica->perfil() ? ' active' : '' ?>"><?= est_h($p['nombre']) ?></a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
