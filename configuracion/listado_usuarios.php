<?php
require_once __DIR__ . '/_inicio.php';

$depth = '../';
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
    <select class="input" id="filtro-area" style="max-width:220px" aria-label="Filtrar por área">
      <option value="">Todas las áreas</option>
    </select>
    <select class="input" id="filtro-estado" style="max-width:180px" aria-label="Filtrar por estado">
      <option value="">Todos los estados</option>
      <option value="activo">Activo</option>
      <option value="Pendiente">Pendiente</option>
      <option value="bloqueado">Bloqueado</option>
      <option value="inactivo">Inactivo</option>
    </select>
    <span class="toolbar-count"><strong id="total-usuarios">0</strong> usuarios</span>
  </div>
  <div class="table-wrap">
    <table id="tabla-usuarios">
      <thead>
        <tr><th>N°</th><th>Usuario</th><th>Email</th><th>Área</th><th>Colegio</th><th>Estado</th><th>Acciones</th></tr>
      </thead>
      <tbody>
        <tr><td colspan="7" class="text-muted">Cargando usuarios…</td></tr>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= $depth ?>configuracion/js/listado_usuarios.js"></script>
<?php finalizar_layout_configuracion(); ?>
