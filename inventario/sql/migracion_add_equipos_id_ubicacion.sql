-- --------------------------------------------------------
-- Migración: agregar columna id_ubicacion a la tabla equipos
-- Ejecutar una sola vez en el servidor de base de datos.
-- --------------------------------------------------------

-- 1. Agregar columna id_ubicacion (nullable, sin FK forzada por compatibilidad MyISAM/InnoDB mixta)
ALTER TABLE `equipos`
  ADD COLUMN `id_ubicacion` INT(11) NULL DEFAULT NULL AFTER `id_colegio`;

-- 2. Índice para acelerar búsquedas por ubicación
ALTER TABLE `equipos`
  ADD KEY `idx_equipos_ubicacion` (`id_ubicacion`);

-- --------------------------------------------------------
-- Nota: id_ubicacion referencia equipo_ubicacion.id_ubicacion (1:N lógico).
-- La FK formal se puede agregar después de verificar integridad de datos:
--
-- ALTER TABLE `equipos`
--   ADD CONSTRAINT `fk_equipos_ubicacion`
--   FOREIGN KEY (`id_ubicacion`) REFERENCES `equipo_ubicacion` (`id_ubicacion`)
--   ON DELETE SET NULL ON UPDATE CASCADE;
-- --------------------------------------------------------
