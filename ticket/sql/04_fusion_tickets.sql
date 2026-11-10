-- Fusión de tickets (QA). Ejecutar manualmente en phpMyAdmin ANTES de habilitar la fusión.
-- Compatible con MySQL 5.7; no altera registros existentes.
CREATE TABLE IF NOT EXISTS ticket_fusiones (
 id_fusion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 id_ticket_principal INT NOT NULL,
 id_ticket_secundario INT NOT NULL,
 id_usuario_fusion INT NOT NULL,
 motivo VARCHAR(500) NOT NULL,
 fecha_fusion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 fecha_cierre_automatico DATETIME NULL,
 UNIQUE KEY uq_fusion_secundario (id_ticket_secundario),
 KEY idx_fusion_principal (id_ticket_principal),
 KEY idx_fusion_actor (id_usuario_fusion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE IF NOT EXISTS ticket_fusion_eventos (
 id_evento BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 id_fusion BIGINT UNSIGNED NOT NULL,
 id_ticket INT NOT NULL,
 id_usuario_actor INT NULL,
 tipo_evento VARCHAR(40) NOT NULL,
 detalle TEXT NULL,
 fecha_evento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 KEY idx_eventos_fusion (id_fusion, fecha_evento),
 KEY idx_eventos_ticket (id_ticket, fecha_evento),
 CONSTRAINT fk_ticket_fusion_evento FOREIGN KEY (id_fusion) REFERENCES ticket_fusiones(id_fusion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
