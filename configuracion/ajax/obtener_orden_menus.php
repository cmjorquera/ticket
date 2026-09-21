<?php
declare(strict_types=1);

require_once __DIR__ . '/../../ajax/_bootstrap.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    responder_json(['error' => 'Método no permitido.'], 405);
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $menus = [];
    $submenus = [];

    foreach ($db->fetchAll(
        'SELECT id_menu, nombre, orden FROM menu_1 WHERE id_menu > 0 ORDER BY CAST(orden AS UNSIGNED), id_menu'
    ) as $menu) {
        $menus[(string) (int) $menu['id_menu']] = [
            'nombre' => (string) $menu['nombre'],
            'orden' => (int) $menu['orden'],
        ];
    }

    foreach ($db->fetchAll(
        'SELECT id_submenu, id_menu, nombre, orden FROM menu_1_sub ORDER BY id_menu, CAST(orden AS UNSIGNED), id_submenu'
    ) as $submenu) {
        $submenus[(string) (int) $submenu['id_submenu']] = [
            'id_menu' => (int) $submenu['id_menu'],
            'nombre' => (string) $submenu['nombre'],
            'orden' => (int) $submenu['orden'],
        ];
    }

    responder_json([
        'menus' => (object) $menus,
        'submenus' => (object) $submenus,
    ]);
} catch (Throwable $ex) {
    error_log('Error al obtener el orden de menús: ' . $ex->getMessage());
    responder_json(['error' => 'No fue posible obtener el orden de los menús.'], 500);
}
