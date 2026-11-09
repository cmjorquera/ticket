-- =====================================================================
-- MENSAJERÍA DE TICKETS (chat usuario ↔ técnico)
-- Lógica: ticket/logica/LOGICA_MENSAJERIA.txt
-- MySQL 5.7. Se puede ejecutar más de una vez. Vuelta atrás: 99_volver_atras.sql
-- =====================================================================

-- 1) ticket_conversaciones.lado: desde qué bandeja se escribió el mensaje
SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_conversaciones' AND COLUMN_NAME = 'lado');
SET @sql := IF(@existe = 0,
  "ALTER TABLE ticket_conversaciones ADD COLUMN lado ENUM('usuario','tecnico','admin') NULL DEFAULT NULL AFTER receptor",
  'SELECT ''lado ya existe''');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Mensajes antiguos: si lo escribió quien pidió el ticket → usuario; si no → técnico
UPDATE ticket_conversaciones m
  JOIN tickets t ON t.id_ticket = m.id_ticket
   SET m.lado = IF(m.emisor = t.id_usuario, 'usuario', 'tecnico')
 WHERE m.lado IS NULL;

-- Índice para contar no leídos rápido
SET @existe := (SELECT COUNT(*) FROM information_schema.STATISTICS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ticket_conversaciones' AND INDEX_NAME = 'idx_tc_no_leidos');
SET @sql := IF(@existe = 0,
  'ALTER TABLE ticket_conversaciones ADD INDEX idx_tc_no_leidos (id_ticket, leido, eliminado)',
  'SELECT ''idx_tc_no_leidos ya existe''');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- 2) archivos_adjuntos_ticket: hoy solo tiene (id, id_ticket, adjunto).
--    Se agregan nombre original, tamaño, quién y cuándo. "adjunto" sigue siendo la ruta.
ALTER TABLE archivos_adjuntos_ticket MODIFY adjunto VARCHAR(255) NOT NULL;

SET @existe := (SELECT COUNT(*) FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'archivos_adjuntos_ticket' AND COLUMN_NAME = 'nombre_archivo');
SET @sql := IF(@existe = 0,
  'ALTER TABLE archivos_adjuntos_ticket
     ADD COLUMN nombre_archivo VARCHAR(255) NULL AFTER adjunto,
     ADD COLUMN tamano INT NULL AFTER nombre_archivo,
     ADD COLUMN id_usuario INT NULL AFTER tamano,
     ADD COLUMN fecha_subida DATETIME NULL AFTER id_usuario',
  'SELECT ''columnas de adjuntos ya existen''');
PREPARE s FROM @sql; EXECUTE s; DEALLOCATE PREPARE s;

-- Revisión
SELECT lado, COUNT(*) AS mensajes, SUM(COALESCE(leido,0) = 0) AS no_leidos
  FROM ticket_conversaciones GROUP BY lado;
