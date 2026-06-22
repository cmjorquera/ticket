-- Migracion: agregar eliminacion logica a equipos.
-- Ejecutar una sola vez. El script evita duplicar columnas si ya existen.

SET @existe_eliminado := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'equipos'
      AND COLUMN_NAME = 'eliminado'
);

SET @sql_eliminado := IF(
    @existe_eliminado = 0,
    'ALTER TABLE equipos ADD COLUMN eliminado TINYINT NOT NULL DEFAULT 0 AFTER id_estado',
    'SELECT ''equipos.eliminado ya existe'' AS mensaje'
);

PREPARE stmt_eliminado FROM @sql_eliminado;
EXECUTE stmt_eliminado;
DEALLOCATE PREPARE stmt_eliminado;

SET @existe_fecha_eliminado := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'equipos'
      AND COLUMN_NAME = 'fecha_eliminado'
);

SET @sql_fecha_eliminado := IF(
    @existe_fecha_eliminado = 0,
    'ALTER TABLE equipos ADD COLUMN fecha_eliminado DATETIME NULL AFTER eliminado',
    'SELECT ''equipos.fecha_eliminado ya existe'' AS mensaje'
);

PREPARE stmt_fecha_eliminado FROM @sql_fecha_eliminado;
EXECUTE stmt_fecha_eliminado;
DEALLOCATE PREPARE stmt_fecha_eliminado;

SET @existe_usuario_elimina := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'equipos'
      AND COLUMN_NAME = 'id_usuario_elimina'
);

SET @sql_usuario_elimina := IF(
    @existe_usuario_elimina = 0,
    'ALTER TABLE equipos ADD COLUMN id_usuario_elimina INT NULL AFTER fecha_eliminado',
    'SELECT ''equipos.id_usuario_elimina ya existe'' AS mensaje'
);

PREPARE stmt_usuario_elimina FROM @sql_usuario_elimina;
EXECUTE stmt_usuario_elimina;
DEALLOCATE PREPARE stmt_usuario_elimina;

SET @existe_idx_eliminado := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'equipos'
      AND INDEX_NAME = 'idx_equipos_eliminado'
);

SET @sql_idx_eliminado := IF(
    @existe_idx_eliminado = 0,
    'ALTER TABLE equipos ADD INDEX idx_equipos_eliminado (eliminado)',
    'SELECT ''idx_equipos_eliminado ya existe'' AS mensaje'
);

PREPARE stmt_idx_eliminado FROM @sql_idx_eliminado;
EXECUTE stmt_idx_eliminado;
DEALLOCATE PREPARE stmt_idx_eliminado;
