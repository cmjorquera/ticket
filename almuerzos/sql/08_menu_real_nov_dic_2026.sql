-- =====================================================================
--  ALMUERZOS – 08: menú REAL de noviembre y diciembre 2026
--  Transcrito de la hoja del concesionario ("Menú Noviembre y diciembre
--  2026", sujeto a modificación).
--  1. LIMPIA noviembre y diciembre 2026: borra el menú de ejemplo y, en
--     cascada, sus días, menús y las elecciones hechas sobre él.
--  2. Carga el menú del día de cada día (Salad bar + fondo + postre).
--     La hoja trae solo un menú: Hipocalórico queda como "Hipocalórico del
--     día" y Picnic como "Sándwich del día" hasta tener el detalle.
--  3. Diciembre: 7 interferiado y 8 feriado (sin servicio); el 2 y el 9
--     llevan la nota de último almuerzo por nivel.
--  * Se puede ejecutar más de una vez (vuelve a dejar todo igual).
-- =====================================================================
USE `crist668_sistema_panel_central`;
SET NAMES utf8mb4;

-- 1. Limpiar (ON DELETE CASCADE: almuerzo_dia, almuerzo_menu, almuerzo_eleccion, almuerzo_cierre)
DELETE FROM almuerzo_mes WHERE anio = 2026 AND mes IN (11, 12);

-- 2. Meses publicados por el encargado (usuario 8)
INSERT INTO almuerzo_mes (anio, mes, estado, id_usuario, publicado_en) VALUES
(2026, 11, 'publicado', 8, NOW()),
(2026, 12, 'publicado', 8, NOW());

-- 3. Días (id_dia: 1 = lunes … 5 = viernes)
INSERT INTO almuerzo_dia (id_mes, fecha, id_dia, hay_servicio, observacion)
SELECT m.id_mes, x.fecha, WEEKDAY(x.fecha) + 1, x.servicio, x.obs
  FROM (
    SELECT '2026-11-02' AS fecha, 1 AS servicio, NULL AS obs
    UNION ALL SELECT '2026-11-03', 1, NULL
    UNION ALL SELECT '2026-11-04', 1, NULL
    UNION ALL SELECT '2026-11-05', 1, NULL
    UNION ALL SELECT '2026-11-06', 1, NULL
    UNION ALL SELECT '2026-11-09', 1, NULL
    UNION ALL SELECT '2026-11-10', 1, NULL
    UNION ALL SELECT '2026-11-11', 1, NULL
    UNION ALL SELECT '2026-11-12', 1, NULL
    UNION ALL SELECT '2026-11-13', 1, NULL
    UNION ALL SELECT '2026-11-16', 1, NULL
    UNION ALL SELECT '2026-11-17', 1, NULL
    UNION ALL SELECT '2026-11-18', 1, NULL
    UNION ALL SELECT '2026-11-19', 1, NULL
    UNION ALL SELECT '2026-11-20', 1, NULL
    UNION ALL SELECT '2026-11-23', 1, NULL
    UNION ALL SELECT '2026-11-24', 1, NULL
    UNION ALL SELECT '2026-11-25', 1, NULL
    UNION ALL SELECT '2026-11-26', 1, NULL
    UNION ALL SELECT '2026-11-27', 1, NULL
    UNION ALL SELECT '2026-11-30', 1, NULL
    UNION ALL SELECT '2026-12-01', 1, NULL
    UNION ALL SELECT '2026-12-02', 1, 'Último almuerzo de 5° básico a III° medio'
    UNION ALL SELECT '2026-12-03', 1, NULL
    UNION ALL SELECT '2026-12-04', 1, NULL
    UNION ALL SELECT '2026-12-07', 0, 'Interferiado'
    UNION ALL SELECT '2026-12-08', 0, 'Feriado'
    UNION ALL SELECT '2026-12-09', 1, 'Último almuerzo de 1° a 4° básico'
  ) x
  JOIN almuerzo_mes m ON m.anio = YEAR(x.fecha) AND m.mes = MONTH(x.fecha);

-- 4. Menús (3 por día con servicio)
INSERT INTO almuerzo_menu (id_dia_menu, id_tipo, entrada, plato_fondo, postre)
SELECT d.id_dia_menu, t.id_tipo, x.entrada, x.plato_fondo, x.postre
  FROM (
    SELECT '2026-11-02' AS fecha, 'menu' AS codigo, 'Salad bar' AS entrada, 'Nuggets con arroz' AS plato_fondo, 'Fruta' AS postre
    UNION ALL SELECT '2026-11-02', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-02', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-03', 'menu', 'Salad bar', 'Carne al jugo con corbatitas', 'Fruta'
    UNION ALL SELECT '2026-11-03', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-03', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-04', 'menu', 'Salad bar', 'Carbonada', 'Helado'
    UNION ALL SELECT '2026-11-04', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-04', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-05', 'menu', 'Salad bar', 'Pollo asado con puré', 'Fruta'
    UNION ALL SELECT '2026-11-05', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-05', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-06', 'menu', 'Salad bar', 'Pizza', 'Fruta'
    UNION ALL SELECT '2026-11-06', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-06', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-09', 'menu', 'Salad bar', 'Chuletas con puré', 'Fruta'
    UNION ALL SELECT '2026-11-09', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-09', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-10', 'menu', 'Salad bar', 'Porotos granados', 'Fruta'
    UNION ALL SELECT '2026-11-10', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-10', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-11', 'menu', 'Salad bar', 'Chapsui de carne con arroz', 'Fruta'
    UNION ALL SELECT '2026-11-11', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-11', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-12', 'menu', 'Salad bar', 'Escalopa con choclo a la crema', 'Fruta'
    UNION ALL SELECT '2026-11-12', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-12', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-13', 'menu', 'Salad bar', 'Fetuccini boloñesa', 'Duraznos al jugo'
    UNION ALL SELECT '2026-11-13', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-13', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-16', 'menu', 'Salad bar', 'Goulash con arroz', 'Fruta'
    UNION ALL SELECT '2026-11-16', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-16', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-17', 'menu', 'Salad bar', 'Lasaña', 'Fruta'
    UNION ALL SELECT '2026-11-17', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-17', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-18', 'menu', 'Salad bar', 'Charquicán', 'Helado'
    UNION ALL SELECT '2026-11-18', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-18', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-19', 'menu', 'Salad bar', 'Carne mechada con puré', 'Fruta'
    UNION ALL SELECT '2026-11-19', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-19', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-20', 'menu', 'Salad bar', 'Ají de gallina con arroz', 'Fruta'
    UNION ALL SELECT '2026-11-20', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-20', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-23', 'menu', 'Salad bar', 'Lomo de cerdo con arroz', 'Piña al jugo'
    UNION ALL SELECT '2026-11-23', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-23', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-24', 'menu', 'Salad bar', 'Lentejas', 'Fruta'
    UNION ALL SELECT '2026-11-24', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-24', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-25', 'menu', 'Salad bar', 'Pollo apanado con puré', 'Fruta'
    UNION ALL SELECT '2026-11-25', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-25', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-26', 'menu', 'Salad bar', 'Hot dog', 'Helado'
    UNION ALL SELECT '2026-11-26', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-26', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-27', 'menu', 'Salad bar', 'Carne al jugo con arroz', 'Fruta'
    UNION ALL SELECT '2026-11-27', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-27', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-11-30', 'menu', 'Salad bar', 'Ravioles de carne con salsa boloñesa', 'Fruta'
    UNION ALL SELECT '2026-11-30', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-11-30', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-12-01', 'menu', 'Salad bar', 'Carne al jugo con puré', 'Fruta'
    UNION ALL SELECT '2026-12-01', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-12-01', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-12-02', 'menu', 'Salad bar', 'Charquicán', 'Fruta'
    UNION ALL SELECT '2026-12-02', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-12-02', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-12-03', 'menu', 'Salad bar', 'Porotos', 'Fruta'
    UNION ALL SELECT '2026-12-03', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-12-03', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-12-04', 'menu', 'Salad bar', 'Picnic', 'Helado'
    UNION ALL SELECT '2026-12-04', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-12-04', 'picnic', NULL, 'Sándwich del día', NULL
    UNION ALL SELECT '2026-12-09', 'menu', 'Salad bar', 'Hamburguesa con puré', 'Fruta'
    UNION ALL SELECT '2026-12-09', 'hipocalorico', 'Salad bar', 'Hipocalórico del día', 'Fruta'
    UNION ALL SELECT '2026-12-09', 'picnic', NULL, 'Sándwich del día', NULL
  ) x
  JOIN almuerzo_dia d  ON d.fecha = x.fecha
  JOIN almuerzo_tipo t ON BINARY t.codigo = BINARY x.codigo;  -- BINARY: evita choque de collation en MySQL 5.7

-- 5. Verificación: 26 días con servicio (78 menús) + 2 sin servicio
SELECT d.fecha, d.hay_servicio, d.observacion, m.entrada, m.plato_fondo, m.postre
  FROM almuerzo_dia d
  LEFT JOIN almuerzo_menu m  ON m.id_dia_menu = d.id_dia_menu
  LEFT JOIN almuerzo_tipo t  ON t.id_tipo = m.id_tipo
 WHERE d.fecha BETWEEN '2026-11-01' AND '2026-12-31' AND (t.codigo = 'menu' OR t.codigo IS NULL)
 ORDER BY d.fecha;
