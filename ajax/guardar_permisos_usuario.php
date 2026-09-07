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
    $perfilSesion = strtolower(trim((string) ($_SESSION['perfil'] ?? '')));
    if (in_array($perfilSesion, ['administrador', 'admin'], true)) {
        return true;
    }
    return (bool) $db->fetchOne(
        "SELECT 1
          WHERE EXISTS (
                    SELECT 1 FROM usuario_perfil up
                    JOIN perfiles p ON p.id_perfil = up.id_perfil
                    WHERE up.id_usuario = ? AND LOWER(p.nombre) IN ('administrador', 'admin')
                )
             OR EXISTS (
                    SELECT 1 FROM usuario_colegio uc
                    JOIN perfiles p ON p.id_perfil = uc.id_perfil
                    WHERE uc.id_usuario = ? AND uc.estado = 1
                      AND LOWER(p.nombre) IN ('administrador', 'admin')
                )",
        [$idUsuario, $idUsuario]
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

    $columna = $db->fetchOne(
        "SELECT COUNT(*) AS total FROM information_schema.COLUMNS
          WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'permisos_menu_1' AND COLUMN_NAME = 'id_submenu'"
    );
    if ((int) ($columna['total'] ?? 0) === 0) {
        responder_json(['ok' => false, 'error' => 'Ejecuta primero sql/permisos_submenus.sql.'], 409);
    }

    $menusValidos = [];
    foreach ($db->fetchAll('SELECT id_menu FROM menu_1 WHERE id_menu > 0') as $menu) {
        $menusValidos[(int) $menu['id_menu']] = true;
    }
    $submenusValidos = [];
    $submenusPorMenu = [];
    foreach ($db->fetchAll('SELECT id_submenu, id_menu FROM menu_1_sub') as $submenu) {
        $idSubmenu = (int) $submenu['id_submenu'];
        $idMenu = (int) $submenu['id_menu'];
        if (isset($menusValidos[$idMenu])) {
            $submenusValidos[$idSubmenu] = $idMenu;
            $submenusPorMenu[$idMenu][] = $idSubmenu;
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
            $submenusSeleccionados[$idSubmenu] = $idMenuPadre;
            $menusSeleccionados[$idMenuPadre] = true;
        }
    }

    // Si llega un padre seleccionado sin ningún hijo suyo, se interpreta como
    // selección directa del padre y se conceden todos sus submenús.
    foreach (array_keys($menusSeleccionados) as $idMenu) {
        $hijos = $submenusPorMenu[$idMenu] ?? [];
        $tieneHijoMarcado = false;
        foreach ($hijos as $idSubmenu) {
            if (isset($submenusSeleccionados[$idSubmenu])) {
                $tieneHijoMarcado = true;
                break;
            }
        }
        if ($hijos && !$tieneHijoMarcado) {
            foreach ($hijos as $idSubmenu) {
                $submenusSeleccionados[$idSubmenu] = $idMenu;
            }
        }
    }

    $pdo = $db->getPDO();
    $pdo->beginTransaction();
    try {
        $db->execute('DELETE FROM permisos_menu_1 WHERE id_usuario = ? AND id_tipo_permiso = 1', [$usuarioObjetivo]);
        foreach (array_keys($menusSeleccionados) as $idMenu) {
            $db->execute(
                'INSERT INTO permisos_menu_1 (id_usuario, id_menu1, id_submenu, id_tipo_permiso) VALUES (?, ?, NULL, 1)',
                [$usuarioObjetivo, $idMenu]
            );
        }
        foreach ($submenusSeleccionados as $idSubmenu => $idMenu) {
            $db->execute(
                'INSERT INTO permisos_menu_1 (id_usuario, id_menu1, id_submenu, id_tipo_permiso) VALUES (?, ?, ?, 1)',
                [$usuarioObjetivo, $idMenu, $idSubmenu]
            );
        }
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $ex;
    }

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
