-- Cambia referencias antiguas de ticket_administrador.php al nuevo ticket_admin_v2.php
UPDATE menu_1
SET archivo = 'ticket_admin_v2.php'
WHERE archivo IN ('ticket_administrador.php', 'ticket_admin.php');

UPDATE menu_1_sub
SET archivo = 'ticket_admin_v2.php'
WHERE archivo IN ('ticket_administrador.php', 'ticket_admin.php');

SELECT 'menu_1' AS tabla, id_menu AS id, nombre, archivo
FROM menu_1
WHERE archivo = 'ticket_admin_v2.php'
UNION ALL
SELECT 'menu_1_sub' AS tabla, id_submenu AS id, nombre, archivo
FROM menu_1_sub
WHERE archivo = 'ticket_admin_v2.php';
