-- Herramientas y Aseo pasan a inventario/herramientas/ e inventario/aseo/.
-- OBLIGATORIO: las carpetas antiguas (inventario_aseo/, inventario_herramintas/)
-- se eliminaron, así que sin esto el menú apunta a páginas que no existen.
-- Revisar antes en producción que 8 = Aseo y 9 = Herramientas.
SELECT id_submenu, nombre, archivo FROM menu_1_sub WHERE id_submenu IN (8, 9);

UPDATE menu_1_sub SET archivo = 'inventario/aseo/index.php'         WHERE id_submenu = 8 AND archivo = 'inventario_aseo/index.php';
UPDATE menu_1_sub SET archivo = 'inventario/herramientas/index.php' WHERE id_submenu = 9 AND archivo = 'inventario_herramintas/index.php';

SELECT id_submenu, nombre, archivo FROM menu_1_sub WHERE id_submenu IN (8, 9);
