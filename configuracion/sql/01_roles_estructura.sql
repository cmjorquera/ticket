-- =====================================================================
--  CONFIGURACIÓN – 01: estructura del modelo de roles (ver logica/LOGICA_ROLES.txt)
--  1. Copia de seguridad de usuario_colegio en usuario_colegio_respaldo.
--  2. usuario_colegio recibe: id_departamento_colegio, rol, creado_por, y la
--     llave única pasa de (persona, colegio, perfil) a
--     (persona, colegio, departamento, rol): así una persona puede ser admin
--     del colegio y además trabajador de un departamento del mismo colegio.
--  3. Crea los departamentos INFORMATICA que faltan en Tabancura, Alerces,
--     Huelén y Huinganal (hoy sus técnicos apuntaban a Informática de Seduc).
--  * No borra nada. Se puede ejecutar más de una vez. MySQL 5.7.
-- =====================================================================
USE `crist668_sistema_panel_central`;
SET NAMES utf8mb4;

-- 1. Respaldo (solo la primera vez: si ya tiene filas, no se toca)
CREATE TABLE IF NOT EXISTS usuario_colegio_respaldo LIKE usuario_colegio;
INSERT INTO usuario_colegio_respaldo (id, id_usuario, id_colegio, id_perfil, estado, fecha_asignacion)
SELECT uc.id, uc.id_usuario, uc.id_colegio, uc.id_perfil, uc.estado, uc.fecha_asignacion
  FROM usuario_colegio uc
 WHERE NOT EXISTS (SELECT 1 FROM (SELECT 1 FROM usuario_colegio_respaldo LIMIT 1) r);

-- 2. Columnas nuevas (MySQL 5.7 no tiene ADD COLUMN IF NOT EXISTS)
SET @t := 'usuario_colegio';
SET @sql := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @t AND COLUMN_NAME = 'id_departamento_colegio') = 0,
  'ALTER TABLE usuario_colegio ADD COLUMN id_departamento_colegio INT NULL COMMENT ''NULL = admin_colegio (todo el colegio)'' AFTER id_colegio',
  'SELECT ''id_departamento_colegio ya existe'' AS aviso');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @t AND COLUMN_NAME = 'rol') = 0,
  'ALTER TABLE usuario_colegio ADD COLUMN rol ENUM(''admin_colegio'',''jefe_departamento'',''trabajador'') NOT NULL DEFAULT ''trabajador'' AFTER id_departamento_colegio',
  'SELECT ''rol ya existe'' AS aviso');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @t AND COLUMN_NAME = 'creado_por') = 0,
  'ALTER TABLE usuario_colegio ADD COLUMN creado_por INT NULL COMMENT ''usuarios.id de quien hizo la asignación'' AFTER estado',
  'SELECT ''creado_por ya existe'' AS aviso');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @t AND INDEX_NAME = 'uk_usuario_colegio') > 0,
  'ALTER TABLE usuario_colegio DROP INDEX uk_usuario_colegio',
  'SELECT ''uk_usuario_colegio ya no existe'' AS aviso');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @t AND INDEX_NAME = 'uk_uc_asignacion') = 0,
  'ALTER TABLE usuario_colegio ADD UNIQUE KEY uk_uc_asignacion (id_usuario, id_colegio, id_departamento_colegio, rol)',
  'SELECT ''uk_uc_asignacion ya existe'' AS aviso');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

SET @sql := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = @t AND INDEX_NAME = 'idx_uc_colegio_depto') = 0,
  'ALTER TABLE usuario_colegio ADD KEY idx_uc_colegio_depto (id_colegio, id_departamento_colegio, rol)',
  'SELECT ''idx_uc_colegio_depto ya existe'' AS aviso');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

-- 3. Departamentos de Informática que faltan
INSERT INTO departamentos_colegio (id_colegio, nombre_departamento, sigla, estado)
SELECT x.id_colegio, 'INFORMATICA', 'INF', 1
  FROM (SELECT 8 AS id_colegio UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 13) x
 WHERE NOT EXISTS (SELECT 1 FROM departamentos_colegio d
                    WHERE d.id_colegio = x.id_colegio AND d.nombre_departamento = 'INFORMATICA');

-- Verificación
SHOW COLUMNS FROM usuario_colegio;
SELECT c.nom_colegio, d.id, d.nombre_departamento
  FROM departamentos_colegio d JOIN colegio c ON c.id_colegio = d.id_colegio
 ORDER BY c.nom_colegio, d.nombre_departamento;
