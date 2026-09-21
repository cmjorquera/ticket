<?php
declare(strict_types=1);

require_once __DIR__ . '/../../../ajax/_bootstrap.php';
Sesion::requerir();

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    responder_json(['ok' => false, 'error' => 'Método no permitido.'], 405);
}

function ids_seleccionados(mixed $valor): array
{
    if (is_string($valor)) {
        $decodificado = json_decode($valor, true);
        $valor = is_array($decodificado) ? $decodificado : [];
    }
    if (!is_array($valor)) {
        return [];
    }

    $ids = [];
    foreach ($valor as $item) {
        if (is_array($item)) {
            $habilitado = !isset($item['id_tipo_permiso']) || (int) $item['id_tipo_permiso'] === 1;
            $id = (int) ($item['id_menu1'] ?? $item['id_menu'] ?? $item['id_submenu'] ?? 0);
            if (!$habilitado) {
                continue;
            }
        } else {
            $id = (int) $item;
        }
        if ($id > 0) {
            $ids[$id] = true;
        }
    }
    return array_keys($ids);
}

function puede_guardar_permisos_menu(Conexion $db, int $idUsuario): bool
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

$csrf = (string) ($_POST['csrf'] ?? '');
$csrfSesion = (string) ($_SESSION['csrf_permisos_menu'] ?? '');
if ($csrfSesion === '' || !hash_equals($csrfSesion, $csrf)) {
    responder_json(['ok' => false, 'error' => 'La sesión de edición expiró. Recarga los permisos.'], 419);
}

try {
    $db = Conexion::getInstance('sistema_panel_central');
    $usuarioActual = (int) Sesion::get('id', 0);
    $usuarioObjetivo = max(0, (int) ($_POST['user_id'] ?? $_POST['id_usuario'] ?? 0));
    $menusRecibidos = ids_seleccionados($_POST['menus'] ?? $_POST['permisos'] ?? []);
    $submenusRecibidos = ids_seleccionados($_POST['submenus'] ?? []);

    if (!puede_guardar_permisos_menu($db, $usuarioActual)) {
        responder_json(['ok' => false, 'error' => 'No tienes autorización para modificar permisos.'], 403);
    }
    if ($usuarioObjetivo <= 0 || !$db->fetchOne('SELECT id FROM usuarios WHERE id = ? LIMIT 1', [$usuarioObjetivo])) {
        responder_json(['ok' => false, 'error' => 'El usuario indicado no existe.'], 404);
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
            $submenusSeleccionados[$idSubmenu] = true;
            $menusSeleccionados[$submenusValidos[$idSubmenu]] = true;
        }
    }

    $pdo = $db->getPDO();
    $pdo->beginTransaction();
    try {
        foreach (array_keys($menusValidos) as $idMenu) {
            $tipoPermiso = isset($menusSeleccionados[$idMenu]) ? 1 : 3;
            $existe = $db->fetchOne(
                'SELECT 1 AS existe FROM permisos_menu_1 WHERE id_usuario = ? AND id_menu1 = ? LIMIT 1',
                [$usuarioObjetivo, $idMenu]
            );
            if ($existe) {
                $db->execute(
                    'UPDATE permisos_menu_1 SET id_tipo_permiso = ? WHERE id_usuario = ? AND id_menu1 = ?',
                    [$tipoPermiso, $usuarioObjetivo, $idMenu]
                );
            } else {
                $db->execute(
                    'INSERT INTO permisos_menu_1 (id_menu1, id_usuario, id_tipo_permiso) VALUES (?, ?, ?)',
                    [$idMenu, $usuarioObjetivo, $tipoPermiso]
                );
            }
        }

        foreach ($submenusValidos as $idSubmenu => $_idMenu) {
            $permiso = isset($submenusSeleccionados[$idSubmenu]) ? 1 : 0;
            $existe = $db->fetchOne(
                'SELECT 1 AS existe FROM permiso_sub_menu WHERE id_usuario = ? AND id_submenu = ? LIMIT 1',
                [$usuarioObjetivo, $idSubmenu]
            );
            if ($existe) {
                $db->execute(
                    'UPDATE permiso_sub_menu SET permiso = ?, fecha_modificacion = CURRENT_TIMESTAMP WHERE id_usuario = ? AND id_submenu = ?',
                    [$permiso, $usuarioObjetivo, $idSubmenu]
                );
            } else {
                $db->execute(
                    'INSERT INTO permiso_sub_menu (id_usuario, id_submenu, permiso) VALUES (?, ?, ?)',
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
        'Permisos heredados actualizados: administrador=%d usuario=%d menus=%d submenus=%d',
        $usuarioActual,
        $usuarioObjetivo,
        count($menusSeleccionados),
        count($submenusSeleccionados)
    ));

    responder_json(['ok' => true, 'mensaje' => 'Permisos guardados']);
} catch (Throwable $ex) {
    error_log('Error al guardar permisos heredados: ' . $ex->getMessage());
    responder_json(['ok' => false, 'error' => 'No fue posible guardar los permisos.'], 500);
}
