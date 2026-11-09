-- =====================================================================
-- BITÁCORA DEL TICKET (avances del técnico + conversación con administración)
-- Lógica: ticket/logica/LOGICA_BITACORA.txt
-- MySQL 5.7. Se puede ejecutar más de una vez.
--  - En QA la tabla avance_tecnicos no existe: se crea.
--  - En producción (sistema antiguo) ya existe con datos: se amplía y se
--    conservan todos los avances (quedan como "leídos", lado técnico).
-- Vuelta atrás: 99_volver_atras.sql (sección bitácora)
-- =====================================================================

CREATE TABLE IF NOT EXISTS avance_tecnicos (
  id            INT NOT NULL AUTO_INCREMENT,
  id_ticket     INT NOT NULL,
  accion        TEXT NOT NULL,
  fecha_avance  DATE NOT NULL,
  hora_avance   TIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_avance_ticket (id_ticket)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- La tabla antigua era latin1 con accion VARCHAR(255): se pasa a utf8mb4 y TEXT
ALTER TABLE avance_tecnicos CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE avance_tecnicos MODIFY accion TEXT NOT NULL;

SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'avance_tecnicos' AND COLUMN_NAME = 'lado');
SET @sql := IF(@existe = 0,
  "ALTER TABLE avance_tecnicos
     ADD COLUMN id_usuario INT NULL AFTER hora_avance,
     ADD COLUMN lado ENUM('tecnico','admin','sistema') NULL DEFAULT NULL AFTER id_usuario,
     ADD COLUMN leido TINYINT(1) NOT NULL DEFAULT 1 AFTER lado,
     ADD COLUMN eliminado TINYINT(1) NOT NULL DEFAULT 0 AFTER leido",
  'SELECT ''columnas de bitácora ya existen''');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;
-- (leido nace en 1 para que los avances antiguos no aparezcan como nuevos;
--  el código guarda 0 en cada entrada nueva)

UPDATE avance_tecnicos SET lado = 'tecnico' WHERE lado IS NULL;

SET @existe := (SELECT COUNT(*) FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'avance_tecnicos' AND INDEX_NAME = 'idx_avance_no_leidos');
SET @sql := IF(@existe = 0,
  'ALTER TABLE avance_tecnicos ADD INDEX idx_avance_no_leidos (id_ticket, leido, lado)',
  'SELECT ''idx_avance_no_leidos ya existe''');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Revisión
SELECT lado, COUNT(*) AS entradas, SUM(leido = 0) AS sin_leer FROM avance_tecnicos GROUP BY lado;
