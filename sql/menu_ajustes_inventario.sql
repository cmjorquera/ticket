-- =====================================================================
--  Menú Inventario: el submenú "Dashboard Inventario PC" (id 23, dentro
--  de Estadística) apuntaba a estadistica/dashboard_inventario.php, que
--  no existe en el sistema nuevo. El dashboard del módulo está en
--  inventario/dashboard.php.
-- =====================================================================
UPDATE menu_1_sub
   SET archivo = 'inventario/dashboard.php'
 WHERE id_submenu = 23
   AND archivo = 'estadistica/dashboard_inventario.php';

SELECT id_submenu, id_menu, nombre, archivo FROM menu_1_sub WHERE id_submenu = 23;
