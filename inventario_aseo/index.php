<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $tituloPagina = 'Inventario de aseo';
    $colegios = $inventario->obtenerColegios();
    $categorias = $inventario->obtenerCategorias();
    $filtros = [
        'id_colegio' => (int) ($_GET['id_colegio'] ?? 0),
        'categoria' => trim((string) ($_GET['categoria'] ?? '')),
        'activo' => isset($_GET['activo']) ? (string) $_GET['activo'] : '1',
        'busqueda' => trim((string) ($_GET['busqueda'] ?? '')),
    ];
    $resumen = $inventario->obtenerResumen($filtros);
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar la portada del inventario de aseo: ' . inventario_mensaje_error($e));
}

$idPagActual = '9';
require __DIR__ . '/componentes/layout_top.php';
?>
<div class="row mx-1 mx-md-3">
    <div class="col-12">
        <div class="card shadow mb-4 px-0 border-0 inv-panel">
            <div class="card-body p-4 p-lg-5">

                <!-- Encabezado -->
                <div class="inv-hero mb-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="inv-kicker">Módulo de bodega</span>
                            <h1 class="inv-title mb-2">Inventario de Aseo</h1>
                            <p class="inv-subtitle mb-0">Controla productos, ingresos, consumos, stock actual y alertas de reposición para cada colegio.</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="reporte.php" class="module-btn module-btn--secondary">
                                <i class="bi bi-bar-chart-line"></i>Reportes
                            </a>
                            <a href="registrar_producto.php" class="module-btn module-btn--primary">
                                <i class="bi bi-plus-circle"></i>Agregar producto
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Indicadores y stock por colegio -->
                <div id="contenedorResumen">
                    <?php require __DIR__ . '/componentes/resumen.php'; ?>
                </div>

                <!-- Listado -->
                <div class="card shadow-sm border-0 inv-panel inv-list-card">
                    <div class="card-body">
                        <div class="inv-card-head">
                            <div>
                                <h5 class="mb-1">Productos de aseo</h5>
                                <p class="text-muted mb-0 small">Stock calculado desde los ingresos y salidas registrados.</p>
                            </div>
                        </div>
                        <div class="inv-toolbar row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label" for="filtroColegio">Colegio</label>
                                <select id="filtroColegio" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($colegios as $colegio): ?>
                                        <option value="<?= (int) $colegio['id_colegio'] ?>" <?= $filtros['id_colegio'] === (int) $colegio['id_colegio'] ? 'selected' : '' ?>><?= inventario_h($colegio['nom_colegio']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" for="filtroCategoria">Categoría</label>
                                <select id="filtroCategoria" class="form-select">
                                    <option value="">Todas</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= inventario_h($categoria) ?>" <?= $filtros['categoria'] === $categoria ? 'selected' : '' ?>><?= inventario_h($categoria) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label" for="filtroActivo">Estado</label>
                                <select id="filtroActivo" class="form-select">
                                    <option value="1" <?= $filtros['activo'] === '1' ? 'selected' : '' ?>>Activos</option>
                                    <option value="" <?= $filtros['activo'] === '' ? 'selected' : '' ?>>Todos</option>
                                    <option value="0" <?= $filtros['activo'] === '0' ? 'selected' : '' ?>>Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="filtroBusqueda">Buscar</label>
                                <input type="text" id="filtroBusqueda" class="form-control" placeholder="Producto, unidad o descripción..." value="<?= inventario_h($filtros['busqueda']) ?>">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle" id="tablaInventario">
                                <thead>
                                    <tr>
                                        <th>N°</th>
                                        <th>Producto</th>
                                        <th>Colegio</th>
                                        <th>Categoría</th>
                                        <th>Unidad</th>
                                        <th>Stock actual</th>
                                        <th>Mínimo</th>
                                        <th>Último movimiento</th>
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

<script>
window.INVENTARIO_CONFIG = {
    tipoModulo: 'aseo',
    endpoints: {
        listar: 'ajax/listar_productos.php',
        detalle: 'ajax/obtener_detalle_producto.php',
        eliminar: 'eliminar_logico_producto.php'
    }
};
</script>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
