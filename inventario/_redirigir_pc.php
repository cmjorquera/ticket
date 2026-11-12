<?php
/**
 * Inventario PC se movió de inventario/ a inventario/pc/ (rama qa, 2026-11).
 * Los archivos de esta carpeta con el mismo nombre que una página de
 * inventario/pc/ solo redirigen aquí, conservando los parámetros (?id=...).
 * Importante: los QR ya impresos apuntan a inventario/equipoQRinformacion.php.
 */
$pagina = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
if (!preg_match('/^[a-zA-Z0-9_]+\.php$/', $pagina) || !is_file(__DIR__ . '/pc/' . $pagina)) {
    $pagina = 'index.php';
}
$query = (string) ($_SERVER['QUERY_STRING'] ?? '');
header('Location: pc/' . $pagina . ($query !== '' ? '?' . $query : ''), true, 301);
exit;
