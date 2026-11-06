-- =====================================================================
--  ALMUERZOS – tablas (módulo almuerzos/)
--  * Compatible con MySQL 5.7/8 (QA) y MariaDB. Se puede ejecutar varias veces.
--  * Menús MENSUALES: una cabecera por mes (almuerzo_menu_mes) y una fila por
--    día hábil con los tres menús (almuerzo_menu_dia).
--  * Respuestas: una por persona y día (almuerzo_pedido).
--  * Sin llaves foráneas hacia usuarios (tabla del sistema antiguo).
-- =====================================================================

-- Base de datos donde se ejecuta (QA). En PRODUCCIÓN cambiar este nombre.
USE `crist668_sistema_panel_central`;

-- Menú de un mes (se publica de una vez desde la plantilla)
CREATE TABLE IF NOT EXISTS almuerzo_menu_mes (
    id_menu_mes   INT AUTO_INCREMENT PRIMARY KEY,
    anio          SMALLINT NOT NULL,
    mes           TINYINT NOT NULL,              -- 1 a 12
    publicado_por INT NOT NULL,                  -- usuarios.id del encargado
    publicado_en  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY ux_almuerzo_menu_mes (anio, mes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Los tres menús de cada día hábil del mes
-- Platos separados por " + " (ej: "Crema de zapallo + Pollo arvejado + Fruta")
CREATE TABLE IF NOT EXISTS almuerzo_menu_dia (
    fecha         DATE NOT NULL PRIMARY KEY,
    id_menu_mes   INT NOT NULL,
    menu_dia      VARCHAR(255) NOT NULL,
    hipocalorico  VARCHAR(255) NOT NULL,
    picnic        VARCHAR(255) NOT NULL,
    KEY idx_almuerzo_dia_mes (id_menu_mes),
    CONSTRAINT fk_almuerzo_dia_mes FOREIGN KEY (id_menu_mes)
        REFERENCES almuerzo_menu_mes (id_menu_mes) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Respuesta de cada persona para cada día
-- opcion: menu = Menú del día · hipo = Hipocalórico · picnic = Picnic · no = no almuerza
CREATE TABLE IF NOT EXISTS almuerzo_pedido (
    id_usuario    INT NOT NULL,
    fecha         DATE NOT NULL,
    opcion        ENUM('menu','hipo','picnic','no') NOT NULL,
    creado_en     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario, fecha),
    KEY idx_almuerzo_pedido_fecha (fecha, opcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'almuerzo_menu_mes' AS tabla, COUNT(*) AS filas FROM almuerzo_menu_mes
UNION ALL SELECT 'almuerzo_menu_dia', COUNT(*) FROM almuerzo_menu_dia
UNION ALL SELECT 'almuerzo_pedido', COUNT(*) FROM almuerzo_pedido;
