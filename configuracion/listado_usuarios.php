<?php
require_once __DIR__ . '/_inicio.php';

$depth = '../';
$pagina_estilos = ['css/listado_usuarios.css?v=' . (string) filemtime(__DIR__ . '/../css/listado_usuarios.css')];
iniciar_layout_configuracion('Listado de usuarios', 'Usuarios', 'listado_usuarios');
?>
<div class="page-header">
  <div>
    <h1>Listado de usuarios</h1>
    <p>Administra cuentas, permisos y estado de acceso.</p>
  </div>
  <div class="page-header-actions">
    <button type="button" class="btn btn-primary" id="btn-agregar-usuario">
      <i class="bi bi-person-plus"></i> Agregar usuario
    </button>
  </div>
</div>

<div class="card">
  <div class="toolbar">
    <div class="input-icon-wrap" style="width:min(100%,360px)">
      <i class="bi bi-search icon"></i>
      <input class="input" id="buscar-usuario" type="search" placeholder="Buscar nombre, email o colegio">
    </div>
    <div class="view-switch" role="group" aria-label="Cambiar vista">
      <button type="button" class="btn-view active" data-view="tabla" title="Vista tabla" aria-label="Vista tabla" aria-pressed="true">
        <i class="bi bi-list-ul" aria-hidden="true"></i>
      </button>
      <button type="button" class="btn-view" data-view="organigrama" title="Vista organigrama" aria-label="Vista organigrama" aria-pressed="false">
        <i class="bi bi-diagram-3" aria-hidden="true"></i>
      </button>
    </div>
    <select class="input" id="filtro-area" style="max-width:220px" aria-label="Filtrar por área">
      <option value="">Todas las áreas</option>
    </select>
    <div class="filtro-pills" id="filtro-estado" aria-label="Filtrar por estado">
      <button type="button" class="pill active" data-val="">Todos</button>
      <button type="button" class="pill" data-val="activo">Activo</button>
      <button type="button" class="pill" data-val="inactivo">Inactivo</button>
      <button type="button" class="pill" data-val="Pendiente">Pendiente</button>
      <button type="button" class="pill" data-val="bloqueado">Bloqueado</button>
    </div>
    <span class="toolbar-count"><strong id="total-usuarios">0</strong> usuarios</span>
  </div>
  <div id="vista-tabla">
    <div class="table-wrap">
      <table id="tabla-usuarios">
        <thead>
          <tr><th>N°</th><th>Usuario</th><th>Email</th><th>Perfiles</th><th>Departamento</th><th>Área</th><th>Colegio</th><th>Estado</th><th>Acciones</th></tr>
        </thead>
        <tbody>
          <tr><td colspan="9" class="text-muted">Cargando usuarios…</td></tr>
        </tbody>
      </table>
    </div>
    <div class="paginacion" id="usr-paginacion"></div>
  </div>
  <div id="vista-organigrama" class="org-view" hidden aria-live="polite"></div>
</div>

<p id="usuarios-feedback" class="text-sm" role="status" hidden style="margin-top:12px"></p>

<!-- Modal para confirmar bloqueo o activación -->
<div class="modal-overlay" id="modal-estado-usuario" onclick="closeModalOutside(event, 'modal-estado-usuario')">
  <div class="modal" style="max-width:420px" role="dialog" aria-modal="true" aria-labelledby="modal-estado-titulo">
    <div class="modal-header">
      <h3 id="modal-estado-titulo">¿Bloquear usuario?</h3>
      <p id="modal-estado-nombre"></p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeModal('modal-estado-usuario')">Cancelar</button>
      <button type="button" class="btn btn-primary" id="modal-estado-confirmar">Confirmar</button>
    </div>
  </div>
</div>

