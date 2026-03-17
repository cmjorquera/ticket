CREATE TABLE IF NOT EXISTS estado_equipo (
    id_estado INT PRIMARY KEY,
    nombre_estado VARCHAR(60) NOT NULL,
    color_badge VARCHAR(20) NOT NULL DEFAULT 'secondary'
);

INSERT INTO estado_equipo (id_estado, nombre_estado, color_badge) VALUES
(1, 'Activo', 'success'),
(2, 'Bodega', 'secondary'),
(3, 'Reparacion', 'warning'),
(4, 'Baja', 'danger'),
(5, 'Prestado', 'info')
ON DUPLICATE KEY UPDATE nombre_estado = VALUES(nombre_estado), color_badge = VALUES(color_badge);

CREATE TABLE IF NOT EXISTS tipo_pc_catalogo (
    id_tipo_pc INT AUTO_INCREMENT PRIMARY KEY,
    nombre_tipo VARCHAR(80) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS equipo_fotos (
    id_foto INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT NOT NULL,
    ruta_foto VARCHAR(255) NOT NULL,
    tipo_foto ENUM('normal', 'panoramica') NOT NULL DEFAULT 'normal',
    principal TINYINT(1) NOT NULL DEFAULT 0,
    orden_foto INT NOT NULL DEFAULT 1,
    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_equipo_fotos_equipo FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS equipo_historial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT NOT NULL,
    accion VARCHAR(60) NOT NULL,
    descripcion TEXT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_equipo_historial_equipo FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo) ON DELETE CASCADE
);
