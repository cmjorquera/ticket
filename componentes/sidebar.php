<?php
require_once __DIR__ . '/../clases/menu_lateral.php';
?>
<aside id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon" aria-hidden="true">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
    </div>
    <div class="logo-text">
      <strong>SEDUC Chile</strong>
      <span>Panel Central</span>
    </div>
  </div>

  <?php menu_lateral((int) $_SESSION['id'], $db, $pagina_actual); ?>
</aside>
