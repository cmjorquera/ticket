<?php
/**
 * ============================================================================
 *  MENÚ LATERAL DINÁMICO  ·  SEDUC Chile - Panel Central
 * ============================================================================
 *
 *  Renderiza el sidebar desde la base de datos mostrando SOLO los menús que el
 *  usuario autenticado tiene asignados en `permisos_menu_1`.
 *
 *  Tablas:
 *    menu_1           id_menu, nombre, archivo, icono, caracteristica, orden
 *    menu_1_sub       id_submenu, id_menu, nombre, archivo, icono, orden
 *    permisos_menu_1  id, id_menu1, id_usuario, id_tipo_permiso
 *
 *  Conexión (clase Conexion, PDO):
 *    $db = Conexion::getInstance('sistema_panel_central');
 *    $db->fetchAll($sql, $params)  -> array
 *    $db->fetchOne($sql, $params)  -> array|false
 *
 *  Funciones públicas:
 *    menu_lateral($id_usuario, $db, $modulo_activo = null)
 *    menu_lateral_js()
 *    verificar_permiso_menu($id_usuario, $id_menu, $db)
 *
 *  Uso mínimo:
 *    require_once __DIR__ . '/menu_lateral.php';
 *    menu_lateral($_SESSION['id'], Conexion::getInstance('sistema_panel_central'));
 *    ...
 *    menu_lateral_js();   // una sola vez, antes de </body>
 *
 *  SEGURIDAD · columna `icono`:
 *    Guarda HTML ya formado (ej: <i class="bi bi-house"></i>) y se imprime SIN
 *    escapar. Trátala como contenido de confianza: solo un administrador debe
 *    poder editar `menu_1.icono` / `menu_1_sub.icono`.
 * ============================================================================
 */

