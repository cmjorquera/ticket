-- =====================================================================
-- MÓDULO EVENTOS (calendario tipo Google Calendar)
-- Lógica: eventos/logica/LOGICA.txt
-- MySQL 5.7. Se puede ejecutar más de una vez.
--   - Si `eventos` no existe (producción nueva), la crea completa.
--   - Si ya existe (QA / sistema antiguo, con sus eventos), solo agrega lo nuevo.
-- Vuelta atrás: 99_volver_atras.sql
-- =====================================================================

-- 1) EVENTOS ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS eventos (
  id                        INT NOT NULL AUTO_INCREMENT,
  titulo                    VARCHAR(255) NOT NULL,
  descripcion               TEXT NOT NULL,
  fecha_inicio              DATE NOT NULL,
  fecha_fin                 DATE NULL,
  hora_inicio               TIME NULL,
  hora_fin                  TIME NULL,
  hora_evento               TIME NOT NULL                COMMENT 'compatibilidad sistema antiguo (= hora_inicio)',
  con_audio                 TINYINT(1) DEFAULT 0,
  solo_presentacion         TINYINT(1) DEFAULT 0,
  musica_ambiental          TINYINT(1) DEFAULT 0,
  cantidad_personas         INT NOT NULL DEFAULT 0,
  ubicacion                 VARCHAR(150) NULL,
  tipo_evento               VARCHAR(50) NOT NULL DEFAULT 'reunion',
  estado                    VARCHAR(30) NOT NULL DEFAULT 'programado' COMMENT 'programado | cancelado | finalizado',
  color_evento              VARCHAR(20) NULL,
  observaciones_logisticas  TEXT NULL,
  correo_enviado            TINYINT(1) NOT NULL DEFAULT 0,
  creado_en                 TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en            DATETIME NULL,
  responsable_id            INT NOT NULL,
  eliminado                 VARCHAR(2) NOT NULL DEFAULT '0',
  id_colegio                INT NULL COMMENT 'colegio del evento',
  PRIMARY KEY (id),
  KEY idx_eventos_fecha_inicio (fecha_inicio),
  KEY idx_eventos_fecha_fin (fecha_fin),
  KEY idx_eventos_responsable (responsable_id),
  KEY idx_eventos_estado (estado),
  KEY idx_eventos_eliminado (eliminado),
  KEY idx_eventos_colegio (id_colegio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Columnas nuevas (si no existen): quién lo creó, todo el día, versión para
-- las invitaciones de calendario (.ics) y si se envió el recordatorio.
SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'eventos' AND COLUMN_NAME = 'creado_por');
SET @s := IF(@c = 0, 'ALTER TABLE eventos ADD COLUMN creado_por INT NULL AFTER responsable_id', 'SELECT ''creado_por ya existe''');
PREPARE p FROM @s; EXECUTE p; DEALLOCATE PREPARE p;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'eventos' AND COLUMN_NAME = 'todo_el_dia');
SET @s := IF(@c = 0, 'ALTER TABLE eventos ADD COLUMN todo_el_dia TINYINT(1) NOT NULL DEFAULT 0 AFTER hora_fin', 'SELECT ''todo_el_dia ya existe''');
PREPARE p FROM @s; EXECUTE p; DEALLOCATE PREPARE p;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'eventos' AND COLUMN_NAME = 'secuencia');
SET @s := IF(@c = 0, 'ALTER TABLE eventos ADD COLUMN secuencia INT NOT NULL DEFAULT 0 COMMENT ''versión de la invitación .ics'' AFTER correo_enviado', 'SELECT ''secuencia ya existe''');
PREPARE p FROM @s; EXECUTE p; DEALLOCATE PREPARE p;

SET @c := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'eventos' AND COLUMN_NAME = 'recordatorio_enviado');
SET @s := IF(@c = 0, 'ALTER TABLE eventos ADD COLUMN recordatorio_enviado TINYINT(1) NOT NULL DEFAULT 0 AFTER secuencia', 'SELECT ''recordatorio_enviado ya existe''');
PREPARE p FROM @s; EXECUTE p; DEALLOCATE PREPARE p;

-- Eventos antiguos: el creador conocido es el responsable
UPDATE eventos SET creado_por = responsable_id WHERE creado_por IS NULL;
UPDATE eventos SET fecha_fin = fecha_inicio WHERE fecha_fin IS NULL;

-- 2) INVITADOS ----------------------------------------------------------
-- Personas del sistema (id_usuario) o correos externos (id_usuario NULL).
-- token: enlace personal para responder Sí / No / Quizás desde el correo.
CREATE TABLE IF NOT EXISTS eventos_invitados (
  id            INT NOT NULL AUTO_INCREMENT,
  id_evento     INT NOT NULL,
  id_usuario    INT NULL,
  email         VARCHAR(150) NOT NULL,
  nombre        VARCHAR(150) NOT NULL DEFAULT '',
  respuesta     ENUM('pendiente','si','no','quizas') NOT NULL DEFAULT 'pendiente',
  respondido_en DATETIME NULL,
  token         CHAR(32) NOT NULL,
  creado_en     DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_evento_email (id_evento, email),
  UNIQUE KEY uk_evento_token (token),
  KEY idx_inv_usuario (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) HISTORIAL (lo usaba el sistema antiguo, pero la tabla no existía) -----
CREATE TABLE IF NOT EXISTS eventos_historial (
  id           INT NOT NULL AUTO_INCREMENT,
  id_evento    INT NOT NULL,
  accion       VARCHAR(30) NOT NULL COMMENT 'creacion | edicion | cancelacion | eliminacion | respuesta',
  descripcion  VARCHAR(500) NOT NULL DEFAULT '',
  id_usuario   INT NULL,
  creado_en    DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_hist_evento (id_evento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Revisión
SELECT (SELECT COUNT(*) FROM eventos) AS eventos,
       (SELECT COUNT(*) FROM eventos_invitados) AS invitados,
       (SELECT COUNT(*) FROM eventos_historial) AS historial;
