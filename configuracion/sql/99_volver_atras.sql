-- =====================================================================
--  CONFIGURACIÓN – 99: VOLVER ATRÁS la migración de roles
--  Devuelve usuario_colegio a como estaba antes de 02_roles_asignaciones.sql
--  usando usuario_colegio_respaldo. Las columnas nuevas quedan (sin uso).
--  Usar solo si algo salió mal.
-- =====================================================================
USE `crist668_sistema_panel_central`;
SET NAMES utf8mb4;
START TRANSACTION;
DELETE FROM usuario_colegio;
INSERT INTO usuario_colegio (id, id_usuario, id_colegio, id_perfil, estado, fecha_asignacion)
SELECT id, id_usuario, id_colegio, id_perfil, estado, fecha_asignacion FROM usuario_colegio_respaldo;
COMMIT;
SELECT COUNT(*) AS filas_restauradas FROM usuario_colegio;
