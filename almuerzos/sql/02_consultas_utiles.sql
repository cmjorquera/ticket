-- =====================================================================
--  ALMUERZOS – consultas útiles (solo lectura)
-- =====================================================================
USE `crist668_sistema_panel_central`;

-- Pedidos de hoy por opción (lo que ve cocina)
SELECT opcion, COUNT(*) AS cantidad
  FROM almuerzo_pedido
 WHERE fecha = CURDATE()
 GROUP BY opcion;

-- Quién pidió qué hoy
SELECT u.id, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre, p.opcion, p.actualizado_en
  FROM usuarios u
  LEFT JOIN almuerzo_pedido p ON p.id_usuario = u.id AND p.fecha = CURDATE()
 WHERE LOWER(u.estado) = 'activo'
 ORDER BY p.opcion, nombre;

-- Menús publicados
SELECT m.anio, m.mes, COUNT(d.fecha) AS dias, m.publicado_por, m.publicado_en
  FROM almuerzo_menu_mes m
  LEFT JOIN almuerzo_menu_dia d ON d.id_menu_mes = m.id_menu_mes
 GROUP BY m.id_menu_mes
 ORDER BY m.anio DESC, m.mes DESC;
