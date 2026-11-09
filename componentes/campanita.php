<?php
/**
 * Campanita de notificaciones (barra superior). La usan componentes/topbar.php
 * e includes/header.php. Datos: ajax/notificaciones.php · js/notificaciones.js
 * Lógica: ticket/logica/LOGICA_NOTIFICACIONES.txt
 */
if (empty($_SESSION['csrf_notificaciones'])) {
    $_SESSION['csrf_notificaciones'] = bin2hex(random_bytes(16));
}
$baseCampanita = (string) ($depth ?? '');
$verNotif = static fn (string $f): int => (int) (@filemtime(__DIR__ . '/../' . $f) ?: 1);
?>
<link rel="stylesheet" href="<?= htmlspecialchars($baseCampanita) ?>css/notificaciones.css?v=<?= $verNotif('css/notificaciones.css') ?>">
<div class="notif" id="notif" data-base="<?= htmlspecialchars($baseCampanita) ?>" data-csrf="<?= htmlspecialchars((string) $_SESSION['csrf_notificaciones']) ?>">
  <button class="notif-btn" id="notif-btn" type="button" aria-label="Notificaciones" aria-haspopup="dialog" aria-expanded="false" aria-controls="notif-panel">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
    <span class="notif-badge" id="notif-badge" hidden>0</span>
  </button>
  <section class="notif-panel" id="notif-panel" role="dialog" aria-label="Notificaciones" hidden>
    <header class="notif-panel__cab">
      <strong>Notificaciones</strong>
      <button type="button" class="notif-panel__todas" id="notif-todas" hidden><i class="bi bi-check2-all"></i> Marcar todas como leídas</button>
    </header>
    <ol class="notif-lista" id="notif-lista" aria-live="polite"></ol>
  </section>
</div>
<script src="<?= htmlspecialchars($baseCampanita) ?>js/notificaciones.js?v=<?= $verNotif('js/notificaciones.js') ?>" defer></script>
