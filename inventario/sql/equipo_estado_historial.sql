CREATE TABLE IF NOT EXISTS equipo_estado_historial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT NOT NULL,
    id_estado_anterior INT NULL,
    id_estado_nuevo INT NOT NULL,
    id_usuario_accion INT NOT NULL,
    fecha_accion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    observacion TEXT NULL,
    INDEX idx_equipo_estado_equipo (id_equipo),
    INDEX idx_equipo_estado_anterior (id_estado_anterior),
    INDEX idx_equipo_estado_nuevo (id_estado_nuevo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
