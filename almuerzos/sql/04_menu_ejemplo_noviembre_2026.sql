-- =====================================================================
--  ALMUERZOS – menú de ejemplo para NOVIEMBRE 2026 (modelo v2)
--  Llena: almuerzo_mes (1), almuerzo_dia (21 días hábiles) y
--         almuerzo_menu (63 filas: menú del día, hipocalórico y picnic).
--  * El picnic es siempre un sándwich (sin entrada ni postre).
--  * Requiere 03_modelo_almuerzos.sql (catálogos de tipos y días).
--  * Se puede ejecutar más de una vez: INSERT IGNORE no duplica ni pisa
--    lo que ya exista (para cambiar un plato, editarlo en almuerzo_menu).
--  * No usa tablas temporales (compatible con el usuario de phpMyAdmin).
-- =====================================================================
USE `crist668_sistema_panel_central`;
SET NAMES utf8mb4;  -- tildes y ñ se guardan bien aunque el cliente use otro juego de caracteres

-- 1. Cabecera del mes (publicado, cargado por el encargado: usuario 8)
INSERT IGNORE INTO almuerzo_mes (anio, mes, estado, id_usuario, publicado_en)
VALUES (2026, 11, 'publicado', 8, NOW());

-- 2. Días hábiles del mes (id_dia: 1 = lunes … 5 = viernes)
INSERT IGNORE INTO almuerzo_dia (id_mes, fecha, id_dia)
SELECT m.id_mes, f.fecha, WEEKDAY(f.fecha) + 1
  FROM almuerzo_mes m
  JOIN (
    SELECT '2026-11-02' AS fecha
    UNION ALL SELECT '2026-11-03'
    UNION ALL SELECT '2026-11-04'
    UNION ALL SELECT '2026-11-05'
    UNION ALL SELECT '2026-11-06'
    UNION ALL SELECT '2026-11-09'
    UNION ALL SELECT '2026-11-10'
    UNION ALL SELECT '2026-11-11'
    UNION ALL SELECT '2026-11-12'
    UNION ALL SELECT '2026-11-13'
    UNION ALL SELECT '2026-11-16'
    UNION ALL SELECT '2026-11-17'
    UNION ALL SELECT '2026-11-18'
    UNION ALL SELECT '2026-11-19'
    UNION ALL SELECT '2026-11-20'
    UNION ALL SELECT '2026-11-23'
    UNION ALL SELECT '2026-11-24'
    UNION ALL SELECT '2026-11-25'
    UNION ALL SELECT '2026-11-26'
    UNION ALL SELECT '2026-11-27'
    UNION ALL SELECT '2026-11-30'
  ) f
 WHERE m.anio = 2026 AND m.mes = 11;

