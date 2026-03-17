CREATE TABLE IF NOT EXISTS software_catalogo (
    id_software INT NOT NULL AUTO_INCREMENT,
    id_colegio INT NOT NULL,
    id_usuario_responsable INT NOT NULL DEFAULT 0,
    nombre_software VARCHAR(180) NOT NULL,
    version_software VARCHAR(80) DEFAULT NULL,
    cantidad_licencias INT NOT NULL DEFAULT 1,
    tipo_licenciamiento ENUM('Suscripcion', 'Licencia perpetua', 'Gratuita') NOT NULL DEFAULT 'Suscripcion',
    pagado_por ENUM('Colegio', 'Persona') NOT NULL DEFAULT 'Colegio',
    costo DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    moneda VARCHAR(10) NOT NULL DEFAULT 'USD',
    proveedor VARCHAR(180) DEFAULT NULL,
    url_referencia VARCHAR(255) DEFAULT NULL,
    observaciones TEXT DEFAULT NULL,
    id_usuario INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_software),
    KEY idx_software_colegio (id_colegio),
    KEY idx_software_responsable (id_usuario_responsable),
    CONSTRAINT fk_software_colegio FOREIGN KEY (id_colegio) REFERENCES colegio (id_colegio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS software_datos_almacenamiento (
    id_dato INT NOT NULL AUTO_INCREMENT,
    id_software INT NOT NULL,
    nombre_contacto VARCHAR(180) DEFAULT NULL,
    rut_contacto VARCHAR(20) DEFAULT NULL,
    email_contacto VARCHAR(180) DEFAULT NULL,
    otros_datos VARCHAR(255) DEFAULT NULL,
    orden_dato INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id_dato),
    KEY idx_dato_software (id_software),
    CONSTRAINT fk_dato_software FOREIGN KEY (id_software) REFERENCES software_catalogo (id_software) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS software_historial (
    id_historial INT NOT NULL AUTO_INCREMENT,
    id_software INT NOT NULL,
    accion VARCHAR(60) NOT NULL,
    detalle VARCHAR(255) NOT NULL,
    id_usuario INT NOT NULL DEFAULT 0,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_historial),
    KEY idx_historial_software (id_software),
    CONSTRAINT fk_historial_software FOREIGN KEY (id_software) REFERENCES software_catalogo (id_software) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS sitios_web_catalogo (
    id_sitio INT NOT NULL AUTO_INCREMENT,
    id_colegio INT NOT NULL,
    id_usuario_responsable INT NOT NULL DEFAULT 0,
    nombre_sitio VARCHAR(180) NOT NULL,
    tipo_sitio ENUM('Web', 'App', 'Cliente') NOT NULL DEFAULT 'Web',
    url_sitio VARCHAR(255) DEFAULT NULL,
    proveedor_hosting VARCHAR(180) DEFAULT NULL,
    estado_sitio ENUM('Activo', 'Mantenimiento', 'En desarrollo', 'Inactivo') NOT NULL DEFAULT 'Activo',
    observaciones TEXT DEFAULT NULL,
    id_usuario INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_sitio),
    KEY idx_sitio_colegio (id_colegio),
    KEY idx_sitio_responsable (id_usuario_responsable),
    CONSTRAINT fk_sitio_colegio FOREIGN KEY (id_colegio) REFERENCES colegio (id_colegio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
