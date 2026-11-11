<?php
require_once __DIR__ . '/_inicio.php';

$depth = '../';
$usuarioActual = (int) Sesion::get('id', 0);
$perfilActual = strtolower(trim((string) Sesion::get('perfil', '')));
$perfilActual = preg_replace('/[\s_-]+/', ' ', $perfilActual) ?: '';
$esSuperAdmin = in_array($perfilActual, ['super admin', 'superadmin'], true)
    || (bool) $db->fetchOne(
        'SELECT 1 FROM usuario_perfil WHERE id_usuario = ? AND id_perfil = 3 LIMIT 1',
        [$usuarioActual]
    );
if (empty($_SESSION['csrf_editar_usuario_avanzado'])) {
    $_SESSION['csrf_editar_usuario_avanzado'] = bin2hex(random_bytes(32));
}
if (empty($_SESSION['csrf_departamentos'])) {
    $_SESSION['csrf_departamentos'] = bin2hex(random_bytes(32));
}
$pagina_estilos = [
    'css/listado_usuarios.css?v=' . (string) filemtime(__DIR__ . '/../css/listado_usuarios.css'),
    'configuracion/css/alta_usuario.css?v=' . (string) filemtime(__DIR__ . '/css/alta_usuario.css'),
    'configuracion/css/permisos_usuario.css?v=' . (string) filemtime(__DIR__ . '/css/permisos_usuario.css'),
];
iniciar_layout_configuracion('Listado de usuarios', 'Usuarios', 'listado_usuarios');
?>
<div class="page-header">
  <div>
    <h1>Listado de usuarios</h1>
    <p>Administra cuentas, permisos y estado de acceso, organizados por colegio.</p>
  </div>
  <div class="page-header-actions">
    <?php if ($esSuperAdmin): ?>
    <button type="button" class="btn btn-primary" id="btn-comunicaciones"><i class="bi bi-send"></i> Enviar comunicado</button>
    <?php endif; ?>
    <button type="button" class="btn btn-outline" id="btn-descargar-usuarios" title="Descargar los usuarios que coinciden con los filtros actuales">
      <i class="bi bi-file-earmark-pdf"></i> Descargar PDF
    </button>
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
      <button type="button" class="btn-view" data-view="organigrama" title="Vista agrupada por colegio" aria-label="Agrupar usuarios por colegio" aria-pressed="false">
        <i class="bi bi-diagram-3" aria-hidden="true"></i>
      </button>
    </div>
    <?php if ($esSuperAdmin): ?>
    <select class="input" id="filtro-colegio" style="max-width:240px" aria-label="Filtrar por colegio">
      <option value="">Todos los colegios</option>
    </select>
    <?php endif; ?>
    <select class="input" id="filtro-area" style="max-width:220px" aria-label="Filtrar por área">
      <option value="">Todas las áreas</option>
    </select>
    <label class="toolbar-check" id="wrap-expandir" hidden title="Abre o cierra todos los departamentos de la vista por colegio">
      <input type="checkbox" id="chk-expandir" checked> Departamentos abiertos
    </label>
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
  <div id="vista-organigrama" class="org-view" data-administrator-id="<?= $usuarioActual ?>" data-super-admin="<?= $esSuperAdmin ? '1' : '0' ?>" data-department-csrf="<?= e((string) $_SESSION['csrf_departamentos']) ?>" hidden aria-live="polite"></div>
</div>

<p id="usuarios-feedback" class="text-sm" role="status" hidden style="margin-top:12px"></p>

