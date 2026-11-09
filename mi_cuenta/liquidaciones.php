<?php
/** Mi cuenta · Liquidaciones (Buk o datos de ejemplo, ver class/Rrhh.php). */
require_once __DIR__ . '/_comun.php';

$fuente = Rrhh::fuente();
$persona = $mcCuenta->persona($mcId);
$error = null;
$liquidaciones = [];
try {
    $liquidaciones = Rrhh::liquidaciones($persona);
} catch (Throwable $e) {
    error_log('[mi_cuenta] liquidaciones: ' . $e->getMessage());
    $error = $e instanceof RuntimeException ? $e->getMessage() : 'No fue posible cargar las liquidaciones.';
}

$pagina_titulo = 'Mis liquidaciones';
$pagina_estilos = ['mi_cuenta/' . mc_v('css/mi_cuenta.css')];
$pagina_scripts = [
    'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js',
    'mi_cuenta/' . mc_v('js/mi_cuenta.js'),
    'mi_cuenta/' . mc_v('js/liquidaciones.js'),
];
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="mc" id="mcApp" data-csrf="<?= mc_e($_SESSION['csrf_mi_cuenta']) ?>">
    <?php mc_cabecera($mcUsuario, 'liquidaciones', $fuente); ?>
    <?php mc_aviso_fuente($fuente, 'liquidaciones'); ?>
    <?php if ($error): ?>
        <div class="mc-aviso mc-aviso--error" role="alert"><i class="bi bi-exclamation-triangle"></i><div><?= mc_e($error) ?></div></div>
    <?php endif; ?>

    <?php if ($liquidaciones): ?>
    <div class="mc-barra">
        <label class="mc-select"><i class="bi bi-calendar3"></i>
            <select id="filtroAnio" aria-label="Año"></select>
        </label>
        <div class="mc-barra__der">
            <?php if ($fuente->tipo() === 'buk'): ?>
                <button type="button" class="btn btn-ghost btn-sm" id="btnActualizar"><i class="bi bi-arrow-clockwise"></i> Actualizar desde Buk</button>
            <?php endif; ?>
            <button type="button" class="btn btn-outline btn-sm" id="btnPrivado" aria-pressed="false" title="Útil si compartes pantalla">
                <i class="bi bi-eye-slash"></i> <span>Ocultar montos</span>
            </button>
        </div>
    </div>

    <div class="mc-kpis" id="mcKpis"></div>

    <section class="card mc-card">
        <header class="mc-card__head">
            <div><h2><i class="bi bi-bar-chart-line"></i> Evolución</h2><p>Sueldo líquido (barras) y bruto (línea) de los últimos meses.</p></div>
        </header>
        <div class="mc-grafico"><canvas id="graficoLiq" aria-label="Gráfico de liquidaciones" role="img"></canvas></div>
    </section>

    <section class="card mc-card">
        <header class="mc-card__head">
            <div><h2><i class="bi bi-table"></i> Liquidaciones</h2><p id="tablaSub"></p></div>
        </header>
        <div class="mc-tabla-wrap">
            <table class="mc-tabla">
                <thead>
                    <tr><th>Mes</th><th class="num">Sueldo bruto</th><th class="num">Descuentos</th><th class="num">Sueldo líquido</th><th class="num mc-ocultar-movil">Aporte patronal</th><th class="num mc-ocultar-movil">Días</th><th class="acc">Acciones</th></tr>
                </thead>
                <tbody id="tablaLiq"></tbody>
            </table>
        </div>
    </section>
    <?php elseif (!$error): ?>
        <div class="card mc-vacio-grande"><i class="bi bi-cash-stack"></i><h2>Aún no hay liquidaciones</h2><p>Cuando Recursos Humanos las publique, aparecerán aquí.</p></div>
    <?php endif; ?>

    <!-- Detalle -->
    <dialog class="mc-dialog" id="dlgLiq" aria-labelledby="dlgLiqTitulo">
        <div class="mc-dialog__box">
            <header class="mc-dialog__head">
                <div><h2 id="dlgLiqTitulo">Liquidación</h2><p id="dlgLiqSub"></p></div>
                <button type="button" class="btn-icon" data-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
            </header>
            <div class="mc-dialog__cuerpo" id="dlgLiqCuerpo"></div>
            <footer class="mc-dialog__pie" id="dlgLiqPie"></footer>
        </div>
    </dialog>
</div>
<script>window.MC_LIQ = <?= json_encode([
    'fuente' => $fuente->tipo(),
    'lista' => $liquidaciones,
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
