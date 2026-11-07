-- =====================================================================
--  ALMUERZOS – 07: planificación semanal
--  Sábado y domingo cada persona deja elegido su almuerzo de lunes a
--  viernes de la semana siguiente (almuerzo_eleccion.origen = 'anticipada').
--  Durante la semana se sigue eligiendo/cambiando el del día entre
--  hora_inicio y hora_cierre (origen = 'diaria').
--  * Se puede ejecutar más de una vez. Si no se ejecuta, el sistema usa
--    igual sábado y domingo (valor por defecto en el código).
-- =====================================================================
USE `crist668_sistema_panel_central`;
SET NAMES utf8mb4;

INSERT IGNORE INTO almuerzo_configuracion (clave, valor, descripcion) VALUES
('dias_planificacion', '6,7', 'Días en que se planifica la semana siguiente (6 = sábado, 7 = domingo). Ej: solo sábado = 6');

-- Para abrirla solo el sábado:
-- UPDATE almuerzo_configuracion SET valor = '6' WHERE clave = 'dias_planificacion';

SELECT clave, valor, descripcion FROM almuerzo_configuracion ORDER BY clave;
