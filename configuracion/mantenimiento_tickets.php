<div class="ticket-admin-panel">
    <div class="ticket-admin-hero">
        <div>
            <div class="ticket-admin-eyebrow">Administracion avanzada</div>
            <h3 class="ticket-admin-title">Mantenimiento de tickets</h3>
            <p class="ticket-admin-text">
                Este espacio puede quedar reservado para acciones delicadas del administrador,
                como fusionar tickets duplicados o eliminar casos completos manteniendo control
                sobre el impacto en la trazabilidad, conversaciones y adjuntos.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="ticket-admin-info-trigger" data-bs-toggle="modal" data-bs-target="#modalInfoMantenimientoTickets" title="Ver tablas involucradas y flujo sugerido">
                <i class="bi bi-info-lg"></i>
            </button>
            <div class="ticket-admin-badge">Acciones de alto impacto</div>
        </div>
    </div>

    <div class="ticket-admin-warning">
        <strong>Importante:</strong> estas operaciones no son cosmeticas. Antes de ejecutarlas,
        conviene dejar un motivo registrado, mostrar una previsualizacion del impacto y exigir
        confirmacion explicita del administrador.
    </div>

    <div class="ticket-admin-grid">
        <div class="ticket-admin-card ticket-admin-card--full">
            <h4>Centro de operaciones</h4>
            <p>
                Te propongo separar esta pestaña en dos acciones claras: <strong>fusionar</strong>
                cuando dos tickets hablan del mismo caso, y <strong>eliminar</strong> cuando un ticket
                fue creado por error y ya no debe existir.
            </p>

            <div class="ticket-admin-action ticket-admin-action--merge">
                <div class="ticket-admin-action-title">
                    <i class="bi bi-intersect"></i>
                    <span>Fusionar tickets</span>
                </div>
                <form class="ticket-admin-form">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ticket principal</label>
                            <input type="text" class="form-control" id="ticketMergePrincipal" placeholder="Ej: #1258">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Ticket secundario</label>
                            <input type="text" class="form-control" id="ticketMergeSecundario" placeholder="Ej: #1291">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Motivo de fusion</label>
                            <textarea class="form-control" id="ticketMergeMotivo" rows="3" placeholder="Describe por que ambos tickets pertenecen al mismo caso."></textarea>
                        </div>
                    </div>
                    <div class="ticket-admin-actions-bar">
                        <button type="button" class="btn btn-primary ticket-admin-btn" id="btnPreviewFusion">
                            <i class="bi bi-search me-1"></i>Previsualizar fusion
                        </button>
                        <button type="button" class="btn btn-outline-primary ticket-admin-btn" id="btnImpactoFusion">
                            <i class="bi bi-diagram-3 me-1"></i>Ver impacto relacionado
                        </button>
                    </div>
                </form>

                <div class="ticket-admin-preview is-hidden" id="ticketMergePreview"></div>
            </div>

            <div class="ticket-admin-action ticket-admin-action--delete">
                <div class="ticket-admin-action-title">
                    <i class="bi bi-trash3"></i>
                    <span>Eliminar ticket</span>
                </div>
                <form class="ticket-admin-form">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Ticket a eliminar</label>
                            <input type="text" class="form-control" id="ticketDeleteId" placeholder="Ej: #1320">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de eliminacion</label>
                            <select class="form-select" id="ticketDeleteTipo">
                                <option value="completa">Eliminacion completa</option>
                                <option value="ocultar">Solo ocultar del sistema</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Justificacion administrativa</label>
                            <textarea class="form-control" id="ticketDeleteMotivo" rows="3" placeholder="Ej: ticket duplicado, prueba interna o error de creacion."></textarea>
                        </div>
                    </div>
                    <div class="ticket-admin-actions-bar">
                        <button type="button" class="btn btn-danger ticket-admin-btn" id="btnPreviewEliminar">
                            <i class="bi bi-exclamation-triangle me-1"></i>Revisar antes de eliminar
                        </button>
                        <button type="button" class="btn btn-outline-secondary ticket-admin-btn" id="btnGuardarBorradorMantenimiento">
                            <i class="bi bi-clock-history me-1"></i>Guardar como borrador
                        </button>
                    </div>
                </form>

                <div class="ticket-admin-preview is-hidden" id="ticketDeletePreview"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInfoMantenimientoTickets" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header ticket-admin-modal-header">
                <div>
                    <div class="ticket-admin-eyebrow mb-1">Ayuda operativa</div>
                    <h5 class="modal-title mb-0">Tablas involucradas y flujo sugerido</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="ticket-admin-modal-grid">
                    <div>
                        <h4>Tablas involucradas</h4>
                        <p>
                            Estas son las piezas que yo consideraria en la operacion para no dejar datos
                            huerfanos ni perder historial importante del caso.
                        </p>

                        <div class="ticket-admin-table-list">
                            <div class="ticket-admin-table-item">
                                <strong>tickets</strong>
                                <span>Registro maestro del caso, solicitante, asunto, categoria, estado y fechas base.</span>
                            </div>
                            <div class="ticket-admin-table-item">
                                <strong>proceso_tickets</strong>
                                <span>Traza operativa del ticket: estados, reasignaciones y eventos del flujo.</span>
                            </div>
                            <div class="ticket-admin-table-item">
                                <strong>archivos_adjuntos_ticket</strong>
                                <span>Documentos y evidencias que deberian migrarse al ticket principal al fusionar.</span>
                            </div>
                            <div class="ticket-admin-table-item">
                                <strong>ticket_conversaciones</strong>
                                <span>Mensajes o hilos de seguimiento vinculados al caso.</span>
                            </div>
                            <div class="ticket-admin-table-item">
                                <strong>comentario_ticket</strong>
                                <span>Hoy no aparece en el codigo revisado; podria estar reemplazada por campos como <code>comentario_administrador</code> y <code>comentario_final</code>.</span>
                            </div>
                            <div class="ticket-admin-table-item">
                                <strong>avance_tecnicos</strong>
                                <span>Bitacora de avances tecnicos que se debe conservar para no romper la historia del caso.</span>
                            </div>
                            <div class="ticket-admin-table-item">
                                <strong>calificacion_tickett</strong>
                                <span>Evaluacion final del servicio; si existe, conviene definir si se conserva o se anula.</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4>Flujo sugerido</h4>
                        <div class="ticket-admin-flow">
                            <div class="ticket-admin-flow-step">
                                <div class="ticket-admin-flow-step-number">1</div>
                                <div>
                                    <strong>Buscar y validar</strong>
                                    <span>El administrador ingresa los tickets y el sistema muestra resumen, estado y dependencias.</span>
                                </div>
                            </div>
                            <div class="ticket-admin-flow-step">
                                <div class="ticket-admin-flow-step-number">2</div>
                                <div>
                                    <strong>Previsualizar impacto</strong>
                                    <span>Se listan adjuntos, comentarios, conversaciones y avances que se moveran o eliminaran.</span>
                                </div>
                            </div>
                            <div class="ticket-admin-flow-step">
                                <div class="ticket-admin-flow-step-number">3</div>
                                <div>
                                    <strong>Confirmar con bitacora</strong>
                                    <span>La accion guarda motivo, usuario administrador, fecha y ticket afectado para auditoria.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
