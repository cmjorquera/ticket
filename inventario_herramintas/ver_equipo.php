<?php
require_once __DIR__ . '/componentes/boot.php';

$idEquipo = (int)($_GET['id_equipo'] ?? 0);
$equipo = $inventario->obtenerEquipoCompleto($idEquipo);

if (!$equipo) {
    header('Location: index.php');
    exit;
}

$tituloPagina = 'Ficha tecnica equipo';
require __DIR__ . '/componentes/layout_top.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1"><?= inventario_h($equipo['nombre_equipo']) ?></h1>
        <p class="text-muted mb-0"><?= inventario_h($equipo['nom_colegio']) ?> | <?= inventario_h($equipo['tipo_pc']) ?> | Serie <?= inventario_h($equipo['numero_serie']) ?></p>
    </div>
    <div class="d-flex gap-2">
        <a href="editar_equipo.php?id_equipo=<?= (int)$equipo['id_equipo'] ?>" class="btn btn-outline-primary">Editar</a>
        <a href="index.php" class="btn btn-light border">Volver</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><span class="text-muted d-block small">Colegio</span><strong><?= inventario_h($equipo['nom_colegio']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Fabricante</span><strong><?= inventario_h($equipo['fabricante']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Producto</span><strong><?= inventario_h($equipo['producto']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Estado</span><strong><?= $inventario->renderBadgeEstado($equipo['id_estado'], $equipo['nombre_estado'], $equipo['color_badge']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Usuario asignado</span><strong><?= inventario_h($equipo['usuario_asignado'] ?: 'Sin asignar') ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">Registrado por</span><strong><?= inventario_h($equipo['usuario_registra']) ?></strong></div>
                    <div class="col-md-4"><span class="text-muted d-block small">QR</span><strong><?= inventario_h($equipo['qr_code']) ?></strong></div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <h5 class="mb-3">Ficha tecnica</h5>
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6>Compra</h6>
                        <ul class="list-unstyled inv-list">
                            <li><span>Proveedor</span><strong><?= inventario_h($equipo['compra']['proveedor'] ?? '-') ?></strong></li>
                            <li><span>Factura</span><strong><?= inventario_h($equipo['compra']['numero_factura'] ?? '-') ?></strong></li>
                            <li><span>Fecha compra</span><strong><?= inventario_h($equipo['compra']['fecha_compra'] ?? '-') ?></strong></li>
                            <li><span>Valor</span><strong>$<?= number_format((float)($equipo['compra']['valor_equipo'] ?? 0), 0, ',', '.') ?></strong></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Procesador y almacenamiento</h6>
                        <ul class="list-unstyled inv-list">
                            <li><span>CPU</span><strong><?= inventario_h(($equipo['procesador']['equipo_fabricante'] ?? '') . ' ' . ($equipo['procesador']['equipo_modelo'] ?? '')) ?></strong></li>
                            <li><span>Velocidad</span><strong><?= inventario_h($equipo['procesador']['equipo_velocidad'] ?? '-') ?></strong></li>
                            <li><span>Disco</span><strong><?= inventario_h(($equipo['almacenamiento']['equipo_modelo'] ?? '') . ' ' . ($equipo['almacenamiento']['equipo_capacidad'] ?? '')) ?></strong></li>
                            <li><span>Formato</span><strong><?= inventario_h($equipo['almacenamiento']['equipo_tamano'] ?? '-') ?></strong></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Software</h6>
                        <ul class="list-unstyled inv-list">
                            <li><span>Windows</span><strong><?= inventario_h($equipo['software']['windows'] ?? '-') ?></strong></li>
                            <li><span>Office</span><strong><?= inventario_h($equipo['software']['office'] ?? '-') ?></strong></li>
                            <li><span>Antivirus</span><strong><?= inventario_h($equipo['software']['antivirus'] ?? '-') ?></strong></li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Observacion compra</h6>
                        <p class="mb-0 text-muted"><?= nl2br(inventario_h($equipo['compra']['observacion'] ?? 'Sin observaciones.')) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <h5 class="mb-3">Memorias RAM</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Slot</th><th>Formato</th><th>Tipo</th><th>Tamano</th><th>Frecuencia</th><th>Marca</th></tr></thead>
                        <tbody>
                        <?php if (!empty($equipo['memorias'])): foreach ($equipo['memorias'] as $memoria): ?>
                            <tr>
                                <td><?= inventario_h($memoria['designacion_memoria']) ?></td>
                                <td><?= inventario_h($memoria['formato_memoria']) ?></td>
                                <td><?= inventario_h($memoria['tipo_memoria']) ?></td>
                                <td><?= inventario_h($memoria['tamano_memoria']) ?></td>
                                <td><?= inventario_h($memoria['frecuencia_memoria']) ?></td>
                                <td><?= inventario_h($memoria['marca_memoria']) ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="6" class="text-center text-muted">Sin memorias registradas.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <h5 class="mb-3 mt-4">Monitores</h5>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead><tr><th>Modelo</th><th>Codigo</th><th>Serie</th><th>Tamano</th><th>Resolucion</th></tr></thead>
                        <tbody>
                        <?php if (!empty($equipo['monitores'])): foreach ($equipo['monitores'] as $monitor): ?>
                            <tr>
                                <td><?= inventario_h($monitor['modelo_monitor']) ?></td>
                                <td><?= inventario_h($monitor['codigo_monitor']) ?></td>
                                <td><?= inventario_h($monitor['serie_monitor']) ?></td>
                                <td><?= inventario_h($monitor['tamano_monitor']) ?></td>
                                <td><?= inventario_h($monitor['resolucion_monitor']) ?></td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center text-muted">Sin monitores asociados.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 inv-panel mb-4">
            <div class="card-body">
                <h5 class="mb-3">QR del equipo</h5>
                <div id="qrEquipo" class="d-flex justify-content-center"></div>
            </div>
        </div>

        <div class="card shadow-sm border-0 inv-panel">
            <div class="card-body">
                <h5 class="mb-3">Galeria</h5>
                <div class="inv-gallery" id="galeria">
                    <?php if (!empty($equipo['fotos'])): foreach ($equipo['fotos'] as $foto): ?>
                        <a href="<?= inventario_h(inventario_url(ltrim($foto['ruta_foto'], '/'))) ?>" target="_blank" class="inv-gallery-item">
                            <img src="<?= inventario_h(inventario_url(ltrim($foto['ruta_foto'], '/'))) ?>" alt="Foto equipo">
                        </a>
                    <?php endforeach; else: ?>
                        <div class="alert alert-light border mb-0">Sin fotos cargadas.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
window.INVENTARIO_CONFIG = window.INVENTARIO_CONFIG || {};
window.INVENTARIO_CONFIG.qrText = <?= json_encode($equipo['qr_code']) ?>;
</script>

<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