<?php if ($esSuperAdmin): ?>
<div class="modal-overlay" id="modal-confirmar-prueba" onclick="closeModalOutside(event,'modal-confirmar-prueba')"><div class="modal" style="max-width:440px" role="dialog" aria-modal="true" aria-labelledby="com-confirmar-titulo"><div class="modal-header"><h3 id="com-confirmar-titulo">Confirmar correo de prueba</h3><p>Se enviará una prueba únicamente a tu correo institucional.</p></div><div class="modal-body"><p>Destinatario: <strong id="com-correo-propio"></strong></p></div><div class="modal-footer"><button type="button" class="btn btn-outline" id="com-cancelar-prueba">Cancelar</button><button type="button" class="btn btn-primary" id="com-confirmar-prueba">Enviar prueba</button></div></div></div>
<div class="modal-overlay" id="modal-comunicaciones" onclick="closeModalOutside(event,'modal-comunicaciones')">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="com-titulo" style="max-width:1050px;width:95vw">
    <div class="modal-header"><h3 id="com-titulo">Enviar comunicado institucional</h3><p>Personaliza el anuncio del Sistema de Centralización v2.0 y envía primero una prueba.</p></div>
    <div class="modal-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));gap:20px;max-height:72vh;overflow:auto">
      <section>
        <label class="form-label" for="com-modo">Destinatarios</label>
        <select class="form-input" id="com-modo"><option value="todos">Todos los usuarios activos</option><option value="colegio">Por colegio</option><option value="area">Por área</option><option value="manual">Selección manual</option></select>
        <select class="form-input" id="com-grupo" style="margin-top:10px" hidden></select>
        <div id="com-manual-wrap" hidden style="margin-top:10px"><input type="search" class="form-input" id="com-buscar" placeholder="Buscar usuario o correo"><label style="display:block;margin:10px 0"><input type="checkbox" id="com-todos-manual"> Seleccionar todos los usuarios visibles</label><div id="com-manual" style="max-height:190px;overflow:auto;border:1px solid #d9e2ef;border-radius:8px;padding:8px"></div></div>
        <p class="text-sm" id="com-contador" style="margin:10px 0">Cargando destinatarios…</p>
        <label class="form-label" for="com-asunto">Asunto</label>
        <input class="form-input" id="com-asunto" maxlength="150" value="Nueva versión del Sistema de Centralización – v2.0">
        <label class="form-label" for="com-mensaje" style="margin-top:12px">Mensaje</label>
        <textarea class="form-input" id="com-mensaje" rows="7" maxlength="3000">Te informamos que ya se encuentra disponible la versión 2.0 del Sistema de Centralización de SEDUC. Esta plataforma reúne en un mismo lugar la gestión de tickets, inventario de equipos, licenciamientos y otras herramientas institucionales. Ingresa con tus credenciales habituales para conocer las mejoras.</textarea>
        <p id="com-estado" role="status" class="text-sm"></p>
      </section>
      <section><strong>Vista previa del correo</strong><div id="com-preview" style="margin-top:10px;padding:20px;background:#fff;border:1px solid #d9e2ef;border-radius:10px;font-family:Arial,sans-serif;color:#26364b"><img src="https://www.siae.cl/email/archivos/1FiVR9m09zWTnZ4IdW1g/header_siae_seduc.png" alt="SEDUC" style="width:100%;height:auto"><div style="height:4px;background:linear-gradient(90deg,#3A913F 50%,#EAAA00 50%,#EAAA00 75%,#005587 75%)"></div><p style="text-align:center;font-size:12px;color:#005587">SISTEMA DE CENTRALIZACIÓN</p><h3 style="text-align:center;color:#003B6F">Nueva versión disponible · v2.0</h3><p>Hola <strong id="com-preview-nombre">Cristian Jorquera</strong>,</p><p id="com-preview-mensaje" style="white-space:pre-line;line-height:1.6"></p><p style="text-align:center"><span style="background:#003B6F;color:white;padding:12px 16px;border-radius:5px;display:inline-block">Acceder al Sistema de Centralización</span></p><img src="https://www.siae.cl/email/archivos/IuNYrJlxxZN9U0v254lx/Footer_colegios.png" alt="Colegios SEDUC" style="width:100%;height:auto"></div></section>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-outline" onclick="closeModal('modal-comunicaciones')">Cancelar</button><button type="button" class="btn btn-outline" id="com-prueba">Enviar prueba a mi correo</button><button type="button" class="btn btn-primary" id="com-enviar" disabled>Enviar comunicado</button></div>
  </div>
</div>
<?php endif; ?>

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

<!-- Modal para confirmar correo masivo por colegio -->
<div class="modal-overlay" id="modal-correo-colegio" onclick="closeModalOutside(event, 'modal-correo-colegio')">
  <div class="modal" style="max-width:460px" role="dialog" aria-modal="true" aria-labelledby="modal-correo-titulo">
    <div class="modal-header">
      <h3 id="modal-correo-titulo">¿Enviar correo a todos los usuarios?</h3>
      <p id="modal-correo-subtitulo"></p>
    </div>
    <div class="modal-body">
      <div class="org-mail-notice"><i class="bi bi-envelope-check" aria-hidden="true"></i><p>Se enviará una notificación individual a cada dirección válida del colegio.</p></div>
      <p id="modal-correo-error" class="text-sm" hidden style="color:var(--danger);margin-top:12px"></p>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" onclick="closeModal('modal-correo-colegio')">Cancelar</button>
      <button type="button" class="btn btn-primary" id="modal-correo-confirmar"><i class="bi bi-send"></i> Sí, enviar correo</button>
    </div>
  </div>
