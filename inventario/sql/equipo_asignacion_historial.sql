CREATE TABLE IF NOT EXISTS `equipo_asignacion_historial` (
    `id_asignacion`       INT          NOT NULL AUTO_INCREMENT,
    `id_equipo`           INT          NOT NULL,
    `id_usuario_anterior` INT          NULL,
    `id_usuario_nuevo`    INT          NULL,
    `id_usuario_accion`   INT          NOT NULL,
    `fecha_accion`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `motivo`              VARCHAR(255) NULL,
    `observacion`         TEXT         NULL,
    PRIMARY KEY (`id_asignacion`),
    INDEX `idx_equipo_asig_equipo`           (`id_equipo`),
    INDEX `idx_equipo_asig_usuario_anterior` (`id_usuario_anterior`),
    INDEX `idx_equipo_asig_usuario_nuevo`    (`id_usuario_nuevo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
