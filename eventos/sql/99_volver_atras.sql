-- Deshace 01_eventos.sql SIN borrar los eventos existentes.
DROP TABLE IF EXISTS eventos_invitados;
DROP TABLE IF EXISTS eventos_historial;
ALTER TABLE eventos DROP COLUMN creado_por, DROP COLUMN todo_el_dia, DROP COLUMN secuencia, DROP COLUMN recordatorio_enviado;
