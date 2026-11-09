<?php
/**
 * Inicio común de las páginas de Mi cuenta: sesión, datos de la persona,
 * CSRF y la cabecera con foto + pestañas (mc_cabecera()).
 */
declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');
require_once dirname(__DIR__) . '/validar_sesion.php';
require_once __DIR__ . '/class/MiCuenta.php';
require_once __DIR__ . '/class/Rrhh.php';

$mcId = (int) $_SESSION['id'];
$mcCuenta = new MiCuenta(Conexion::getInstance('sistema_panel_central'));
$mcUsuario = $mcCuenta->usuario($mcId);
if (!$mcUsuario) {
    http_response_code(404);
    exit('Usuario no encontrado.');
}
if (empty($_SESSION['csrf_mi_cuenta'])) {
    $_SESSION['csrf_mi_cuenta'] = bin2hex(random_bytes(32));
}

function mc_e(?string $t): string
{
    return htmlspecialchars((string) $t, ENT_QUOTES, 'UTF-8');
}

function mc_v(string $ruta): string
{
    $f = __DIR__ . '/' . $ruta;
    return $ruta . '?v=' . (is_file($f) ? filemtime($f) : time());
}

function mc_iniciales(array $u): string
{
    $i = mb_substr(trim((string) $u['nombre']), 0, 1) . mb_substr(trim((string) $u['apellido_paterno']), 0, 1);
    return mb_strtoupper($i !== '' ? $i : 'U');
}

/** Cabecera común: foto, nombre, cargo y pestañas. */
function mc_cabecera(array $u, string $seccion, ?FuenteRrhh $fuente = null): void
{
    $tabs = ['perfil' => ['Perfil', 'bi-person'], 'liquidaciones' => ['Liquidaciones', 'bi-cash-stack'], 'documentos' => ['Documentos', 'bi-folder2-open']];
    ?>
    <section class="mc-hero">
        <div class="mc-hero__fondo" aria-hidden="true"></div>
        <div class="mc-hero__cuerpo">
            <div class="mc-avatar" id="mcAvatar" data-iniciales="<?= mc_e(mc_iniciales($u)) ?>">
                <?php if ((int) $u['tiene_foto']): ?>
                    <img src="foto.php?v=<?= time() ?>" alt="Foto de <?= mc_e($u['nombre']) ?>">
                <?php else: ?>
                    <span><?= mc_e(mc_iniciales($u)) ?></span>
                <?php endif; ?>
            </div>
            <div class="mc-hero__texto">
                <h1><?= mc_e($u['nombre_completo']) ?></h1>
                <p><?= mc_e($u['cargo'] ?: 'Sin cargo') ?><?= $u['nombre_area'] ? ' · ' . mc_e($u['nombre_area']) : '' ?></p>
                <ul class="mc-hero__chips">
                    <?php if ($u['email']): ?><li><i class="bi bi-envelope"></i><?= mc_e($u['email']) ?></li><?php endif; ?>
                    <?php if ($u['anexo']): ?><li><i class="bi bi-telephone"></i>Anexo <?= mc_e($u['anexo']) ?></li><?php endif; ?>
                    <?php if ($fuente): ?>
                        <li class="mc-fuente mc-fuente--<?= mc_e($fuente->tipo()) ?>" title="Origen de liquidaciones y documentos">
                            <i class="bi <?= $fuente->tipo() === 'buk' ? 'bi-cloud-check' : ($fuente->tipo() === 'demo' ? 'bi-eye' : 'bi-cloud-slash') ?>"></i>
                            <?= $fuente->tipo() === 'buk' ? 'Conectado a Buk' : ($fuente->tipo() === 'demo' ? 'Datos de ejemplo' : 'Buk sin conectar') ?>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        <nav class="mc-tabs" aria-label="Mi cuenta">
            <?php foreach ($tabs as $k => [$nombre, $icono]): ?>
                <a href="<?= $k ?>.php" class="<?= $k === $seccion ? 'is-activa' : '' ?>" <?= $k === $seccion ? 'aria-current="page"' : '' ?>>
                    <i class="bi <?= $icono ?>"></i> <?= $nombre ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </section>
    <?php
}

/** Aviso cuando los datos son de ejemplo o falta la conexión. */
function mc_aviso_fuente(FuenteRrhh $fuente, string $que): void
{
    if ($fuente->tipo() === 'demo'): ?>
        <div class="mc-aviso mc-aviso--demo" role="note">
            <i class="bi bi-info-circle"></i>
            <div><strong>Vista previa con datos de ejemplo.</strong> Cuando el sistema se conecte a Buk, aquí verás tus <?= mc_e($que) ?> reales. Los montos y archivos de esta página son inventados y no tienen validez.</div>
        </div>
    <?php elseif ($fuente->tipo() === 'ninguna'): ?>
        <div class="mc-aviso" role="note">
            <i class="bi bi-cloud-slash"></i>
            <div><strong>Conexión con Buk pendiente.</strong> Mientras tanto, solicita tus <?= mc_e($que) ?> a Recursos Humanos.</div>
        </div>
    <?php endif;
}
