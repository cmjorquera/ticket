-- --------------------------------------------------------
-- Migración: agregar columna id_usuario_registra a la tabla equipos
-- Ejecutar una sola vez en el servidor de base de datos.
-- --------------------------------------------------------

-- 1. Agregar columna id_usuario_registra (nullable, guarda el usuario que registró el equipo)
ALTER TABLE `equipos`
  ADD COLUMN `id_usuario_registra` INT(11) NULL DEFAULT NULL AFTER `id_usuario_asignado`;

-- 2. Índice para búsquedas por usuario registrador
ALTER TABLE `equipos`
  ADD KEY `idx_equipos_usuario_registra` (`id_usuario_registra`);

-- 3. (Opcional) Backfill: copiar id_usuario como id_usuario_registra en equipos existentes
--    Ejecutar solo si se desea mantener trazabilidad histórica de registros anteriores.
-- UPDATE `equipos` SET `id_usuario_registra` = `id_usuario` WHERE `id_usuario_registra` IS NULL;

-- --------------------------------------------------------
-- Nota: id_usuario_registra es inmutable una vez creado el equipo.
-- Se autocompleta desde $_SESSION['id'] al guardar; nunca desde el formulario.
-- La FK formal se puede agregar después:
--
-- ALTER TABLE `equipos`
--   ADD CONSTRAINT `fk_equipos_usuario_registra`
--   FOREIGN KEY (`id_usuario_registra`) REFERENCES `usuarios` (`id`)
--   ON DELETE SET NULL ON UPDATE CASCADE;
-- --------------------------------------------------------
