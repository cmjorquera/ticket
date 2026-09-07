<?php

/**
 * Genera la navegación y el footer del sidebar según los permisos del usuario.
 *
 * Las secciones se mantienen en PHP para no modificar la tabla menu_1.
 * El contenedor <aside> y el logo pertenecen a componentes/sidebar.php.
 *
 * @param int $id_usuario ID del usuario autenticado
 * @param object $db Instancia de Conexion
 * @param string|null $pagina_actual Archivo actual, con o sin extensión
 * @return void
 */
function menu_lateral($id_usuario, $db, $pagina_actual = null)
{
    $secciones_por_menu = [
        8  => 'PRINCIPAL',
        1  => 'PRINCIPAL',
        2  => 'PRINCIPAL',
        3  => 'OPERACIÓN',
        4  => 'OPERACIÓN',
        5  => 'OPERACIÓN',
        11 => 'OPERACIÓN',
        12 => 'OPERACIÓN',
        13 => 'OPERACIÓN',
        7  => 'ADMINISTRACIÓN',
        9  => 'ADMINISTRACIÓN',
        10 => 'ADMINISTRACIÓN',
        15 => 'ADMINISTRACIÓN',
        16 => 'ADMINISTRACIÓN',
        17 => 'OPERACIÓN',   // Ticket (menú padre nuevo)
        18 => 'OPERACIÓN',   // Ticket (menú padre nuevo)
    ];
    $orden_secciones = ['PRINCIPAL', 'ADMINISTRACIÓN', 'OPERACIÓN'];
    $depth = (string) ($GLOBALS['depth'] ?? '');

    $menus = $db->fetchAll(
        "SELECT DISTINCT m.id_menu, m.nombre, m.archivo, m.icono, m.caracteristica, m.orden
         FROM permisos_menu_1 pm
         JOIN menu_1 m ON pm.id_menu1 = m.id_menu
         WHERE pm.id_usuario = ?
         ORDER BY CAST(m.orden AS UNSIGNED) ASC",
        [$id_usuario]
    );

    $menus_agrupados = [];
    foreach ($menus as $menu) {
        $id_menu = (int) $menu['id_menu'];
        if (isset($secciones_por_menu[$id_menu])) {
            $menus_agrupados[$secciones_por_menu[$id_menu]][] = $menu;
        }
    }

    $pagina_ruta = (string) (parse_url((string) $pagina_actual, PHP_URL_PATH) ?: $pagina_actual);
    $pagina_actual = basename($pagina_ruta, '.php');
    ?>
    <nav class="sidebar-nav" aria-label="Menú principal">
        <?php if (empty($menus_agrupados)): ?>
            <div class="nav-group-label">Sin menús disponibles</div>
        <?php else: ?>
            <?php foreach ($orden_secciones as $seccion): ?>
                <?php if (empty($menus_agrupados[$seccion])) continue; ?>

                <div class="nav-group-label"><?= htmlspecialchars($seccion, ENT_QUOTES, 'UTF-8') ?></div>

                <?php foreach ($menus_agrupados[$seccion] as $menu): ?>
                    <?php
                    $archivo = (string) ($menu['archivo'] ?? '');
                    $ruta_archivo = (string) (parse_url($archivo, PHP_URL_PATH) ?: $archivo);
                    $archivo_normalizado = basename($ruta_archivo, '.php');
                    $activo = $pagina_actual !== '' && $pagina_actual === $archivo_normalizado;
                    $es_ruta_absoluta = preg_match('#^(?:[a-z][a-z0-9+.-]*:|/|\#)#i', $archivo) === 1;
                    $href = $es_ruta_absoluta ? $archivo : $depth . $archivo;
                    $icono_raw = (string) ($menu['icono'] ?? '');
                    $icono = trim((string) preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', '', $icono_raw));
                    $icono = $icono ?: '<i class="bi bi-circle"></i>';

                    // Los submenús heredan el acceso del menú padre; no existe
                    // una tabla de permisos adicional para menu_1_sub.
                    $submenu = $db->fetchAll(
                        "SELECT id_submenu, nombre, archivo, icono, orden
                         FROM menu_1_sub
                         WHERE id_menu = ?
                         ORDER BY orden ASC",
                        [(int) $menu['id_menu']]
                    );

                    $tiene_sub_activo = false;
                    foreach ($submenu as $sub) {
                        $sub_archivo = (string) ($sub['archivo'] ?? '');
                        $sub_base = basename($sub_archivo, '.php');
                        $sub_base2 = pathinfo($sub_archivo, PATHINFO_FILENAME);
                        if ($pagina_actual !== '' && ($sub_base === $pagina_actual || $sub_base2 === $pagina_actual)) {
                            $tiene_sub_activo = true;
                            break;
                        }
                    }
                    $activo_padre = $activo || $tiene_sub_activo;
                    $group_class = $tiene_sub_activo ? 'nav-item-group open' : 'nav-item-group';
                    ?>
                    <?php if (!empty($submenu)): ?>
                        <div class="<?= $group_class ?>">
                            <button type="button"
                                    class="nav-item nav-item-parent<?= $activo_padre ? ' active' : '' ?>"
                                    onclick="toggleSubMenu(this)"
                                    aria-expanded="<?= $tiene_sub_activo ? 'true' : 'false' ?>"
                                    title="<?= htmlspecialchars($menu['caracteristica'] ?? $menu['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                <?= $icono ?>
                                <span><?= htmlspecialchars($menu['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                                <svg class="chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <polyline points="9 18 15 12 9 6"/>
                                </svg>
                            </button>
                            <div class="nav-submenu">
                                <?php foreach ($submenu as $sub): ?>
                                    <?php
                                    $sub_icono_raw = (string) ($sub['icono'] ?? '');
                                    $sub_icono = trim((string) preg_replace('/style\s*=\s*["\'][^"\']*["\']/i', '', $sub_icono_raw));
                                    $sub_icono = $sub_icono ?: '<i class="bi bi-circle"></i>';
                                    $sub_archivo = (string) ($sub['archivo'] ?? '');
                                    $sub_ruta = (string) (parse_url($sub_archivo, PHP_URL_PATH) ?: $sub_archivo);
                                    $sub_base = basename($sub_ruta, '.php');
                                    $sub_base2 = pathinfo($sub_ruta, PATHINFO_FILENAME);
                                    $sub_activo = $pagina_actual !== '' && ($sub_base === $pagina_actual || $sub_base2 === $pagina_actual);
                                    $sub_es_ruta_absoluta = preg_match('#^(?:[a-z][a-z0-9+.-]*:|/|\#)#i', $sub_archivo) === 1;
                                    $sub_href = $sub_es_ruta_absoluta ? $sub_archivo : $depth . $sub_archivo;
                                    ?>
                                    <a href="<?= htmlspecialchars($sub_href, ENT_QUOTES, 'UTF-8') ?>"
                                       class="nav-item nav-subitem<?= $sub_activo ? ' active' : '' ?>"
                                       <?= $sub_activo ? 'aria-current="page"' : '' ?>>
                                        <?= $sub_icono ?>
                                        <span><?= htmlspecialchars($sub['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="<?= htmlspecialchars($href, ENT_QUOTES, 'UTF-8') ?>"
                           class="nav-item<?= $activo ? ' active' : '' ?>"
                           title="<?= htmlspecialchars($menu['caracteristica'] ?? $menu['nombre'], ENT_QUOTES, 'UTF-8') ?>"
                           <?= $activo ? 'aria-current="page"' : '' ?>>
                            <?= $icono ?>
                            <span><?= htmlspecialchars($menu['nombre'], ENT_QUOTES, 'UTF-8') ?></span>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= htmlspecialchars($depth . 'cerrar_sesion.php', ENT_QUOTES, 'UTF-8') ?>" class="nav-item">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
            <span>Cerrar sesión</span>
        </a>
        <button type="button" class="collapse-btn" onclick="toggleSidebar()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            <span>Colapsar</span>
        </button>
    </div>
    <?php
}

/**
 * Verifica si un usuario tiene permiso para acceder a un menú específico.
 */
function verificar_permiso_menu($id_usuario, $id_menu, $db)
{
    $resultado = $db->fetchOne(
        "SELECT COUNT(*) AS tiene_permiso
         FROM permisos_menu_1
         WHERE id_usuario = ? AND id_menu1 = ?",
        [$id_usuario, $id_menu]
    );

    return isset($resultado['tiene_permiso']) && $resultado['tiene_permiso'] > 0;
}

?>
