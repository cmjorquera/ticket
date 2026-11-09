-- Deshace 01_mensajeria.sql (el código sigue funcionando sin estas columnas,
-- pero un ticket pedido y atendido por la misma persona pierde los "no leídos").
ALTER TABLE ticket_conversaciones DROP INDEX idx_tc_no_leidos;
ALTER TABLE ticket_conversaciones DROP COLUMN lado;
ALTER TABLE archivos_adjuntos_ticket
  DROP COLUMN nombre_archivo, DROP COLUMN tamano, DROP COLUMN id_usuario, DROP COLUMN fecha_subida;
