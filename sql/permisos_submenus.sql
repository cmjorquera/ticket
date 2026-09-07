-- Ejecutar una vez en crist668_sistema_panel_central antes de usar permisos jerárquicos.
ALTER TABLE permisos_menu_1
    ADD COLUMN IF NOT EXISTS id_submenu INT NULL AFTER id_menu1;

-- Conserva el acceso actual: cada menú ya concedido recibe inicialmente todos sus hijos.
INSERT INTO permisos_menu_1 (id_usuario, id_menu1, id_submenu, id_tipo_permiso)
SELECT DISTINCT pm.id_usuario, pm.id_menu1, sm.id_submenu, pm.id_tipo_permiso
  FROM permisos_menu_1 pm
  JOIN menu_1_sub sm ON sm.id_menu = pm.id_menu1
 WHERE pm.id_submenu IS NULL
   AND pm.id_tipo_permiso = 1
   AND NOT EXISTS (
       SELECT 1 FROM permisos_menu_1 existente
        WHERE existente.id_usuario = pm.id_usuario
          AND existente.id_menu1 = pm.id_menu1
          AND existente.id_submenu = sm.id_submenu
          AND existente.id_tipo_permiso = 1
   );
