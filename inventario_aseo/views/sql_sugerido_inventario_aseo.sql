CREATE TABLE IF NOT EXISTS aseo_categoria_catalogo (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(100) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1
);

INSERT INTO aseo_categoria_catalogo (nombre_categoria, activo) VALUES
('Desinfectantes', 1),
('Limpieza general', 1),
('Pisos', 1),
('Banos', 1),
('Vidrios', 1),
('Lavanderia', 1),
('Consumibles', 1),
('Otro', 1)
ON DUPLICATE KEY UPDATE activo = VALUES(activo);

CREATE TABLE IF NOT EXISTS aseo_unidad_catalogo (
    id_unidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre_unidad VARCHAR(80) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1
);

INSERT INTO aseo_unidad_catalogo (nombre_unidad, activo) VALUES
('Litro', 1),
('Botella', 1),
('Bidon', 1),
('Unidad', 1),
('Caja', 1),
('Bolsa', 1),
('Paquete', 1),
('Galon', 1)
ON DUPLICATE KEY UPDATE activo = VALUES(activo);

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
    CONSTRAINT fk_aseo_producto_colegio FOREIGN KEY (id_colegio) REFERENCES colegio(id_colegio),
    CONSTRAINT fk_aseo_producto_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

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
    CONSTRAINT fk_aseo_movimiento_producto FOREIGN KEY (id_producto) REFERENCES aseo_productos(id_producto) ON DELETE CASCADE,
    CONSTRAINT fk_aseo_movimiento_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

CREATE TABLE IF NOT EXISTS aseo_historial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    accion VARCHAR(60) NOT NULL,
    descripcion TEXT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_aseo_historial_producto FOREIGN KEY (id_producto) REFERENCES aseo_productos(id_producto) ON DELETE CASCADE,
    CONSTRAINT fk_aseo_historial_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);
