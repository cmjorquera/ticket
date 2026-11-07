-- =====================================================================
--  ALMUERZOS – MODELO DE BASE DE DATOS (versión 2)
--  Diagrama: almuerzos/sql/modelo_almuerzos.png
--
--  * Compatible con MySQL 5.7/8 (QA) y MariaDB. Se puede ejecutar varias veces:
--    CREATE TABLE IF NOT EXISTS + INSERT IGNORE, no borra datos.
--  * Los nombres NO chocan con las tablas de la versión 1
--    (almuerzo_menu_mes, almuerzo_menu_dia, almuerzo_pedido), así que pueden
--    convivir mientras se adapta el código. Después se borran las de la v1.
--  * Sin llaves foráneas hacia usuarios / colegio (tablas del sistema antiguo);
--    entre las tablas del módulo sí hay llaves foráneas.
--
--  Resumen
--  -------
--  Catálogos          almuerzo_tipo, almuerzo_dia_semana, almuerzo_configuracion
--  Menú del mes       almuerzo_mes  → almuerzo_dia  → almuerzo_menu (1 por tipo y día)
--  Elecciones         almuerzo_eleccion   (1 por persona y día; diaria o anticipada)
--  Cierre de las 10   almuerzo_cierre     (1 por día: totales y PDF enviado)
-- =====================================================================

-- Base de datos donde se ejecuta (QA). En PRODUCCIÓN cambiar este nombre.
USE `crist668_sistema_panel_central`;
SET NAMES utf8mb4;  -- tildes y ñ se guardan bien aunque el cliente use otro juego de caracteres

