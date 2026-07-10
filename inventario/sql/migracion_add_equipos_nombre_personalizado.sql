-- --------------------------------------------------------
-- Migración: agregar columna nombre_personalizado a la tabla equipos
-- Ejecutar una sola vez en el servidor de base de datos.
-- --------------------------------------------------------

-- 1. Agregar columna nombre_personalizado (nullable, nombre amigable escrito por el usuario)
ALTER TABLE `equipos`
  ADD COLUMN `nombre_personalizado` VARCHAR(150) NULL DEFAULT NULL AFTER `nombre_equipo`;

-- --------------------------------------------------------
-- Nota: nombre_personalizado es el nombre amigable escrito por el usuario
-- (ej: "PC Direccion"). No reemplaza a nombre_equipo, que sigue siendo el
-- identificador tecnico generado automaticamente como PC-{numero_serie}.
--
-- Registros existentes quedan con nombre_personalizado = NULL. El sistema
-- debe soportarlo: si es NULL o vacio, se muestra solo nombre_equipo.
-- No se debe hacer backfill inventando nombres para equipos antiguos.
-- --------------------------------------------------------