-- 3. Menús de cada día (3 por día)
INSERT IGNORE INTO almuerzo_menu (id_dia_menu, id_tipo, entrada, plato_fondo, postre)
SELECT d.id_dia_menu, t.id_tipo, x.entrada, x.plato_fondo, x.postre
  FROM (
    SELECT '2026-11-02' AS fecha, 'menu' AS codigo, 'Crema de zapallo' AS entrada, 'Arroz con pollo' AS plato_fondo, 'Fruta de la estación' AS postre
    UNION ALL SELECT '2026-11-02', 'hipocalorico', 'Ensalada verde', 'Pechuga a la plancha con quínoa', 'Yogur natural'
    UNION ALL SELECT '2026-11-02', 'picnic', NULL, 'Sándwich de ave pimiento', NULL
    UNION ALL SELECT '2026-11-03', 'menu', 'Ensalada chilena', 'Porotos granados', 'Mote con huesillo'
    UNION ALL SELECT '2026-11-03', 'hipocalorico', 'Crema de zapallo italiano', 'Reineta al vapor con verduras', 'Fruta'
    UNION ALL SELECT '2026-11-03', 'picnic', NULL, 'Sándwich de pollo y verduras', NULL
    UNION ALL SELECT '2026-11-04', 'menu', 'Sopa de verduras', 'Carne mechada con puré', 'Leche asada'
    UNION ALL SELECT '2026-11-04', 'hipocalorico', 'Ensalada de tomate', 'Omelette de espinaca', 'Gelatina light'
    UNION ALL SELECT '2026-11-04', 'picnic', NULL, 'Sándwich de jamón queso', NULL
    UNION ALL SELECT '2026-11-05', 'menu', 'Ensalada de repollo', 'Pastel de choclo', 'Fruta picada'
    UNION ALL SELECT '2026-11-05', 'hipocalorico', 'Caldo de verduras', 'Pavo al horno con puré de coliflor', 'Fruta picada'
    UNION ALL SELECT '2026-11-05', 'picnic', NULL, 'Sándwich de pollo César', NULL
    UNION ALL SELECT '2026-11-06', 'menu', 'Consomé', 'Lentejas con arroz', 'Sémola con leche'
    UNION ALL SELECT '2026-11-06', 'hipocalorico', 'Ensalada de pepino', 'Salteado de verduras con tofu', 'Manzana'
    UNION ALL SELECT '2026-11-06', 'picnic', NULL, 'Sándwich de queso y tomate', NULL
    UNION ALL SELECT '2026-11-09', 'menu', 'Ensalada surtida', 'Merluza frita con papas cocidas', 'Gelatina'
    UNION ALL SELECT '2026-11-09', 'hipocalorico', 'Ensalada de lechuga', 'Atún con garbanzos y verduras', 'Fruta'
    UNION ALL SELECT '2026-11-09', 'picnic', NULL, 'Sándwich de ave pimiento', NULL
    UNION ALL SELECT '2026-11-10', 'menu', 'Crema de espárragos', 'Charquicán con huevo frito', 'Manzana asada'
    UNION ALL SELECT '2026-11-10', 'hipocalorico', 'Crema de brócoli', 'Pollo al limón con arroz integral', 'Yogur light'
    UNION ALL SELECT '2026-11-10', 'picnic', NULL, 'Sándwich de pollo y verduras', NULL
    UNION ALL SELECT '2026-11-11', 'menu', 'Ensalada de betarraga', 'Tallarines con salsa bolognesa', 'Fruta de la estación'
    UNION ALL SELECT '2026-11-11', 'hipocalorico', 'Ensalada verde', 'Pechuga a la plancha con quínoa', 'Yogur natural'
    UNION ALL SELECT '2026-11-11', 'picnic', NULL, 'Sándwich de jamón queso', NULL
    UNION ALL SELECT '2026-11-12', 'menu', 'Sopa de pollo', 'Cazuela de vacuno', 'Arroz con leche'
    UNION ALL SELECT '2026-11-12', 'hipocalorico', 'Crema de zapallo italiano', 'Reineta al vapor con verduras', 'Fruta'
    UNION ALL SELECT '2026-11-12', 'picnic', NULL, 'Sándwich de pollo César', NULL
    UNION ALL SELECT '2026-11-13', 'menu', 'Ensalada mixta', 'Pollo al jugo con papas doradas', 'Flan'
    UNION ALL SELECT '2026-11-13', 'hipocalorico', 'Ensalada de tomate', 'Omelette de espinaca', 'Gelatina light'
    UNION ALL SELECT '2026-11-13', 'picnic', NULL, 'Sándwich de queso y tomate', NULL
    UNION ALL SELECT '2026-11-16', 'menu', 'Crema de choclo', 'Pescado al horno con arroz', 'Fruta'
    UNION ALL SELECT '2026-11-16', 'hipocalorico', 'Caldo de verduras', 'Pavo al horno con puré de coliflor', 'Fruta picada'
    UNION ALL SELECT '2026-11-16', 'picnic', NULL, 'Sándwich de ave pimiento', NULL
    UNION ALL SELECT '2026-11-17', 'menu', 'Ensalada de pepino', 'Pantrucas', 'Duraznos en almíbar'
    UNION ALL SELECT '2026-11-17', 'hipocalorico', 'Ensalada de pepino', 'Salteado de verduras con tofu', 'Manzana'
    UNION ALL SELECT '2026-11-17', 'picnic', NULL, 'Sándwich de pollo y verduras', NULL
    UNION ALL SELECT '2026-11-18', 'menu', 'Ensalada de tomate', 'Albóndigas con arroz', 'Jalea'
    UNION ALL SELECT '2026-11-18', 'hipocalorico', 'Ensalada de lechuga', 'Atún con garbanzos y verduras', 'Fruta'
    UNION ALL SELECT '2026-11-18', 'picnic', NULL, 'Sándwich de jamón queso', NULL
    UNION ALL SELECT '2026-11-19', 'menu', 'Crema de verduras', 'Pollo asado con ensalada rusa', 'Fruta picada'
    UNION ALL SELECT '2026-11-19', 'hipocalorico', 'Crema de brócoli', 'Pollo al limón con arroz integral', 'Yogur light'
    UNION ALL SELECT '2026-11-19', 'picnic', NULL, 'Sándwich de pollo César', NULL
    UNION ALL SELECT '2026-11-20', 'menu', 'Ensalada de lechuga', 'Garbanzos con longaniza', 'Leche nevada'
    UNION ALL SELECT '2026-11-20', 'hipocalorico', 'Ensalada verde', 'Pechuga a la plancha con quínoa', 'Yogur natural'
    UNION ALL SELECT '2026-11-20', 'picnic', NULL, 'Sándwich de queso y tomate', NULL
    UNION ALL SELECT '2026-11-23', 'menu', 'Sopa de fideos', 'Lasaña de carne', 'Fruta de la estación'
    UNION ALL SELECT '2026-11-23', 'hipocalorico', 'Crema de zapallo italiano', 'Reineta al vapor con verduras', 'Fruta'
    UNION ALL SELECT '2026-11-23', 'picnic', NULL, 'Sándwich de ave pimiento', NULL
    UNION ALL SELECT '2026-11-24', 'menu', 'Ensalada chilena', 'Pollo arvejado con puré', 'Sémola con salsa de frutilla'
    UNION ALL SELECT '2026-11-24', 'hipocalorico', 'Ensalada de tomate', 'Omelette de espinaca', 'Gelatina light'
    UNION ALL SELECT '2026-11-24', 'picnic', NULL, 'Sándwich de pollo y verduras', NULL
    UNION ALL SELECT '2026-11-25', 'menu', 'Crema de zapallo italiano', 'Reineta frita con arroz', 'Gelatina'
    UNION ALL SELECT '2026-11-25', 'hipocalorico', 'Caldo de verduras', 'Pavo al horno con puré de coliflor', 'Fruta picada'
    UNION ALL SELECT '2026-11-25', 'picnic', NULL, 'Sándwich de jamón queso', NULL
    UNION ALL SELECT '2026-11-26', 'menu', 'Ensalada de repollo', 'Carbonada', 'Fruta'
    UNION ALL SELECT '2026-11-26', 'hipocalorico', 'Ensalada de pepino', 'Salteado de verduras con tofu', 'Manzana'
    UNION ALL SELECT '2026-11-26', 'picnic', NULL, 'Sándwich de pollo César', NULL
    UNION ALL SELECT '2026-11-27', 'menu', 'Ensalada surtida', 'Bistec a lo pobre', 'Helado'
    UNION ALL SELECT '2026-11-27', 'hipocalorico', 'Ensalada de lechuga', 'Atún con garbanzos y verduras', 'Fruta'
    UNION ALL SELECT '2026-11-27', 'picnic', NULL, 'Sándwich de queso y tomate', NULL
    UNION ALL SELECT '2026-11-30', 'menu', 'Consomé', 'Pastel de papas', 'Manzana asada'
    UNION ALL SELECT '2026-11-30', 'hipocalorico', 'Crema de brócoli', 'Pollo al limón con arroz integral', 'Yogur light'
    UNION ALL SELECT '2026-11-30', 'picnic', NULL, 'Sándwich de ave pimiento', NULL
  ) x
  JOIN almuerzo_dia d  ON d.fecha = x.fecha
  JOIN almuerzo_tipo t ON BINARY t.codigo = BINARY x.codigo;  -- BINARY: evita choque de collation en MySQL 5.7

-- 4. Verificación: menú del mes ya legible
SELECT d.fecha, s.nombre AS dia, t.nombre AS tipo, m.entrada, m.plato_fondo, m.postre
  FROM almuerzo_menu m
  JOIN almuerzo_dia d        ON d.id_dia_menu = m.id_dia_menu
  JOIN almuerzo_dia_semana s ON s.id_dia = d.id_dia
  JOIN almuerzo_tipo t       ON t.id_tipo = m.id_tipo
 WHERE d.fecha BETWEEN '2026-11-01' AND '2026-11-30'
 ORDER BY d.fecha, t.orden;
