-- Deshace 01_inventario_pc_carpeta.sql
UPDATE menu_1_sub SET archivo = 'inventario/index.php' WHERE id_submenu = 10 AND archivo = 'inventario/pc/index.php';
