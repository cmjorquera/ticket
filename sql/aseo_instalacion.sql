-- =====================================================================
--  Inventario de Aseo – instalación (solo estructura + catálogos)
--  Módulo: inventario_aseo/
--
--  * Compatible con MySQL 5.7/8 (QA) y MariaDB.
--  * Se puede ejecutar más de una vez: CREATE TABLE IF NOT EXISTS e
--    INSERT IGNORE; no borra ni modifica datos existentes.
--  * Sin llaves foráneas hacia `usuarios` ni `colegio` (tablas del sistema
--    antiguo que pueden ser MyISAM); los índices sí se crean. Las relaciones
--    internas del módulo sí tienen FK.
-- =====================================================================

-- Base de datos donde se ejecuta (QA). En PRODUCCIÓN cambiar este nombre.
USE `crist668_sistema_panel_central`;

CREATE TABLE IF NOT EXISTS aseo_categoria_catalogo (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY ux_aseo_categoria_nombre (nombre_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO aseo_categoria_catalogo (nombre_categoria, activo) VALUES
('Desinfectantes', 1),
('Limpieza general', 1),
('Pisos', 1),
('Baños', 1),
('Vidrios', 1),
('Lavandería', 1),
('Consumibles', 1),
('Otro', 1);

CREATE TABLE IF NOT EXISTS aseo_unidad_catalogo (
    id_unidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre_unidad VARCHAR(80) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY ux_aseo_unidad_nombre (nombre_unidad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO aseo_unidad_catalogo (nombre_unidad, activo) VALUES
('Litro', 1),
('Botella', 1),
('Bidón', 1),
('Unidad', 1),
('Caja', 1),
('Bolsa', 1),
('Paquete', 1),
('Galón', 1);

CREATE TABLE IF NOT EXISTS aseo_productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_colegio INT NOT NULL,
    nombre_producto VARCHAR(150) NOT NULL,
    categoria VARCHAR(100) NOT NULL,
    unidad_medida VARCHAR(80) NOT NULL,
    descripcion TEXT NULL,
    stock_minimo DECIMAL(12,2) NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_aseo_producto_colegio (id_colegio),
    KEY idx_aseo_producto_usuario (id_usuario),
    KEY idx_aseo_producto_activo (activo),
    KEY idx_aseo_producto_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS aseo_movimientos (
    id_movimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    tipo_movimiento ENUM('ingreso', 'salida') NOT NULL,
    cantidad DECIMAL(12,2) NOT NULL DEFAULT 0,
    fecha_movimiento DATETIME NOT NULL,
    responsable VARCHAR(150) DEFAULT '',
    observacion TEXT NULL,
    id_usuario INT NOT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_aseo_mov_producto (id_producto),
    KEY idx_aseo_mov_fecha (fecha_movimiento),
    KEY idx_aseo_mov_usuario (id_usuario),
    CONSTRAINT fk_aseo_movimiento_producto FOREIGN KEY (id_producto) REFERENCES aseo_productos (id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS aseo_historial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    accion VARCHAR(60) NOT NULL,
    descripcion TEXT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_aseo_historial_producto (id_producto),
    KEY idx_aseo_historial_usuario (id_usuario),
    CONSTRAINT fk_aseo_historial_producto FOREIGN KEY (id_producto) REFERENCES aseo_productos (id_producto) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Menú: quien ve "Inventario PC" (submenú 10) también ve
-- "Inventario Aseo" (submenú 8). Solo agrega, no quita permisos.
-- ---------------------------------------------------------------------
INSERT INTO permiso_sub_menu (id_usuario, id_submenu, permiso)
SELECT p.id_usuario, 8, 1
  FROM permiso_sub_menu p
 WHERE p.id_submenu = 10 AND p.permiso = 1
   AND NOT EXISTS (SELECT 1 FROM permiso_sub_menu x WHERE x.id_usuario = p.id_usuario AND x.id_submenu = 8);

SELECT 'aseo_productos' AS tabla, COUNT(*) AS filas FROM aseo_productos
UNION ALL SELECT 'aseo_categoria_catalogo', COUNT(*) FROM aseo_categoria_catalogo
UNION ALL SELECT 'aseo_unidad_catalogo', COUNT(*) FROM aseo_unidad_catalogo
UNION ALL SELECT 'permisos submenu 8', COUNT(*) FROM permiso_sub_menu WHERE id_submenu = 8;
