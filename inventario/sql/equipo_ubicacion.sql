-- --------------------------------------------------------
-- Tabla: equipo_ubicacion
-- Objetivo: Ubicaciones internas por colegio para asignación de equipos.
-- Cada colegio tiene sus propias ubicaciones.
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `equipo_ubicacion` (
  `id_ubicacion`     INT(11)      NOT NULL AUTO_INCREMENT,
  `id_colegio`       INT(11)      NOT NULL,
  `nombre_ubicacion` VARCHAR(150) NOT NULL,
  `tipo_ubicacion`   VARCHAR(100) DEFAULT NULL,
  `descripcion`      TEXT         DEFAULT NULL,
  `estado`           TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_ubicacion`),
  KEY `idx_equipo_ubicacion_colegio` (`id_colegio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Índice único para evitar ubicaciones duplicadas por colegio
-- Ejecutar solo si el índice no existe aún:
-- ALTER TABLE `equipo_ubicacion`
--   ADD UNIQUE KEY `uq_ubicacion_colegio_nombre` (`id_colegio`, `nombre_ubicacion`);

-- --------------------------------------------------------
-- Carga inicial de ubicaciones para todos los colegios activos
-- Usa SELECT para no hardcodear IDs de colegios.
-- El NOT EXISTS evita duplicados si el script se corre más de una vez.
-- --------------------------------------------------------

INSERT INTO equipo_ubicacion (id_colegio, nombre_ubicacion, tipo_ubicacion, descripcion, estado)
SELECT c.id_colegio, 'Sala Computación 1', 'Sala Computación', 'Sala principal de computadores del colegio.', 1
FROM colegio c
WHERE c.estado = 1
AND NOT EXISTS (
    SELECT 1 FROM equipo_ubicacion eu
    WHERE eu.id_colegio = c.id_colegio AND eu.nombre_ubicacion = 'Sala Computación 1'
);

INSERT INTO equipo_ubicacion (id_colegio, nombre_ubicacion, tipo_ubicacion, descripcion, estado)
SELECT c.id_colegio, 'Sala Computación 2', 'Sala Computación', 'Segunda sala de computadores del colegio.', 1
FROM colegio c
WHERE c.estado = 1
AND NOT EXISTS (
    SELECT 1 FROM equipo_ubicacion eu
    WHERE eu.id_colegio = c.id_colegio AND eu.nombre_ubicacion = 'Sala Computación 2'
);

INSERT INTO equipo_ubicacion (id_colegio, nombre_ubicacion, tipo_ubicacion, descripcion, estado)
SELECT c.id_colegio, 'Biblioteca', 'Biblioteca', 'Equipos ubicados en biblioteca.', 1
FROM colegio c
WHERE c.estado = 1
AND NOT EXISTS (
    SELECT 1 FROM equipo_ubicacion eu
    WHERE eu.id_colegio = c.id_colegio AND eu.nombre_ubicacion = 'Biblioteca'
);

INSERT INTO equipo_ubicacion (id_colegio, nombre_ubicacion, tipo_ubicacion, descripcion, estado)
SELECT c.id_colegio, 'Dirección', 'Oficina', 'Equipos ubicados en dirección.', 1
FROM colegio c
WHERE c.estado = 1
AND NOT EXISTS (
    SELECT 1 FROM equipo_ubicacion eu
    WHERE eu.id_colegio = c.id_colegio AND eu.nombre_ubicacion = 'Dirección'
);

INSERT INTO equipo_ubicacion (id_colegio, nombre_ubicacion, tipo_ubicacion, descripcion, estado)
SELECT c.id_colegio, 'UTP', 'Oficina', 'Equipos ubicados en UTP.', 1
FROM colegio c
WHERE c.estado = 1
AND NOT EXISTS (
    SELECT 1 FROM equipo_ubicacion eu
    WHERE eu.id_colegio = c.id_colegio AND eu.nombre_ubicacion = 'UTP'
);

INSERT INTO equipo_ubicacion (id_colegio, nombre_ubicacion, tipo_ubicacion, descripcion, estado)
SELECT c.id_colegio, 'Inspectoría', 'Oficina', 'Equipos ubicados en inspectoría.', 1
FROM colegio c
WHERE c.estado = 1
AND NOT EXISTS (
    SELECT 1 FROM equipo_ubicacion eu
    WHERE eu.id_colegio = c.id_colegio AND eu.nombre_ubicacion = 'Inspectoría'
);

INSERT INTO equipo_ubicacion (id_colegio, nombre_ubicacion, tipo_ubicacion, descripcion, estado)
SELECT c.id_colegio, 'Enfermería', 'Enfermería', 'Equipo ubicado en enfermería.', 1
FROM colegio c
WHERE c.estado = 1
AND NOT EXISTS (
    SELECT 1 FROM equipo_ubicacion eu
    WHERE eu.id_colegio = c.id_colegio AND eu.nombre_ubicacion = 'Enfermería'
);

INSERT INTO equipo_ubicacion (id_colegio, nombre_ubicacion, tipo_ubicacion, descripcion, estado)
SELECT c.id_colegio, 'Portería', 'Portería', 'Equipo ubicado en portería.', 1
FROM colegio c
WHERE c.estado = 1
AND NOT EXISTS (
    SELECT 1 FROM equipo_ubicacion eu
    WHERE eu.id_colegio = c.id_colegio AND eu.nombre_ubicacion = 'Portería'
);

INSERT INTO equipo_ubicacion (id_colegio, nombre_ubicacion, tipo_ubicacion, descripcion, estado)
SELECT c.id_colegio, 'Laboratorio Ciencias', 'Laboratorio', 'Equipos ubicados en laboratorio de ciencias.', 1
FROM colegio c
WHERE c.estado = 1
AND NOT EXISTS (
    SELECT 1 FROM equipo_ubicacion eu
    WHERE eu.id_colegio = c.id_colegio AND eu.nombre_ubicacion = 'Laboratorio Ciencias'
);

INSERT INTO equipo_ubicacion (id_colegio, nombre_ubicacion, tipo_ubicacion, descripcion, estado)
SELECT c.id_colegio, 'Bodega Informática', 'Bodega', 'Equipos guardados o disponibles en bodega informática.', 1
FROM colegio c
WHERE c.estado = 1
AND NOT EXISTS (
    SELECT 1 FROM equipo_ubicacion eu
    WHERE eu.id_colegio = c.id_colegio AND eu.nombre_ubicacion = 'Bodega Informática'
);
