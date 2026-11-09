<?php
/** Mi cuenta · Perfil: datos personales, foto, contraseña, colegios y actividad. */
require_once __DIR__ . '/_comun.php';

$colegios = $mcCuenta->colegios($mcId);
$actividad = $mcCuenta->actividad($mcId);
$zona = new DateTimeZone('America/Santiago');
$hoy = new DateTimeImmutable('today', $zona);

// Cumpleaños: días que faltan
$cumple = null;
if ($mcUsuario['fecha_nacimiento']) {
    $n = new DateTimeImmutable($mcUsuario['fecha_nacimiento'], $zona);
    // (un 29 de febrero en año no bisiesto pasa al 1 de marzo)
    $proximo = DateTimeImmutable::createFromFormat('!Y-m-d', $hoy->format('Y') . $n->format('-m-d'), $zona);
    if ($proximo < $hoy) {
        $proximo = $proximo->modify('+1 year');
    }
    $cumple = ['dias' => (int) $hoy->diff($proximo)->days, 'edad' => (int) $n->diff($proximo)->y];
}
$desde = $mcUsuario['fecha_creacion'] ? new DateTimeImmutable($mcUsuario['fecha_creacion']) : null;
$mesesNombre = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];

$pagina_titulo = 'Mi perfil';
$pagina_estilos = ['mi_cuenta/' . mc_v('css/mi_cuenta.css')];
$pagina_scripts = ['mi_cuenta/' . mc_v('js/mi_cuenta.js'), 'mi_cuenta/' . mc_v('js/perfil.js')];
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="mc" id="mcApp" data-csrf="<?= mc_e($_SESSION['csrf_mi_cuenta']) ?>">
    <?php mc_cabecera($mcUsuario, 'perfil'); ?>
    <?php if (!empty($_SESSION['debe_cambiar_clave'])): ?>
        <div class="mc-aviso mc-aviso--demo mc-aviso--clave" role="alert" id="avisoClave">
            <i class="bi bi-key"></i>
            <div><strong>Bienvenido/a. Antes de continuar, cambia tu contraseña.</strong> Ingresaste con una clave temporal: escríbela en "Contraseña actual" y elige una nueva. Después podrás usar todo el sistema.</div>
        </div>
    <?php endif; ?>

    <div class="mc-grid">
        <div class="mc-col">
            <!-- Datos personales -->
            <section class="card mc-card">
                <header class="mc-card__head">
                    <div><h2><i class="bi bi-person-vcard"></i> Datos personales</h2><p>Nombre, correo y cargo los actualiza un administrador.</p></div>
                </header>
                <dl class="mc-fijos">
                    <div><dt>Nombre</dt><dd><?= mc_e($mcUsuario['nombre_completo']) ?></dd></div>
                    <div><dt>Correo</dt><dd><?= mc_e($mcUsuario['email'] ?: '—') ?></dd></div>
                    <div><dt>Cargo</dt><dd><?= mc_e($mcUsuario['cargo'] ?: '—') ?></dd></div>
                    <div><dt>Área</dt><dd><?= mc_e($mcUsuario['nombre_area'] ?: '—') ?></dd></div>
                </dl>
                <form class="mc-form" id="formDatos" novalidate>
                    <div class="mc-campos">
                        <label class="mc-campo">
                            <span>Teléfono</span>
                            <input type="tel" name="telefono" maxlength="15" inputmode="tel" value="<?= mc_e($mcUsuario['telefono']) ?>" placeholder="+56 9 1234 5678">
                        </label>
                        <label class="mc-campo">
                            <span>Anexo</span>
                            <input type="text" name="anexo" maxlength="10" inputmode="numeric" value="<?= mc_e($mcUsuario['anexo']) ?>" placeholder="358">
                        </label>
                        <label class="mc-campo">
                            <span>Fecha de nacimiento</span>
                            <input type="date" name="fecha_nacimiento" value="<?= mc_e($mcUsuario['fecha_nacimiento']) ?>" max="<?= $hoy->modify('-15 years')->format('Y-m-d') ?>">
                        </label>
                    </div>
                    <div class="mc-form__pie">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check2"></i> Guardar datos</button>
                    </div>
                </form>
            </section>

            <!-- Seguridad -->
            <section class="card mc-card" id="seccionClave">
                <header class="mc-card__head">
                    <div><h2><i class="bi bi-shield-lock"></i> Contraseña</h2><p>Úsala solo en este sistema y no la compartas.</p></div>
                </header>
                <form class="mc-form" id="formClave" novalidate autocomplete="off">
                    <input type="text" name="usuario" value="<?= mc_e($mcUsuario['email']) ?>" autocomplete="username" hidden>
                    <div class="mc-campos mc-campos--clave">
                        <label class="mc-campo mc-campo--ancho">
                            <span>Contraseña actual</span>
                            <span class="mc-clave"><input type="password" name="actual" autocomplete="current-password" required><button type="button" class="mc-ojo" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button></span>
                        </label>
                        <label class="mc-campo">
                            <span>Nueva contraseña</span>
                            <span class="mc-clave"><input type="password" name="nueva" id="claveNueva" autocomplete="new-password" required maxlength="72"><button type="button" class="mc-ojo" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button></span>
                        </label>
                        <label class="mc-campo">
                            <span>Repetir nueva contraseña</span>
                            <span class="mc-clave"><input type="password" name="repetir" id="claveRepetir" autocomplete="new-password" required maxlength="72"><button type="button" class="mc-ojo" aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button></span>
                        </label>
                    </div>
                    <div class="mc-fuerza" aria-live="polite">
                        <div class="mc-fuerza__barra"><span id="fuerzaBarra"></span></div>
                        <span id="fuerzaTexto">Escribe una contraseña nueva</span>
                    </div>
                    <ul class="mc-reglas" id="reglasClave">
                        <li data-regla="largo"><i class="bi bi-circle"></i> 8 caracteres o más</li>
                        <li data-regla="letra"><i class="bi bi-circle"></i> Una letra</li>
                        <li data-regla="numero"><i class="bi bi-circle"></i> Un número</li>
                        <li data-regla="iguales"><i class="bi bi-circle"></i> Las dos coinciden</li>
                    </ul>
                    <div class="mc-form__pie">
                        <button type="submit" class="btn btn-primary" id="btnClave" disabled><i class="bi bi-key"></i> Cambiar contraseña</button>
                    </div>
                </form>
            </section>
        </div>

        <aside class="mc-col">
            <!-- Foto -->
            <section class="card mc-card mc-foto">
                <div class="mc-foto__img" id="fotoGrande">
                    <?php if ((int) $mcUsuario['tiene_foto']): ?>
                        <img src="foto.php?v=<?= time() ?>" alt="Tu foto">
                    <?php else: ?>
                        <span><?= mc_e(mc_iniciales($mcUsuario)) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <h2>Foto de perfil</h2>
                    <p>JPG, PNG o WEBP de hasta 3 MB. Se recorta cuadrada.</p>
                    <div class="mc-foto__acciones">
                        <label class="btn btn-outline btn-sm"><i class="bi bi-camera"></i> Subir foto<input type="file" id="inputFoto" accept="image/jpeg,image/png,image/webp" hidden></label>
                        <button type="button" class="btn btn-ghost btn-sm" id="btnQuitarFoto" <?= (int) $mcUsuario['tiene_foto'] ? '' : 'hidden' ?>><i class="bi bi-trash3"></i> Quitar</button>
                    </div>
                </div>
            </section>

            <!-- Actividad -->
            <section class="card mc-card">
                <header class="mc-card__head"><div><h2><i class="bi bi-activity"></i> Mi actividad</h2></div></header>
                <div class="mc-stats">
                    <?php if (isset($actividad['tickets_creados'])): ?>
                        <a class="mc-stat" href="../ticket/"><strong><?= (int) $actividad['tickets_creados'] ?></strong><span>Tickets creados</span></a>
                    <?php endif; ?>
                    <?php if (!empty($actividad['tickets_atendidos'])): ?>
                        <a class="mc-stat" href="../ticket/"><strong><?= (int) $actividad['tickets_atendidos'] ?></strong><span>Tickets atendidos</span></a>
                    <?php endif; ?>
                    <?php if (isset($actividad['almuerzos_mes'])): ?>
                        <a class="mc-stat" href="../almuerzos/"><strong><?= (int) $actividad['almuerzos_mes'] ?></strong><span>Almuerzos este mes</span></a>
                    <?php endif; ?>
                </div>
                <ul class="mc-datos-extra">
                    <?php if ($desde): ?>
                        <li><i class="bi bi-calendar-check"></i> En el sistema desde <?= $mesesNombre[(int) $desde->format('n')] ?> de <?= $desde->format('Y') ?></li>
                    <?php endif; ?>
                    <?php if ($cumple): ?>
                        <li class="<?= $cumple['dias'] === 0 ? 'is-cumple' : '' ?>"><i class="bi bi-cake2"></i>
                            <?= $cumple['dias'] === 0 ? '¡Feliz cumpleaños! 🎉' : ($cumple['dias'] === 1 ? 'Mañana es tu cumpleaños' : 'Faltan ' . $cumple['dias'] . ' días para tu cumpleaños') ?>
                        </li>
                    <?php else: ?>
                        <li class="is-muted"><i class="bi bi-cake2"></i> Agrega tu fecha de nacimiento para recordar tu cumpleaños</li>
                    <?php endif; ?>
                </ul>
            </section>

            <!-- Colegios -->
            <section class="card mc-card">
                <header class="mc-card__head"><div><h2><i class="bi bi-buildings"></i> Mis colegios</h2><p>Dónde tienes acceso y con qué perfil.</p></div></header>
                <?php if ($colegios): ?>
                    <ul class="mc-colegios">
                        <?php foreach ($colegios as $c): ?>
                            <li><span class="mc-colegios__ico"><i class="bi bi-building"></i></span><strong><?= mc_e($c['nom_colegio']) ?></strong><span class="mc-tag"><?= mc_e($c['nombre_perfil'] ?: 'Sin perfil') ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="mc-vacio">No tienes colegios asignados.</p>
                <?php endif; ?>
            </section>

            <!-- Accesos -->
            <nav class="mc-accesos" aria-label="Accesos rápidos">
                <a href="liquidaciones.php"><i class="bi bi-cash-stack"></i><span>Mis liquidaciones</span><i class="bi bi-chevron-right"></i></a>
                <a href="documentos.php"><i class="bi bi-folder2-open"></i><span>Mis documentos</span><i class="bi bi-chevron-right"></i></a>
            </nav>
        </aside>
    </div>
</div>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
