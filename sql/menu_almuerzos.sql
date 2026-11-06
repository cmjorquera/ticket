-- =====================================================================
--  Menú "Almuerzos" (id_menu = 19) visible para TODOS los usuarios
--  Base de datos: crist668_sistema_panel_central (QA)
--  Ejecutar en phpMyAdmin, pestaña SQL, PASO POR PASO.
--
--  En el código ya está:
--   - almuerzos/index.php                    (página del módulo)
--   - clases/menu_lateral.php  19 => PRINCIPAL (sección del menú lateral)
--   - helpers/permisos.php     usuarios nuevos reciben "almuerzos"
-- =====================================================================


-- ---------------------------------------------------------------------
-- PASO 1. Revisar (solo lectura). El id 19 NO debe existir todavía.
-- ---------------------------------------------------------------------
SELECT id_menu, nombre, archivo, orden FROM menu_1 ORDER BY id_menu;


-- ---------------------------------------------------------------------
-- PASO 2. Crear el menú. Queda al final del orden actual.
--         Si el id 19 ya existe, MySQL da error de clave duplicada y
--         no se crea nada (es seguro); en ese caso avisar antes de seguir.
-- ---------------------------------------------------------------------
INSERT INTO menu_1 (id_menu, nombre, abreviacion, archivo, icono, caracteristica, orden)
SELECT 19,
       'Almuerzos',
       'alm',
       'almuerzos/index.php',
       '<i class="bi bi-cup-hot"></i>',
       'Gestión de almuerzos del personal SEDUC.',
       CAST(COALESCE(MAX(CAST(orden AS UNSIGNED)), 0) + 1 AS CHAR)
  FROM menu_1;


-- ---------------------------------------------------------------------
-- PASO 3. Permiso para TODOS los usuarios existentes.
--         Se puede ejecutar más de una vez: no duplica filas.
-- ---------------------------------------------------------------------
INSERT INTO permisos_menu_1 (id_usuario, id_menu1, id_submenu, id_tipo_permiso)
SELECT u.id, 19, NULL, 1
  FROM usuarios u
 WHERE NOT EXISTS (
       SELECT 1
         FROM permisos_menu_1 p
        WHERE p.id_usuario = u.id
          AND p.id_menu1   = 19
          AND p.id_submenu IS NULL
          AND p.id_tipo_permiso = 1
 );


-- ---------------------------------------------------------------------
-- PASO 4. Verificar: ambos números deben ser iguales.
-- ---------------------------------------------------------------------
SELECT (SELECT COUNT(*) FROM usuarios) AS total_usuarios,
       (SELECT COUNT(DISTINCT id_usuario) FROM permisos_menu_1
         WHERE id_menu1 = 19 AND id_tipo_permiso = 1) AS usuarios_con_almuerzos;


-- ---------------------------------------------------------------------
-- DESHACER (solo si se quiere quitar el menú):
-- DELETE FROM permisos_menu_1 WHERE id_menu1 = 19;
-- DELETE FROM menu_1 WHERE id_menu = 19;
-- ---------------------------------------------------------------------
