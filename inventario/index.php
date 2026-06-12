<?php
require_once __DIR__ . '/componentes/boot.php';

$modoDiagnostico = isset($_GET['diag']) && (string)$_GET['diag'] === '1';

if ($modoDiagnostico) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Diagnostico inventario\n";
    echo "======================\n";
    echo "boot.php cargado correctamente\n";

    try {
        $tituloPagina = 'Inventario de computadores';
        echo "titulo ok\n";

        $colegios = $inventario->obtenerColegios();
        echo "obtenerColegios ok: " . count($colegios) . " registros\n";

        $usuarios = $inventario->obtenerUsuarios();
        echo "obtenerUsuarios ok: " . count($usuarios) . " registros\n";

        $estados = $inventario->obtenerEstados();
        echo "obtenerEstados ok: " . count($estados) . " registros\n";

        $tiposPc = $inventario->obtenerTiposPc();
        echo "obtenerTiposPc ok: " . count($tiposPc) . " registros\n";

        $filtros = [
            'id_colegio' => (int)($_GET['id_colegio'] ?? 0),
            'id_estado' => (int)($_GET['id_estado'] ?? 0),
            'tipo_pc' => trim((string)($_GET['tipo_pc'] ?? '')),
            'id_usuario_asignado' => (int)($_GET['id_usuario_asignado'] ?? 0),
            'busqueda' => trim((string)($_GET['busqueda'] ?? '')),
        ];
        echo "filtros ok\n";

        $resumen = $inventario->obtenerResumen($filtros);
        echo "obtenerResumen ok\n";
        echo "diagnostico completado sin errores\n";
    } catch (Throwable $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
        echo "ARCHIVO: " . $e->getFile() . "\n";
        echo "LINEA: " . $e->getLine() . "\n";
    }

    exit;
}

try {
    $tituloPagina = 'Inventario de computadores';
    $colegios = $inventario->obtenerColegios();
    $usuarios = $inventario->obtenerUsuarios();
    $estados = $inventario->obtenerEstados();
    $tiposPc = $inventario->obtenerTiposPc();
    $filtros = [
        'id_colegio' => (int)($_GET['id_colegio'] ?? 0),
        'id_estado' => (int)($_GET['id_estado'] ?? 0),
        'tipo_pc' => trim((string)($_GET['tipo_pc'] ?? '')),
        'id_usuario_asignado' => (int)($_GET['id_usuario_asignado'] ?? 0),
        'busqueda' => trim((string)($_GET['busqueda'] ?? '')),
    ];
    $resumen = $inventario->obtenerResumen($filtros);
    $coloresColegio = $inventario->obtenerColoresColegio($idUsuarioSession);
} catch (Throwable $e) {
    inventario_responder_error('Error al cargar la portada del inventario: ' . $e->getMessage());
}

// Aplicar colores del colegio al hero si están configurados
$_c1 = preg_match('/^#[0-9a-fA-F]{3,8}$/', $coloresColegio['color_principal'] ?? '') ? $coloresColegio['color_principal'] : '';
$_c2 = preg_match('/^#[0-9a-fA-F]{3,8}$/', $coloresColegio['color_secundario'] ?? '') ? $coloresColegio['color_secundario'] : '';
$_heroBranded = $_c1 !== '';
$_heroStyle   = $_heroBranded ? ' style="background: linear-gradient(135deg, ' . $_c1 . ' 0%, ' . ($_c2 ?: $_c1) . ' 100%)"' : '';

$idPagActual = '9';
require __DIR__ . '/componentes/layout_top.php';
?>
<div class="row mx-1 mx-md-3">
    <div class="col-12">
        <div class="card shadow mb-4 px-0 border-0 inv-panel">
            <div class="card-body p-4 p-lg-5">
                <div class="inv-hero mb-4<?= $_heroBranded ? ' inv-hero--branded' : '' ?>"<?= $_heroStyle ?>>
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                        <div>
                            <span class="inv-kicker">Modulo institucional</span>
                            <h1 class="inv-title mb-2">Gestión de Inventario</h1>
                            <p class="inv-subtitle mb-0">Registra, organiza y da seguimiento al equipamiento tecnológico por colegio desde una sola vista operativa.</p>
                        </div>
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="carga_masiva.php" class="btn btn-outline-primary">
                                <i class="bi bi-cloud-upload me-1"></i>Carga masiva
                            </a>
                            <a href="registrar_equipo.php" class="btn btn-primary">
                                <i class="bi bi-plus-circle me-1"></i>Agregar equipo
                            </a>
                        </div>
                    </div>
                </div>

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
                                        <option value="<?= (int)$colegio['id_colegio'] ?>" <?= $filtros['id_colegio'] === (int)$colegio['id_colegio'] ? 'selected' : '' ?>>
                                            <?= inventario_h($colegio['nom_colegio']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Estado</label>
                                <select id="filtroEstado" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($estados as $estado): ?>
                                        <option value="<?= (int)$estado['id_estado'] ?>" <?= $filtros['id_estado'] === (int)$estado['id_estado'] ? 'selected' : '' ?>>
                                            <?= inventario_h($estado['nombre_estado']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tipo</label>
                                <select id="filtroTipo" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($tiposPc as $tipo): ?>
                                        <option value="<?= inventario_h($tipo) ?>" <?= $filtros['tipo_pc'] === $tipo ? 'selected' : '' ?>><?= inventario_h($tipo) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Usuario asignado</label>
                                <select id="filtroUsuario" class="form-select">
                                    <option value="">Todos</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= (int)$usuario['id'] ?>" <?= $filtros['id_usuario_asignado'] === (int)$usuario['id'] ? 'selected' : '' ?>>
                                            <?= inventario_h($usuario['nombre_completo']) ?>
                                        </option>
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
                                        <th>Equipo</th>
                                        <th>Colegio</th>
                                        <th>Tipo</th>
                                        <th>Serie</th>
                                        <th>Asignado</th>
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
                <h5 class="modal-title" id="modalGaleriaEquipoLabel">Imagenes del equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body" id="modalGaleriaBody">
                <div class="text-center py-5 text-muted">Cargando galeria...</div>
            </div>
        </div>
    </div>
</div>

<script>
window.INVENTARIO_CONFIG = {
    endpoints: {
        listar: 'ajax/listar_equipos.php',
        detalle: 'ajax/obtener_detalle_equipo.php',
        eliminar: 'eliminar_logico_equipo.php'
    }
};
</script>

<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
