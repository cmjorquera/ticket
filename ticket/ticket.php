<?php
declare(strict_types=1);

$depth = '../';
require_once __DIR__ . '/../configuracion/_inicio.php';

$usuarioId = (int) Sesion::get('id', 0);
$usuarioNombre = (string) Sesion::get('nombre', 'Usuario');
$categorias = [];
$colegios = [];
$errorCarga = null;

try {
    $categorias = $db->fetchAll(
        'SELECT id_categoria, nombre_categoria FROM categoria_de_ticket WHERE estado = 1 ORDER BY orden ASC, nombre_categoria ASC'
    );
    if (es_administrador_global($usuarioId, $db)) {
        $colegios = $db->fetchAll('SELECT id_colegio, nom_colegio FROM colegio WHERE estado = 1 ORDER BY nom_colegio ASC');
    } else {
        $colegios = $db->fetchAll(
            'SELECT DISTINCT c.id_colegio, c.nom_colegio
               FROM usuario_colegio uc
               JOIN colegio c ON c.id_colegio = uc.id_colegio AND c.estado = 1
              WHERE uc.id_usuario = ? AND uc.estado = 1
           ORDER BY c.nom_colegio ASC',
            [$usuarioId]
        );
    }
} catch (Throwable $ex) {
    error_log('Error al cargar formulario de ticket: ' . $ex->getMessage());
    $errorCarga = 'No fue posible cargar categorías y colegios. Verifica la instalación de las tablas de tickets.';
}

$csrf = ticket_csrf_token();
iniciar_layout_configuracion('Crear ticket', 'Tickets', 'ticket');
?>
<link rel="stylesheet" href="<?= e($depth) ?>ticket/tickets.css">

<div class="page-header">
  <div>
    <h1>Crear nuevo ticket</h1>
    <p>Registra un problema o solicitud para el equipo de soporte.</p>
  </div>
  <div class="page-header-actions">
    <a class="btn btn-outline" href="ticket_asignados.php"><i class="bi bi-list-check"></i> Tickets asignados</a>
  </div>
</div>

<div class="ticket-shell">
  <section class="card ticket-intake" aria-labelledby="ticket-form-title">
    <aside class="ticket-intake__rail">
      <i class="bi bi-ticket-perforated-fill" aria-hidden="true"></i>
      <h2 id="ticket-form-title">Mesa de ayuda</h2>
      <p>Cuéntanos qué ocurre. La categoría determina el equipo técnico que recibirá el caso.</p>
      <div class="ticket-intake__steps" aria-label="Proceso del ticket">
        <div class="ticket-intake__step"><span>01</span> Describe el caso</div>
        <div class="ticket-intake__step"><span>02</span> Se asigna un técnico</div>
        <div class="ticket-intake__step"><span>03</span> Sigue su resolución</div>
      </div>
    </aside>

    <form class="ticket-form" id="form-ticket" novalidate>
      <input type="hidden" name="accion" value="crear">
      <input type="hidden" name="csrf" value="<?= e($csrf) ?>">

      <?php if ($errorCarga): ?>
        <div class="ticket-message error" style="display:block"><?= e($errorCarga) ?></div>
      <?php endif; ?>
      <div class="ticket-message" id="ticket-mensaje" role="status" aria-live="polite"></div>

      <div class="ticket-form-grid">
        <div class="form-group">
          <label class="form-label" for="ticket-usuario">Solicitante</label>
          <input class="form-input" id="ticket-usuario" value="<?= e($usuarioNombre) ?>" disabled>
        </div>
        <div class="form-group">
          <label class="form-label" for="ticket-prioridad">Prioridad</label>
          <select class="form-input" id="ticket-prioridad" name="prioridad">
            <option value="baja">Baja</option>
            <option value="media" selected>Media</option>
            <option value="alta">Alta</option>
            <option value="crítica">Crítica</option>
          </select>
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
        <div class="form-group">
          <label class="form-label" for="ticket-colegio">Colegio *</label>
          <select class="form-input" id="ticket-colegio" name="colegio_id" required <?= !$colegios ? 'disabled' : '' ?>>
            <option value="">Seleccionar colegio</option>
            <?php foreach ($colegios as $colegio): ?>
              <option value="<?= (int) $colegio['id_colegio'] ?>"><?= e($colegio['nom_colegio']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="ticket-asunto">Asunto *</label>
        <input class="form-input" id="ticket-asunto" name="asunto" maxlength="180" placeholder="Ej.: La impresora de secretaría no responde" required>
      </div>
      <div class="form-group">
        <label class="form-label" for="ticket-descripcion">Descripción *</label>
        <textarea class="form-input" id="ticket-descripcion" name="descripcion" maxlength="10000" placeholder="Indica qué ocurrió, desde cuándo y qué intentaste hacer." required></textarea>
      </div>

      <div class="ticket-form-actions">
        <span class="ticket-note"><i class="bi bi-shield-check"></i> Solo se muestran colegios asociados a tu cuenta.</span>
        <div class="flex gap-2">
          <button class="btn btn-outline" type="reset">Limpiar</button>
          <button class="btn btn-primary" id="ticket-guardar" type="submit" <?= ($errorCarga || !$categorias || !$colegios) ? 'disabled' : '' ?>>
            <i class="bi bi-send-fill"></i> Crear ticket
          </button>
        </div>
      </div>
    </form>
  </section>
</div>

<script>
(() => {
  const form = document.getElementById('form-ticket');
  const message = document.getElementById('ticket-mensaje');
  const button = document.getElementById('ticket-guardar');
  if (!form || !button) return;

  form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    button.disabled = true;
    button.innerHTML = '<i class="bi bi-hourglass-split"></i> Creando…';
    message.className = 'ticket-message';
    try {
      const response = await fetch('../ajax/guardar_ticket.php', {method: 'POST', body: new FormData(form), credentials: 'same-origin'});
      const data = await response.json();
      if (!response.ok || !data.ok) throw new Error(data.error || 'No fue posible crear el ticket.');
      message.className = 'ticket-message ok';
      message.textContent = `${data.mensaje} Folio #${data.ticket_id}.`;
      form.reset();
    } catch (error) {
      message.className = 'ticket-message error';
      message.textContent = error.message;
    } finally {
      button.disabled = false;
      button.innerHTML = '<i class="bi bi-send-fill"></i> Crear ticket';
    }
  });
})();
</script>

<?php finalizar_layout_configuracion(); ?>