<!-- Modal de permisos -->
<div class="modal-overlay" id="modal-permisos" onclick="closeModalOutside(event, 'modal-permisos')">
  <div class="modal" style="max-width:600px" role="dialog" aria-modal="true" aria-labelledby="modal-permisos-titulo">
    <div class="modal-header">
      <h3 id="modal-permisos-titulo">Permisos</h3>
      <p id="modal-permisos-nombre"></p>
    </div>
    <div class="modal-body" id="modal-permisos-contenido"><p class="text-muted">Cargando…</p></div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeModal('modal-permisos')">Cancelar</button>
      <button type="button" class="btn btn-primary" id="modal-permisos-guardar">Guardar permisos</button>
    </div>
  </div>
</div>

<!-- Modal para crear o editar usuarios -->
<div class="modal-overlay" id="modal-usuario" onclick="closeModalOutside(event, 'modal-usuario')">
  <div class="modal modal-usuario-wide" role="dialog" aria-modal="true" aria-labelledby="modal-usuario-titulo">
    <div class="modal-header"><h3 id="modal-usuario-titulo">Nuevo usuario</h3><p>Completa los datos del usuario.</p></div>
    <div class="modal-body">
      <input type="hidden" id="modal-usuario-id">
      <div class="modal-usuario-grid">
        <section class="modal-usuario-section" aria-labelledby="modal-usuario-datos">
          <p class="form-label modal-section-title" id="modal-usuario-datos">Datos del usuario</p>
          <div class="modal-form-grid">
            <div class="form-group"><label class="form-label" for="u-nombre">Nombre *</label><input type="text" class="form-input" id="u-nombre" placeholder="Nombre"></div>
            <div class="form-group"><label class="form-label" for="u-apellido-pat">Apellido paterno *</label><input type="text" class="form-input" id="u-apellido-pat" placeholder="Apellido paterno"></div>
            <div class="form-group"><label class="form-label" for="u-apellido-mat">Apellido materno</label><input type="text" class="form-input" id="u-apellido-mat" placeholder="Apellido materno"></div>
            <div class="form-group"><label class="form-label" for="u-email">Email *</label><input type="email" class="form-input" id="u-email" placeholder="correo@seduc.cl"></div>
            <div class="form-group"><label class="form-label" for="u-telefono">Teléfono</label><input type="text" class="form-input" id="u-telefono" placeholder="Teléfono"></div>
            <div class="form-group"><label class="form-label" for="u-area">Área *</label><select class="form-input" id="u-area"><option value="">Seleccionar</option></select></div>
            <div class="form-group"><label class="form-label" for="u-perfil">Perfil *</label><select class="form-input" id="u-perfil"><option value="">Seleccionar</option></select></div>
            <div class="form-group"><label class="form-label" for="u-colegio">Colegio</label><select class="form-input" id="u-colegio"><option value="">Sin colegio</option></select></div>
            <div class="form-group"><label class="form-label" for="u-sexo">Sexo *</label><select class="form-input" id="u-sexo"><option value="">Seleccionar</option><option value="M">Masculino</option><option value="F">Femenino</option></select></div>
          </div>
        </section>
        <section class="modal-usuario-section modal-usuario-permisos" id="u-menus-wrap" aria-labelledby="modal-usuario-menus">
          <p class="form-label modal-section-title" id="modal-usuario-menus">Permisos automáticos</p>
          <div id="u-perfil-permisos" class="perfil-permisos-preview">
            <div class="perfil-permisos-empty"><i class="bi bi-person-badge"></i><p>Selecciona un perfil para revisar los accesos que recibirá.</p></div>
          </div>
        </section>
      </div>
      <p id="modal-usuario-error" class="text-sm" hidden style="color:var(--danger);margin-top:12px"></p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeModal('modal-usuario')">Cancelar</button>
      <button type="button" class="btn btn-primary" id="modal-usuario-guardar">Guardar</button>
    </div>
  </div>
</div>

<script src="<?= $depth ?>js/paginacion.js"></script>
<script src="<?= $depth ?>configuracion/js/listado_usuarios.js?v=<?= (int) filemtime(__DIR__ . '/js/listado_usuarios.js') ?>"></script>
<?php finalizar_layout_configuracion(); ?>
