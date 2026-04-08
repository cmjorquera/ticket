<div class="ticket-admin-panel">
    <div class="ticket-admin-hero">
        <div>
            <div class="ticket-admin-eyebrow">Administracion avanzada</div>
            <h3 class="ticket-admin-title">Mantenimiento de tickets</h3>
            <!-- <p class="ticket-admin-text">
                Este espacio puede quedar reservado para acciones delicadas del administrador,
                como fusionar tickets duplicados o eliminar casos completos manteniendo control
                sobre el impacto en la trazabilidad, conversaciones y adjuntos.
            </p> -->
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
        <div class="ticket-admin-selector-grid">
            <a href="mantenimiento_ticket_eliminar.php" class="ticket-admin-selector-card ticket-admin-selector-card--danger">
                <div class="ticket-admin-selector-icon">
                    <i class="bi bi-trash3"></i>
                </div>
                <h4>Eliminar Ticket</h4>
                <p>Revisa el ticket dentro de este modulo antes de ejecutar una eliminacion administrativa.</p>
                <div class="ticket-admin-selector-meta">
                    <div><i class="bi bi-check2"></i> Flujo aislado en permisos</div>
                    <div><i class="bi bi-check2"></i> Revision antes de eliminar</div>
                    <div><i class="bi bi-check2"></i> Sin tocar tablas compartidas</div>
                </div>
            </a>

            <a href="mantenimiento_ticket_fusion.php" class="ticket-admin-selector-card ticket-admin-selector-card--primary">
                <div class="ticket-admin-selector-icon">
                    <i class="bi bi-shuffle"></i>
                </div>
                <h4>Fusionar Tickets</h4>
                <p>Une tickets relacionados sin salir del panel administrativo de permisos.</p>
                <div class="ticket-admin-selector-meta">
                    <div><i class="bi bi-check2"></i> Trabajo sobre tickets reales</div>
                    <div><i class="bi bi-check2"></i> Resumen previo del impacto</div>
                    <div><i class="bi bi-check2"></i> Flujo administrativo guiado</div>
                </div>
            </a>
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
