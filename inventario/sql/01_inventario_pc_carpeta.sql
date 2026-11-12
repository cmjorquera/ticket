-- Inventario PC pasa de inventario/ a inventario/pc/.
-- inventario/index.php queda como redirección, así que el sistema funciona
-- aunque esto no se ejecute; esto solo hace que el menú apunte directo a la
-- página nueva y la marque como activa.
-- Revisar antes en producción que id_submenu 10 sea "Inventario PC".
SELECT id_submenu, nombre, archivo FROM menu_1_sub WHERE id_submenu = 10;

UPDATE menu_1_sub SET archivo = 'inventario/pc/index.php' WHERE id_submenu = 10 AND archivo = 'inventario/index.php';

SELECT id_submenu, nombre, archivo FROM menu_1_sub WHERE id_submenu = 10;
