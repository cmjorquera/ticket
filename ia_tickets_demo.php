<?php
/* Demo visual de modulos inteligentes para tickets.
 * Esta vista no usa base de datos. Solo consume una logica simulada por AJAX.
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo IA para Tickets</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css">
    <link rel="stylesheet" href="assets/css/ia_tickets_demo.css">
</head>
<body class="ia-demo-body">
    <div class="ia-shell">
        <div class="container-fluid py-4 py-lg-5">
            <div class="ia-hero-card mb-4">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <span class="ia-kicker"><i class="bi bi-cpu me-2"></i>Simulacion visual de asistencia inteligente</span>
                        <h1 class="ia-title mt-3 mb-3">Demo IA para Tickets</h1>
                        <p class="ia-subtitle mb-0">
                            Vista de prueba para evaluar como se verian modulos de clasificacion, sugerencia de tecnico y prediccion de resolucion
                            dentro del sistema de soporte, usando reglas simuladas y una interfaz lista para evolucionar a IA real.
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <div class="ia-hero-side">
                            <div class="ia-hero-side-icon">
                                <i class="bi bi-diagram-3"></i>
                            </div>
                            <div>
                                <strong class="d-block">Estado del laboratorio</strong>
                                <span>Frontend funcional con analisis simulado por AJAX</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="ia-summary-card">
                        <div class="ia-summary-icon bg-primary-subtle text-primary">
                            <i class="bi bi-bar-chart-line"></i>
                        </div>
                        <div>
                            <span class="ia-summary-label">Tickets analizados hoy</span>
                            <strong class="ia-summary-value">128</strong>
                            <small class="text-muted d-block">Dato visual ficticio para la demo</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ia-summary-card">
                        <div class="ia-summary-icon bg-success-subtle text-success">
                            <i class="bi bi-patch-check"></i>
                        </div>
                        <div>
                            <span class="ia-summary-label">Precision simulada</span>
                            <strong class="ia-summary-value">91%</strong>
                            <small class="text-muted d-block">Clasificacion guiada por reglas y keywords</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="ia-summary-card">
                        <div class="ia-summary-icon bg-warning-subtle text-warning">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <span class="ia-summary-label">Tiempo promedio estimado</span>
                            <strong class="ia-summary-value">2.8 h</strong>
                            <small class="text-muted d-block">Promedio visual de resolucion proyectada</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ia-panel-card mb-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="ia-section-title mb-1">Crear ticket de prueba</h2>
                        <p class="text-muted mb-0">Ingresa un caso y presiona analizar para ver la reaccion simulada de los modulos inteligentes.</p>
                    </div>
                    <div class="ia-chip-group">
                        <span class="ia-chip"><i class="bi bi-lightning-charge me-1"></i>Clasificacion automatica</span>
                        <span class="ia-chip"><i class="bi bi-person-check me-1"></i>Sugerencia operativa</span>
                        <span class="ia-chip"><i class="bi bi-graph-up-arrow me-1"></i>Prediccion estimada</span>
                    </div>
                </div>

                <form id="formIaTicket" novalidate>
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <label for="asunto" class="form-label">Asunto</label>
                            <input type="text" class="form-control ia-form-control" id="asunto" name="asunto" placeholder="Ej: No funciona el wifi del laboratorio">
                        </div>
                        <div class="col-lg-3">
                            <label for="categoria_manual" class="form-label">Categoria manual</label>
                            <select class="form-select ia-form-control" id="categoria_manual" name="categoria_manual">
                                <option value="">Sin definir</option>
                                <option value="Redes">Redes</option>
                                <option value="Impresoras">Impresoras</option>
                                <option value="Correo">Correo</option>
                                <option value="Hardware / Soporte">Hardware / Soporte</option>
                                <option value="Software">Software</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label for="colegio_area" class="form-label">Colegio o area</label>
                            <select class="form-select ia-form-control" id="colegio_area" name="colegio_area">
                                <option value="Colegio Cordillera">Colegio Cordillera</option>
                                <option value="Colegio Tabancura">Colegio Tabancura</option>
                                <option value="Colegio Huelen">Colegio Huelen</option>
                                <option value="Colegio Los Andes">Colegio Los Andes</option>
                                <option value="SEDUC - Informatica">SEDUC - Informatica</option>
                                <option value="SEDUC - Finanzas">SEDUC - Finanzas</option>
                            </select>
                        </div>

                        <div class="col-lg-9">
                            <label for="descripcion" class="form-label">Descripcion</label>
                            <textarea class="form-control ia-form-control" id="descripcion" name="descripcion" rows="6" placeholder="Describe el problema, el impacto y cualquier contexto que ayude a simular el analisis."></textarea>
                        </div>
                        <div class="col-lg-3">
                            <label for="prioridad_manual" class="form-label">Prioridad manual</label>
                            <select class="form-select ia-form-control" id="prioridad_manual" name="prioridad_manual">
                                <option value="">Sin definir</option>
                                <option value="Baja">Baja</option>
                                <option value="Media">Media</option>
                                <option value="Alta">Alta</option>
                                <option value="Critica">Critica</option>
                            </select>

                            <div class="ia-tip-box mt-3">
                                <strong class="d-block mb-2">Pruebas sugeridas</strong>
                                <ul class="mb-0 ps-3">
                                    <li>"No funciona el wifi del segundo piso"</li>
                                    <li>"Outlook no abre y es urgente"</li>
                                    <li>"La impresora no imprime y aparece error"</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-4">
                        <button type="submit" id="btnAnalizarTicket" class="btn ia-btn-primary">
                            <i class="bi bi-stars me-2"></i>Analizar Ticket
                        </button>
                        <button type="button" id="btnCargarEjemplo" class="btn ia-btn-outline">
                            <i class="bi bi-magic me-2"></i>Cargar ejemplo
                        </button>
                    </div>
                </form>
            </div>

            <div class="row g-4">
                <div class="col-xl-4">
                    <div class="ia-result-card h-100">
                        <div class="ia-result-head">
                            <div class="ia-result-icon bg-primary-subtle text-primary">
                                <i class="bi bi-tags"></i>
                            </div>
                            <div>
                                <h3 class="ia-card-title mb-1">Clasificacion automatica</h3>
                                <p class="text-muted mb-0">Categoria detectada por reglas simuladas</p>
                            </div>
                        </div>
                        <div class="ia-result-body">
                            <div class="mb-3">
                                <span class="ia-label">Categoria detectada</span>
                                <div id="categoriaDetectada" class="ia-value">Pendiente de analisis</div>
                            </div>
                            <div class="mb-3">
                                <span class="ia-label">Confianza</span>
                                <div class="d-flex align-items-center gap-3">
                                    <strong id="confianzaTexto" class="ia-highlight">0%</strong>
                                    <span id="badgeCategoria" class="badge ia-badge-soft">Sin clasificar</span>
                                </div>
                                <div class="progress ia-progress mt-3">
                                    <div id="confianzaBarra" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100">0%</div>
                                </div>
                            </div>
                            <div>
                                <span class="ia-label">Lectura visual</span>
                                <div id="clasificacionDetalle" class="ia-muted-box">
                                    El motor mostrara una categoria estimada, confianza y badges visuales cuando analices un ticket.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="ia-result-card h-100">
                        <div class="ia-result-head">
                            <div class="ia-result-icon bg-success-subtle text-success">
                                <i class="bi bi-person-workspace"></i>
                            </div>
                            <div>
                                <h3 class="ia-card-title mb-1">Sugerencia de tecnico</h3>
                                <p class="text-muted mb-0">Asignacion guiada por categoria y contexto</p>
                            </div>
                        </div>
                        <div class="ia-result-body">
                            <div class="mb-3">
                                <span class="ia-label">Tecnico sugerido</span>
                                <div id="tecnicoSugerido" class="ia-value">Sin recomendacion aun</div>
                            </div>
                            <div class="mb-3">
                                <span class="ia-label">Especialidad</span>
                                <div id="especialidadTecnico" class="fw-semibold text-dark">Pendiente</div>
                            </div>
                            <div class="mb-3">
                                <span class="ia-label">Motivo de sugerencia</span>
                                <div id="motivoTecnico" class="ia-muted-box">La explicacion del por que se sugiere un tecnico aparecera aqui.</div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <span id="cargaTecnico" class="badge ia-badge-soft">Carga actual: -</span>
                                <span id="estadoTecnico" class="badge ia-badge-soft">Estado: Disponible</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="ia-result-card h-100">
                        <div class="ia-result-head">
                            <div class="ia-result-icon bg-danger-subtle text-danger">
                                <i class="bi bi-stopwatch"></i>
                            </div>
                            <div>
                                <h3 class="ia-card-title mb-1">Prediccion de resolucion</h3>
                                <p class="text-muted mb-0">Tiempo, riesgo y urgencia estimada</p>
                            </div>
                        </div>
                        <div class="ia-result-body">
                            <div class="mb-3">
                                <span class="ia-label">Tiempo estimado</span>
                                <div id="tiempoEstimado" class="ia-value">Sin estimacion</div>
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <div class="ia-mini-box">
                                        <span class="ia-label">Riesgo</span>
                                        <strong id="riesgoTicket">-</strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="ia-mini-box">
                                        <span class="ia-label">Urgencia</span>
                                        <strong id="urgenciaTicket">-</strong>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <span class="ia-label">Indicador visual</span>
                                <div class="progress ia-progress mt-3">
                                    <div id="riesgoBarra" class="progress-bar bg-success" role="progressbar" style="width: 10%;" aria-valuemin="0" aria-valuemax="100">Inicial</div>
                                </div>
                            </div>
                            <div id="prediccionDetalle" class="ia-muted-box mt-3">
                                La urgencia y el riesgo se ajustaran segun palabras clave detectadas en el ticket.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="ia-result-card">
                        <div class="ia-result-head">
                            <div class="ia-result-icon bg-info-subtle text-info">
                                <i class="bi bi-collection"></i>
                            </div>
                            <div>
                                <h3 class="ia-card-title mb-1">Tickets similares encontrados</h3>
                                <p class="text-muted mb-0">Casos de referencia simulados para apoyar al analista o tecnico.</p>
                            </div>
                        </div>
                        <div class="ia-result-body">
                            <div class="table-responsive">
                                <table class="table ia-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID Ticket</th>
                                            <th>Asunto</th>
                                            <th>Categoria</th>
                                            <th>Solucion aplicada</th>
                                            <th>Tiempo de resolucion</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tablaSimilares">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Analiza un ticket para cargar casos similares.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function ($) {
            'use strict';

            function escapeHtml(texto) {
                return $('<div>').text(texto == null ? '' : String(texto)).html();
            }

            function colorPorRiesgo(riesgo) {
                const valor = String(riesgo || '').toLowerCase();
                if (valor === 'alto') { return { clase: 'bg-danger', width: 92, label: 'Riesgo alto' }; }
                if (valor === 'medio') { return { clase: 'bg-warning text-dark', width: 62, label: 'Riesgo medio' }; }
                return { clase: 'bg-success', width: 32, label: 'Riesgo bajo' };
            }

            function badgeClase(valor) {
                const texto = String(valor || '').toLowerCase();
                if (texto.includes('alto') || texto.includes('critica') || texto.includes('ocupada') || texto.includes('demanda')) { return 'bg-danger-subtle text-danger'; }
                if (texto.includes('media') || texto.includes('medio')) { return 'bg-warning-subtle text-warning'; }
                if (texto.includes('redes')) { return 'bg-primary-subtle text-primary'; }
                if (texto.includes('software')) { return 'bg-info-subtle text-info'; }
                return 'bg-success-subtle text-success';
            }

            function renderResultado(data) {
                $('#categoriaDetectada').text(data.categoria_detectada || 'Sin clasificar');
                $('#confianzaTexto').text((data.confianza || 0) + '%');
                $('#badgeCategoria')
                    .attr('class', 'badge ' + badgeClase(data.categoria_detectada))
                    .text(data.badge_categoria || data.categoria_detectada || 'Sin clasificar');
                $('#confianzaBarra')
                    .css('width', (data.confianza || 0) + '%')
                    .attr('aria-valuenow', data.confianza || 0)
                    .text((data.confianza || 0) + '%');
                $('#clasificacionDetalle').html(
                    '<strong>Lectura simulada:</strong> ' + escapeHtml(data.detalle_clasificacion || 'Sin detalle')
                );

                $('#tecnicoSugerido').text(data.tecnico_sugerido || 'Sin recomendacion');
                $('#especialidadTecnico').text(data.especialidad || 'Pendiente');
                $('#motivoTecnico').text(data.motivo_sugerencia || 'Sin motivo disponible.');
                $('#cargaTecnico')
                    .attr('class', 'badge ia-badge-soft ' + badgeClase(data.estado_carga))
                    .text('Carga actual: ' + (data.carga_actual || '-'));
                $('#estadoTecnico')
                    .attr('class', 'badge ia-badge-soft ' + badgeClase(data.estado_carga))
                    .text('Estado: ' + (data.estado_carga || 'Disponible'));

                $('#tiempoEstimado').text(data.tiempo_estimado || '-');
                $('#riesgoTicket').text(data.riesgo || '-');
                $('#urgenciaTicket').text(data.urgencia || '-');
                $('#prediccionDetalle').text(data.detalle_prediccion || 'Sin detalle de prediccion.');

                const riesgo = colorPorRiesgo(data.riesgo);
                $('#riesgoBarra')
                    .attr('class', 'progress-bar ' + riesgo.clase)
                    .css('width', riesgo.width + '%')
                    .text(riesgo.label);

                const similares = Array.isArray(data.similares) ? data.similares : [];
                if (!similares.length) {
                    $('#tablaSimilares').html('<tr><td colspan="5" class="text-center text-muted py-4">No se encontraron tickets similares simulados.</td></tr>');
                    return;
                }

                $('#tablaSimilares').html(similares.map(function (item) {
                    return '<tr>' +
                        '<td><span class="ia-ticket-id">#' + escapeHtml(item.id_ticket) + '</span></td>' +
                        '<td>' + escapeHtml(item.asunto) + '</td>' +
                        '<td><span class="badge ' + badgeClase(item.categoria) + '">' + escapeHtml(item.categoria) + '</span></td>' +
                        '<td>' + escapeHtml(item.solucion) + '</td>' +
                        '<td>' + escapeHtml(item.tiempo_resolucion) + '</td>' +
                    '</tr>';
                }).join(''));
            }

            function cargarEjemplo() {
                $('#asunto').val('No funciona el wifi del laboratorio y es urgente');
                $('#descripcion').val('Desde esta manana la conexion esta caida en el segundo piso. Los alumnos no pueden acceder a internet y necesitamos una solucion inmediata.');
                $('#categoria_manual').val('');
                $('#colegio_area').val('Colegio Cordillera');
                $('#prioridad_manual').val('Alta');
            }

            $('#btnCargarEjemplo').on('click', cargarEjemplo);

            $('#formIaTicket').on('submit', function (e) {
                e.preventDefault();

                const asunto = $.trim($('#asunto').val());
                const descripcion = $.trim($('#descripcion').val());

                if (!asunto || !descripcion) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Faltan datos',
                        text: 'Debes ingresar al menos asunto y descripcion para analizar el ticket.'
                    });
                    return;
                }

                Swal.fire({
                    title: 'Analizando ticket',
                    text: 'La demo esta simulando los modulos inteligentes...',
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'ajax/procesar_ia_ticket.php',
                    type: 'POST',
                    dataType: 'json',
                    data: $(this).serialize()
                }).done(function (response) {
                    Swal.close();

                    if (!response || response.ok !== true) {
                        Swal.fire('Error', response && response.mensaje ? response.mensaje : 'No fue posible procesar el ticket.', 'error');
                        return;
                    }

                    renderResultado(response.data || {});
                }).fail(function (xhr) {
                    Swal.close();
                    const mensaje = xhr.responseJSON && xhr.responseJSON.mensaje ? xhr.responseJSON.mensaje : 'Ocurrio un problema al conectar con la simulacion.';
                    Swal.fire('Error', mensaje, 'error');
                });
            });
        })(jQuery);
    </script>
</body>
</html>