</div>

<!-- Modal para crear o editar usuarios -->
<div class="modal-overlay" id="modal-usuario" data-super-admin="<?= $esSuperAdmin ? '1' : '0' ?>" data-csrf="<?= e((string) $_SESSION['csrf_editar_usuario_avanzado']) ?>" onclick="closeModalOutside(event, 'modal-usuario')">
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
            <div class="form-group" id="u-perfil-group"><label class="form-label" for="u-perfil">Perfil *</label><select class="form-input" id="u-perfil"><option value="">Seleccionar</option></select></div>
            <?php if ($esSuperAdmin): ?>
              <div class="form-group" id="u-perfiles-multiples-group" hidden><span class="form-label">Perfiles *</span><div id="u-perfiles-multiples" class="perfil-permisos-list"></div></div>
            <?php endif; ?>
            <div class="form-group" id="u-colegio-group"><label class="form-label" for="u-colegio">Colegio</label><select class="form-input" id="u-colegio"><option value="">Sin colegio</option></select></div>
            <div class="form-group" id="u-departamento-group"><label class="form-label" for="u-departamento">Departamento</label><select class="form-input" id="u-departamento" disabled><option value="">Selecciona primero un colegio</option></select></div>
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

<!-- Nuevo usuario (modelo de roles: configuracion/logica/LOGICA_ROLES.txt) -->
<dialog class="alta" id="dlgAlta" aria-labelledby="altaTitulo">
  <form class="alta__box" id="formAlta" novalidate autocomplete="off">
    <header class="alta__head">
      <div>
        <h2 id="altaTitulo"><i class="bi bi-person-plus"></i> Nuevo usuario</h2>
        <p>Le llegará un <strong>correo con un enlace</strong> para que elija su contraseña.</p>
      </div>
      <button type="button" class="btn-icon" data-alta-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
    </header>

    <div class="alta__cuerpo" id="altaFormulario">
      <section class="alta__col">
        <h3><span>1</span> Datos de la persona</h3>
        <div class="alta__grid">
          <label class="alta__campo"><span>Nombre *</span><input name="nombre" maxlength="100" required></label>
          <label class="alta__campo"><span>Apellido paterno *</span><input name="apellido_paterno" maxlength="100" required></label>
          <label class="alta__campo"><span>Apellido materno</span><input name="apellido_materno" maxlength="100"></label>
          <label class="alta__campo"><span>Email *</span><input name="email" type="email" maxlength="100" required placeholder="nombre@colegio.cl"></label>
          <label class="alta__campo"><span>Teléfono</span><input name="telefono" type="tel" maxlength="15" placeholder="+56 9 1234 5678"></label>
          <label class="alta__campo"><span>Anexo</span><input name="anexo" inputmode="numeric" maxlength="10"></label>
          <label class="alta__campo alta__campo--ancho"><span>Cargo (texto)</span><input name="cargo" maxlength="100" placeholder="Ej: Profesor de Matemáticas"></label>
          <label class="alta__campo"><span>Sexo</span>
            <select name="sexo"><option value="0">Sin indicar</option><option value="1">Masculino</option><option value="2">Femenino</option></select>
          </label>
        </div>
      </section>

      <section class="alta__col">
        <h3><span>2</span> Dónde trabaja</h3>
        <label class="alta__campo">
          <span>Colegio *</span>
          <div class="alta__colegio">
            <span class="alta__escudo" id="altaEscudo" aria-hidden="true"><i class="bi bi-building"></i></span>
            <select name="id_colegio" id="altaColegio" required></select>
          </div>
        </label>
        <div class="alta__campo">
          <span>Cargo en el colegio *</span>
          <div class="alta__roles" id="altaRoles" role="radiogroup" aria-label="Cargo en el colegio"></div>
        </div>
        <label class="alta__campo" id="altaDepWrap">
          <span>Departamento *</span>
          <select name="id_departamento_colegio" id="altaDep"></select>
          <small class="alta__ayuda" id="altaDepAyuda"></small>
        </label>
        <div class="alta__campo">
          <span>Además puede</span>
          <label class="alta__check"><input type="checkbox" name="tecnico" id="altaTecnico"> <span><strong>Atender tickets</strong> (técnico)</span></label>
          <label class="alta__check" id="altaSuperWrap" hidden><input type="checkbox" name="super_admin" id="altaSuper"> <span><strong>Super administrador</strong> · ve y administra todo</span></label>
        </div>
        <div class="alta__resumen" id="altaResumen" aria-live="polite"></div>
      </section>
    </div>

    <div class="alta__exito" id="altaExito" hidden>
      <div class="alta__exito-ico"><i class="bi bi-check-lg"></i></div>
      <h3 id="altaExitoTitulo">Usuario creado</h3>
      <p id="altaExitoTexto"></p>
      <p class="alta__correo" id="altaCorreo"></p>
      <div class="alta__enlace">
        <span>Enlace de activación</span>
        <input type="text" id="altaEnlace" readonly aria-label="Enlace de activación">
        <button type="button" class="btn btn-outline btn-sm" id="altaCopiar"><i class="bi bi-clipboard"></i> Copiar</button>
      </div>
      <p class="alta__nota"><i class="bi bi-info-circle"></i> El enlace vence en 7 días. Mientras no lo use, su estado será <strong>Pendiente</strong> y podrás reenviarlo desde el listado.</p>
    </div>

    <p class="alta__error" id="altaError" role="alert" hidden></p>
    <footer class="alta__pie" id="altaPie">
      <button type="button" class="btn btn-outline" data-alta-cerrar>Cancelar</button>
      <button type="submit" class="btn btn-primary" id="altaCrear"><i class="bi bi-person-check"></i> Crear usuario</button>
    </footer>
    <footer class="alta__pie" id="altaPieExito" hidden>
      <button type="button" class="btn btn-outline" id="altaOtro"><i class="bi bi-person-plus"></i> Crear otro</button>
      <button type="button" class="btn btn-primary" data-alta-cerrar>Listo</button>
    </footer>
  </form>
