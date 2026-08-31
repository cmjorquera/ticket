<?php
/**
 * badge-estado.php
 * Componente reutilizable: badge de estado con punto de color.
 *
 * Uso:
 *   require_once __DIR__ . '/componentes/badge-estado.php';
 *   renderBadgeEstado($tipo, $label);   // $label es opcional
 *
 * Tipos reconocidos (case-insensitive): activo, pendiente, bloqueado, inactivo.
 * Cualquier otro valor cae en el estilo "inactivo" (gris).
 */

if (!function_exists('renderBadgeEstado')) {

    function renderBadgeEstado($tipo, $label = null): void
    {
        $tipo = strtolower(trim((string) $tipo));

        $mapa = [
            'activo'    => ['is-activo',    'Activo'],
            'pendiente' => ['is-pendiente', 'Pendiente'],
            'bloqueado' => ['is-bloqueado', 'Bloqueado'],
            'inactivo'  => ['is-inactivo',  'Inactivo'],
        ];

        [$clase, $textoDefault] = $mapa[$tipo] ?? ['is-inactivo', ucfirst($tipo !== '' ? $tipo : 'Sin estado')];

        $texto = ($label !== null && trim((string) $label) !== '') ? $label : $textoDefault;

        echo '<span class="lu-badge-estado ' . $clase . '">'
           . '<span class="lu-badge-estado__dot"></span>'
           . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8')
           . '</span>';
    }
}
