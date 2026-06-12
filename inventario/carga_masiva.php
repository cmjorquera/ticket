<?php
require_once __DIR__ . '/componentes/boot.php';

$tituloPagina = 'Carga masiva de equipos';
$idPagActual  = '9';

require __DIR__ . '/componentes/layout_top.php';
?>

<div class="row mx-1 mx-md-3">
    <div class="col-12">

        <!-- ── Hero con degradado azul → teal ─────────────────────────────── -->
        <div class="cm-hero mb-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <span class="inv-kicker">Importacion masiva</span>
                    <h1 class="inv-title mb-2">Carga masiva de equipos</h1>
                    <p class="inv-subtitle mb-0">
                        Importa múltiples equipos desde un archivo Excel. El colegio se asigna
                        automáticamente según tu usuario — no es necesario ingresarlo en la planilla.
                    </p>
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

        <!-- ── Tarjeta 1: Descargar plantilla ────────────────────────────── -->
        <div class="card shadow-sm border-0 inv-panel mb-3">
            <div class="card-body p-4">
                <h5 class="mb-1">
                    <span class="cm-step-num-sm">1</span>
                    Descargar plantilla Excel
                </h5>
                <p class="text-muted mb-3 small">
                    La plantilla incluye el encabezado de tu colegio, las columnas con nombres legibles
                    y una fila de ejemplo en gris. <strong>Elimina la fila de ejemplo</strong> antes de subir.
                    La hoja <strong>Estados</strong> lista los IDs de estado disponibles.
                </p>
                <a href="exportar_plantilla.php" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel me-2"></i>Descargar plantilla Excel
                </a>
            </div>
        </div>

        <!-- ── Tarjeta 2+3: Subir archivo ─────────────────────────────────── -->
        <div class="card shadow-sm border-0 inv-panel mb-3">
            <div class="card-body p-4">
                <h5 class="mb-1">
                    <span class="cm-step-num-sm">3</span>
                    Subir archivo completado
                </h5>
                <p class="text-muted mb-3 small">
                    Arrastra el archivo o haz clic en la zona de carga. Formatos aceptados:
                    <code>.xlsx</code> o <code>.xls</code>.
                    Cada fila se procesa de forma <strong>independiente</strong>: los errores en una fila
                    no afectan al resto.
                </p>

                <div id="cmDropZone"
                     class="cm-dropzone mb-3"
                     role="button"
                     tabindex="0"
                     aria-label="Zona de carga — arrastra el archivo Excel aquí o haz clic para seleccionarlo">
                    <i class="bi bi-cloud-upload cm-dropzone-icon"></i>
                    <p class="cm-dropzone-text mb-1">Arrastra el archivo aquí o haz clic para seleccionarlo</p>
                    <p class="text-muted small mb-0">Formatos aceptados: <strong>.xlsx</strong>, <strong>.xls</strong></p>
                </div>

                <input type="file" id="cmFileInput" accept=".xlsx,.xls" class="d-none" aria-label="Seleccionar archivo Excel">

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <span id="cmFileName" class="text-muted small">Ningún archivo seleccionado</span>
                    <button id="btnCargarMasiva" class="btn btn-primary" disabled>
                        <span id="cmSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                        <i class="bi bi-upload me-1"></i>Procesar carga
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Tarjeta 4: Resultados (oculta hasta procesar) ─────────────── -->
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

    </div>
</div>

<script src="js/carga_masiva.js?v=<?= inventario_h(inventario_asset_version('js/carga_masiva.js')) ?>" defer></script>
<?php require __DIR__ . '/componentes/layout_bottom.php'; ?>
