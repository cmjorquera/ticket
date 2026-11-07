-- =====================================================================
--  ALMUERZOS – 06: foto de cada menú + picnic = solo sándwich
--  1. Agrega almuerzo_menu.imagen (ruta de la foto real del menú).
--     Si un menú no tiene foto, la pantalla muestra una imagen referencial.
--  2. El picnic es siempre un sándwich: se quitan entrada y postre del picnic
--     y los platos de ejemplo que no eran sándwich pasan a serlo.
--  * Se puede ejecutar más de una vez. Compatible con MySQL 5.7.
-- =====================================================================
USE `crist668_sistema_panel_central`;
SET NAMES utf8mb4;

-- 1. Columna imagen (solo si no existe; MySQL 5.7 no tiene ADD COLUMN IF NOT EXISTS)
SET @hay := (SELECT COUNT(*) FROM information_schema.COLUMNS
              WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'almuerzo_menu' AND COLUMN_NAME = 'imagen');
SET @sql := IF(@hay = 0,
    'ALTER TABLE almuerzo_menu ADD COLUMN imagen VARCHAR(255) NULL COMMENT ''Foto del menú: img/menus/archivo.jpg o https://...'' AFTER postre',
    'SELECT ''La columna imagen ya existe'' AS aviso');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- 2. Picnic: solo el sándwich
-- Meses publicados con la plantilla antigua del picnic ("Sándwich + bebida + postre"):
-- el sándwich quedó como entrada; se pasa a plato principal.
UPDATE almuerzo_menu m
  JOIN almuerzo_tipo t ON t.id_tipo = m.id_tipo
   SET m.plato_fondo = m.entrada
 WHERE t.codigo = 'picnic' AND m.entrada LIKE 'S_ndwich%' AND m.plato_fondo NOT LIKE 'S_ndwich%';

UPDATE almuerzo_menu m
  JOIN almuerzo_tipo t ON t.id_tipo = m.id_tipo
   SET m.entrada = NULL, m.postre = NULL
 WHERE t.codigo = 'picnic';

-- Platos del menú de ejemplo de noviembre que no eran sándwich
UPDATE almuerzo_menu m JOIN almuerzo_tipo t ON t.id_tipo = m.id_tipo
   SET m.plato_fondo = CASE m.plato_fondo
        WHEN 'Wrap de pollo y verduras'   THEN 'Sándwich de pollo y verduras'
        WHEN 'Ensalada César para llevar' THEN 'Sándwich de pollo César'
        WHEN 'Empanada de horno'          THEN 'Sándwich de queso y tomate'
        ELSE m.plato_fondo END
 WHERE t.codigo = 'picnic';

-- Verificación
SELECT d.fecha, m.plato_fondo AS picnic, m.imagen
  FROM almuerzo_menu m
  JOIN almuerzo_tipo t ON t.id_tipo = m.id_tipo AND t.codigo = 'picnic'
  JOIN almuerzo_dia d  ON d.id_dia_menu = m.id_dia_menu
 ORDER BY d.fecha;
