-- =====================================================================
--  MENSAJERÍA: tabla mensajes_chat (misma estructura del sistema antiguo)
--  Base de datos: crist668_sistema_panel_central (QA)
--  La usa Colaboradores para enviar mensajes. Seguro: no borra nada y se
--  puede ejecutar más de una vez.
-- =====================================================================

-- Base de datos donde se ejecuta (QA). En PRODUCCIÓN cambiar este nombre.
-- Va explícita para que phpMyAdmin no la ejecute en otra base por error.
USE `crist668_sistema_panel_central`;

CREATE TABLE IF NOT EXISTS `mensajes_chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_padre` int(11) DEFAULT NULL,
  `id_conversacion` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `de` int(11) NOT NULL,
  `para` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `leido` tinyint(1) DEFAULT 0,
  `urgente` tinyint(1) DEFAULT 0,
  `eliminado` tinyint(1) DEFAULT 0,
  `prioridad` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mensajes_para` (`para`, `leido`, `eliminado`),
  KEY `idx_mensajes_de` (`de`),
  KEY `idx_mensajes_conversacion` (`id_conversacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
