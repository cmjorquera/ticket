<?php
require_once __DIR__ . '/../clases/menu_lateral.php';
?>
<aside id="sidebar">
  <div class="sidebar-logo">
    <div class="logo-icon" aria-hidden="true">
      <img src="<?= htmlspecialchars(($GLOBALS['depth'] ?? '') . 'imagenes/logo_seduc.png', ENT_QUOTES, 'UTF-8') ?>" alt="">
    </div>
    <div class="logo-text">
      <strong>SEDUC Chile</strong>
      <span>Panel Central</span>
    </div>
  </div>

  <?php menu_lateral((int) $_SESSION['id'], $db, $pagina_actual); ?>
</aside>
