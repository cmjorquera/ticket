-- =====================================================================
--  INSTALACIÓN DEL MÓDULO INVENTARIO (Inventario PC + Monitores)
--  Base de datos: crist668_sistema_panel_central (QA)
--  Generado desde inventario/logica/tablas/ del sistema antiguo:
--  SOLO ESTRUCTURA (sin datos de producción) + catálogos de estados y tipos.
--  Seguro: CREATE TABLE IF NOT EXISTS / INSERT IGNORE. No borra nada y se
--  puede ejecutar más de una vez.
-- =====================================================================

-- Base de datos donde se ejecuta (QA). En PRODUCCIÓN cambiar este nombre.
-- Va explícita para que phpMyAdmin no la ejecute en otra base por error.
USE `crist668_sistema_panel_central`;


SET NAMES utf8mb4;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- ------------------------------------------------------------ usuario_colegio
CREATE TABLE IF NOT EXISTS `usuario_colegio` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_perfil` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1 COMMENT '1 = activo | 0 = inactivo',
  `fecha_asignacion` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_usuario_colegio` (`id_usuario`,`id_colegio`,`id_perfil`),
  KEY `idx_usuario` (`id_usuario`),
  KEY `idx_colegio` (`id_colegio`),
  KEY `idx_estado` (`estado`),
  KEY `idx_usuario_colegio_id_usuario` (`id_usuario`),
  KEY `idx_usuario_colegio_id_colegio` (`id_colegio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ estado_equipo
CREATE TABLE IF NOT EXISTS `estado_equipo` (
  `id_estado` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_estado` varchar(100) NOT NULL,
  `color_badge` varchar(50) NOT NULL,
  `estado` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id_estado`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT IGNORE INTO `estado_equipo` (`id_estado`, `nombre_estado`, `color_badge`, `estado`) VALUES
(1, 'Activo', 'success', 1),
(2, 'Bodega', 'secondary', 1),
(3, 'Reparación', 'warning', 1),
(4, 'Baja', 'danger', 1),
(5, 'Prestado', 'info', 1);

-- ------------------------------------------------------------ tipo_pc_catalogo
CREATE TABLE IF NOT EXISTS `tipo_pc_catalogo` (
  `id_tipo_pc` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_tipo` varchar(100) NOT NULL,
  `activo` tinyint(4) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_tipo_pc`),
  UNIQUE KEY `uq_tipo_pc_nombre` (`nombre_tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
INSERT IGNORE INTO `tipo_pc_catalogo` (`id_tipo_pc`, `nombre_tipo`, `activo`) VALUES
(1, 'Desktop', 1),
(2, 'Notebook', 1),
(3, 'Tablet', 1),
(4, 'Otro', 1);

-- ------------------------------------------------------------ equipo_ubicacion
CREATE TABLE IF NOT EXISTS `equipo_ubicacion` (
  `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_colegio` int(11) NOT NULL,
  `nombre_ubicacion` varchar(150) NOT NULL,
  `tipo_ubicacion` varchar(80) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT 1,
  PRIMARY KEY (`id_ubicacion`),
  UNIQUE KEY `uq_ubicacion_colegio_nombre` (`id_colegio`,`nombre_ubicacion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ equipos
CREATE TABLE IF NOT EXISTS `equipos` (
  `id_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) DEFAULT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_ubicacion` int(11) DEFAULT NULL,
  `id_usuario_asignado` int(11) DEFAULT NULL,
  `id_usuario_registra` int(11) DEFAULT NULL,
  `nombre_equipo` varchar(255) DEFAULT NULL,
  `nombre_personalizado` varchar(150) DEFAULT NULL,
  `fabricante` varchar(255) DEFAULT NULL,
  `producto` varchar(255) DEFAULT NULL,
  `numero_serie` varchar(255) DEFAULT NULL,
  `tipo_pc` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `id_estado` int(11) NOT NULL DEFAULT 1,
  `eliminado` tinyint(4) NOT NULL DEFAULT 0,
  `fecha_eliminado` datetime DEFAULT NULL,
  `id_usuario_elimina` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_equipo`),
  UNIQUE KEY `ux_equipos_numero_serie` (`numero_serie`),
  KEY `idx_equipos_asignado` (`id_usuario_asignado`),
  KEY `idx_equipos_colegio` (`id_colegio`),
  KEY `idx_equipos_creado_por` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ equipos_compra
CREATE TABLE IF NOT EXISTS `equipos_compra` (
  `id_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `valor_equipo` int(10) DEFAULT NULL,
  `proveedor` varchar(255) DEFAULT NULL,
  `numero_factura` varchar(255) DEFAULT NULL,
  `fecha_compra` date DEFAULT '0000-00-00',
  `observacion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_equipo`),
  CONSTRAINT `equipos_compra_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ equipo_almacenamiento
CREATE TABLE IF NOT EXISTS `equipo_almacenamiento` (
  `id_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `equipo_modelo` varchar(100) DEFAULT NULL,
  `equipo_capacidad` varchar(50) DEFAULT NULL,
  `equipo_tamano` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_equipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ equipo_memoria
CREATE TABLE IF NOT EXISTS `equipo_memoria` (
  `id_memoria` int(11) NOT NULL AUTO_INCREMENT,
  `id_equipo` int(11) DEFAULT NULL,
  `designacion_memoria` varchar(255) DEFAULT NULL,
  `formato_memoria` varchar(255) DEFAULT NULL,
  `tipo_memoria` varchar(255) DEFAULT NULL,
  `tamano_memoria` int(11) DEFAULT NULL,
  `frecuencia_memoria` int(11) DEFAULT NULL,
  `marca_memoria` varchar(255) DEFAULT NULL,
  `orden_memoria` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_memoria`),
  KEY `id_equipo` (`id_equipo`),
  CONSTRAINT `equipo_memoria_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ equipo_monitor
CREATE TABLE IF NOT EXISTS `equipo_monitor` (
  `id_monitor` int(11) NOT NULL AUTO_INCREMENT,
  `id_equipo` int(11) DEFAULT NULL,
  `modelo_monitor` varchar(255) DEFAULT NULL,
  `codigo_monitor` varchar(255) DEFAULT NULL,
  `serie_monitor` varchar(11) DEFAULT NULL,
  `tamano_monitor` int(10) DEFAULT NULL,
  `resolucion_monitor` varchar(50) DEFAULT NULL,
  `orden_monitor` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_monitor`),
  KEY `id_equipo` (`id_equipo`),
  CONSTRAINT `equipo_monitor_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ equipo_procesador
CREATE TABLE IF NOT EXISTS `equipo_procesador` (
  `id_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `equipo_fabricante` varchar(100) DEFAULT NULL,
  `equipo_modelo` varchar(100) DEFAULT NULL,
  `equipo_velocidad` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_equipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ equipo_software
CREATE TABLE IF NOT EXISTS `equipo_software` (
  `id_equipo` int(11) NOT NULL AUTO_INCREMENT,
  `windows` varchar(50) DEFAULT NULL,
  `office` varchar(100) DEFAULT NULL,
  `antivirus` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_equipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ equipo_fotos
CREATE TABLE IF NOT EXISTS `equipo_fotos` (
  `id_foto` int(11) NOT NULL AUTO_INCREMENT,
  `id_equipo` int(11) NOT NULL,
  `ruta_foto` varchar(255) NOT NULL,
  `tipo_foto` enum('normal','otro') NOT NULL DEFAULT 'normal',
  `principal` tinyint(1) NOT NULL DEFAULT 0,
  `orden_foto` int(11) NOT NULL DEFAULT 1,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_foto`),
  KEY `idx_equipo_fotos_equipo` (`id_equipo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ equipo_movimiento
CREATE TABLE IF NOT EXISTS `equipo_movimiento` (
  `id_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_equipo` int(11) NOT NULL,
  `id_ubicacion_origen` int(11) DEFAULT NULL,
  `id_ubicacion_destino` int(11) NOT NULL,
  `id_usuario_movimiento` int(11) NOT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT current_timestamp(),
  `motivo` varchar(255) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  PRIMARY KEY (`id_movimiento`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ equipo_estado_historial
CREATE TABLE IF NOT EXISTS `equipo_estado_historial` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `id_equipo` int(11) NOT NULL,
  `id_estado_anterior` int(11) DEFAULT NULL,
  `id_estado_nuevo` int(11) NOT NULL,
  `id_usuario_accion` int(11) NOT NULL,
  `fecha_accion` datetime NOT NULL DEFAULT current_timestamp(),
  `observacion` text DEFAULT NULL,
  PRIMARY KEY (`id_historial`),
  KEY `idx_equipo_estado_equipo` (`id_equipo`),
  KEY `idx_equipo_estado_anterior` (`id_estado_anterior`),
  KEY `idx_equipo_estado_nuevo` (`id_estado_nuevo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ equipo_asignacion_historial
CREATE TABLE IF NOT EXISTS `equipo_asignacion_historial` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_equipo` int(11) NOT NULL,
  `id_usuario_anterior` int(11) DEFAULT NULL,
  `id_usuario_nuevo` int(11) DEFAULT NULL,
  `id_usuario_accion` int(11) NOT NULL,
  `fecha_accion` datetime DEFAULT current_timestamp(),
  `motivo` varchar(255) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  PRIMARY KEY (`id_asignacion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ monitores
CREATE TABLE IF NOT EXISTS `monitores` (
  `id_monitor` int(11) NOT NULL AUTO_INCREMENT,
  `id_monitor_legacy` int(11) DEFAULT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_ubicacion` int(11) DEFAULT NULL,
  `id_usuario_asignado` int(11) DEFAULT NULL,
  `id_usuario_registra` int(11) DEFAULT NULL,
  `id_estado` int(11) NOT NULL DEFAULT 1,
  `nombre_monitor` varchar(150) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(150) DEFAULT NULL,
  `numero_serie` varchar(150) DEFAULT NULL,
  `codigo_interno` varchar(100) DEFAULT NULL,
  `tamano_monitor` varchar(50) DEFAULT NULL,
  `resolucion_monitor` varchar(100) DEFAULT NULL,
  `tipo_panel` varchar(100) DEFAULT NULL,
  `tipo_conexion` varchar(150) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `eliminado` tinyint(1) NOT NULL DEFAULT 0,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_monitor`),
  UNIQUE KEY `uq_monitores_legacy` (`id_monitor_legacy`),
  KEY `idx_monitor_colegio` (`id_colegio`),
  KEY `idx_monitor_ubicacion` (`id_ubicacion`),
  KEY `idx_monitor_usuario` (`id_usuario_asignado`),
  KEY `idx_monitor_estado` (`id_estado`),
  KEY `idx_monitor_eliminado` (`eliminado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ monitor_compra
CREATE TABLE IF NOT EXISTS `monitor_compra` (
  `id_monitor` int(11) NOT NULL,
  `valor_monitor` int(11) DEFAULT NULL,
  `proveedor` varchar(150) DEFAULT NULL,
  `numero_factura` varchar(100) DEFAULT NULL,
  `fecha_compra` date DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  PRIMARY KEY (`id_monitor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ monitor_fotos
CREATE TABLE IF NOT EXISTS `monitor_fotos` (
  `id_foto` int(11) NOT NULL AUTO_INCREMENT,
  `id_monitor` int(11) NOT NULL,
  `ruta_foto` varchar(255) NOT NULL,
  `tipo_foto` enum('normal','panoramica') NOT NULL DEFAULT 'normal',
  `principal` tinyint(4) NOT NULL DEFAULT 0,
  `orden_foto` int(11) NOT NULL DEFAULT 0,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_foto`),
  KEY `idx_monitor_fotos_monitor` (`id_monitor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ monitor_movimiento
CREATE TABLE IF NOT EXISTS `monitor_movimiento` (
  `id_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_monitor` int(11) NOT NULL,
  `id_ubicacion_origen` int(11) DEFAULT NULL,
  `id_ubicacion_destino` int(11) DEFAULT NULL,
  `id_usuario_movimiento` int(11) NOT NULL,
  `fecha_movimiento` datetime NOT NULL DEFAULT current_timestamp(),
  `motivo` varchar(255) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  PRIMARY KEY (`id_movimiento`),
  KEY `idx_monitor_mov_monitor` (`id_monitor`),
  KEY `idx_monitor_mov_origen` (`id_ubicacion_origen`),
  KEY `idx_monitor_mov_destino` (`id_ubicacion_destino`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ------------------------------------------------------------ monitor_asignacion_historial
CREATE TABLE IF NOT EXISTS `monitor_asignacion_historial` (
  `id_asignacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_monitor` int(11) NOT NULL,
  `id_usuario_anterior` int(11) DEFAULT NULL,
  `id_usuario_nuevo` int(11) DEFAULT NULL,
  `id_usuario_accion` int(11) NOT NULL,
  `fecha_accion` datetime DEFAULT current_timestamp(),
  `motivo` varchar(255) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  PRIMARY KEY (`id_asignacion`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- (Las columnas de migraciones antiguas ya van incluidas en los CREATE TABLE.
--  Compatible con MySQL y MariaDB.)

-- ---------------------------------------------------------------------
-- VERIFICACIÓN (opcional, en la pestaña SQL): deben aparecer las tablas
-- equipos, equipo_*, monitor*, estado_equipo, tipo_pc_catalogo y usuario_colegio.
--   SHOW TABLES;
-- (No se consulta information_schema: en phpMyAdmin de HostGator el usuario
--  temporal no tiene permiso y muestra un error aunque todo se haya creado.)
-- ---------------------------------------------------------------------
