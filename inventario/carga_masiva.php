<?php
require_once __DIR__ . '/componentes/boot.php';

$tituloPagina = 'Carga masiva de equipos';
$idPagActual  = '9';

// Colores del colegio para el hero (igual que index.php)
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

        <!-- ── Hero: colores del colegio o degradado oscuro por defecto ──── -->
        <div class="cm-hero mb-4<?= $_heroBranded ? ' inv-hero--branded' : '' ?>"<?= $_heroStyle ?>>
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <span class="inv-kicker">Importacion masiva</span>
                    <h1 class="inv-title mb-2">Carga masiva de equipos</h1>
                    <p class="inv-subtitle mb-3">
                        Importa múltiples equipos desde un archivo Excel. El colegio se asigna
                        automáticamente según tu usuario — no es necesario ingresarlo en la planilla.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="cm-feature-pill"><i class="bi bi-file-earmark-excel me-1"></i>Formato Excel (.xlsx / .xls)</span>
                        <span class="cm-feature-pill"><i class="bi bi-eye me-1"></i>Previsualizacion antes de insertar</span>
                        <span class="cm-feature-pill"><i class="bi bi-list-check me-1"></i>Detalle de errores por fila</span>
                        <span class="cm-feature-pill"><i class="bi bi-building me-1"></i>Colegio asignado por sesión</span>
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <a href="index.php" class="btn btn-light btn-sm px-3">
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

        <!-- ── Layout dos columnas: contenido | panel de carga ───────────── -->
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
                            La plantilla incluye una sola hoja visible: <strong>Inventario</strong>. Los desplegables
                            usan catalogos internos ocultos y el colegio se determina por tu sesion.
                            <strong>Elimina la fila de ejemplo</strong> antes de subir.
                        </p>
                        <a href="exportar_plantilla.php" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel me-2"></i>Descargar plantilla Excel
                        </a>
                    </div>
                </div>

                <!-- Tarjeta 4: Previsualizacion/resultados (oculta hasta procesar) -->
                <div id="cmResultados" class="card shadow-sm border-0 inv-panel d-none mb-3">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <h5 class="mb-0">
                                <span class="cm-step-num-sm">4</span>
                                <span id="cmTituloResultados">Previsualizacion de equipos</span>
                            </h5>
                        </div>
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-3">
                            <div id="cmResumenResultados" class="d-flex flex-wrap gap-2 align-items-center"></div>
                            <div>
                            <button type="button" id="btnInsertarPc" class="btn btn-success d-none" disabled>
                                <span id="cmSpinnerInsertar" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                <i class="bi bi-check2-circle me-1"></i>Insertar PC
                            </button>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle border" id="cmTablaResultados">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 46px;">
                                            <input type="checkbox" id="cmSeleccionarTodos" class="form-check-input" title="Seleccionar todos los validos">
                                        </th>
                                        <th class="text-center" style="width: 70px;">Fila</th>
                                        <th>Nombre del equipo</th>
                                        <th>Numero de serie</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Ubicacion</th>
                                        <th>Usuario asignado</th>
                                        <th>Fabricante</th>
                                        <th>Producto / modelo</th>
                                        <th>Valor</th>
                                        <th>Errores / advertencias</th>
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
                        <i class="bi bi-file-earmark-arrow-up me-2"></i>CARGAR EQUIPOS (.XLSX)
                    </div>
                    <div class="cm-upload-panel-body">
                        <p class="text-muted small mb-3">
                            Completa la plantilla y sube el archivo. La previsualizacion se ejecutara automaticamente.
                            Nada se inserta hasta que selecciones filas y confirmes.
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

                        <button id="btnCargarMasiva" class="btn btn-primary w-100 d-none" disabled aria-hidden="true" tabindex="-1">
                            <span id="cmSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                            <i class="bi bi-search me-1"></i>Cargar / previsualizar
                        </button>
                    </div>
                </div>
            </div><!-- /col-lg-4 -->

        </div><!-- /row -->
    </div>
</div>

<div class="modal fade" id="modalConfirmarInsertarPc" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">¿Está seguro de insertar estos PC?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div id="cmConfirmarResumen" class="d-flex flex-column gap-2 mb-3">
                    <div class="d-flex justify-content-between gap-3"><span>Total filas seleccionadas</span><strong id="cmConfirmarCantidad">0</strong></div>
                    <div class="d-flex justify-content-between gap-3"><span>Total validas seleccionadas</span><strong id="cmConfirmarValidas">0</strong></div>
                    <div class="d-flex justify-content-between gap-3"><span>Con ubicacion nueva</span><strong id="cmConfirmarUbicacionesNuevas">0</strong></div>
                    <div class="d-flex justify-content-between gap-3 text-danger"><span>Con error</span><strong id="cmConfirmarErrores">0</strong></div>
                </div>
                <p class="text-muted mb-0">Las filas no seleccionadas no serán ingresadas.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnConfirmarInsertarPc">
                    Sí, insertar PC
                </button>
            </div>
        </div>
    </div>
</div>

<script src="js/carga_masiva.js?v=<?= inventario_h(inventario_asset_version('js/carga_masiva.js')) ?>" defer></script>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
