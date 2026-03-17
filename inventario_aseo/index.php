<?php
require_once __DIR__ . '/componentes/boot.php';

try {
    $tituloPagina = 'Inventario de aseo';
    $colegios = $inventario->obtenerColegios();
    $categorias = $inventario->obtenerCategorias();
    $resumen = $inventario->obtenerResumen([
        'id_colegio' => (int) ($_GET['id_colegio'] ?? 0),
        'categoria' => trim((string) ($_GET['categoria'] ?? '')),
        'activo' => isset($_GET['activo']) ? (string) $_GET['activo'] : '1',
        'busqueda' => trim((string) ($_GET['busqueda'] ?? '')),
    ]);
    $instalacion = $inventario->obtenerEstadoInstalacion();
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar la portada del inventario de aseo: ' . $e->getMessage());
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
                            <span class="inv-kicker">Modulo de bodega</span>
                            <h1 class="inv-title mb-2">Inventario de Aseo</h1>
                            <p class="inv-subtitle mb-0">Controla productos, ingresos, consumos, stock actual y alertas de reposicion para cada colegio.</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="registrar_producto.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Agregar producto
                            </a>
                            <a href="reporte.php" class="btn btn-outline-dark">
                                <i class="bi bi-bar-chart-line me-1"></i>Reportes
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (!$instalacion['instalado']): ?>
                    <div class="alert alert-warning border mb-4">
                        <strong>Faltan tablas para dejar operativo el modulo.</strong>
                        Ejecuta el SQL sugerido en <code>inventario_aseo/views/sql_sugerido_inventario_aseo.sql</code>.
                        <br>Tablas faltantes: <?= inventario_h(implode(', ', $instalacion['faltantes'])) ?>
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
                                        <option value="<?= (int) $colegio['id_colegio'] ?>"><?= inventario_h($colegio['nom_colegio']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Categoria</label>
                                <select id="filtroCategoria" class="form-select">
                                    <option value="">Todas</option>
                                    <?php foreach ($categorias as $categoria): ?>
                                        <option value="<?= inventario_h($categoria) ?>"><?= inventario_h($categoria) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select id="filtroActivo" class="form-select">
                                    <option value="1">Activos</option>
                                    <option value="">Todos</option>
                                    <option value="0">Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Buscar</label>
                                <input type="text" id="filtroBusqueda" class="form-control" placeholder="Producto, unidad o descripcion...">
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle" id="tablaInventario">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Producto</th>
                                        <th>Colegio</th>
                                        <th>Categoria</th>
                                        <th>Unidad</th>
                                        <th>Stock actual</th>
                                        <th>Minimo</th>
                                        <th>Ultimo movimiento</th>
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
