-- Ejecutar MANUALMENTE en la base sistema_panel_central del entorno correspondiente.
-- No ejecutar desde Codex ni durante el despliegue.
CREATE TABLE IF NOT EXISTS comunicaciones_envios (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 id_administrador INT NOT NULL,
 id_destinatario INT NOT NULL,
 email VARCHAR(190) NOT NULL,
 asunto VARCHAR(150) NOT NULL,
 estado ENUM('enviado','error') NOT NULL,
 detalle VARCHAR(255) NOT NULL DEFAULT '',
 firma CHAR(64) NOT NULL,
 creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX idx_comunicaciones_fecha (creado_en),
 INDEX idx_comunicaciones_destinatario (id_destinatario),
 INDEX idx_comunicaciones_firma (firma)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
