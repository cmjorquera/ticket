-- =====================================================================
--  INVENTARIO: columnas que faltaron en la primera instalación de QA
--  (el ALTER original usaba "ADD COLUMN IF NOT EXISTS", que MySQL no
--  admite). Ejecutar UNA sola vez, solo en QA, después de
--  inventario_instalacion.sql. Si se ejecuta de nuevo, MySQL avisa
--  "Duplicate column" y no cambia nada.
-- =====================================================================

USE `crist668_sistema_panel_central`;

ALTER TABLE `equipos`
  ADD COLUMN `nombre_personalizado` VARCHAR(150) NULL DEFAULT NULL AFTER `nombre_equipo`,
  ADD COLUMN `fecha_eliminado` DATETIME NULL AFTER `eliminado`,
  ADD COLUMN `id_usuario_elimina` INT NULL AFTER `fecha_eliminado`;

ALTER TABLE `monitores`
  ADD COLUMN `eliminado` TINYINT(1) NOT NULL DEFAULT 0 AFTER `observacion`,
  ADD KEY `idx_monitor_eliminado` (`eliminado`);
