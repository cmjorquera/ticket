<?php

/**
 * FUNCIÓN: menu_lateral()
 * 
 * Genera el menú lateral dinámicamente basado en:
 * - Permisos del usuario en permisos_menu_1
 * - Menús disponibles en menu_1
 * - Submenús en menu_1_sub
 * 
 * @param int $id_usuario - ID del usuario autenticado
 * @param object $db - Objeto Conexion/PDO para consultas
 * @param string $modulo_activo - Módulo actual (opcional, para highlight)
 * @return void - Imprime el HTML del menú
 */
function menu_lateral($id_usuario, $db, $modulo_activo = null)
{
    // 1. Obtener todos los menús que el usuario tiene permiso de ver
    $sql_permisos = "
        SELECT DISTINCT pm.id_menu1, m.id_menu, m.nombre, m.abreviacion, 
                        m.archivo, m.icono, m.caracteristica, m.orden
        FROM permisos_menu_1 pm
        JOIN menu_1 m ON pm.id_menu1 = m.id_menu
        WHERE pm.id_usuario = ?
        ORDER BY CAST(m.orden AS UNSIGNED) ASC
    ";
    
    $menus = $db->fetchAll($sql_permisos, [$id_usuario]);
    
    if (empty($menus)) {
        echo '<div class="sidebar-nav"><p class="text-muted">Sin menús disponibles</p></div>';
        return;
    }

    // 2. Agrupar menús por orden (algunos tienen secciones)
    $menu_ids = array_column($menus, 'id_menu');

    // 3. Obtener TODOS los submenús que pertenecen a estos menús
    $placeholders = str_repeat('?,', count($menu_ids) - 1) . '?';
    $sql_submenus = "
        SELECT ms.id_submenu, ms.id_menu, ms.nombre, ms.archivo, ms.icono, ms.orden
        FROM menu_1_sub ms
        WHERE ms.id_menu IN ($placeholders)
        ORDER BY ms.id_menu ASC, CAST(ms.orden AS UNSIGNED) ASC
    ";
    $submenus = $db->fetchAll($sql_submenus, $menu_ids);
    
    // Agrupar submenús por id_menu
    $submenus_agrupados = [];
    foreach ($submenus as $sub) {
        if (!isset($submenus_agrupados[$sub['id_menu']])) {
            $submenus_agrupados[$sub['id_menu']] = [];
        }
        $submenus_agrupados[$sub['id_menu']][] = $sub;
    }

    // 4. Renderizar el HTML del menú
    ?>
    <nav class="sidebar-nav">
        <?php foreach ($menus as $menu): ?>
            <!-- MENÚ PRINCIPAL -->
            <div class="menu-item" data-menu-id="<?php echo $menu['id_menu']; ?>">
                <!-- Si tiene submenús, mostrar expandible, si no, mostrar como enlace directo -->
                <?php if (isset($submenus_agrupados[$menu['id_menu']]) && count($submenus_agrupados[$menu['id_menu']]) > 0): ?>
                    
                    <!-- MENÚ CON SUBMENÚS (expandible) -->
                    <a href="#" class="menu-link" onclick="toggleSubmenu(event, <?php echo $menu['id_menu']; ?>)" 
                       title="<?php echo htmlspecialchars($menu['caracteristica']); ?>">
                        <span class="menu-icon">
                            <?php echo $menu['icono']; ?>
                        </span>
                        <span class="menu-text">
                            <?php echo htmlspecialchars($menu['nombre']); ?>
                        </span>
                        <span class="menu-arrow">▼</span>
                    </a>

                    <!-- SUBMENÚS (ocultos por defecto) -->
                    <div class="submenu-container" id="submenu-<?php echo $menu['id_menu']; ?>" style="display: none;">
                        <?php foreach ($submenus_agrupados[$menu['id_menu']] as $submenu): ?>
                            <a href="<?php echo htmlspecialchars($submenu['archivo']); ?>" 
                               class="submenu-link"
                               title="<?php echo htmlspecialchars($submenu['nombre']); ?>">
                                <span class="submenu-icon">
                                    <?php echo $submenu['icono']; ?>
                                </span>
                                <span class="submenu-text">
                                    <?php echo htmlspecialchars($submenu['nombre']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    
                    <!-- MENÚ SIN SUBMENÚS (enlace directo) -->
                    <a href="<?php echo htmlspecialchars($menu['archivo']); ?>" 
                       class="menu-link"
                       title="<?php echo htmlspecialchars($menu['caracteristica']); ?>">
                        <span class="menu-icon">
                            <?php echo $menu['icono']; ?>
                        </span>
                        <span class="menu-text">
                            <?php echo htmlspecialchars($menu['nombre']); ?>
                        </span>
                    </a>

                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </nav>
    <?php
}


/**
 * FUNCIÓN: toggleSubmenu()
 * 
 * JavaScript para expandir/contraer submenús
 * Llamar esta función en el <head> o footer
 */
function menu_lateral_js()
{
    ?>
    <script>
        function toggleSubmenu(event, menuId) {
            event.preventDefault();
            
            const container = document.getElementById(`submenu-${menuId}`);
            const arrow = event.currentTarget.querySelector('.menu-arrow');
            
            if (container.style.display === 'none' || container.style.display === '') {
                container.style.display = 'block';
                if (arrow) arrow.style.transform = 'rotate(180deg)';
            } else {
                container.style.display = 'none';
                if (arrow) arrow.style.transform = 'rotate(0deg)';
            }
        }

        // Expandir menús activos al cargar la página (opcional)
        document.addEventListener('DOMContentLoaded', () => {
            const url = window.location.pathname;
            
            document.querySelectorAll('.submenu-link').forEach(link => {
                if (url.includes(link.getAttribute('href'))) {
                    const menuId = link.closest('.menu-item').dataset.menuId;
                    const container = document.getElementById(`submenu-${menuId}`);
                    if (container) {
                        container.style.display = 'block';
                        const arrow = link.closest('.menu-item').querySelector('.menu-arrow');
                        if (arrow) arrow.style.transform = 'rotate(180deg)';
                        link.classList.add('active');
                    }
                }
            });

            document.querySelectorAll('.menu-link:not([onclick])').forEach(link => {
                if (url.includes(link.getAttribute('href'))) {
                    link.classList.add('active');
                }
            });
        });
    </script>
    <?php
}


/**
 * FUNCIÓN: verificar_permiso_menu()
 * 
 * Verifica si un usuario tiene permiso para acceder a un menú específico
 * Útil para validar acceso en las páginas
 * 
 * @param int $id_usuario - ID del usuario
 * @param int $id_menu - ID del menú a verificar
 * @param object $db - Objeto Conexion/PDO
 * @return bool - true si tiene permiso, false si no
 */
function verificar_permiso_menu($id_usuario, $id_menu, $db)
{
    $sql = "
        SELECT COUNT(*) as tiene_permiso
        FROM permisos_menu_1
        WHERE id_usuario = ? AND id_menu1 = ?
    ";
    
    $resultado = $db->fetchOne($sql, [$id_usuario, $id_menu]);
    
    return isset($resultado['tiene_permiso']) && $resultado['tiene_permiso'] > 0;
}

?>