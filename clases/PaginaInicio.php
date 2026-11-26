<?php
declare(strict_types=1);

require_once __DIR__ . '/Conexion.php';

/**
 * Página de inicio personalizada por usuario (usuarios.pagina_inicio).
 *
 * Si la persona tiene una, el login la lleva ahí en vez de a contenedores.php
 * y el ítem "INICIO" del menú lateral apunta a esa página.
 * Solo se aceptan páginas de la lista PERMITIDAS (nunca una ruta libre).
 * Columna: agenda/sql/01_pagina_inicio.sql.
 */
final class PaginaInicio
{
    /** ruta (desde la raíz del sitio) => descripción */
    public const PERMITIDAS = [
        'agenda/index.php' => 'Agenda: eventos y almuerzos',
    ];

    /** Página de inicio del usuario, o null si usa la de siempre. */
    public static function de(Conexion $db, int $idUsuario): ?string
    {
        if ($idUsuario <= 0) {
            return null;
        }
        try {
            $hay = $db->fetchOne(
                "SELECT 1 FROM information_schema.COLUMNS
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'pagina_inicio'"
            );
            if (!$hay) {
                return null;
            }
            $fila = $db->fetchOne('SELECT pagina_inicio FROM usuarios WHERE id = ?', [$idUsuario]);
        } catch (Throwable $e) {
            error_log('[pagina_inicio] ' . $e->getMessage());
            return null;
        }
        $pagina = trim((string) ($fila['pagina_inicio'] ?? ''));
        return isset(self::PERMITIDAS[$pagina]) ? $pagina : null;
    }

    /** La de la sesión actual (se calcula una vez y queda en $_SESSION). */
    public static function deSesion(Conexion $db): ?string
    {
        if (!array_key_exists('pagina_inicio', $_SESSION)) {
            $_SESSION['pagina_inicio'] = self::de($db, (int) ($_SESSION['id'] ?? 0)) ?? '';
        }
        $p = (string) $_SESSION['pagina_inicio'];
        return isset(self::PERMITIDAS[$p]) ? $p : null;
    }
}
