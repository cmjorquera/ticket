-- =====================================================================
--  Mensajes – instalación (módulo mensajes/)
--  * Compatible con MySQL 5.7/8 (QA) y MariaDB. Se puede ejecutar varias veces.
--  * No borra mensajes. Crea mensajes_chat si falta (misma estructura de
--    sql/mensajes_chat.sql) y la tabla de carpetas por usuario.
--  * Apunta el menú Mensajes a la carpeta nueva.
-- =====================================================================

-- Base de datos donde se ejecuta (QA). En PRODUCCIÓN cambiar este nombre.
USE `crist668_sistema_panel_central`;

CREATE TABLE IF NOT EXISTS `mensajes_chat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_padre` int(11) DEFAULT NULL,
  `id_conversacion` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `de` int(11) NOT NULL,
  `para` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT CURRENT_TIMESTAMP,
  `leido` tinyint(1) DEFAULT 0,
  `urgente` tinyint(1) DEFAULT 0,
  `eliminado` tinyint(1) DEFAULT 0,
  `prioridad` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_mensajes_para` (`para`, `leido`, `eliminado`),
  KEY `idx_mensajes_de` (`de`),
  KEY `idx_mensajes_conversacion` (`id_conversacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Archivado / eliminado POR USUARIO (no afecta a la otra persona)
CREATE TABLE IF NOT EXISTS `mensajes_chat_carpeta` (
  `id_usuario` int(11) NOT NULL,
  `id_conversacion` int(11) NOT NULL,
  `carpeta` enum('archivado','eliminado') NOT NULL,
  `actualizado_en` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`, `id_conversacion`),
  KEY `idx_carpeta_conversacion` (`id_conversacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Menú: Mensajes > Enviados / Recibidos / Archivados
UPDATE menu_1      SET archivo = 'mensajes/index.php'      WHERE id_menu = 2;
UPDATE menu_1_sub  SET archivo = 'mensajes/enviados.php'   WHERE id_submenu = 1;
UPDATE menu_1_sub  SET archivo = 'mensajes/index.php'      WHERE id_submenu = 2;
UPDATE menu_1_sub  SET archivo = 'mensajes/archivados.php' WHERE id_submenu = 3;

SELECT 'mensajes_chat' AS tabla, COUNT(*) AS filas FROM mensajes_chat
UNION ALL SELECT 'mensajes_chat_carpeta', COUNT(*) FROM mensajes_chat_carpeta;
SELECT id_submenu, nombre, archivo FROM menu_1_sub WHERE id_submenu IN (1, 2, 3);