</dialog>

<!-- Permisos en panel lateral (entra de derecha a izquierda). Carga usuarios_permisos.php?fragmento=1 -->
<dialog class="oc-perm" id="dlgPermisos" aria-labelledby="ocPermTitulo">
  <div class="oc-perm__box">
    <header class="oc-perm__head">
      <span class="user-avatar oc-perm__avatar" id="ocPermAvatar" aria-hidden="true"></span>
      <div class="oc-perm__titulo">
        <h2 id="ocPermTitulo">Permisos</h2>
        <p id="ocPermSub"></p>
      </div>
      <a class="btn-icon" id="ocPermPagina" href="#" title="Abrir en página completa" aria-label="Abrir en página completa"><i class="bi bi-box-arrow-up-right"></i></a>
      <button type="button" class="btn-icon" data-oc-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
    </header>
    <div class="oc-perm__cuerpo" id="ocPermCuerpo"></div>
  </div>
</dialog>

<?php require __DIR__ . '/../componentes/modal_agregar_departamento.php'; ?>

<script src="<?= $depth ?>js/permisos.js"></script>
<script src="<?= $depth ?>js/paginacion.js"></script>
<script src="<?= $depth ?>configuracion/js/usuarios_acordeon.js?v=<?= (int) filemtime(__DIR__ . '/js/usuarios_acordeon.js') ?>"></script>
<script src="<?= $depth ?>configuracion/js/listado_usuarios.js?v=<?= (int) filemtime(__DIR__ . '/js/listado_usuarios.js') ?>"></script>
<script src="<?= $depth ?>configuracion/js/permisos_usuario.js?v=<?= (int) filemtime(__DIR__ . '/js/permisos_usuario.js') ?>"></script>
<script src="<?= $depth ?>configuracion/js/alta_usuario.js?v=<?= (int) filemtime(__DIR__ . '/js/alta_usuario.js') ?>"></script>
<?php if ($esSuperAdmin): ?><script src="<?= $depth ?>configuracion/js/comunicaciones.js?v=<?= (int) filemtime(__DIR__ . '/js/comunicaciones.js') ?>"></script><?php endif; ?>
<?php finalizar_layout_configuracion(); ?>
