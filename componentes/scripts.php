<script src="<?= $depth ?? '' ?>js/sidebar.js"></script>
<?php foreach (($pagina_scripts ?? []) as $scriptPagina): ?>
<script src="<?= ($depth ?? '') . htmlspecialchars((string) $scriptPagina, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
<?php require __DIR__ . '/chat_flotante.php'; ?>
