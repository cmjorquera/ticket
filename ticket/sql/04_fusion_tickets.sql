-- 04_fusion_tickets.sql
-- Ejecutar MANUALMENTE en QA mediante phpMyAdmin, antes de habilitar la funcionalidad.
-- No modifica ni elimina tickets existentes.
CREATE TABLE IF NOT EXISTS ticket_fusiones (
  id_fusion BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_ticket_principal INT NOT NULL,
  id_ticket_secundario INT NOT NULL,
  id_usuario_fusion INT NOT NULL,
  motivo VARCHAR(500) NOT NULL,
  fecha_fusion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_cierre_automatico DATETIME NULL,
  PRIMARY KEY (id_fusion),
  UNIQUE KEY uq_ticket_secundario (id_ticket_secundario),
  KEY idx_fusiones_principal (id_ticket_principal),
  KEY idx_fusiones_usuario (id_usuario_fusion),
  CONSTRAINT chk_fusion_distintos CHECK (id_ticket_principal <> id_ticket_secundario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Registro de eventos de fusión/cierre independiente de proceso_tickets.
CREATE TABLE IF NOT EXISTS ticket_fusion_eventos (
  id_evento BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_fusion BIGINT UNSIGNED NOT NULL,
  id_ticket INT NOT NULL,
  id_usuario_actor INT NULL,
  tipo_evento VARCHAR(40) NOT NULL,
  detalle TEXT NULL,
  fecha_evento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_evento),
  KEY idx_fusion_eventos_fusion (id_fusion, fecha_evento),
  KEY idx_fusion_eventos_ticket (id_ticket, fecha_evento),
  CONSTRAINT fk_fusion_evento FOREIGN KEY (id_fusion)
    REFERENCES ticket_fusiones(id_fusion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
