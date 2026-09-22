<?php
declare(strict_types=1);

require_once __DIR__ . '/_bootstrap.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

function ids_permisos(mixed $valor): array
{
    if (!is_array($valor)) {
        return [];
    }
    return array_values(array_unique(array_filter(
        array_map('intval', $valor),
        static fn (int $id): bool => $id > 0
    )));
}

function puede_administrar_permisos(Conexion $db, int $idUsuario): bool
{
    $perfilSesion = strtolower(trim((string) Sesion::get('perfil', '')));
    $perfilSesion = preg_replace('/[\s_-]+/', ' ', $perfilSesion) ?: '';
    if (in_array($perfilSesion, ['administrador', 'admin', 'super admin', 'superadmin'], true)) {
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

$csrf = (string) ($_POST['csrf'] ?? '');
$csrfSesion = (string) ($_SESSION['csrf_usuarios_permisos'] ?? '');
if ($csrfSesion === '' || !hash_equals($csrfSesion, $csrf)) {
    responder_json(['ok' => false, 'error' => 'La sesión de edición expiró. Recarga la página.'], 419);
}

$usuarioActual = (int) ($_SESSION['id'] ?? 0);
$usuarioObjetivo = (int) ($_POST['usuario_id'] ?? 0);
$menusRecibidos = ids_permisos($_POST['menus'] ?? []);
$submenusRecibidos = ids_permisos($_POST['submenus'] ?? []);

try {
    $db = Conexion::getInstance('sistema_panel_central');
    if (!puede_administrar_permisos($db, $usuarioActual)) {
        responder_json(['ok' => false, 'error' => 'No tienes autorización para modificar permisos.'], 403);
    }
    if ($usuarioObjetivo <= 0 || !$db->fetchOne('SELECT id FROM usuarios WHERE id = ? LIMIT 1', [$usuarioObjetivo])) {
        responder_json(['ok' => false, 'error' => 'El usuario indicado no existe.'], 404);
    }

    $estructuraSubmenus = $db->fetchOne(
        "SELECT COUNT(DISTINCT COLUMN_NAME) AS total
           FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'permiso_sub_menu'
            AND COLUMN_NAME IN ('id_permiso_sub', 'id_usuario', 'id_submenu', 'permiso')"
    );
    if ((int) ($estructuraSubmenus['total'] ?? 0) !== 4) {
        responder_json(['ok' => false, 'error' => 'La tabla permiso_sub_menu no está disponible o su estructura es incorrecta.'], 409);
    }

    $menusValidos = [];
    foreach ($db->fetchAll('SELECT id_menu FROM menu_1 WHERE id_menu > 0') as $menu) {
        $menusValidos[(int) $menu['id_menu']] = true;
    }
    $submenusValidos = [];
    foreach ($db->fetchAll('SELECT id_submenu, id_menu FROM menu_1_sub') as $submenu) {
        $idSubmenu = (int) $submenu['id_submenu'];
        $idMenu = (int) $submenu['id_menu'];
        if (isset($menusValidos[$idMenu])) {
            $submenusValidos[$idSubmenu] = $idMenu;
        }
    }

    $menusSeleccionados = [];
    foreach ($menusRecibidos as $idMenu) {
        if (isset($menusValidos[$idMenu])) {
            $menusSeleccionados[$idMenu] = true;
        }
    }
    $submenusSeleccionados = [];
    foreach ($submenusRecibidos as $idSubmenu) {
        if (isset($submenusValidos[$idSubmenu])) {
            $idMenuPadre = $submenusValidos[$idSubmenu];
            $submenusSeleccionados[$idSubmenu] = true;
            $menusSeleccionados[$idMenuPadre] = true;
        }
    }

    $pdo = $db->getPDO();
    $pdo->beginTransaction();
    try {
        // ✅ CORREGIDO: permisos_menu_1 (era permisos_menu_1)
        $db->execute(
            'DELETE FROM permisos_menu_1 WHERE id_usuario = ?',
            [$usuarioObjetivo]
        );
        foreach (array_keys($menusSeleccionados) as $idMenu) {
            // ✅ CORREGIDO: permisos_menu_1 (era permisos_menu_1)
            $db->execute(
                'INSERT INTO permisos_menu_1 (id_usuario, id_menu, estado) VALUES (?, ?, 1)',
                [$usuarioObjetivo, $idMenu]
            );
        }

        foreach ($submenusValidos as $idSubmenu => $_idMenu) {
            $permiso = isset($submenusSeleccionados[$idSubmenu]) ? 1 : 0;
            $registro = $db->fetchOne(
                'SELECT id FROM permiso_sub_menu WHERE id_usuario = ? AND id_submenu = ? LIMIT 1',
                [$usuarioObjetivo, $idSubmenu]
            );
            if ($registro) {
                $db->execute(
                    'UPDATE permiso_sub_menu SET estado = ? WHERE id_usuario = ? AND id_submenu = ?',
                    [$permiso, $usuarioObjetivo, $idSubmenu]
                );
            } else {
                $db->execute(
                    'INSERT INTO permiso_sub_menu (id_usuario, id_submenu, estado) VALUES (?, ?, ?)',
                    [$usuarioObjetivo, $idSubmenu, $permiso]
                );
            }
        }
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $ex;
    }

    error_log(sprintf(
        'Permisos actualizados: administrador=%d usuario=%d menus=%d submenus=%d',
        $usuarioActual,
        $usuarioObjetivo,
        count($menusSeleccionados),
        count($submenusSeleccionados)
    ));

    responder_json([
        'ok' => true,
        'mensaje' => 'Permisos guardados correctamente.',
        'menus_guardados' => count($menusSeleccionados),
        'submenus_guardados' => count($submenusSeleccionados),
    ]);
} catch (Throwable $ex) {
    error_log('Error al guardar permisos jerárquicos: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible guardar los permisos.'], 500);
}