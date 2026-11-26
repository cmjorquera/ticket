-- Deshace 02_herramientas_aseo_carpeta.sql
UPDATE menu_1_sub SET archivo = 'inventario_aseo/index.php'        WHERE id_submenu = 8 AND archivo = 'inventario/aseo/index.php';
UPDATE menu_1_sub SET archivo = 'inventario_herramintas/index.php' WHERE id_submenu = 9 AND archivo = 'inventario/herramientas/index.php';
