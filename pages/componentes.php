<?php

require_once __DIR__ . '/../validar_sesion.php';

$pagina_titulo = 'Componentes UI';

require_once __DIR__ . '/../includes/header.php';

?>

<div class="page-header">
    <h2><i class="fa-solid fa-code"></i> Componentes UI</h2>
</div>

<div class="card">
    <div class="card-header">
        <h3>Upload drag & drop</h3>
    </div>
    <div class="card-body">
        <div class="dropzone" id="dropzone">
            <input type="file" id="file-input" multiple hidden>
            <div class="dropzone-icon"><i class="fa-solid fa-cloud-arrow-up"></i></div>
            <strong>Arrastra archivos aquí</strong>
            <p class="text-muted mt-3">o haz clic para seleccionarlos</p>
        </div>
        <div class="file-grid" id="file-preview"></div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3>Skeleton y empty state</h3>
    </div>
    <div class="card-body">
        <div id="demo-empty"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    initDragUpload();
    showEmptyState('demo-empty', 'Sin resultados', 'Este componente aparece cuando una tabla o lista no tiene datos.');
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

