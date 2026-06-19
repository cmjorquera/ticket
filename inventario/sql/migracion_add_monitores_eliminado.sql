-- Migración: agregar columna eliminado a monitores (eliminación lógica)
-- Ejecutar una sola vez en producción.

ALTER TABLE `monitores`
    ADD COLUMN `eliminado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `observacion`;

ALTER TABLE `monitores`
    ADD KEY `idx_monitor_eliminado` (`eliminado`);
