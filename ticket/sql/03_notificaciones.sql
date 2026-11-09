-- =====================================================================
-- NOTIFICACIONES (campanita de la barra superior)
-- Lógica: ticket/logica/LOGICA_NOTIFICACIONES.txt
-- MySQL 5.7. Se puede ejecutar más de una vez.
-- Vuelta atrás: DROP TABLE notificaciones;
-- =====================================================================
CREATE TABLE IF NOT EXISTS notificaciones (
  id            INT NOT NULL AUTO_INCREMENT,
  id_usuario    INT NOT NULL                 COMMENT 'quién la recibe',
  tipo          VARCHAR(40) NOT NULL         COMMENT 'ticket_creado, ticket_asignado, ticket_comenzado, ticket_estado, ticket_mensaje, ticket_bitacora',
  titulo        VARCHAR(150) NOT NULL,
  texto         VARCHAR(255) NOT NULL DEFAULT '',
  url           VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'relativa a la raíz del sitio',
  id_ticket     INT NULL,
  id_actor      INT NULL                     COMMENT 'quién provocó el aviso',
  cantidad      INT NOT NULL DEFAULT 1       COMMENT 'mensajes agrupados en un solo aviso',
  leida         TINYINT(1) NOT NULL DEFAULT 0,
  creada        DATETIME NOT NULL,
  actualizada   DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_notif_usuario (id_usuario, leida, actualizada),
  KEY idx_notif_ticket (id_usuario, id_ticket, tipo, leida)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'notificaciones lista' AS resultado, COUNT(*) AS filas FROM notificaciones;
