-- =====================================================================
--  Menú Mensajes: quita "Enviados" (submenú 1) y "Archivados" (submenú 3).
--  Quedan como pestañas dentro de la página Mensajes; el menú deja solo
--  "Recibidos". No borra mensajes ni archivos: solo las filas del menú
--  y sus permisos. Se puede ejecutar más de una vez.
-- =====================================================================
USE `crist668_sistema_panel_central`;

DELETE FROM permiso_sub_menu WHERE id_submenu IN (1, 3);
DELETE FROM permisos_menu_1  WHERE id_submenu IN (1, 3);
DELETE FROM menu_1_sub       WHERE id_submenu IN (1, 3) AND id_menu = 2;

SELECT id_submenu, nombre, archivo FROM menu_1_sub WHERE id_menu = 2;
