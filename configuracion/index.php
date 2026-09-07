<?php
require_once __DIR__ . '/_inicio.php';
iniciar_layout_configuracion('Configuración', 'Configuración', 'index');
?>
<div class="page-header"><div><h1>Configuración</h1><p>Administración central del sistema.</p></div></div>
<div class="quick-grid">
  <a class="quick-card" href="listado_usuarios.php"><div class="top"><strong>Usuarios</strong></div><p>Listado, estado y permisos</p><small>Administrar cuentas</small></a>
  <a class="quick-card" href="mantenedor_colegios.php"><div class="top"><strong>Colegios</strong></div><p>Establecimientos activos</p><small>Consultar colegios</small></a>
  <a class="quick-card" href="admin_categorias.php"><div class="top"><strong>Categorías</strong></div><p>Categorías y técnicos</p><small>Administrar asignaciones</small></a>
  <a class="quick-card" href="mantenimiento_tickets.php"><div class="top"><strong>Tickets</strong></div><p>Herramientas de mantenimiento</p><small>Eliminar, recuperar o fusionar</small></a>
</div>
<?php finalizar_layout_configuracion(); ?>
