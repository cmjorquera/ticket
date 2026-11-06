-- =====================================================================
--  Menú Inventario: el submenú "Dashboard Inventario PC" (id 23, dentro
--  de Estadística) apuntaba a estadistica/dashboard_inventario.php, que
--  no existe en el sistema nuevo. El dashboard del módulo está en
--  inventario/dashboard.php.
-- =====================================================================

-- Base de datos donde se ejecuta (QA). En PRODUCCIÓN cambiar este nombre.
-- Va explícita para que phpMyAdmin no la ejecute en otra base por error.
USE `crist668_sistema_panel_central`;

UPDATE menu_1_sub
   SET archivo = 'inventario/dashboard.php'
 WHERE id_submenu = 23
   AND archivo = 'estadistica/dashboard_inventario.php';

SELECT id_submenu, id_menu, nombre, archivo FROM menu_1_sub WHERE id_submenu = 23;
