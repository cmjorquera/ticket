-- =====================================================================
-- AGENDA · página de inicio personalizada (eventos + almuerzos)
-- Ejecutar en la BD del sistema (QA y luego producción). Se puede correr
-- más de una vez sin problema.
--
-- 1) Agrega usuarios.pagina_inicio (NULL = entra a la página de siempre).
-- 2) Asigna la Agenda a caspillaga@seduc.cl.
-- 3) Le da permiso para ver/descargar los pedidos de almuerzo
--    (almuerzo_configuracion.ids_ver_pedidos, lista de ids separada por coma).
--
-- Para agregar a otra persona más adelante: repetir los pasos 2 y 3
-- cambiando el correo. La persona debe cerrar sesión y volver a entrar.
-- =====================================================================

-- 1) Columna (solo si no existe)
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'usuarios' AND COLUMN_NAME = 'pagina_inicio');
SET @sql := IF(@existe = 0,
    "ALTER TABLE usuarios ADD COLUMN pagina_inicio VARCHAR(100) NULL DEFAULT NULL COMMENT 'Página al iniciar sesión (ver clases/PaginaInicio.php)'",
    'SELECT ''columna pagina_inicio ya existe'' AS info');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- 2) Página de inicio
UPDATE usuarios SET pagina_inicio = 'agenda/index.php' WHERE email = 'caspillaga@seduc.cl';

-- 3) Permiso para ver pedidos de almuerzo (agrega su id sin duplicarlo)
SET @id := (SELECT id FROM usuarios WHERE email = 'caspillaga@seduc.cl' LIMIT 1);

INSERT INTO almuerzo_configuracion (clave, valor, descripcion)
SELECT 'ids_ver_pedidos', @id, 'Ids de usuario (coma) que pueden ver y descargar los pedidos de almuerzo sin ser encargados'
  FROM DUAL WHERE @id IS NOT NULL
ON DUPLICATE KEY UPDATE valor = IF(FIND_IN_SET(@id, REPLACE(valor, ' ', '')) > 0, valor,
                                   TRIM(BOTH ',' FROM CONCAT(valor, ',', @id)));

-- Verificación
SELECT id, email, nombre, pagina_inicio FROM usuarios WHERE pagina_inicio IS NOT NULL;
SELECT clave, valor FROM almuerzo_configuracion WHERE clave = 'ids_ver_pedidos';

-- Para quitarle la página de inicio a alguien:
-- UPDATE usuarios SET pagina_inicio = NULL WHERE email = 'correo@seduc.cl';
