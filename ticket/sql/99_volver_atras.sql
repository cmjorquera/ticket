-- Deshace 01_mensajeria.sql (el código sigue funcionando sin estas columnas,
-- pero un ticket pedido y atendido por la misma persona pierde los "no leídos").
ALTER TABLE ticket_conversaciones DROP INDEX idx_tc_no_leidos;
ALTER TABLE ticket_conversaciones DROP COLUMN lado;
ALTER TABLE archivos_adjuntos_ticket
  DROP COLUMN nombre_archivo, DROP COLUMN tamano, DROP COLUMN id_usuario, DROP COLUMN fecha_subida;

-- ---- Bitácora (02_bitacora.sql) ----
-- Si la tabla la creó 02_bitacora.sql (QA) y no se quieren conservar los avances:
--   DROP TABLE avance_tecnicos;
-- Si venía del sistema antiguo (producción), solo quitar las columnas nuevas:
--   ALTER TABLE avance_tecnicos DROP INDEX idx_avance_no_leidos,
--     DROP COLUMN id_usuario, DROP COLUMN lado, DROP COLUMN leido, DROP COLUMN eliminado;
