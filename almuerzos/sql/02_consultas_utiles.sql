-- =====================================================================
--  ALMUERZOS – consultas útiles (solo lectura) – modelo v2
-- =====================================================================
USE `crist668_sistema_panel_central`;
SET NAMES utf8mb4;

-- Menú de cada día del mes actual
SELECT d.fecha, s.nombre AS dia, t.nombre AS tipo, m.entrada, m.plato_fondo, m.postre
  FROM almuerzo_menu m
  JOIN almuerzo_dia d        ON d.id_dia_menu = m.id_dia_menu
  JOIN almuerzo_dia_semana s ON s.id_dia = d.id_dia
  JOIN almuerzo_tipo t       ON t.id_tipo = m.id_tipo
 WHERE d.fecha >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
   AND d.fecha <  DATE_FORMAT(CURDATE() + INTERVAL 1 MONTH, '%Y-%m-01')
 ORDER BY d.fecha, t.orden;

-- Totales de hoy para cocina (NULL = no almuerza)
SELECT COALESCE(t.nombre, 'No almuerza') AS opcion, COUNT(*) AS cantidad
  FROM almuerzo_eleccion e
  JOIN almuerzo_dia d   ON d.id_dia_menu = e.id_dia_menu AND d.fecha = CURDATE()
  LEFT JOIN almuerzo_menu m ON m.id_menu = e.id_menu
  LEFT JOIN almuerzo_tipo t ON t.id_tipo = m.id_tipo
 GROUP BY opcion;

-- Quién eligió qué hoy (solo colegio Seduc, 15)
SELECT u.id, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre,
       CASE WHEN e.id_eleccion IS NULL THEN 'Sin respuesta' ELSE COALESCE(t.nombre, 'No almuerza') END AS eleccion,
       e.origen, e.actualizado_en
  FROM usuarios u
  JOIN usuario_colegio uc ON uc.id_usuario = u.id AND uc.estado = 1 AND uc.id_colegio = 15
  LEFT JOIN almuerzo_dia d       ON d.fecha = CURDATE()
  LEFT JOIN almuerzo_eleccion e  ON e.id_usuario = u.id AND e.id_dia_menu = d.id_dia_menu
  LEFT JOIN almuerzo_menu m      ON m.id_menu = e.id_menu
  LEFT JOIN almuerzo_tipo t      ON t.id_tipo = m.id_tipo
 WHERE LOWER(u.estado) = 'activo'
 ORDER BY eleccion, nombre;

-- Meses cargados
SELECT ms.anio, ms.mes, ms.estado, COUNT(d.id_dia_menu) AS dias, ms.publicado_en
  FROM almuerzo_mes ms
  LEFT JOIN almuerzo_dia d ON d.id_mes = ms.id_mes AND d.hay_servicio = 1
 GROUP BY ms.id_mes
 ORDER BY ms.anio DESC, ms.mes DESC;
