<?php
if (!isset($depth)) {
    $script = $_SERVER['SCRIPT_FILENAME'] ?? '';
    $root = realpath(__DIR__ . '/..');
    $rel = str_replace('\\', '/', str_replace((string) $root, '', dirname($script)));
    $depth = $rel && $rel !== '/' ? '../' : '';
}
$GLOBALS['depth'] = $depth;
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title><?= htmlspecialchars($titulo_pagina ?? 'Panel Central') ?> — SEDUC Chile</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"/>
<link rel="stylesheet" href="<?= $depth ?? '' ?>css/tokens.css"/>
<link rel="stylesheet" href="<?= $depth ?? '' ?>css/layout.css"/>
<link rel="stylesheet" href="<?= $depth ?? '' ?>css/components.css"/>
<?php if (!empty($con_charts)): ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<?php endif; ?>
<?php foreach (($pagina_estilos ?? []) as $estilo): ?>
<link rel="stylesheet" href="<?= ($depth ?? '') . htmlspecialchars((string) $estilo, ENT_QUOTES, 'UTF-8') ?>"/>
<?php endforeach; ?>
<?php foreach (($pagina_scripts_head ?? []) as $scriptHead): ?>
<script src="<?= htmlspecialchars((string) $scriptHead, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
</head>
<body>
