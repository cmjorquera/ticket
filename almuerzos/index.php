<?php
/**
 * Almuerzos – interfaz (etapa 1, con datos de ejemplo).
 * Cada persona elige su almuerzo del día entre 09:00 y 12:00:
 * Menú del día, Hipocalórico o Picnic. Administración carga el menú del mes.
 * Todavía no se guarda en base de datos: ver almuerzos/logica/LOGICA.txt.
 */
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../validar_sesion.php';
require_once __DIR__ . '/../helpers/tickets.php';

$idUsuario = (int) ($_SESSION['id'] ?? 0);
$nombreUsuario = trim((string) ($_SESSION['nombre'] ?? 'Usuario'));
$esAdmin = false;
try {
    $esAdmin = es_administrador_global($idUsuario, Conexion::getInstance('sistema_panel_central'));
} catch (Throwable $e) {
    error_log('[almuerzos] perfil: ' . $e->getMessage());
}

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
            <p>Elige tu almuerzo de cada día entre las <strong>09:00</strong> y las <strong>12:00</strong>.</p>
        </div>
        <div class="alm-head__acciones">
            <a href="#almMes" class="btn btn-outline"><i class="bi bi-calendar3"></i> Menú del mes</a>
            <?php if ($esAdmin): ?>
                <button type="button" class="btn btn-primary" id="btnCargarMes"><i class="bi bi-upload"></i> Cargar almuerzos del mes</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="alm-demo" role="note">
        <i class="bi bi-info-circle"></i>
        <span>Vista de prueba: los menús son de ejemplo y tu elección se guarda solo en este navegador.</span>
        <label class="alm-demo__switch"><input type="checkbox" id="chkSimular"> Simular horario abierto</label>
    </div>

    <!-- Estado del horario de pedidos -->
    <div class="alm-estado" id="almEstado" aria-live="polite">
        <span class="alm-estado__dot"></span>
        <div>
            <strong id="almEstadoTitulo">Cargando…</strong>
            <span id="almEstadoDetalle"></span>
        </div>
        <div class="alm-estado__reloj" id="almReloj">--:--</div>
    </div>

    <div class="alm-grid">
        <!-- Almuerzo de hoy -->
        <section class="card alm-hoy">
            <div class="alm-card-head">
                <div>
                    <h2 id="almHoyTitulo">Almuerzo de hoy</h2>
                    <p id="almHoySub">Elige una opción. Puedes cambiarla hasta las 12:00.</p>
                </div>
                <span class="alm-pill" id="almMiPedido">Sin elegir</span>
            </div>
            <div class="alm-opciones" id="almOpciones"></div>
            <div class="alm-hoy__pie">
                <button type="button" class="btn btn-ghost btn-sm" id="btnNoAlmuerzo"><i class="bi bi-x-circle"></i> Hoy no almorzaré</button>
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
            <?php if ($esAdmin): ?>
            <section class="card alm-resumen">
                <div class="alm-card-head"><div><h2>Para cocina · hoy</h2><p>Pedidos recibidos hasta ahora (ejemplo).</p></div></div>
                <ul class="alm-conteo" id="almCocina"></ul>
            </section>
            <?php endif; ?>
        </aside>
    </div>

    <!-- Menú del mes -->
    <section class="card alm-mes" id="almMes">
        <div class="alm-card-head">
            <div>
                <h2>Menú del mes</h2>
                <p>Toca un día para ver el detalle. Tu elección aparece marcada.</p>
            </div>
            <div class="alm-mes__nav">
                <button type="button" class="btn-icon" id="btnMesAnterior" aria-label="Mes anterior"><i class="bi bi-chevron-left"></i></button>
                <strong id="almMesTitulo"></strong>
                <button type="button" class="btn-icon" id="btnMesSiguiente" aria-label="Mes siguiente"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>
        <div class="alm-leyenda">
            <span><i class="alm-ico alm-ico--menu"></i>Menú del día</span>
            <span><i class="alm-ico alm-ico--hipo"></i>Hipocalórico</span>
            <span><i class="alm-ico alm-ico--picnic"></i>Picnic</span>
        </div>
        <div class="alm-cal" id="almCalendario"></div>
    </section>

    <!-- Detalle de un día -->
    <dialog class="alm-dialog" id="dlgDia">
        <form method="dialog" class="alm-dialog__box">
            <div class="alm-dialog__head">
                <h2 id="dlgDiaTitulo">Día</h2>
                <button class="btn-icon" value="cerrar" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
            </div>
            <div id="dlgDiaCuerpo"></div>
        </form>
    </dialog>

    <?php if ($esAdmin): ?>
    <!-- Cargar almuerzos del mes -->
    <dialog class="alm-dialog alm-dialog--lg" id="dlgCargar">
        <div class="alm-dialog__box">
            <div class="alm-dialog__head">
                <h2>Cargar almuerzos del mes</h2>
                <button type="button" class="btn-icon" data-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
            </div>
            <ol class="alm-pasos">
                <li>
                    <strong>Elige el mes</strong>
                    <input type="month" id="cargarMes" class="alm-input">
                </li>
                <li>
                    <strong>Descarga la plantilla</strong>
                    <p>Una fila por día hábil, con los tres menús. Ábrela en Excel y complétala.</p>
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
            <div class="alm-dialog__pie">
                <button type="button" class="btn btn-outline" data-cerrar>Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnPublicar" disabled><i class="bi bi-check2-circle"></i> Publicar menú</button>
            </div>
        </div>
    </dialog>
    <?php endif; ?>

    <div class="alm-toast" id="almToast" role="status" hidden></div>
</div>
<script>window.ALM_CONFIG = <?= json_encode(['usuario' => $idUsuario, 'nombre' => $nombreUsuario, 'admin' => $esAdmin], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?>;</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
