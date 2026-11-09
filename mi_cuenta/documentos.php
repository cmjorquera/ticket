<?php
/** Mi cuenta · Documentos (Buk o datos de ejemplo, ver class/Rrhh.php). */
require_once __DIR__ . '/_comun.php';

$fuente = Rrhh::fuente();
$persona = $mcCuenta->persona($mcId);
$error = null;
$documentos = [];
try {
    $documentos = Rrhh::documentos($persona);
} catch (Throwable $e) {
    error_log('[mi_cuenta] documentos: ' . $e->getMessage());
    $error = $e instanceof RuntimeException ? $e->getMessage() : 'No fue posible cargar los documentos.';
}

$pagina_titulo = 'Mis documentos';
$pagina_estilos = ['mi_cuenta/' . mc_v('css/mi_cuenta.css')];
$pagina_scripts = ['mi_cuenta/' . mc_v('js/mi_cuenta.js'), 'mi_cuenta/' . mc_v('js/documentos.js')];
require_once dirname(__DIR__) . '/includes/header.php';
?>
<div class="mc" id="mcApp" data-csrf="<?= mc_e($_SESSION['csrf_mi_cuenta']) ?>">
    <?php mc_cabecera($mcUsuario, 'documentos', $fuente); ?>
    <?php mc_aviso_fuente($fuente, 'documentos'); ?>
    <?php if ($error): ?>
        <div class="mc-aviso mc-aviso--error" role="alert"><i class="bi bi-exclamation-triangle"></i><div><?= mc_e($error) ?></div></div>
    <?php endif; ?>

    <?php if ($documentos): ?>
    <div class="mc-carpetas" id="mcCarpetas" role="tablist" aria-label="Carpetas"></div>

    <section class="card mc-card">
        <header class="mc-card__head mc-card__head--barra">
            <div><h2 id="docTitulo"><i class="bi bi-folder2-open"></i> Todos los documentos</h2><p id="docSub"></p></div>
            <div class="mc-buscar">
                <i class="bi bi-search"></i>
                <input type="search" id="buscarDoc" placeholder="Buscar documento…" aria-label="Buscar documento">
            </div>
        </header>
        <ul class="mc-docs" id="listaDocs"></ul>
    </section>
    <?php elseif (!$error): ?>
        <div class="card mc-vacio-grande"><i class="bi bi-folder2"></i><h2>No hay documentos</h2><p>Cuando Recursos Humanos cargue documentos en tu ficha, aparecerán aquí.</p></div>
    <?php endif; ?>
</div>
<script>window.MC_DOCS = <?= json_encode(['fuente' => $fuente->tipo(), 'lista' => array_map(static function (array $d): array {
    unset($d['url'], $d['mime']);   // los enlaces de Buk no salen al navegador
    return $d;
}, $documentos)], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
