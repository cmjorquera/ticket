<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $tituloPagina = 'Inventario de herramientas';
    $colegios = $inventario->obtenerColegios();
    $usuarios = $inventario->obtenerUsuarios();
    $estados = $inventario->obtenerEstados();
    $categorias = $inventario->obtenerCategorias();
    $filtros = [
        'id_colegio' => (int)($_GET['id_colegio'] ?? 0),
        'id_estado' => (int)($_GET['id_estado'] ?? 0),
        'categoria' => trim((string)($_GET['categoria'] ?? '')),
        'id_usuario_asignado' => (int)($_GET['id_usuario_asignado'] ?? 0),
        'busqueda' => trim((string)($_GET['busqueda'] ?? '')),
    ];
    $resumen = $inventario->obtenerResumen($filtros);
    $instalacion = $inventario->obtenerEstadoInstalacion();
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar la portada del inventario de herramientas: ' . $e->getMessage());
}

$idPagActual = '9';
require __DIR__ . '/componentes/layout_top.php';
?>
<div class="row mx-1 mx-md-3">
    <div class="col-12">
        <div class="card shadow mb-4 px-0 border-0 inv-panel">
            <div class="card-body p-4 p-lg-5">
                <div class="inv-hero mb-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="inv-kicker">Modulo operativo</span>
                            <h1 class="inv-title mb-2">Inventario de Herramientas</h1>
                            <p class="inv-subtitle mb-0">Controla herramientas por colegio, bodega o empresa con seguimiento de stock, prestamos, mantenciones y responsables.</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <button type="button" class="btn btn-outline-dark" id="btnCargaMasivaHerramientas">
                                <i class="bi bi-file-earmark-excel me-1"></i>Carga masiva
                            </button>
                            <a href="registrar_herramienta.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Agregar herramienta
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (!$instalacion['instalado']): ?>
                    <div class="alert alert-warning border mb-4">
                        <strong>Modulo listo para desarrollar, pero faltan tablas.</strong>
                        Ejecuta el SQL sugerido en <code>inventario_herramintas/views/sql_sugerido_inventario_herramientas.sql</code>.<br>
                        Tablas faltantes: <?= inventario_h(implode(', ', $instalacion['faltantes'])) ?>
                    </div>
                <?php endif; ?>

                <div id="contenedorResumen">
                    <?php require __DIR__ . '/componentes/resumen.php'; ?>
                </div>

                <div class="card shadow-sm border-0 inv-panel">
                    <div class="card-body">
                        <div class="row g-3 align-items-end mb-4">
                            <div class="col-md-3">
                                <label class="form-label">Colegio</label>
                                <select id="filtroColegio" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($colegios as $colegio): ?>
                                        <option value="<?= (int)$colegio['id_colegio'] ?>" <?= $filtros['id_colegio'] === (int)$colegio['id_colegio'] ? 'selected' : '' ?>><?= inventario_h($colegio['nom_colegio']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select id="filtroEstado" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($estados as $estado): ?>
                                        <option value="<?= (int)$estado['id_estado'] ?>" <?= $filtros['id_estado'] === (int)$estado['id_estado'] ? 'selected' : '' ?>><?= inventario_h($estado['nombre_estado']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Categoria</label>
                                <select id="filtroCategoria" class="form-select">
                                    <option value="">Todas</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= inventario_h($categoria) ?>" <?= $filtros['categoria'] === $categoria ? 'selected' : '' ?>><?= inventario_h($categoria) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Responsable</label>
                                <select id="filtroUsuario" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= (int)$usuario['id'] ?>" <?= $filtros['id_usuario_asignado'] === (int)$usuario['id'] ? 'selected' : '' ?>><?= inventario_h($usuario['nombre_completo']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Buscar</label>
                                <input type="text" id="filtroBusqueda" class="form-control" placeholder="Nombre, serie, QR..." value="<?= inventario_h($filtros['busqueda']) ?>">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle" id="tablaInventario">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Herramienta</th>
                                        <th>Colegio</th>
                                        <th>Categoria</th>
                                        <th>Serie</th>
                                        <th>Cantidad</th>
                                        <th>Responsable</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
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
</div>

<div class="modal fade" id="modalGaleriaEquipo" tabindex="-1" aria-labelledby="modalGaleriaEquipoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title" id="modalGaleriaEquipoLabel">Imagenes de la herramienta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalGaleriaBody"><div class="text-center py-5 text-muted">Cargando galeria...</div></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCargaMasivaHerramientas" tabindex="-1" aria-labelledby="modalCargaMasivaHerramientasLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="modalCargaMasivaHerramientasLabel">Carga masiva de herramientas</h5>
                    <p class="text-muted mb-0">Sube un archivo Excel, revisa los registros y luego inserta las nuevas herramientas.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formCargaMasivaHerramientas" enctype="multipart/form-data" class="mb-4">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-7">
                            <label class="form-label">Archivo Excel</label>
                            <input type="file" name="archivo_excel" id="archivoExcelHerramientas" class="form-control" accept=".xlsx,.xls,.csv" required>
                        </div>
                        <div class="col-lg-5">
                            <div class="inv-massive-hint h-100">
                                <strong>Columnas recomendadas:</strong>
                                colegio, nombre_herramienta, numero_serie, categoria, marca, modelo, cantidad, stock_minimo, ubicacion, estado, usuario_asignado.
                            </div>
                        </div>
                        <div class="col-12 d-flex flex-wrap gap-2">
                            <button type="submit" class="btn btn-primary" id="btnAnalizarCargaMasiva">
                                <i class="bi bi-search me-1"></i>Previsualizar archivo
                            </button>
                            <button type="button" class="btn btn-success" id="btnGuardarCargaMasiva" disabled>
                                <i class="bi bi-database-add me-1"></i>Cargar herramientas
                            </button>
                            <button type="button" class="btn btn-light border" id="btnLimpiarCargaMasiva">Limpiar</button>
                        </div>
                    </div>
                </form>

                <div id="resumenCargaMasiva" class="mb-3"></div>
                <div id="tablaPreviewCargaMasiva" class="inv-massive-preview">
                    <div class="alert alert-light border mb-0">Aun no se ha analizado ningun archivo.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.INVENTARIO_CONFIG = {
    tipoModulo: 'herramientas',
    endpoints: {
        listar: 'ajax/listar_herramientas.php',
        detalle: 'ajax/obtener_detalle_herramienta.php',
        eliminar: 'eliminar_logico_herramienta.php',
        previewCargaMasiva: 'ajax/preview_carga_masiva_herramientas.php',
        guardarCargaMasiva: 'ajax/guardar_carga_masiva_herramientas.php'
    }
};
</script>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