if (!function_exists('menu_lateral')) {

    /**
     * Imprime el menú lateral completo (HTML) para un usuario.
     *
     * @param int|string  $id_usuario     ID del usuario autenticado ($_SESSION['id']).
     * @param object       $db             Instancia de Conexion (fetchAll/fetchOne).
     * @param string|null  $modulo_activo  (opcional) archivo de la página actual a
     *                                      resaltar. Si se omite se detecta por URL.
     * @return void
     */
    function menu_lateral($id_usuario, $db, $modulo_activo = null)
    {
        $id_usuario = (int) $id_usuario;

        if ($id_usuario <= 0) {
            echo '<aside class="sidebar" id="sidebar" aria-label="Menú lateral">'
               . '<div class="sidebar-empty">Sesión no válida.</div></aside>';
            return;
        }

        /* -- 1. Menús principales permitidos -------------------------------- */
        //  INNER JOIN con permisos_menu_1: sin fila de permiso, el menú no sale.
        //  DISTINCT: un usuario puede tener varias filas (distinto id_tipo_permiso).
        //  CAST(orden): `menu_1.orden` es VARCHAR -> orden numérico correcto.
        $menus = $db->fetchAll(
            "SELECT DISTINCT
                    m.id_menu, m.nombre, m.archivo, m.icono, m.caracteristica, m.orden
               FROM menu_1 m
               INNER JOIN permisos_menu_1 p ON p.id_menu1 = m.id_menu
              WHERE p.id_usuario = ?
              ORDER BY CAST(m.orden AS UNSIGNED) ASC, m.nombre ASC",
            [$id_usuario]
        );

        if (empty($menus)) {
            echo '<aside class="sidebar" id="sidebar" aria-label="Menú lateral">'
               . '<div class="sidebar-empty">Sin menús asignados.</div></aside>';
            return;
        }

        /* -- 2. Submenús de esos menús ------------------------------------- */
        $ids_menu   = array_map('intval', array_column($menus, 'id_menu'));
        $marcadores = implode(',', array_fill(0, count($ids_menu), '?'));

        $submenus = $db->fetchAll(
            "SELECT s.id_submenu, s.id_menu, s.nombre, s.archivo, s.icono, s.orden
               FROM menu_1_sub s
              WHERE s.id_menu IN ($marcadores)
              ORDER BY s.id_menu ASC, CAST(s.orden AS UNSIGNED) ASC, s.nombre ASC",
            $ids_menu
        );

        $subs_por_menu = [];
        foreach ($submenus as $s) {
            $subs_por_menu[(int) $s['id_menu']][] = $s;
        }

        /* -- 3. Página activa -------------------------------------------- */
        $uri_actual = $modulo_activo;
        if ($uri_actual === null || $uri_actual === '') {
            $uri_actual = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
        }
        $uri_actual = trim((string) $uri_actual, '/');

        /* -- 4. Render -------------------------------------------------- */
        echo '<aside class="sidebar" id="sidebar" aria-label="Menú lateral">';
        echo   '<div class="sidebar-header">';
        echo     '<span class="sidebar-brand"><i class="bi bi-mortarboard-fill"></i> <span>Panel Central</span></span>';
        echo     '<button type="button" class="sidebar-close" onclick="toggleSidebar(false)" aria-label="Cerrar menú"><i class="bi bi-x-lg"></i></button>';
        echo   '</div>';
        echo   '<nav class="sidebar-nav"><ul class="menu-list">';

        foreach ($menus as $m) {
            $id_menu     = (int) $m['id_menu'];
            $hijos       = $subs_por_menu[$id_menu] ?? [];
            $tiene_hijos = !empty($hijos);

            $hijo_activo = false;
            foreach ($hijos as $h) {
                if (menu_lateral_es_activo($h['archivo'], $uri_actual)) {
                    $hijo_activo = true;
                    break;
                }
            }
            $activo = menu_lateral_es_activo($m['archivo'], $uri_actual) || $hijo_activo;

            $clases = 'menu-item';
            if ($tiene_hijos) { $clases .= ' has-submenu'; }
            if ($hijo_activo) { $clases .= ' open'; }

            echo '<li class="' . $clases . '" data-menu-id="' . $id_menu . '">';

            if ($tiene_hijos) {
                // Menú expandible
                echo '<a href="#" '
                   .    'class="menu-link' . ($activo ? ' active' : '') . '" '
                   .    'data-menu-id="' . $id_menu . '" '
                   .    'onclick="toggleSubmenu(event, ' . $id_menu . ')" '
                   .    'aria-expanded="' . ($hijo_activo ? 'true' : 'false') . '" '
                   .    'title="' . htmlspecialchars($m['caracteristica'] ?? $m['nombre'], ENT_QUOTES) . '">';
                echo   '<span class="menu-icon">' . $m['icono'] . '</span>';
                echo   '<span class="menu-text">' . htmlspecialchars($m['nombre']) . '</span>';
                echo   '<span class="menu-arrow"><i class="bi bi-chevron-down"></i></span>';
                echo '</a>';

                echo '<ul class="submenu-container" data-parent="' . $id_menu . '">';
                foreach ($hijos as $h) {
                    $h_activo = menu_lateral_es_activo($h['archivo'], $uri_actual);
                    echo '<li class="submenu-item" data-submenu-id="' . (int) $h['id_submenu'] . '">';
                    echo   '<a href="' . htmlspecialchars($h['archivo']) . '" '
                       .      'class="submenu-link' . ($h_activo ? ' active' : '') . '"'
                       .      ($h_activo ? ' aria-current="page"' : '') . '>';
                    echo     '<span class="submenu-icon">' . $h['icono'] . '</span>';
                    echo     '<span class="submenu-text">' . htmlspecialchars($h['nombre']) . '</span>';
                    echo   '</a>';
                    echo '</li>';
                }
                echo '</ul>';

            } else {
                // Enlace directo
                echo '<a href="' . htmlspecialchars($m['archivo']) . '" '
                   .    'class="menu-link' . ($activo ? ' active' : '') . '"'
                   .    ($activo ? ' aria-current="page"' : '') . ' '
                   .    'title="' . htmlspecialchars($m['caracteristica'] ?? $m['nombre'], ENT_QUOTES) . '">';
                echo   '<span class="menu-icon">' . $m['icono'] . '</span>';
                echo   '<span class="menu-text">' . htmlspecialchars($m['nombre']) . '</span>';
                echo '</a>';
            }

            echo '</li>';
        }

        echo   '</ul></nav>';
        echo '</aside>';
        echo '<div class="sidebar-backdrop" id="sidebar-backdrop" onclick="toggleSidebar(false)"></div>';
    }
}


if (!function_exists('menu_lateral_es_activo')) {

    /**
     * ¿El `archivo` de un menú corresponde a la página actual?
     * Coincide si la URL termina exactamente con esa ruta, o si comparten
     * nombre de archivo (evitando el genérico "index.php").
     *
     * @param string $archivo    Valor de menu_1.archivo / menu_1_sub.archivo.
     * @param string $uri_actual Path actual normalizado (sin / inicial).
     * @return bool
     */
    function menu_lateral_es_activo($archivo, $uri_actual)
    {
        $archivo = trim((string) $archivo, '/');
        if ($archivo === '' || $uri_actual === '') {
            return false;
        }
        if ($uri_actual === $archivo) {
            return true;
        }
        $sufijo = '/' . $archivo;
        if (strlen($uri_actual) > strlen($sufijo)
            && substr($uri_actual, -strlen($sufijo)) === $sufijo) {
            return true;
        }
        $base = basename($archivo);
        return $base !== 'index.php' && $base === basename($uri_actual);
    }
}


