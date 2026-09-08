<?php
/**
 * Modal de creación de tickets.
 *
 * Variables provistas por ticket.php:
 * $csrf, $usuarioId, $usuarioNombre, $puedeElegirSolicitante,
 * $categorias, $colegioSesion y $errorCarga.
 */
?>
<div class="modal-overlay" id="modal-crear-ticket" onclick="closeModalOutside(event,'modal-crear-ticket')">
  <div class="modal ticket-create-modal" role="dialog" aria-modal="true" aria-labelledby="ticket-modal-title">
    <form id="form-ticket" novalidate>
      <div class="modal-header ticket-modal-header">
        <div>
          <span class="ticket-modal-kicker">Mesa de ayuda</span>
          <h3 id="ticket-modal-title">Crear nuevo ticket</h3>
          <p>Registra un problema o una solicitud para el equipo de soporte.</p>
        </div>
        <button class="ticket-modal-close" type="button" onclick="closeModal('modal-crear-ticket')" aria-label="Cerrar modal"><i class="bi bi-x-lg"></i></button>
      </div>

      <div class="modal-body ticket-form">
        <input type="hidden" name="accion" value="crear">
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <div class="ticket-message" id="ticket-mensaje" role="status" aria-live="polite"></div>

        <div class="ticket-form-grid">
          <div class="form-group">
            <label class="form-label" for="ticket-solicitante">Solicitante</label>
            <?php if ($puedeElegirSolicitante): ?>
              <select class="form-input" id="ticket-solicitante" name="solicitante_id" data-dynamic-user>
                <option value="<?= $usuarioId ?>"><?= e($usuarioNombre) ?></option>
              </select>
            <?php else: ?>
              <input class="form-input" id="ticket-solicitante" value="<?= e($usuarioNombre) ?>" readonly>
              <input type="hidden" name="solicitante_id" value="<?= $usuarioId ?>">
            <?php endif; ?>
          </div>
          <div class="form-group">
            <label class="form-label" for="ticket-categoria">Categoría *</label>
            <select class="form-input" id="ticket-categoria" name="categoria_id" required <?= !$categorias ? 'disabled' : '' ?>>
              <option value="">Seleccionar categoría</option>
              <?php foreach ($categorias as $categoria): ?>
                <option value="<?= (int) $categoria['id_categoria'] ?>"><?= e($categoria['nombre_categoria']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="ticket-asunto">Asunto *</label>
          <input class="form-input" id="ticket-asunto" name="asunto" minlength="5" maxlength="180" placeholder="Ej.: La impresora de secretaría no responde" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="ticket-descripcion">Descripción *</label>
          <div class="ticket-editor" data-ticket-editor>
            <div class="ticket-editor__toolbar" role="toolbar" aria-label="Formato de descripción">
              <select class="ticket-editor__format" aria-label="Formato del texto" data-editor-format>
                <option value="p">Normal</option>
                <option value="h3">Título</option>
              </select>
              <span class="ticket-editor__separator"></span>
              <button type="button" data-editor-command="bold" aria-label="Negrita"><strong>B</strong></button>
              <button type="button" data-editor-command="italic" aria-label="Cursiva"><em>I</em></button>
              <button type="button" data-editor-command="underline" aria-label="Subrayado"><u>U</u></button>
              <span class="ticket-editor__separator"></span>
              <button type="button" data-editor-command="insertOrderedList" aria-label="Lista numerada"><i class="bi bi-list-ol"></i></button>
              <button type="button" data-editor-command="insertUnorderedList" aria-label="Lista con viñetas"><i class="bi bi-list-ul"></i></button>
              <button type="button" data-editor-link aria-label="Agregar enlace"><i class="bi bi-link-45deg"></i></button>
            </div>
            <div class="ticket-editor__surface" id="ticket-descripcion" contenteditable="true" role="textbox" aria-multiline="true" data-placeholder="Indica qué ocurrió, desde cuándo y qué intentaste hacer."></div>
          </div>
          <input type="hidden" id="ticket-descripcion-value" name="descripcion">
          <div class="ticket-editor__meta"><span>Formato básico permitido</span><span id="ticket-description-count">0/10000</span></div>
        </div>
        <div class="form-group ticket-files">
          <label class="form-label">Adjuntos</label>
          <input type="file" id="ticket-archivos" name="archivos[]" multiple hidden accept="image/jpeg,image/png,image/webp,application/pdf,.doc,.docx,.xls,.xlsx">
          <button class="ticket-file-picker" type="button" onclick="document.getElementById('ticket-archivos').click()">
            <i class="bi bi-paperclip"></i><span><strong>Elegir archivos</strong><small id="ticket-file-count">Sin archivos seleccionados</small></span>
          </button>
          <div class="ticket-file-list" id="ticket-file-list"></div>
          <span class="ticket-note">Hasta 5 archivos de 5 MB cada uno. Imágenes, PDF, Word o Excel.</span>
        </div>

        <div class="ticket-account-note"><i class="bi bi-building-check"></i><span>El colegio se asignará automáticamente: <strong id="ticket-colegio-contexto"><?= e((string) ($colegioSesion['nom_colegio'] ?? 'sin colegio asociado')) ?></strong>.</span></div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-outline" id="ticket-limpiar" type="reset">Limpiar</button>
        <div class="ticket-create-actions">
          <button class="btn btn-outline ticket-draft-button" id="ticket-borrador" type="button" <?= ($errorCarga || !$categorias || (!$colegioSesion && !$puedeElegirSolicitante)) ? 'disabled' : '' ?>><i class="bi bi-file-earmark"></i> Guardar borrador</button>
          <button class="btn btn-primary" id="ticket-guardar" type="submit" <?= ($errorCarga || !$categorias || (!$colegioSesion && !$puedeElegirSolicitante)) ? 'disabled' : '' ?>><i class="bi bi-send-fill"></i> Crear ticket</button>
        </div>
      </div>
    </form>
  </div>
</div>
