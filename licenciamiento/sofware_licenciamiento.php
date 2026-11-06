<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $tituloPagina = 'Software y Licencias';
    $colegios = $inventario->obtenerColegios();
    $usuarios = $inventario->obtenerUsuarios();
    $tiposLicencia = $inventario->obtenerTiposLicenciamiento();
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar Software y Licencias: ' . inventario_mensaje_error($e));
}

$idPagActual = '15';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="row mx-1 mx-md-3">
    <div class="col-12">
        <div class="card shadow mb-4 px-0 border-0 inv-panel">
            <div class="card-body p-4 p-lg-5">

                <!-- Encabezado -->
                <div class="inv-hero mb-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="inv-kicker">Módulo Licenciamiento</span>
                            <h1 class="inv-title mb-2">Software y Licencias</h1>
                            <p class="inv-subtitle mb-0">Registro de programas, versiones, cantidad de licencias, forma de pago y datos que almacenan.</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="sitios_web_apps.php" class="module-btn module-btn--secondary">
                                <i class="bi bi-globe"></i>Sitios web y apps
                            </a>
                            <a href="consulta_nueva.php" class="module-btn module-btn--secondary">
                                <i class="bi bi-search"></i>Consulta
                            </a>
                            <a href="dashboard.php" class="module-btn module-btn--secondary">
                                <i class="bi bi-bar-chart-line"></i>Dashboard
                            </a>
                            <a href="descargar_pdf.php?tipo=software_listado" id="btnPdfSoftware" class="module-btn module-btn--secondary" target="_blank">
                                <i class="bi bi-file-earmark-pdf"></i>Descargar PDF
                            </a>
                            <a href="registrar_software.php" class="module-btn module-btn--primary">
                                <i class="bi bi-plus-circle"></i>Agregar software
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Indicadores (se actualizan con los filtros) -->
                <div id="resumenTabInventario" class="mb-2"></div>

                <!-- Listado -->
                <div class="card shadow-sm border-0 inv-panel inv-list-card">
                    <div class="card-body">
                        <div class="inv-card-head">
                            <div>
                                <h5 class="mb-1">Software registrado</h5>
                                <p class="text-muted mb-0 small">Listado de programas activos según filtros.</p>
                            </div>
                        </div>
                        <div class="inv-toolbar row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label" for="filtroSoftwareColegio">Colegio</label>
                                <select id="filtroSoftwareColegio" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($colegios as $colegio): ?>
                                        <option value="<?= (int) $colegio['id_colegio'] ?>"><?= inventario_h($colegio['nom_colegio']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="filtroSoftwareUsuario">Responsable</label>
                                <select id="filtroSoftwareUsuario" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= (int) $usuario['id'] ?>"><?= inventario_h($usuario['nombre_completo']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="filtroSoftwareLicencia">Licenciamiento</label>
                                <select id="filtroSoftwareLicencia" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($tiposLicencia as $tipo): ?>
                                        <option value="<?= inventario_h($tipo) ?>"><?= inventario_h($tipo) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="filtroSoftwareBusqueda">Buscar</label>
                                <input type="text" id="filtroSoftwareBusqueda" class="form-control" placeholder="Nombre, versión, proveedor...">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle" id="tablaSoftware">
                                <thead><tr><th>N°</th><th>Software</th><th>Colegio</th><th>Versión</th><th>Licencias</th><th>Tipo</th><th>Pagado por</th><th>Responsable</th><th>Opciones</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
window.INVENTARIO_CONFIG = {
    pagina: 'software',
    endpoints: {
        listarSoftware: 'ajax/listar_softwares.php',
        listarSitios: 'ajax/listar_sitios_web.php',
        eliminarSoftware: 'eliminar_logico_software.php',
        eliminarSitio: 'eliminar_logico_sitio_web.php'
    }
};
</script>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
