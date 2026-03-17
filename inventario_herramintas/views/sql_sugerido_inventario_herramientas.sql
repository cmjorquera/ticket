CREATE TABLE IF NOT EXISTS estado_herramienta (
    id_estado INT PRIMARY KEY,
    nombre_estado VARCHAR(60) NOT NULL,
    color_badge VARCHAR(20) NOT NULL DEFAULT 'secondary'
);

INSERT INTO estado_herramienta (id_estado, nombre_estado, color_badge) VALUES
(1, 'Disponible', 'success'),
(2, 'En bodega', 'secondary'),
(3, 'En reparacion', 'warning'),
(4, 'Baja', 'danger'),
(5, 'Prestada', 'info'),
(6, 'Extraviada', 'dark')
ON DUPLICATE KEY UPDATE
    nombre_estado = VALUES(nombre_estado),
    color_badge = VALUES(color_badge);

CREATE TABLE IF NOT EXISTS categoria_herramienta_catalogo (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    nombre_categoria VARCHAR(80) NOT NULL UNIQUE,
    activo TINYINT(1) NOT NULL DEFAULT 1
);

INSERT INTO categoria_herramienta_catalogo (nombre_categoria, activo) VALUES
('Manual', 1),
('Electrica', 1),
('Medicion', 1),
('Seguridad', 1),
('Mantencion', 1),
('Jardineria', 1),
('Limpieza', 1),
('Otro', 1)
ON DUPLICATE KEY UPDATE
    activo = VALUES(activo);

CREATE TABLE IF NOT EXISTS herramientas (
    id_herramienta INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_colegio INT NOT NULL,
    id_usuario_asignado INT NULL,
    nombre_herramienta VARCHAR(150) NOT NULL,
    marca VARCHAR(120) DEFAULT '',
    modelo VARCHAR(120) DEFAULT '',
    numero_serie VARCHAR(120) NOT NULL,
    categoria VARCHAR(80) NOT NULL,
    qr_code VARCHAR(120) NOT NULL,
    id_estado INT NOT NULL DEFAULT 1,
    cantidad INT NOT NULL DEFAULT 1,
    stock_minimo INT NOT NULL DEFAULT 0,
    ubicacion VARCHAR(150) DEFAULT '',
    observaciones TEXT NULL,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_herramientas_numero_serie (numero_serie),
    UNIQUE KEY uq_herramientas_qr_code (qr_code),
    KEY idx_herramientas_colegio (id_colegio),
    KEY idx_herramientas_usuario (id_usuario),
    KEY idx_herramientas_usuario_asignado (id_usuario_asignado),
    KEY idx_herramientas_estado (id_estado),
    KEY idx_herramientas_categoria (categoria),
    CONSTRAINT fk_herramientas_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    CONSTRAINT fk_herramientas_colegio FOREIGN KEY (id_colegio) REFERENCES colegio(id_colegio),
    CONSTRAINT fk_herramientas_usuario_asignado FOREIGN KEY (id_usuario_asignado) REFERENCES usuarios(id),
    CONSTRAINT fk_herramientas_estado FOREIGN KEY (id_estado) REFERENCES estado_herramienta(id_estado)
);

CREATE TABLE IF NOT EXISTS herramienta_detalle (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_herramienta INT NOT NULL,
    tipo_energia VARCHAR(60) DEFAULT '',
    medida VARCHAR(80) DEFAULT '',
    capacidad VARCHAR(80) DEFAULT '',
    requiere_mantencion TINYINT(1) NOT NULL DEFAULT 0,
    frecuencia_mantencion_dias INT NOT NULL DEFAULT 0,
    fecha_ultima_mantencion DATE NULL,
    fecha_proxima_mantencion DATE NULL,
    garantia_hasta DATE NULL,
    UNIQUE KEY uq_herramienta_detalle_herramienta (id_herramienta),
    CONSTRAINT fk_herramienta_detalle FOREIGN KEY (id_herramienta) REFERENCES herramientas(id_herramienta) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS herramienta_compra (
    id_compra INT AUTO_INCREMENT PRIMARY KEY,
    id_herramienta INT NOT NULL,
    valor_herramienta DECIMAL(12,2) NOT NULL DEFAULT 0,
    proveedor VARCHAR(150) DEFAULT '',
    numero_factura VARCHAR(120) DEFAULT '',
    fecha_compra DATE NULL,
    observacion TEXT NULL,
    UNIQUE KEY uq_herramienta_compra_herramienta (id_herramienta),
    KEY idx_herramienta_compra_fecha (fecha_compra),
    CONSTRAINT fk_herramienta_compra FOREIGN KEY (id_herramienta) REFERENCES herramientas(id_herramienta) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS herramienta_prestamos (
    id_prestamo INT AUTO_INCREMENT PRIMARY KEY,
    id_herramienta INT NOT NULL,
    responsable_entrega VARCHAR(150) DEFAULT '',
    responsable_recibe VARCHAR(150) DEFAULT '',
    fecha_salida DATE NULL,
    fecha_devolucion_prevista DATE NULL,
    fecha_devolucion_real DATE NULL,
    estado_prestamo VARCHAR(60) DEFAULT '',
    observacion TEXT NULL,
    KEY idx_herramienta_prestamos_herramienta (id_herramienta),
    KEY idx_herramienta_prestamos_salida (fecha_salida),
    KEY idx_herramienta_prestamos_estado (estado_prestamo),
    CONSTRAINT fk_herramienta_prestamos FOREIGN KEY (id_herramienta) REFERENCES herramientas(id_herramienta) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS herramienta_mantenciones (
    id_mantencion INT AUTO_INCREMENT PRIMARY KEY,
    id_herramienta INT NOT NULL,
    fecha_mantencion DATE NULL,
    tipo_mantencion VARCHAR(120) DEFAULT '',
    proveedor VARCHAR(150) DEFAULT '',
    costo DECIMAL(12,2) NOT NULL DEFAULT 0,
    proxima_fecha DATE NULL,
    observacion TEXT NULL,
    KEY idx_herramienta_mantenciones_herramienta (id_herramienta),
    KEY idx_herramienta_mantenciones_fecha (fecha_mantencion),
    KEY idx_herramienta_mantenciones_proxima (proxima_fecha),
    CONSTRAINT fk_herramienta_mantenciones FOREIGN KEY (id_herramienta) REFERENCES herramientas(id_herramienta) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS herramienta_fotos (
    id_foto INT AUTO_INCREMENT PRIMARY KEY,
    id_herramienta INT NOT NULL,
    ruta_foto VARCHAR(255) NOT NULL,
    principal TINYINT(1) NOT NULL DEFAULT 0,
    orden_foto INT NOT NULL DEFAULT 1,
    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_herramienta_fotos_herramienta (id_herramienta),
    KEY idx_herramienta_fotos_principal (id_herramienta, principal),
    CONSTRAINT fk_herramienta_fotos FOREIGN KEY (id_herramienta) REFERENCES herramientas(id_herramienta) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS herramienta_historial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_herramienta INT NOT NULL,
    accion VARCHAR(60) NOT NULL,
    descripcion TEXT NOT NULL,
    id_usuario INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_herramienta_historial_herramienta (id_herramienta),
    KEY idx_herramienta_historial_usuario (id_usuario),
    KEY idx_herramienta_historial_fecha (fecha),
    CONSTRAINT fk_herramienta_historial FOREIGN KEY (id_herramienta) REFERENCES herramientas(id_herramienta) ON DELETE CASCADE,
    CONSTRAINT fk_herramienta_historial_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);
