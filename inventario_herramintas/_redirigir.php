<?php
/**
 * Este módulo se movió a inventario/herramientas/ (rama qa, 2026-11).
 * Los archivos de esta carpeta solo redirigen, conservando los parámetros,
 * para no romper el menú (si aún no se ejecuta inventario/sql/02_...) ni
 * enlaces guardados. Se puede borrar la carpeta cuando ya nadie la use.
 */
$pagina = basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
if (!preg_match('/^[a-zA-Z0-9_]+\.php$/', $pagina) || !is_file(__DIR__ . '/../inventario/herramientas/' . $pagina)) {
    $pagina = 'index.php';
}
$query = (string) ($_SERVER['QUERY_STRING'] ?? '');
header('Location: ../inventario/herramientas/' . $pagina . ($query !== '' ? '?' . $query : ''), true, 301);
exit;