if (!function_exists('verificar_permiso_menu')) {

    /**
     * ¿El usuario tiene permiso sobre un menú principal?
     *
     *   if (!verificar_permiso_menu($_SESSION['id'], 7, $db)) {
     *       http_response_code(403);
     *       die('No tienes permiso para acceder a esta sección.');
     *   }
     *
     * @param int|string $id_usuario
     * @param int|string $id_menu     menu_1.id_menu
     * @param object      $db          Instancia de Conexion.
     * @return bool
     */
    function verificar_permiso_menu($id_usuario, $id_menu, $db)
    {
        $id_usuario = (int) $id_usuario;
        $id_menu    = (int) $id_menu;
        if ($id_usuario <= 0 || $id_menu <= 0) {
            return false;
        }

        $fila = $db->fetchOne(
            "SELECT 1
               FROM permisos_menu_1
              WHERE id_usuario = ? AND id_menu1 = ?
              LIMIT 1",
            [$id_usuario, $id_menu]
        );

        return $fila !== false;
    }
}


if (!function_exists('menu_lateral_js')) {

    /**
     * Imprime el <script> que controla el menú:
     *   - toggleSubmenu(event, menuId): expandir/contraer con animación real.
     *   - Modo acordeón (cierra los demás). Desactiva con ACORDEON = false.
     *   - Detecta la página activa por URL y abre su submenú padre.
     *   - toggleSidebar(forzar): abre/cierra el sidebar en móvil. Cierra con Esc.
     *
     * Expone en window: toggleSubmenu(event, menuId), toggleSidebar(forzar).
     *
     * @return void
     */
    function menu_lateral_js()
    {
        ?>
<script>
(function () {
    'use strict';

    var ACORDEON = true; // al abrir un submenú, cierra los demás

    function refMenu(menuId) {
        var item = document.querySelector('.menu-item[data-menu-id="' + menuId + '"]');
        return item ? item : null;
    }

    function abrir(item) {
        var cont = item.querySelector('.submenu-container');
        var link = item.querySelector('.menu-link');
        if (!cont) { return; }
        item.classList.add('open');
        cont.style.maxHeight = cont.scrollHeight + 'px';
        if (link) { link.setAttribute('aria-expanded', 'true'); }
    }

    function cerrar(item) {
        var cont = item.querySelector('.submenu-container');
        var link = item.querySelector('.menu-link');
        if (!cont) { return; }
        item.classList.remove('open');
        cont.style.maxHeight = null;
        if (link) { link.setAttribute('aria-expanded', 'false'); }
    }

    function toggleSubmenu(event, menuId) {
        if (event && event.preventDefault) { event.preventDefault(); }
        var item = refMenu(menuId);
        if (!item) { return; }

        var abierto = item.classList.contains('open');
        if (ACORDEON && !abierto) {
            document.querySelectorAll('.menu-item.open').forEach(function (otro) {
                if (otro !== item) { cerrar(otro); }
            });
        }
        abierto ? cerrar(item) : abrir(item);
    }

    function toggleSidebar(forzar) {
        var sidebar  = document.getElementById('sidebar');
        var backdrop = document.getElementById('sidebar-backdrop');
        if (!sidebar) { return; }
        var abrirSb = (typeof forzar === 'boolean')
            ? forzar
            : !sidebar.classList.contains('is-open');
        sidebar.classList.toggle('is-open', abrirSb);
        if (backdrop) { backdrop.classList.toggle('is-visible', abrirSb); }
        document.body.classList.toggle('sidebar-abierto', abrirSb);
    }

    function marcarActivo() {
        var actual = window.location.pathname.replace(/\/+$/, '');
        document.querySelectorAll('#sidebar a[href]').forEach(function (a) {
            var href = a.getAttribute('href');
            if (!href || href === '#') { return; }
            var destino = a.pathname.replace(/\/+$/, '');
            var coincide = (destino === actual) ||
                (actual.length > destino.length &&
                 actual.slice(-destino.length) === destino &&
                 destino.split('/').pop() !== 'index.php');
            if (coincide) {
                a.classList.add('active');
                a.setAttribute('aria-current', 'page');
                var padre = a.closest('.menu-item.has-submenu');
                if (padre) { abrir(padre); }
            }
        });
    }

    function init() {
        // Submenús que vienen abiertos desde PHP: fijar altura para animar.
        document.querySelectorAll('.menu-item.open .submenu-container').forEach(function (c) {
            c.style.maxHeight = c.scrollHeight + 'px';
        });
        marcarActivo();

        window.addEventListener('resize', function () {
            document.querySelectorAll('.menu-item.open .submenu-container').forEach(function (c) {
                c.style.maxHeight = c.scrollHeight + 'px';
            });
            if (window.innerWidth > 768) { toggleSidebar(false); }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { toggleSidebar(false); }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.toggleSubmenu = toggleSubmenu;
    window.toggleSidebar = toggleSidebar;
})();
</script>
        <?php
    }
}
