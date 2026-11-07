<?php
/**
 * Almuerzos: cada persona elige su almuerzo del día (horario en almuerzo_configuracion)
 * (Menú del día, Hipocalórico o Picnic). El encargado publica el menú de
 * cada mes y descarga los pedidos en PDF. Tablas en almuerzos/sql/.
 */
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../validar_sesion.php';
require_once __DIR__ . '/class/Almuerzos.php';

$idUsuario = (int) ($_SESSION['id'] ?? 0);
$alm = new Almuerzos(Conexion::getInstance('sistema_panel_central'));
$esEncargado = $alm->esEncargado($idUsuario);   // almuerzo_configuracion.id_usuario_encargado
if (empty($_SESSION['csrf_almuerzos'])) {
    $_SESSION['csrf_almuerzos'] = bin2hex(random_bytes(32));
}
$hoyChile = new DateTimeImmutable('today', new DateTimeZone('America/Santiago'));

function alm_v(string $ruta): string
{
    $f = __DIR__ . '/' . $ruta;
    return is_file($f) ? (string) filemtime($f) : (string) time();
}

$pagina_titulo = 'Almuerzos';
$pagina_estilos = ['almuerzos/css/almuerzos.css?v=' . alm_v('css/almuerzos.css')];
$pagina_scripts = ['almuerzos/js/almuerzos.js?v=' . alm_v('js/almuerzos.js')];
require_once __DIR__ . '/../includes/header.php';
?>
<div class="alm" id="almApp">

    <div class="page-header alm-head">
        <div>
            <h1><i class="bi bi-cup-hot" aria-hidden="true"></i> Almuerzos</h1>
            <p>Elige tu almuerzo de cada día entre las <strong><?= htmlspecialchars($alm->horaInicio(), ENT_QUOTES, 'UTF-8') ?></strong> y las <strong><?= htmlspecialchars($alm->horaFin(), ENT_QUOTES, 'UTF-8') ?></strong>.</p>
        </div>
        <div class="alm-head__acciones">
            <button type="button" class="btn btn-outline" id="btnVerMes"><i class="bi bi-calendar3"></i> Menú del mes</button>
            <?php if ($esEncargado): ?>
                <button type="button" class="btn btn-primary" id="btnCargarMes"><i class="bi bi-upload"></i> Cargar almuerzos del mes</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="alm-aviso" id="almAviso" hidden></div>

    <?php if ($esEncargado && Almuerzos::PERMITIR_SIMULAR_HORARIO): ?>
    <div class="alm-demo" role="note">
        <i class="bi bi-tools"></i>
        <span>Solo para pruebas en QA: permite pedir fuera del horario (solo para ti).</span>
        <label class="alm-demo__switch"><input type="checkbox" id="chkSimular" <?= !empty($_SESSION['alm_simular']) ? 'checked' : '' ?>> Simular horario abierto</label>
    </div>
    <?php endif; ?>

    <!-- Estado del horario de pedidos -->
    <div class="alm-estado" id="almEstado" aria-live="polite">
        <span class="alm-estado__dot"></span>
        <div>
            <strong id="almEstadoTitulo">Cargando…</strong>
            <span id="almEstadoDetalle"></span>
        </div>
        <div class="alm-estado__reloj" id="almReloj">--:--</div>
    </div>

    <!-- Planificación semanal (sábado y domingo: almuerzos de lunes a viernes siguientes) -->
    <section class="card alm-semana" id="almSemana" hidden>
        <div class="alm-card-head">
            <div>
                <h2><i class="bi bi-calendar-week" aria-hidden="true"></i> Planifica tu semana</h2>
                <p id="almSemanaSub"></p>
            </div>
            <span class="alm-pill" id="almSemanaProgreso"></span>
        </div>
        <div class="alm-semana__dias" id="almSemanaDias"></div>
    </section>

    <div class="alm-grid">
        <!-- Almuerzo de hoy -->
        <section class="card alm-hoy">
            <div class="alm-card-head">
                <div>
                    <h2 id="almHoyTitulo">Almuerzo de hoy</h2>
                    <p id="almHoySub"></p>
                </div>
                <span class="alm-pill" id="almMiPedido">Sin elegir</span>
            </div>
            <div class="alm-opciones" id="almOpciones"><div class="alm-vacio"><p>Cargando…</p></div></div>
            <div class="alm-hoy__pie">
                <button type="button" class="btn btn-ghost btn-sm" id="btnNoAlmuerzo" data-opcion="no" hidden><i class="bi bi-x-circle"></i> Hoy no almorzaré</button>
                <span class="alm-nota" id="almNota"></span>
            </div>
        </section>

        <!-- Resumen -->
        <aside class="alm-lado">
            <section class="card alm-resumen">
                <div class="alm-card-head"><div><h2>Mis pedidos del mes</h2><p id="almMesNombre"></p></div></div>
                <ul class="alm-conteo" id="almConteo"></ul>
                <div class="alm-proximos">
                    <h3>Próximos días</h3>
                    <ul id="almProximos"></ul>
                </div>
            </section>
            <?php if ($esEncargado): ?>
            <section class="card alm-resumen">
                <div class="alm-card-head"><div><h2>Para cocina · hoy</h2><p>Respuestas de todos los usuarios.</p></div></div>
                <ul class="alm-conteo" id="almCocina"></ul>
                <form class="alm-pdf" action="pdf_pedidos.php" method="get" target="_blank">
                    <label for="pdfFecha">Lista por persona</label>
                    <div>
                        <input type="date" name="fecha" id="pdfFecha" class="alm-input" value="<?= htmlspecialchars($hoyChile->format('Y-m-d'), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</button>
                    </div>
                </form>
            </section>
            <?php endif; ?>
        </aside>
    </div>

    <!-- Menú del mes (modal) -->
    <dialog class="alm-dialog alm-dialog--xl" id="dlgMes" aria-labelledby="almMesTitulo">
        <div class="alm-dialog__box">
            <div class="alm-dialog__head">
                <div class="alm-mes__nav">
                    <button type="button" class="btn-icon" id="btnMesAnterior" aria-label="Mes anterior"><i class="bi bi-chevron-left"></i></button>
                    <h2 id="almMesTitulo">Menú del mes</h2>
                    <button type="button" class="btn-icon" id="btnMesSiguiente" aria-label="Mes siguiente"><i class="bi bi-chevron-right"></i></button>
                </div>
                <div class="alm-mes__acciones">
                    <a href="pdf_menu_mes.php" class="btn btn-primary btn-sm" id="btnPdfMes" target="_blank" rel="noopener"><i class="bi bi-file-earmark-pdf"></i> Descargar PDF</a>
                    <button type="button" class="btn-icon" data-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
                </div>
            </div>
            <div class="alm-dialog__cuerpo">
                <div class="alm-leyenda">
                    <span><i class="alm-ico alm-ico--menu"></i>Menú del día</span>
                    <span><i class="alm-ico alm-ico--hipo"></i>Hipocalórico</span>
                    <span><i class="alm-ico alm-ico--picnic"></i>Picnic</span>
                    <span class="alm-leyenda__info" id="almMesInfo"></span>
                </div>
                <div class="alm-cal" id="almCalendario"></div>
            </div>
        </div>
    </dialog>

    <!-- Detalle de un día -->
    <dialog class="alm-dialog" id="dlgDia">
        <div class="alm-dialog__box">
            <div class="alm-dialog__head">
                <h2 id="dlgDiaTitulo">Día</h2>
                <button type="button" class="btn-icon" data-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="alm-dialog__cuerpo" id="dlgDiaCuerpo"></div>
        </div>
    </dialog>

    <?php if ($esEncargado): ?>
    <!-- Cargar almuerzos del mes -->
    <dialog class="alm-dialog alm-dialog--lg" id="dlgCargar">
        <div class="alm-dialog__box">
            <div class="alm-dialog__head">
                <h2>Cargar almuerzos del mes</h2>
                <button type="button" class="btn-icon" data-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="alm-dialog__cuerpo">
                <ol class="alm-pasos">
                    <li>
                        <strong>Elige el mes</strong>
                        <input type="month" id="cargarMes" class="alm-input">
                        <span class="alm-nota" id="cargarMesEstado"></span>
                    </li>
                    <li>
                        <strong>Descarga la plantilla</strong>
                        <p>Una fila por día hábil, con los tres menús. Separa los platos con “+” (entrada + fondo + postre). En picnic escribe solo el sándwich. Ábrela en Excel y complétala.</p>
                        <button type="button" class="btn btn-outline btn-sm" id="btnPlantilla"><i class="bi bi-download"></i> Plantilla (.csv)</button>
                    </li>
                    <li>
                        <strong>Sube el archivo</strong>
                        <label class="alm-drop" id="almDrop">
                            <input type="file" id="cargarArchivo" accept=".csv,text/csv">
                            <i class="bi bi-cloud-arrow-up"></i>
                            <span>Arrastra el archivo aquí o haz clic para elegirlo (.csv)</span>
                        </label>
                        <button type="button" class="btn btn-ghost btn-sm" id="btnEjemplo"><i class="bi bi-magic"></i> Usar menú de ejemplo</button>
                    </li>
                </ol>
                <div class="alm-preview" id="almPreview" hidden>
                    <div class="alm-preview__head"><strong id="almPreviewTitulo">Vista previa</strong><span id="almPreviewAviso"></span></div>
                    <div class="alm-tabla-wrap">
                        <table class="alm-tabla">
                            <thead><tr><th>Fecha</th><th>Menú del día</th><th>Hipocalórico</th><th>Picnic</th></tr></thead>
                            <tbody id="almPreviewFilas"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="alm-dialog__pie">
                <button type="button" class="btn btn-outline" data-cerrar>Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnPublicar" disabled><i class="bi bi-check2-circle"></i> Publicar menú</button>
            </div>
        </div>
    </dialog>
    <?php endif; ?>

    <div class="alm-toast" id="almToast" role="status" hidden></div>
</div>
<script>window.ALM_CONFIG = <?= json_encode([
    'csrf' => $_SESSION['csrf_almuerzos'],
    'encargado' => $esEncargado,
    'inicio' => $alm->horaInicio(),
    'fin' => $alm->horaFin(),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
