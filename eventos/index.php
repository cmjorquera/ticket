<?php
/**
 * Eventos: calendario institucional tipo Google Calendar (mes / semana / día / agenda).
 * Crear (clic en un día o arrastrando horas), mover arrastrando, invitar personas
 * (reciben correo con invitación .ics y responden Sí / No / Quizás) y logística.
 * Tablas: eventos/sql/01_eventos.sql · Lógica: eventos/logica/LOGICA.txt
 */
header('Content-Type: text/html; charset=UTF-8');
require_once __DIR__ . '/../validar_sesion.php';
require_once __DIR__ . '/class/Eventos.php';

$actor = (int) ($_SESSION['id'] ?? 0);
$db = Conexion::getInstance('sistema_panel_central');
$ev = new Eventos($db);
if (empty($_SESSION['csrf_eventos'])) {
    $_SESSION['csrf_eventos'] = bin2hex(random_bytes(32));
}
$instalado = (bool) $db->fetchOne(
    "SELECT 1 FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'eventos_invitados'"
);
$colegios = $ev->colegiosParaCrear($actor);
$yo = $db->fetchOne("SELECT id, TRIM(CONCAT_WS(' ', nombre, apellido_paterno)) AS nombre, email FROM usuarios WHERE id = ?", [$actor]) ?: [];

$v = static fn (string $f): string => (string) (@filemtime(__DIR__ . '/' . $f) ?: 1);
$pagina_titulo = 'Eventos';
$pagina_estilos = ['eventos/css/eventos.css?v=' . $v('css/eventos.css')];
$pagina_scripts = [
    'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js',
    'https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/es.global.min.js',
    'eventos/js/eventos.js?v=' . $v('js/eventos.js'),
];
require_once __DIR__ . '/../includes/header.php';
$e = static fn ($s): string => htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
?>
<div class="ev" id="evApp">
  <div class="page-header ev-cab">
    <div>
      <h1>Eventos y calendario</h1>
      <p>Agenda institucional SEDUC. Crea un evento con un clic en el calendario; los invitados reciben la invitación en su correo.</p>
    </div>
    <button class="btn btn-primary" type="button" id="evNuevo" <?= $instalado ? '' : 'disabled' ?>><i class="bi bi-plus-lg"></i> Nuevo evento</button>
  </div>

  <?php if (!$instalado): ?>
    <div class="ev-aviso"><i class="bi bi-database-exclamation"></i> Falta instalar el módulo en la base de datos: ejecutar <code>eventos/sql/01_eventos.sql</code>.</div>
  <?php else: ?>
  <div class="ev-layout">
    <section class="card ev-cal-card">
      <div class="ev-filtros">
        <div class="ev-tipos" role="group" aria-label="Tipos de evento">
          <?php foreach (Eventos::TIPOS as $clave => [$nombre, $color]): ?>
            <button type="button" class="ev-chip is-on" data-tipo="<?= $e($clave) ?>" style="--c:<?= $e($color) ?>"><span></span><?= $e($nombre) ?></button>
          <?php endforeach; ?>
        </div>
        <div class="ev-filtros__der">
          <?php if (count($colegios) > 1): ?>
            <select class="form-input ev-select" id="evFiltroColegio" aria-label="Colegio">
              <option value="">Todos los colegios</option>
              <?php foreach ($colegios as $c): ?><option value="<?= (int) $c['id'] ?>"><?= $e($c['nombre']) ?></option><?php endforeach; ?>
            </select>
          <?php endif; ?>
          <label class="ev-check"><input type="checkbox" id="evMios"> Solo mis eventos</label>
          <label class="ev-check"><input type="checkbox" id="evCancelados"> Ver cancelados</label>
        </div>
      </div>
      <div id="evCalendario" class="ev-calendario"></div>
    </section>
    <aside class="card ev-proximos">
      <h2><i class="bi bi-calendar-week"></i> Próximos 14 días</h2>
      <ol id="evProximos" class="ev-proximos__lista"><li class="ev-vacio">Cargando…</li></ol>
    </aside>
  </div>
  <?php endif; ?>
</div>

