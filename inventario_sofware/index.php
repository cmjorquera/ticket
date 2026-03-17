<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $tituloPagina = 'Inventario de software';
    $colegios = $inventario->obtenerColegios();
    $usuarios = $inventario->obtenerUsuarios();
    $tiposLicenciamiento = $inventario->obtenerTiposLicenciamiento();
    $tiposSitio = $inventario->obtenerTiposSitio();
    $instalacion = $inventario->obtenerEstadoInstalacion();
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar la portada del inventario de software: ' . $e->getMessage());
}

$idPagActual = '9';
require __DIR__ . '/componentes/layout_top.inc';
?>
<div class="row mx-1 mx-md-3">
    <div class="col-12">
        <div class="card shadow mb-4 px-0 border-0 inv-panel">
            <div class="card-body p-4 p-lg-5">
                <div class="inv-hero mb-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <!--<span class="inv-kicker">Modulo operativo</span>-->
                            <h1 class="inv-title mb-2">Inventario de Sofware</h1>
                            <p class="inv-subtitle mb-0">Controla licencias, programas, cuentas relacionadas y registros de sitios web, apps o clientes.</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="consulta_.php" class="btn btn-outline-primary"><i class="bi bi-search me-1"></i>Consulta</a>
                            <a href="dashboard.php" class="btn btn-outline-primary"><i class="bi bi-bar-chart-line me-1"></i>Dashboard</a>
                        </div>
                    </div>
                </div>

                <?php if (!$instalacion['instalado']): ?>
                    <div class="alert alert-warning border mb-4">
                        <strong>Faltan tablas para completar el modulo.</strong>
                        Ejecuta el SQL sugerido en <code>inventario_sofware/views/sql_sugerido_inventario_software.sql</code>.<br>
                        Tablas faltantes: <?= inventario_h(implode(', ', $instalacion['faltantes'])) ?>
                    </div>
                <?php endif; ?>
                <div class="card shadow-sm border-0 inv-panel">
                    <div class="card-body">
                        <ul class="nav nav-pills inv-tabs mb-4" id="inventarioSoftwareTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-software" data-bs-toggle="tab" data-bs-target="#panel-software" type="button" role="tab" aria-controls="panel-software" aria-selected="true">Software y licencias</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-sitios" data-bs-toggle="tab" data-bs-target="#panel-sitios" type="button" role="tab" aria-controls="panel-sitios" aria-selected="false">Sitios web, apps y clientes</button>
                            </li>
                        </ul>

                        <div id="resumenTabInventario" class="mb-4"></div>

                        <div class="tab-content" id="inventarioSoftwareTabsContent">
                            <div class="tab-pane fade show active" id="panel-software" role="tabpanel" aria-labelledby="tab-software" tabindex="0">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                    <div>
                                        <h4 class="mb-1">Software y licencias</h4>
                                        <p class="text-muted mb-0">Registro de nombre, version, cantidad, licenciamiento, pago y datos de almacenamiento.</p>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="descargar_pdf.php?tipo=software_listado" id="btnPdfSoftware" class="btn btn-outline-danger" target="_blank">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>Descargar PDF
                                        </a>
                                        <a href="registrar_software.php" class="btn btn-primary">
                                            <i class="bi bi-plus-circle me-1"></i>Agregar software
                                        </a>
                                    </div>
                                </div>
                                <div class="row g-3 align-items-end mb-4">
                                    <div class="col-md-3"><label class="form-label">Colegio</label><select id="filtroSoftwareColegio" class="form-select"><option value="">Todos</option><?php foreach ($colegios as $colegio): ?><option value="<?= (int)$colegio['id_colegio'] ?>"><?= inventario_h($colegio['nom_colegio']) ?></option><?php endforeach; ?></select></div>
                                    <div class="col-md-3"><label class="form-label">Responsable</label><select id="filtroSoftwareUsuario" class="form-select"><option value="">Todos</option><?php foreach ($usuarios as $usuario): ?><option value="<?= (int)$usuario['id'] ?>"><?= inventario_h($usuario['nombre_completo']) ?></option><?php endforeach; ?></select></div>
                                    <div class="col-md-3"><label class="form-label">Licenciamiento</label><select id="filtroSoftwareLicencia" class="form-select"><option value="">Todos</option><?php foreach ($tiposLicenciamiento as $tipo): ?><option value="<?= inventario_h($tipo) ?>"><?= inventario_h($tipo) ?></option><?php endforeach; ?></select></div>
                                    <div class="col-md-3"><label class="form-label">Buscar</label><input type="text" id="filtroSoftwareBusqueda" class="form-control" placeholder="Nombre, version, proveedor..."></div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle" id="tablaSoftware">
                                        <thead><tr><th>ID</th><th>Software</th><th>Colegio</th><th>Version</th><th>Licencias</th><th>Tipo</th><th>Pagado por</th><th>Responsable</th><th>Acciones</th></tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="panel-sitios" role="tabpanel" aria-labelledby="tab-sitios" tabindex="0">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                    <div>
                                        <h4 class="mb-1">Sitios web, apps y clientes</h4>
                                        <p class="text-muted mb-0">Registro de tipo web, app o cliente, url, proveedor y estado.</p>
                                    </div>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <a href="descargar_pdf.php?tipo=sitios_listado" id="btnPdfSitios" class="btn btn-outline-danger" target="_blank">
                                            <i class="bi bi-file-earmark-pdf me-1"></i>Descargar PDF
                                        </a>
                                        <a href="registrar_sitio_web.php" class="btn btn-outline-dark">
                                            <i class="bi bi-globe me-1"></i>Agregar sitio web
                                        </a>
                                    </div>
                                </div>
                                <div class="row g-3 align-items-end mb-4">
                                    <div class="col-md-3"><label class="form-label">Colegio</label><select id="filtroSitioColegio" class="form-select"><option value="">Todos</option><?php foreach ($colegios as $colegio): ?><option value="<?= (int)$colegio['id_colegio'] ?>"><?= inventario_h($colegio['nom_colegio']) ?></option><?php endforeach; ?></select></div>
                                    <div class="col-md-3"><label class="form-label">Responsable</label><select id="filtroSitioUsuario" class="form-select"><option value="">Todos</option><?php foreach ($usuarios as $usuario): ?><option value="<?= (int)$usuario['id'] ?>"><?= inventario_h($usuario['nombre_completo']) ?></option><?php endforeach; ?></select></div>
                                    <div class="col-md-3"><label class="form-label">Tipo</label><select id="filtroSitioTipo" class="form-select"><option value="">Todos</option><?php foreach ($tiposSitio as $tipo): ?><option value="<?= inventario_h($tipo) ?>"><?= inventario_h($tipo) ?></option><?php endforeach; ?></select></div>
                                    <div class="col-md-3"><label class="form-label">Buscar</label><input type="text" id="filtroSitioBusqueda" class="form-control" placeholder="Nombre, url, hosting..."></div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover align-middle" id="tablaSitios">
                                        <thead><tr><th>ID</th><th>Nombre</th><th>Colegio</th><th>Tipo</th><th>URL</th><th>Estado</th><th>Responsable</th><th>Acciones</th></tr></thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
window.INVENTARIO_CONFIG = {
    endpoints: {
        listarSoftware: 'ajax/listar_softwares.php',
        listarSitios: 'ajax/listar_sitios_web.php',
        eliminarSoftware: 'eliminar_logico_software.php',
        eliminarSitio: 'eliminar_logico_sitio_web.php'
    }
};
</script>
<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>
