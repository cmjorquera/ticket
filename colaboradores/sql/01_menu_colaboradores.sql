-- Colaboradores pasa a su propia carpeta (colaboradores/index.php).
-- colaboradores.php en la raíz queda como redirección, así que el sistema
-- funciona aunque esto no se ejecute; esto solo hace que el menú lateral
-- apunte directo a la página nueva y la marque como activa.
UPDATE menu_1 SET archivo = 'colaboradores/index.php' WHERE id_menu = 1;

SELECT id_menu, nombre, archivo FROM menu_1 WHERE id_menu = 1;
