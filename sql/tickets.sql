-- Estructura mínima requerida por el módulo ticket/*.php.
-- Ejecutar en crist668_sistema_panel_central antes de habilitar el menú.
CREATE TABLE IF NOT EXISTS tickets (
    id_ticket INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_categoria INT NOT NULL,
    id_colegio INT NOT NULL,
    id_tecnico_asignado INT NULL,
    asunto VARCHAR(180) NOT NULL,
    descripcion TEXT NOT NULL,
    prioridad ENUM('baja','media','alta','crítica') NOT NULL DEFAULT 'media',
    estado ENUM('nuevo','en_proceso','resuelto','cerrado') NOT NULL DEFAULT 'nuevo',
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta DATETIME NULL,
    PRIMARY KEY (id_ticket),
    KEY idx_ticket_tecnico_estado (id_tecnico_asignado, estado),
    KEY idx_ticket_colegio_estado (id_colegio, estado),
    KEY idx_ticket_categoria (id_categoria),
    KEY idx_ticket_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ticket_adjuntos (
    id_adjunto INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_ticket INT UNSIGNED NOT NULL,
    nombre_original VARCHAR(255) NOT NULL,
    nombre_archivo VARCHAR(80) NOT NULL,
    tipo_mime VARCHAR(120) NOT NULL,
    tamano INT UNSIGNED NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_adjunto),
    KEY idx_adjunto_ticket (id_ticket),
    CONSTRAINT fk_adjunto_ticket FOREIGN KEY (id_ticket) REFERENCES tickets (id_ticket) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
