-- =====================================================================
--  ALMUERZOS – borrar las tablas de la VERSIÓN 1
--  La pantalla ya usa el modelo v2 (03_modelo_almuerzos.sql), así que estas
--  tablas no se usan más. Estaban vacías (solo pruebas).
--  Ejecutar UNA vez, después de desplegar el código nuevo.
-- =====================================================================
USE `crist668_sistema_panel_central`;

DROP TABLE IF EXISTS almuerzo_pedido;
DROP TABLE IF EXISTS almuerzo_menu_dia;   -- primero la hija (llave foránea)
DROP TABLE IF EXISTS almuerzo_menu_mes;

SHOW TABLES LIKE 'almuerzo%';