-- ---------------------------------------------------------------------
-- 1. CATÁLOGO: tipos de menú (Menú del día, Hipocalórico, Picnic)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS almuerzo_tipo (
    id_tipo      TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    codigo       VARCHAR(20)  NOT NULL,          -- menu | hipocalorico | picnic
    nombre       VARCHAR(60)  NOT NULL,
    descripcion  VARCHAR(255) NULL,
    icono        VARCHAR(40)  NULL,              -- ícono Bootstrap (bi-…)
    color        CHAR(7)      NULL,              -- color en pantalla y PDF
    orden        TINYINT UNSIGNED NOT NULL DEFAULT 1,
    activo       TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (id_tipo),
    UNIQUE KEY ux_almuerzo_tipo_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO almuerzo_tipo (codigo, nombre, descripcion, icono, color, orden) VALUES
('menu',         'Menú del día', 'Almuerzo tradicional: entrada, plato de fondo y postre.', 'bi-egg-fried',   '#005B96', 1),
('hipocalorico', 'Hipocalórico', 'Opción liviana, baja en calorías.',                       'bi-heart-pulse', '#3C8D2F', 2),
('picnic',       'Picnic',       'Para llevar: sándwich o similar, bebida y fruta.',        'bi-basket',      '#C98A00', 3);

-- ---------------------------------------------------------------------
-- 2. CATÁLOGO: días de la semana con servicio (siempre lunes a viernes)
--    id_dia = número ISO del día (1 = lunes … 5 = viernes), igual que
--    date('N') en PHP y WEEKDAY(fecha) + 1 en MySQL.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS almuerzo_dia_semana (
    id_dia       TINYINT UNSIGNED NOT NULL,      -- 1..5
    nombre       VARCHAR(15) NOT NULL,
    abreviatura  CHAR(3)     NOT NULL,
    activo       TINYINT(1)  NOT NULL DEFAULT 1, -- por si algún día se agrega el sábado
    PRIMARY KEY (id_dia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO almuerzo_dia_semana (id_dia, nombre, abreviatura) VALUES
(1, 'Lunes',     'Lun'),
(2, 'Martes',    'Mar'),
(3, 'Miércoles', 'Mié'),
(4, 'Jueves',    'Jue'),
(5, 'Viernes',   'Vie');

-- ---------------------------------------------------------------------
-- 3. CONFIGURACIÓN del módulo (horario, encargado, colegio del PDF)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS almuerzo_configuracion (
    clave          VARCHAR(50)  NOT NULL,
    valor          VARCHAR(255) NOT NULL,
    descripcion    VARCHAR(255) NULL,
    actualizado_en DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO almuerzo_configuracion (clave, valor, descripcion) VALUES
('hora_inicio',          '08:00', 'Desde qué hora se puede elegir el almuerzo del día'),
('hora_cierre',          '10:00', 'A esta hora se cierra la elección y se genera el PDF'),
('id_usuario_encargado', '8',     'Usuario que publica los menús y recibe el PDF (usuarios.id)'),
('id_colegio_pdf',       '15',    'El PDF incluye solo usuarios de este colegio (15 = Seduc SpA)'),
('dias_anticipacion',    '31',    'Hasta cuántos días hacia adelante se puede dejar elegido'),
('dias_planificacion',   '6,7',   'Días en que se planifica la semana siguiente (6 = sábado, 7 = domingo)');

-- ---------------------------------------------------------------------
-- 4. MENÚ DEL MES (cabecera): los menús se cargan mes a mes
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS almuerzo_mes (
    id_mes         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    anio           SMALLINT UNSIGNED NOT NULL,
    mes            TINYINT UNSIGNED  NOT NULL,   -- 1..12
    estado         ENUM('borrador','publicado') NOT NULL DEFAULT 'borrador',
    id_usuario     INT NOT NULL,                 -- quién lo cargó (usuarios.id)
    publicado_en   DATETIME NULL,
    creado_en      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_mes),
    UNIQUE KEY ux_almuerzo_mes (anio, mes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. DÍAS del mes con servicio (una fila por fecha hábil)
--    hay_servicio = 0 para feriados o días sin almuerzo.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS almuerzo_dia (
    id_dia_menu    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_mes         INT UNSIGNED NOT NULL,
    fecha          DATE NOT NULL,
    id_dia         TINYINT UNSIGNED NOT NULL,    -- almuerzo_dia_semana (1..5)
    hay_servicio   TINYINT(1) NOT NULL DEFAULT 1,
    observacion    VARCHAR(255) NULL,            -- ej: "Feriado", "Aniversario"
    PRIMARY KEY (id_dia_menu),
    UNIQUE KEY ux_almuerzo_dia_fecha (fecha),
    KEY idx_almuerzo_dia_mes (id_mes),
    KEY idx_almuerzo_dia_semana (id_dia),
    CONSTRAINT fk_almdia_mes    FOREIGN KEY (id_mes) REFERENCES almuerzo_mes (id_mes) ON DELETE CASCADE,
    CONSTRAINT fk_almdia_semana FOREIGN KEY (id_dia) REFERENCES almuerzo_dia_semana (id_dia)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. MENÚ de cada tipo en cada día (3 filas por día: menú, hipocalórico, picnic)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS almuerzo_menu (
    id_menu        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_dia_menu    INT UNSIGNED NOT NULL,
    id_tipo        TINYINT UNSIGNED NOT NULL,
    entrada        VARCHAR(120) NULL,
    plato_fondo    VARCHAR(160) NOT NULL,
    postre         VARCHAR(120) NULL,            -- picnic: solo plato_fondo (el sándwich)
    imagen         VARCHAR(255) NULL,            -- foto: img/menus/archivo.jpg o https://...
    observacion    VARCHAR(255) NULL,            -- ej: "sin gluten", "contiene maní"
    PRIMARY KEY (id_menu),
    UNIQUE KEY ux_almuerzo_menu_dia_tipo (id_dia_menu, id_tipo),
    KEY idx_almuerzo_menu_tipo (id_tipo),
    CONSTRAINT fk_almmenu_dia  FOREIGN KEY (id_dia_menu) REFERENCES almuerzo_dia (id_dia_menu) ON DELETE CASCADE,
    CONSTRAINT fk_almmenu_tipo FOREIGN KEY (id_tipo)     REFERENCES almuerzo_tipo (id_tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. ELECCIÓN de cada persona para cada día
--    id_menu NULL = "ese día no almuerzo".
--    origen: diaria (eligió ese día entre 08:00 y 10:00) o anticipada
--    (la dejó programada antes, para varios días del mes).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS almuerzo_eleccion (
    id_eleccion    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_usuario     INT NOT NULL,                 -- usuarios.id
    id_dia_menu    INT UNSIGNED NOT NULL,
    id_menu        INT UNSIGNED NULL,
    origen         ENUM('diaria','anticipada') NOT NULL DEFAULT 'diaria',
    creado_en      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_eleccion),
    UNIQUE KEY ux_almuerzo_eleccion (id_usuario, id_dia_menu),
    KEY idx_almuerzo_eleccion_dia (id_dia_menu, id_menu),
    CONSTRAINT fk_almeleccion_dia  FOREIGN KEY (id_dia_menu) REFERENCES almuerzo_dia (id_dia_menu) ON DELETE CASCADE,
    CONSTRAINT fk_almeleccion_menu FOREIGN KEY (id_menu)     REFERENCES almuerzo_menu (id_menu) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. CIERRE DIARIO (10:00): totales del día y registro del PDF enviado
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS almuerzo_cierre (
    id_cierre        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_dia_menu      INT UNSIGNED NOT NULL,
    cerrado_en       DATETIME NOT NULL,
    total_menu       SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    total_hipocalorico SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    total_picnic     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    total_no_almuerza SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    total_sin_respuesta SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    archivo_pdf      VARCHAR(255) NULL,          -- ruta del PDF generado
    enviado_a        VARCHAR(255) NULL,          -- correo(s) del encargado / concesionaria
    enviado_en       DATETIME NULL,
    PRIMARY KEY (id_cierre),
    UNIQUE KEY ux_almuerzo_cierre_dia (id_dia_menu),
    CONSTRAINT fk_almcierre_dia FOREIGN KEY (id_dia_menu) REFERENCES almuerzo_dia (id_dia_menu) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Verificación
-- ---------------------------------------------------------------------
SELECT 'almuerzo_tipo' AS tabla, COUNT(*) AS filas FROM almuerzo_tipo
UNION ALL SELECT 'almuerzo_dia_semana', COUNT(*) FROM almuerzo_dia_semana
UNION ALL SELECT 'almuerzo_configuracion', COUNT(*) FROM almuerzo_configuracion
UNION ALL SELECT 'almuerzo_mes', COUNT(*) FROM almuerzo_mes
UNION ALL SELECT 'almuerzo_dia', COUNT(*) FROM almuerzo_dia
UNION ALL SELECT 'almuerzo_menu', COUNT(*) FROM almuerzo_menu
UNION ALL SELECT 'almuerzo_eleccion', COUNT(*) FROM almuerzo_eleccion
UNION ALL SELECT 'almuerzo_cierre', COUNT(*) FROM almuerzo_cierre;