<!-- Crear / editar -->
<div class="modal-overlay" id="evModal" onclick="closeModalOutside(event,'evModal')">
  <div class="modal ev-modal" role="dialog" aria-modal="true" aria-labelledby="evModalTitulo">
    <form id="evForm" novalidate>
      <div class="modal-header"><h3 id="evModalTitulo">Nuevo evento</h3><p>Los invitados recibirán una invitación de calendario en su correo.</p></div>
      <div class="modal-body ev-form">
        <input type="hidden" name="id">
        <input class="ev-titulo-input" name="titulo" maxlength="255" placeholder="Agregar título" required aria-label="Título">
        <div class="ev-fila ev-fechas">
          <i class="bi bi-clock"></i>
          <div class="ev-fechas__grid">
            <input class="form-input" type="date" name="fecha_inicio" required aria-label="Fecha de inicio">
            <input class="form-input ev-hora" type="time" name="hora_inicio" aria-label="Hora de inicio" step="300">
            <span class="ev-guion">–</span>
            <input class="form-input ev-hora" type="time" name="hora_fin" aria-label="Hora de término" step="300">
            <input class="form-input" type="date" name="fecha_fin" aria-label="Fecha de término">
            <label class="ev-check"><input type="checkbox" name="todo_el_dia" value="1"> Todo el día</label>
          </div>
        </div>
        <div class="ev-fila"><i class="bi bi-geo-alt"></i><input class="form-input" name="ubicacion" maxlength="150" placeholder="Agregar ubicación (sala, colegio, enlace de videollamada…)"></div>
        <div class="ev-fila ev-dos">
          <i class="bi bi-tag"></i>
          <select class="form-input" name="tipo_evento" aria-label="Tipo"><?php foreach (Eventos::TIPOS as $clave => [$nombre]): ?><option value="<?= $e($clave) ?>"><?= $e($nombre) ?></option><?php endforeach; ?></select>
          <select class="form-input" name="id_colegio" aria-label="Colegio"><?php foreach ($colegios as $c): ?><option value="<?= (int) $c['id'] ?>"><?= $e($c['nombre']) ?></option><?php endforeach; ?></select>
        </div>
        <div class="ev-fila"><i class="bi bi-person-badge"></i>
          <div class="ev-persona">
            <input type="hidden" name="responsable_id">
            <input class="form-input" id="evResponsable" placeholder="Responsable" autocomplete="off" aria-label="Responsable">
            <ul class="ev-sugerencias" id="evRespSug" hidden></ul>
          </div>
        </div>
        <div class="ev-fila"><i class="bi bi-people"></i>
          <div class="ev-persona">
            <div class="ev-chips" id="evInvitados"></div>
            <input class="form-input" id="evInvitar" placeholder="Agregar invitados: nombre o correo (Enter para agregar un correo externo)" autocomplete="off" aria-label="Agregar invitados">
            <ul class="ev-sugerencias" id="evInvSug" hidden></ul>
          </div>
        </div>
        <div class="ev-fila"><i class="bi bi-text-left"></i><textarea class="form-input" name="descripcion" rows="3" maxlength="5000" placeholder="Agregar descripción"></textarea></div>
        <details class="ev-logistica">
          <summary><i class="bi bi-speaker"></i> Logística (audio, presentación, asistentes)</summary>
          <div class="ev-logistica__grid">
            <label class="ev-check"><input type="checkbox" name="con_audio" value="1"> Audio / micrófono</label>
            <label class="ev-check"><input type="checkbox" name="solo_presentacion" value="1"> Presentación (proyector)</label>
            <label class="ev-check"><input type="checkbox" name="musica_ambiental" value="1"> Música ambiental</label>
            <label>Asistentes <input class="form-input" type="number" name="cantidad_personas" min="0" max="100000" value="0"></label>
            <textarea class="form-input" name="observaciones_logisticas" rows="2" maxlength="3000" placeholder="Observaciones para el equipo de soporte"></textarea>
          </div>
        </details>
        <div class="ticket-message" id="evFormMsg" role="status"></div>
      </div>
      <div class="modal-footer">
        <span class="ev-nota" id="evNotaCorreo"></span>
        <button class="btn btn-outline" type="button" onclick="closeModal('evModal')">Cancelar</button>
        <button class="btn btn-primary" type="submit" id="evGuardar"><i class="bi bi-check2"></i> Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Detalle (panel lateral) -->
<div class="ev-detalle" id="evDetalle" hidden>
  <button class="ev-detalle__fondo" type="button" data-ev-cerrar aria-label="Cerrar"></button>
  <aside class="ev-detalle__caja" role="dialog" aria-modal="true" aria-labelledby="evDetTitulo" tabindex="-1">
    <header class="ev-detalle__cab">
      <div class="ev-detalle__acciones" id="evDetAcciones"></div>
      <button class="ev-x" type="button" data-ev-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
    </header>
    <div class="ev-detalle__cuerpo" id="evDetCuerpo"></div>
  </aside>
</div>

<script>window.EV = <?= json_encode([
    'csrf' => $_SESSION['csrf_eventos'],
    'yo' => ['id' => $actor, 'nombre' => (string) ($yo['nombre'] ?? ''), 'email' => (string) ($yo['email'] ?? '')],
    'tipos' => array_map(static fn ($t) => ['nombre' => $t[0], 'color' => $t[1]], Eventos::TIPOS),
    'instalado' => $instalado,
    'abrir' => (int) ($_GET['evento'] ?? 0),
], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
