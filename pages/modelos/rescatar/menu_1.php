<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../ajax/_bootstrap.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    responder_json(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

function puede_gestionar_permisos_menu(Conexion $db, int $idUsuario): bool
{
    $perfil = strtolower(trim((string) Sesion::get('perfil', '')));
    $perfil = preg_replace('/[\s_-]+/', ' ', $perfil) ?: '';
    if (in_array($perfil, ['administrador', 'admin', 'super admin', 'superadmin'], true)) {
        return true;
    }

    return (bool) $db->fetchOne(
        "SELECT 1
           FROM usuario_perfil up
           JOIN perfiles p ON p.id_perfil = up.id_perfil
          WHERE up.id_usuario = ?
            AND (
                p.id_perfil = 3
                OR LOWER(REPLACE(REPLACE(TRIM(p.nombre), '_', ' '), '-', ' '))
                   IN ('administrador', 'admin', 'super admin', 'superadmin')
            )
          LIMIT 1",
        [$idUsuario]
    );
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $usuarioActual = (int) Sesion::get('id', 0);
    $usuarioObjetivo = max(0, (int) ($_GET['user_id'] ?? 0));

    if (!puede_gestionar_permisos_menu($db, $usuarioActual)) {
        responder_json(['ok' => false, 'error' => 'No tienes autorización para consultar permisos.'], 403);
    }
    if ($usuarioObjetivo <= 0 || !$db->fetchOne('SELECT id FROM usuarios WHERE id = ? LIMIT 1', [$usuarioObjetivo])) {
        responder_json(['ok' => false, 'error' => 'El usuario indicado no existe.'], 404);
    }

    if (empty($_SESSION['csrf_permisos_menu'])) {
        $_SESSION['csrf_permisos_menu'] = bin2hex(random_bytes(32));
    }

    $permisosMenu = [];
    foreach ($db->fetchAll(
        'SELECT id_menu1, id_tipo_permiso FROM permisos_menu_1 WHERE id_usuario = ?',
        [$usuarioObjetivo]
    ) as $permiso) {
        $permisosMenu[(int) $permiso['id_menu1']] = (int) $permiso['id_tipo_permiso'];
    }

    $permisosSubmenu = [];
    foreach ($db->fetchAll(
        'SELECT id_submenu, permiso FROM permiso_sub_menu WHERE id_usuario = ?',
        [$usuarioObjetivo]
    ) as $permiso) {
        $permisosSubmenu[(int) $permiso['id_submenu']] = (int) $permiso['permiso'];
    }

    $submenusPorMenu = [];
    foreach ($db->fetchAll(
        'SELECT id_submenu, id_menu, nombre, orden FROM menu_1_sub ORDER BY id_menu, CAST(orden AS UNSIGNED), id_submenu'
    ) as $submenu) {
        $idMenu = (int) $submenu['id_menu'];
        $idSubmenu = (int) $submenu['id_submenu'];
        $permisoExplicito = array_key_exists($idSubmenu, $permisosSubmenu);
        $submenusPorMenu[$idMenu][] = [
            'id_submenu' => $idSubmenu,
            'id_menu' => $idMenu,
            'nombre' => (string) $submenu['nombre'],
            'orden' => (int) $submenu['orden'],
            'permiso' => $permisoExplicito
                ? $permisosSubmenu[$idSubmenu]
                : (($permisosMenu[$idMenu] ?? 3) === 1 ? 1 : 0),
        ];
    }

    $menus = [];
    foreach ($db->fetchAll(
        'SELECT id_menu, nombre, orden FROM menu_1 WHERE id_menu > 0 ORDER BY CAST(orden AS UNSIGNED), id_menu'
    ) as $menu) {
        $idMenu = (int) $menu['id_menu'];
        $menus[] = [
            'id_menu' => $idMenu,
            'nombre' => (string) $menu['nombre'],
            'orden' => (int) $menu['orden'],
            'submenus' => $submenusPorMenu[$idMenu] ?? [],
            'id_tipo_permiso' => $permisosMenu[$idMenu] ?? 3,
        ];
    }

    responder_json([
        'ok' => true,
        'csrf' => (string) $_SESSION['csrf_permisos_menu'],
        'data' => $menus,
    ]);
} catch (Throwable $ex) {
    error_log('Error al rescatar permisos de menús: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible cargar los permisos.'], 500);
}
