<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $tituloPagina  = 'Sitios Web, Apps y Clientes';
    $colegios      = $inventario->obtenerColegios();
    $usuarios      = $inventario->obtenerUsuarios();
    $tiposSitio    = $inventario->obtenerTiposSitio();
    $instalacion   = $inventario->obtenerEstadoInstalacion();
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar Sitios Web: ' . $e->getMessage());
}

$idPagActual = '15';
require __DIR__ . '/componentes/layout_top.inc';
?>

<div class="row mx-1 mx-md-3">
    <div class="col-12">
        <div class="card-body">

            <!-- Cabecera -->
            <div class="inv-hero mb-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div>
                        <h1 class="inv-title mb-1">Sitios Web, Apps y Clientes</h1>
                        <p class="inv-subtitle mb-0">
                            Registro de tipo web, app o cliente, URL, proveedor y estado de cada servicio.
                        </p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap align-items-center">
                        <!-- Navegación entre las dos páginas -->
                        <a href="sofware_licenciamiento.php" class="btn btn-outline-secondary btn-icon-split inv-main-cta">
                            <span class="icon"><i class="bi bi-box-seam"></i></span>
                            <span class="text">Software</span>
                        </a>
                        <a href="consulta_nueva.php" class="btn btn-primary btn-icon-split inv-main-cta">
                            <span class="icon"><i class="bi bi-search"></i></span>
                            <span class="text">Consulta</span>
                        </a>
                        <a href="dashboard.php" class="btn btn-secondary btn-icon-split inv-main-cta">
                            <span class="icon"><i class="bi bi-bar-chart-line"></i></span>
                            <span class="text">Dashboard</span>
                        </a>
                    </div>
                </div>
            </div>

            <?php if (!$instalacion['instalado']): ?>
            <div class="alert alert-warning border mb-4">
                <strong>Faltan tablas para completar el módulo.</strong>
                Ejecuta el SQL en <code>licenciamiento/views/sql_sugerido_inventario_software.sql</code>.<br>
                Tablas faltantes: <?= inventario_h(implode(', ', $instalacion['faltantes'])) ?>
            </div>
            <?php endif; ?>

            <!-- Resumen dinámico -->
            <div id="resumenTabInventario" class="mb-4"></div>

            <div class="card shadow mb-4 px-0">
                <div class="card-body">

                    <!-- Acciones -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                        <div>
                            <h4 class="mb-1">Sitios web, apps y clientes</h4>
                            <p class="text-muted mb-0">
                                Gestiona cada sitio, su URL, proveedor de hosting y estado actual.
                            </p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="descargar_pdf.php?tipo=sitios_listado"
                               id="btnPdfSitios"
                               class="btn btn-danger btn-icon-split inv-main-cta"
                               target="_blank">
                                <span class="icon"><i class="bi bi-file-earmark-pdf"></i></span>
                                <span class="text">Descargar PDF</span>
                            </a>
                            <a href="registrar_sitio_web.php"
                               class="btn btn-primary btn-icon-split inv-main-cta">
                                <span class="icon"><i class="bi bi-globe"></i></span>
                                <span class="text">Agregar sitio web</span>
                            </a>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="ticket-admin-filtros px-3 pt-3">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label">Colegio</label>
                                <select id="filtroSitioColegio" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($colegios as $colegio): ?>
                                    <option value="<?= (int)$colegio['id_colegio'] ?>">
                                        <?= inventario_h($colegio['nom_colegio']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Responsable</label>
                                <select id="filtroSitioUsuario" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= (int)$usuario['id'] ?>">
                                        <?= inventario_h($usuario['nombre_completo']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Tipo</label>
                                <select id="filtroSitioTipo" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($tiposSitio as $tipo): ?>
                                    <option value="<?= inventario_h($tipo) ?>"><?= inventario_h($tipo) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Buscar</label>
                                <input type="text"
                                       id="filtroSitioBusqueda"
                                       class="form-control"
                                       placeholder="Nombre, URL, hosting...">
                            </div>
                        </div>
                    </div>

                    <!-- Tabla -->
                    <div class="table-responsive mt-3">
                        <table class="table table-bordered table-hover table-striped align-middle"
                               id="tablaSitios">
                            <thead>
                                <tr>
                                    <th class="col-id">ID</th>
                                    <th class="col-asunto">Nombre</th>
                                    <th class="col-de">Colegio</th>
                                    <th class="col-fecha-hora">Tipo</th>
                                    <th class="col-dias-restantes">URL</th>
                                    <th class="col-estado">Estado</th>
                                    <th class="col-tecnico">Responsable</th>
                                    <th class="col-opciones">Opciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
window.INVENTARIO_CONFIG = {
    pagina: 'sitios',
    endpoints: {
        listarSoftware:   'ajax/listar_softwares.php',
        listarSitios:     'ajax/listar_sitios_web.php',
        eliminarSoftware: 'eliminar_logico_software.php',
        eliminarSitio:    'eliminar_logico_sitio_web.php'
    }
};
</script>

<?php require __DIR__ . '/componentes/layout_bottom.inc'; ?>