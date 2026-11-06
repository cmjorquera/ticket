-- =====================================================================
--  Licenciamiento (Software + Sitios web / Apps) – instalación
--  Módulo: licenciamiento/
--
--  * Compatible con MySQL 5.7/8 (QA) y MariaDB.
--  * Se puede ejecutar más de una vez: CREATE TABLE IF NOT EXISTS e
--    INSERT IGNORE; no borra ni modifica datos existentes.
--  * Misma estructura que las tablas del sistema antiguo
--    (acceso_sistema_panel), para poder migrar los datos tal cual.
--  * Sin llaves foráneas hacia `usuarios` ni `colegio`.
-- =====================================================================

-- Base de datos donde se ejecuta (QA). En PRODUCCIÓN cambiar este nombre.
USE `crist668_sistema_panel_central`;

CREATE TABLE IF NOT EXISTS software_catalogo (
    id_software INT AUTO_INCREMENT PRIMARY KEY,
    id_colegio INT NOT NULL,
    id_usuario_responsable INT NOT NULL DEFAULT 0,
    id_tipo_usuario INT NOT NULL DEFAULT 0,
    nombre_software VARCHAR(180) NOT NULL,
    version_software VARCHAR(80) NULL,
    cantidad_licencias INT NOT NULL DEFAULT 1,
    tipo_licenciamiento ENUM('Suscripcion','Licencia perpetua','Gratuita') NOT NULL DEFAULT 'Suscripcion',
    fecha_inicio_licencia DATE NULL,
    fecha_fin_licencia DATE NULL,
    pagado_por ENUM('Colegio','Seduc','Persona') NOT NULL DEFAULT 'Colegio',
    costo DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    moneda VARCHAR(10) NOT NULL DEFAULT 'USD',
    proveedor VARCHAR(180) NULL,
    url_referencia VARCHAR(255) NULL,
    observaciones TEXT NULL,
    id_usuario INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_software_colegio (id_colegio),
    KEY idx_software_responsable (id_usuario_responsable),
    KEY idx_software_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS software_datos_almacenamiento (
    id_dato INT AUTO_INCREMENT PRIMARY KEY,
    id_software INT NOT NULL,
    nombre_contacto VARCHAR(180) NULL,
    rut_contacto VARCHAR(20) NULL,
    email_contacto VARCHAR(180) NULL,
    otros_datos VARCHAR(255) NULL,
    orden_dato INT NOT NULL DEFAULT 1,
    KEY idx_dato_software (id_software)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS software_historial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_software INT NOT NULL,
    accion VARCHAR(60) NOT NULL,
    detalle VARCHAR(255) NOT NULL,
    id_usuario INT NOT NULL DEFAULT 0,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_historial_software (id_software)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventario_software_datos_sensibles (
    id_dato_sensible INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_dato_sensible_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO inventario_software_datos_sensibles (nombre, descripcion, activo) VALUES
('RUT', 'Identificador tributario o de identidad', 1),
('Nombre', 'Nombre completo de personas', 1),
('Nombre completo', 'Nombre y apellidos de una persona', 1),
('Email', 'Correo electrónico personal o institucional', 1),
('Teléfono', 'Número de contacto', 1),
('Dirección', 'Dirección física o de despacho', 1),
('Tarjeta de crédito', 'Números o información de pago', 1),
('Curso', 'Curso o nivel del alumno', 1),
('Calificaciones', 'Notas y evaluaciones', 1);

CREATE TABLE IF NOT EXISTS software_datos_sensibles_rel (
    id_relacion INT AUTO_INCREMENT PRIMARY KEY,
    id_software INT NOT NULL,
    id_dato_sensible INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_software_dato_sensible (id_software, id_dato_sensible),
    KEY idx_rel_dato_sensible (id_dato_sensible)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inventario_software_tipo_usuario (
    id_tipo_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_tipo_usuario_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO inventario_software_tipo_usuario (nombre, descripcion, activo) VALUES
('Colaborador', NULL, 1),
('Alumno', NULL, 1),
('Apoderado', NULL, 1),
('Proveedor', NULL, 1),
('Otro', NULL, 1);

CREATE TABLE IF NOT EXISTS software_tipo_usuario_rel (
    id_relacion INT AUTO_INCREMENT PRIMARY KEY,
    id_software INT NOT NULL,
    id_tipo_usuario INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_software_tipo_usuario (id_software, id_tipo_usuario),
    KEY idx_rel_tipo_usuario (id_tipo_usuario),
    KEY idx_rel_tipo_software (id_software)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sitios_web_catalogo (
    id_sitio INT AUTO_INCREMENT PRIMARY KEY,
    id_colegio INT NOT NULL,
    id_usuario_responsable INT NOT NULL DEFAULT 0,
    nombre_sitio VARCHAR(180) NOT NULL,
    tipo_sitio ENUM('Web','App','Cliente') NOT NULL DEFAULT 'Web',
    url_sitio VARCHAR(255) NULL,
    proveedor_hosting VARCHAR(180) NULL,
    estado_sitio ENUM('Activo','Mantenimiento','En desarrollo','Inactivo') NOT NULL DEFAULT 'Activo',
    observaciones TEXT NULL,
    id_usuario INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_sitio_colegio (id_colegio),
    KEY idx_sitio_responsable (id_usuario_responsable),
    KEY idx_sitio_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Menú: quien ve "Inventario PC" (submenú 10) también ve
-- "Sofware" (submenú 20) y "Sitios web App" (submenú 21).
-- Y quien tiene el menú Inventario (9) también tiene Licenciamientos (15).
-- Solo agrega, no quita permisos.
-- ---------------------------------------------------------------------
INSERT INTO permiso_sub_menu (id_usuario, id_submenu, permiso)
SELECT p.id_usuario, 20, 1
  FROM permiso_sub_menu p
 WHERE p.id_submenu = 10 AND p.permiso = 1
   AND NOT EXISTS (SELECT 1 FROM permiso_sub_menu x WHERE x.id_usuario = p.id_usuario AND x.id_submenu = 20);

INSERT INTO permiso_sub_menu (id_usuario, id_submenu, permiso)
SELECT p.id_usuario, 21, 1
  FROM permiso_sub_menu p
 WHERE p.id_submenu = 10 AND p.permiso = 1
   AND NOT EXISTS (SELECT 1 FROM permiso_sub_menu x WHERE x.id_usuario = p.id_usuario AND x.id_submenu = 21);

INSERT INTO permisos_menu_1 (id_menu1, id_submenu, id_usuario, id_tipo_permiso)
SELECT DISTINCT 15, NULL, pm.id_usuario, 1
  FROM permisos_menu_1 pm
 WHERE pm.id_menu1 = 9 AND pm.id_tipo_permiso = 1
   AND NOT EXISTS (SELECT 1 FROM permisos_menu_1 x WHERE x.id_usuario = pm.id_usuario AND x.id_menu1 = 15);

SELECT 'software_catalogo' AS tabla, COUNT(*) AS filas FROM software_catalogo
UNION ALL SELECT 'sitios_web_catalogo', COUNT(*) FROM sitios_web_catalogo
UNION ALL SELECT 'datos sensibles (catálogo)', COUNT(*) FROM inventario_software_datos_sensibles
UNION ALL SELECT 'tipos de usuario (catálogo)', COUNT(*) FROM inventario_software_tipo_usuario
UNION ALL SELECT 'permisos submenú 20', COUNT(*) FROM permiso_sub_menu WHERE id_submenu = 20
UNION ALL SELECT 'permisos submenú 21', COUNT(*) FROM permiso_sub_menu WHERE id_submenu = 21;
