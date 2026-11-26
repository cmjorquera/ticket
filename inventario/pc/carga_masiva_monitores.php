<?php
require_once __DIR__ . '/componentes/boot.php';

$tituloPagina = 'Carga masiva de monitores';
$idPagActual  = '9';

$coloresColegio = $inventario->obtenerColoresColegio($idUsuarioSession);
$_c1 = preg_match('/^#[0-9a-fA-F]{3,8}$/', $coloresColegio['color_principal']  ?? '') ? $coloresColegio['color_principal']  : '';
$_c2 = preg_match('/^#[0-9a-fA-F]{3,8}$/', $coloresColegio['color_secundario'] ?? '') ? $coloresColegio['color_secundario'] : '';
$_heroBranded = $_c1 !== '';
$_heroStyle   = $_heroBranded
    ? ' style="background: linear-gradient(135deg, ' . $_c1 . ' 0%, ' . ($_c2 ?: $_c1) . ' 100%)"'
    : '';

require __DIR__ . '/componentes/layout_top.php';
?>

<div class="row mx-1 mx-md-3">
    <div class="col-12">

        <!-- ── Hero ──────────────────────────────────────────────────────────── -->
        <div class="cm-hero mb-4<?= $_heroBranded ? ' inv-hero--branded' : '' ?>"<?= $_heroStyle ?>>
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <span class="inv-kicker">Importacion masiva</span>
                    <h1 class="inv-title mb-2">Carga masiva de monitores</h1>
                    <p class="inv-subtitle mb-3">
                        Importa múltiples monitores desde un archivo Excel. El colegio se asigna
                        automáticamente según tu usuario — no es necesario ingresarlo en la planilla.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="cm-feature-pill"><i class="bi bi-file-earmark-excel me-1"></i>Formato Excel (.xlsx / .xls)</span>
                        <span class="cm-feature-pill"><i class="bi bi-arrow-repeat me-1"></i>Inserción independiente por fila</span>
                        <span class="cm-feature-pill"><i class="bi bi-list-check me-1"></i>Detalle de errores por fila</span>
                        <span class="cm-feature-pill"><i class="bi bi-building me-1"></i>Colegio asignado por sesión</span>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="index.php?tab=monitores" class="btn btn-light btn-sm px-3">
                        <i class="bi bi-arrow-left me-1"></i>Volver al inventario
                    </a>
                </div>
            </div>
        </div>

        <!-- ── Stepper horizontal ─────────────────────────────────────────── -->
        <div class="cm-stepper mb-4" id="cmStepper">
            <div class="cm-stepper-step active" data-step="1">
                <div class="cm-stepper-circle">1</div>
                <div class="cm-stepper-label">Descargar plantilla</div>
            </div>
            <div class="cm-stepper-line" data-line="1"></div>
            <div class="cm-stepper-step" data-step="2">
                <div class="cm-stepper-circle">2</div>
                <div class="cm-stepper-label">Completar datos</div>
            </div>
            <div class="cm-stepper-line" data-line="2"></div>
            <div class="cm-stepper-step" data-step="3">
                <div class="cm-stepper-circle">3</div>
                <div class="cm-stepper-label">Subir archivo</div>
            </div>
            <div class="cm-stepper-line" data-line="3"></div>
            <div class="cm-stepper-step" data-step="4">
                <div class="cm-stepper-circle">4</div>
                <div class="cm-stepper-label">Revisar resultado</div>
            </div>
        </div>

        <!-- ── Layout dos columnas ───────────────────────────────────────────── -->
        <div class="row g-4 align-items-start">

            <!-- Columna izquierda: instrucciones + resultados -->
            <div class="col-lg-8">

                <!-- Tarjeta 1: Descargar plantilla -->
                <div class="card shadow-sm border-0 inv-panel mb-3">
                    <div class="card-body p-4">
                        <h5 class="mb-1">
                            <span class="cm-step-num-sm">1</span>
                            Descargar plantilla Excel
                        </h5>
                        <p class="text-muted mb-3 small">
                            La plantilla incluye el encabezado de tu colegio, las 17 columnas necesarias
                            y una fila de ejemplo en gris. <strong>Elimina la fila de ejemplo</strong> antes de subir.
                            Usa los desplegables para <strong>Usuario asignado</strong>, <strong>Ubicacion</strong>
                            y <strong>Estado</strong>. El nombre del monitor se genera automaticamente.
                        </p>
                        <a href="exportar_plantilla_monitores.php" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel me-2"></i>Descargar plantilla Excel
                        </a>
                    </div>
                </div>

                <!-- Tarjeta 4: Resultados (oculta hasta procesar) -->
                <div id="cmResultados" class="card shadow-sm border-0 inv-panel d-none mb-3">
                    <div class="card-body p-4">
                        <h5 class="mb-3">
                            <span class="cm-step-num-sm">4</span>
                            Resultado de la importación
                        </h5>

                        <div id="cmResumenResultados" class="mb-3 d-flex flex-wrap gap-2 align-items-center"></div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle border" id="cmTablaResultados">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 90px;">Fila Excel</th>
                                        <th class="text-center" style="width: 100px;">Estado</th>
                                        <th>Mensaje</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div><!-- /col-lg-8 -->

            <!-- Columna derecha: panel de carga -->
            <div class="col-lg-4">
                <div class="cm-upload-panel sticky-top" style="top: 1.5rem;">
                    <div class="cm-upload-panel-header">
                        <i class="bi bi-display me-2"></i>CARGAR MONITORES (.XLSX)
                    </div>
                    <div class="cm-upload-panel-body">
                        <p class="text-muted small mb-3">
                            Completa la plantilla con los datos y sube el archivo aquí.
                            Cada fila se procesa de forma <strong>independiente</strong>.
                        </p>

                        <div id="cmDropZone"
                             class="cm-dropzone mb-3"
                             role="button"
                             tabindex="0"
                             aria-label="Zona de carga — arrastra el archivo Excel aquí o haz clic para seleccionarlo">
                            <i class="bi bi-cloud-upload cm-dropzone-icon"></i>
                            <p class="cm-dropzone-text mb-1">Arrastra el archivo aquí o haz clic</p>
                            <p class="text-muted small mb-0">Formatos: <strong>.xlsx</strong>, <strong>.xls</strong></p>
                        </div>

                        <input type="file" id="cmFileInput" accept=".xlsx,.xls" class="d-none" aria-label="Seleccionar archivo Excel">

                        <div class="mb-3">
                            <span id="cmFileName" class="text-muted small">Ningún archivo seleccionado</span>
                        </div>

                        <button id="btnCargarMasiva" class="btn btn-primary w-100" disabled>
                            <span id="cmSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            <i class="bi bi-upload me-1"></i>Cargar y mostrar
                        </button>
                    </div>
                </div>
            </div><!-- /col-lg-4 -->

        </div><!-- /row -->
    </div>
</div>

<script src="js/carga_masiva_monitores.js?v=<?= inventario_h(inventario_asset_version('js/carga_masiva_monitores.js')) ?>" defer></script>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
