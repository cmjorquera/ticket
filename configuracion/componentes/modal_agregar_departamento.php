<?php
declare(strict_types=1);
?>
<div class="modal-overlay" id="Modal_AgregarDepartamento" onclick="closeModalOutside(event, 'Modal_AgregarDepartamento')">
  <div class="modal" style="max-width:480px" role="dialog" aria-modal="true" aria-labelledby="modal-departamento-titulo">
    <div class="modal-header">
      <h3 id="modal-departamento-titulo">Agregar departamento</h3>
      <p id="modal-departamento-colegio">Completa los datos del nuevo departamento.</p>
    </div>
    <div class="modal-body">
      <input type="hidden" id="departamento-id-colegio">
      <div class="form-group">
        <label class="form-label" for="departamento-nombre">Nombre departamento *</label>
        <input type="text" class="form-input" id="departamento-nombre" maxlength="100" autocomplete="off">
      </div>
      <div class="form-group">
        <label class="form-label" for="departamento-sigla">Sigla *</label>
        <input type="text" class="form-input" id="departamento-sigla" minlength="2" maxlength="3" autocomplete="off">
      </div>
      <p id="modal-departamento-error" class="text-sm" role="alert" hidden style="color:var(--danger);margin-top:10px"></p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeModal('Modal_AgregarDepartamento')">Cancelar</button>
      <button type="button" class="btn btn-primary" id="departamento-guardar"><i class="bi bi-floppy"></i> Guardar</button>
    </div>
  </div>
</div>
