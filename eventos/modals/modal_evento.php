<div class="modal fade eventos-modal" id="modalEvento" tabindex="-1" aria-labelledby="modalEventoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="form-evento">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEventoLabel">Nuevo evento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id" value="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Título</label>
                            <input type="text" class="form-control" name="titulo" required>
                        </div>
                        <!-- <div class="col-md-6">
                            <label class="form-label">Ubicación</label>
                            <input type="text" class="form-control" name="ubicacion">
                        </div> -->
                        <div class="col-12">
                            <label class="form-label">Descripción</label>
                            <textarea class="form-control" name="descripcion" rows="3"></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Hora inicio</label>
                            <input type="time" class="form-control" name="hora_inicio" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha término</label>
                            <input type="date" class="form-control" name="fecha_fin" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Hora término</label>
                            <input type="time" class="form-control" name="hora_fin" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cantidad personas</label>
                            <input type="number" class="form-control" name="cantidad_personas" min="0" value="0">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Responsable</label>
                            <select class="form-select" name="responsable_id" required>
                                <option value="">Seleccione...</option>
                                <?php foreach (eventos_obtener_responsables() as $responsable): ?>
                                    <option value="<?php echo (int) $responsable['id']; ?>">
                                        <?php echo htmlspecialchars($responsable['nombre_completo'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tipo de evento</label>
                            <select class="form-select" name="tipo_evento">
                                <option value="reunion">Reunión</option>
                                <option value="capacitacion">Capacitación</option>
                                <option value="celebracion">Celebración</option>
                                <option value="actividad escolar">Actividad escolar</option>
                            </select>
                        </div>
                        <div class="col-md-3 eventos-estado-wrap d-none" id="eventos-estado-wrap">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="estado">
                                <option value="programado">Programado</option>
                                <option value="cancelado">Cancelado</option>
                                <option value="finalizado">Finalizado</option>
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <div class="eventos-modal-panel h-100">
                                <label class="form-label">Color del evento</label>
                                <input type="color" class="form-control form-control-color" name="color_evento" value="#2563eb">
                                <p class="eventos-modal-help mb-0">Define un color visible para identificar el evento en el calendario.</p>
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="eventos-modal-panel h-100">
                                <label class="form-label">Observaciones logísticas</label>
                                <textarea class="form-control" name="observaciones_logisticas" rows="5" placeholder="Ej.: montaje, mobiliario, apoyo previo, acceso, horarios especiales..."></textarea>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="eventos-modal-panel">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                                    <div>
                                        <label class="form-label d-block mb-1">Requerimientos técnicos</label>
                                        <p class="eventos-modal-help mb-0">Marca solo lo necesario para preparar el evento con anticipación.</p>
                                    </div>
                                </div>
                                <div class="eventos-checks">
                                    <div class="eventos-check-card">
                                        <input class="form-check-input" type="checkbox" value="1" name="con_audio" id="con_audio">
                                        <label class="form-check-label" for="con_audio">
                                            <span class="eventos-check-icon"><i class="bi bi-volume-up"></i></span>
                                            <span class="eventos-check-copy">
                                                <strong>Con audio</strong>
                                                <small>Micrófonos, parlantes o amplificación.</small>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="eventos-check-card">
                                        <input class="form-check-input" type="checkbox" value="1" name="musica_ambiental" id="musica_ambiental">
                                        <label class="form-check-label" for="musica_ambiental">
                                            <span class="eventos-check-icon"><i class="bi bi-music-note-beamed"></i></span>
                                            <span class="eventos-check-copy">
                                                <strong>Música ambiental</strong>
                                                <small>Ambientación sonora durante la actividad.</small>
                                            </span>
                                        </label>
                                    </div>
                                    <div class="eventos-check-card">
                                        <input class="form-check-input" type="checkbox" value="1" name="solo_presentacion" id="solo_presentacion">
                                        <label class="form-check-label" for="solo_presentacion">
                                            <span class="eventos-check-icon"><i class="bi bi-easel2"></i></span>
                                            <span class="eventos-check-copy">
                                                <strong>Solo presentación</strong>
                                                <small>Pantalla o apoyo visual sin audio adicional.</small>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="btn btn-outline-danger d-none me-auto" id="btn-eliminar-evento">Eliminar</button>
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary" id="btn-guardar-evento">Guardar evento</button>
                </div>
            </form>
        </div>
    </div>
</div>
