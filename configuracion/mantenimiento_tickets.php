<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="m-0 font-weight-bold text-primary">Mantenimiento de tickets</h6>
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalInfoMantenimientoTickets" title="Ver tablas involucradas y flujo sugerido">
            <i class="bi bi-info-lg me-1"></i>Informacion
        </button>
    </div>
    <div class="card-body">
        <div class="alert alert-warning mb-4">
            <strong>Importante:</strong> estas operaciones no son cosmeticas. Antes de ejecutarlas, conviene dejar un motivo registrado, mostrar una previsualizacion del impacto y exigir confirmacion explicita del administrador.
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Eliminar Ticket</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Revisa el ticket dentro de este modulo antes de ejecutar una eliminacion administrativa.</p>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="mantenimiento_ticket_eliminar.php" class="btn btn-danger">Abrir modulo</a>
                            <a href="mantenimiento_ticket_recuperar.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-counterclockwise me-1"></i>Listado de tickets eliminados
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow h-100">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Fusionar Tickets</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Une tickets relacionados sin salir del panel administrativo de permisos.</p>
                        <a href="mantenimiento_ticket_fusion.php" class="btn btn-primary">Abrir modulo</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalInfoMantenimientoTickets" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title mb-0">Tablas involucradas y flujo sugerido</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h4>Tablas involucradas</h4>
                        <p>
                            Estas son las piezas que yo consideraria en la operacion para no dejar datos
                            huerfanos ni perder historial importante del caso.
                        </p>

                        <div class="list-group">
                            <div class="list-group-item">
                                <strong>tickets</strong>
                                <span>Registro maestro del caso, solicitante, asunto, categoria, estado y fechas base.</span>
                            </div>
                            <div class="list-group-item">
                                <strong>proceso_tickets</strong>
                                <span>Traza operativa del ticket: estados, reasignaciones y eventos del flujo.</span>
                            </div>
                            <div class="list-group-item">
                                <strong>archivos_adjuntos_ticket</strong>
                                <span>Documentos y evidencias que deberian migrarse al ticket principal al fusionar.</span>
                            </div>
                            <div class="list-group-item">
                                <strong>ticket_conversaciones</strong>
                                <span>Mensajes o hilos de seguimiento vinculados al caso.</span>
                            </div>
                            <div class="list-group-item">
                                <strong>comentario_ticket</strong>
                                <span>Hoy no aparece en el codigo revisado; podria estar reemplazada por campos como <code>comentario_administrador</code> y <code>comentario_final</code>.</span>
                            </div>
                            <div class="list-group-item">
                                <strong>avance_tecnicos</strong>
                                <span>Bitacora de avances tecnicos que se debe conservar para no romper la historia del caso.</span>
                            </div>
                            <div class="list-group-item">
                                <strong>calificacion_tickett</strong>
                                <span>Evaluacion final del servicio; si existe, conviene definir si se conserva o se anula.</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 mb-4">
                        <h4>Flujo sugerido</h4>
                        <div class="list-group">
                            <div class="list-group-item">
                                <strong>1. Buscar y validar</strong>
                                <div>El administrador ingresa los tickets y el sistema muestra resumen, estado y dependencias.</div>
                            </div>
                            <div class="list-group-item">
                                <strong>2. Previsualizar impacto</strong>
                                <div>Se listan adjuntos, comentarios, conversaciones y avances que se moveran o eliminaran.</div>
                            </div>
                            <div class="list-group-item">
                                <strong>3. Confirmar con bitacora</strong>
                                <div>La accion guarda motivo, usuario administrador, fecha y ticket afectado para auditoria.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
