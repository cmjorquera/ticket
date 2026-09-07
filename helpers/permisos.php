<?php
declare(strict_types=1);

/** Permisos iniciales asignados al crear un usuario. */

function normalizar_nombre_permiso(string $valor): string
{
    $valor = function_exists('mb_strtolower') ? mb_strtolower(trim($valor), 'UTF-8') : strtolower(trim($valor));
    $ascii = function_exists('iconv') ? iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $valor) : false;
    $valor = $ascii !== false ? $ascii : $valor;
    return trim((string) preg_replace('/[^a-z0-9]+/', ' ', $valor));
}

function buscar_permiso_por_alias(array $filas, array $aliases): ?array
{
    foreach ($aliases as $alias) {
        $alias = normalizar_nombre_permiso($alias);
        foreach ($filas as $fila) {
            $texto = normalizar_nombre_permiso((string) ($fila['nombre'] ?? '') . ' ' . (string) ($fila['archivo'] ?? ''));
            if ($texto === $alias || str_contains($texto, $alias)) {
                return $fila;
            }
        }
    }
    return null;
}

function insertar_permiso_defecto(Conexion $db, int $idMenu, int $idUsuario, ?int $idSubmenu, bool $soportaSubmenus): void
{
    if ($soportaSubmenus) {
        $db->execute(
            'INSERT INTO permisos_menu_1 (id_usuario, id_menu1, id_submenu, id_tipo_permiso) VALUES (?, ?, ?, 1)',
            [$idUsuario, $idMenu, $idSubmenu]
        );
        return;
    }
    if ($idSubmenu === null) {
        $db->execute(
            'INSERT INTO permisos_menu_1 (id_usuario, id_menu1, id_tipo_permiso) VALUES (?, ?, 1)',
            [$idUsuario, $idMenu]
        );
    }
}

function asignar_permisos_por_defecto(int $usuarioId, int $perfilId, Conexion $db): bool
{
    try {
        $perfil = $db->fetchOne('SELECT nombre FROM perfiles WHERE id_perfil = ? LIMIT 1', [$perfilId]);
        if (!$perfil) {
            throw new RuntimeException('El perfil seleccionado no existe.');
        }
        $perfilNombre = normalizar_nombre_permiso((string) $perfil['nombre']);
        if ((str_contains($perfilNombre, 'administrador') || $perfilId === 3) && !str_contains($perfilNombre, 'colegio') && !str_contains($perfilNombre, 'area')) {
            $tipoPerfil = 'administrador';
        } elseif ((str_contains($perfilNombre, 'admin') && str_contains($perfilNombre, 'colegio')) || $perfilId === 4) {
            $tipoPerfil = 'admin_colegio';
        } elseif ((str_contains($perfilNombre, 'admin') && str_contains($perfilNombre, 'area')) || $perfilId === 5) {
            $tipoPerfil = 'admin_area';
        } elseif (str_contains($perfilNombre, 'tecn') || $perfilId === 2) {
            $tipoPerfil = 'tecnico';
        } else {
            $tipoPerfil = 'usuario';
        }

        $menus = array_map(static fn (array $fila): array => [
            'id_menu' => (int) $fila['id_menu'],
            'nombre' => (string) $fila['nombre'],
            'archivo' => (string) ($fila['archivo'] ?? ''),
        ], $db->fetchAll('SELECT id_menu, nombre, archivo FROM menu_1 WHERE id_menu > 0'));
        $submenus = array_map(static fn (array $fila): array => [
            'id_submenu' => (int) $fila['id_submenu'],
            'id_menu' => (int) $fila['id_menu'],
            'nombre' => (string) $fila['nombre'],
            'archivo' => (string) ($fila['archivo'] ?? ''),
        ], $db->fetchAll('SELECT id_submenu, id_menu, nombre, archivo FROM menu_1_sub'));

        $columna = $db->fetchOne(
            "SELECT COUNT(*) AS total FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'permisos_menu_1' AND COLUMN_NAME = 'id_submenu'"
        );
        $soportaSubmenus = (int) ($columna['total'] ?? 0) > 0;
        if (!$soportaSubmenus) {
            throw new RuntimeException('Falta ejecutar sql/permisos_submenus.sql.');
        }
        $menusSeleccionados = [];
        $submenusSeleccionados = [];

        if ($tipoPerfil === 'administrador') {
            foreach ($menus as $menu) {
                $menusSeleccionados[$menu['id_menu']] = true;
            }
            foreach ($submenus as $submenu) {
                $submenusSeleccionados[$submenu['id_submenu']] = $submenu['id_menu'];
            }
        } else {
            $configuracion = [
                'usuario' => [
                    'menus' => [['inicio', 'dashboard'], ['colaboradores']],
                    'submenus' => [['crear ticket', 'nuevo ticket'], ['mis tickets']],
                ],
                'tecnico' => [
                    'menus' => [['inicio', 'dashboard'], ['colaboradores'], ['administracion']],
                    'submenus' => [['crear ticket', 'nuevo ticket'], ['mis tickets'], ['tickets asignados', 'ticket asignado'], ['admin categorias', 'categorias y tecnicos']],
                ],
                'admin_colegio' => [
                    'menus' => [['inicio', 'dashboard'], ['colaboradores'], ['administracion']],
                    'submenus' => [['crear ticket', 'nuevo ticket'], ['mis tickets'], ['listado usuarios', 'usuarios'], ['admin categorias', 'categorias y tecnicos'], ['permisos']],
                ],
                'admin_area' => [
                    'menus' => [['inicio', 'dashboard'], ['colaboradores'], ['administracion']],
                    'submenus' => [['crear ticket', 'nuevo ticket'], ['mis tickets'], ['admin categorias', 'categorias y tecnicos']],
                ],
            ][$tipoPerfil];

            foreach ($configuracion['menus'] as $aliases) {
                $menu = buscar_permiso_por_alias($menus, $aliases);
                if ($menu) {
                    $menusSeleccionados[$menu['id_menu']] = true;
                }
            }
            foreach ($configuracion['submenus'] as $aliases) {
                $submenu = buscar_permiso_por_alias($submenus, $aliases);
                if ($submenu) {
                    $submenusSeleccionados[$submenu['id_submenu']] = $submenu['id_menu'];
                    $menusSeleccionados[$submenu['id_menu']] = true;
                }
            }
        }

        $db->execute('DELETE FROM permisos_menu_1 WHERE id_usuario = ?', [$usuarioId]);
        foreach (array_keys($menusSeleccionados) as $idMenu) {
            insertar_permiso_defecto($db, (int) $idMenu, $usuarioId, null, $soportaSubmenus);
        }
        foreach ($submenusSeleccionados as $idSubmenu => $idMenu) {
            insertar_permiso_defecto($db, (int) $idMenu, $usuarioId, (int) $idSubmenu, $soportaSubmenus);
        }
        return true;
    } catch (Throwable $ex) {
        error_log('Error al asignar permisos por defecto: ' . $ex->getMessage());
        return false;
    }
}
