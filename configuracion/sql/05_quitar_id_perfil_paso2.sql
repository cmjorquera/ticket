-- =====================================================================
--  CONFIGURACIÓN – 05: quitar usuario_colegio.id_perfil  · PASO 2 de 2
--  SOLO DESPUÉS de subir el código de la rama qa a TODOS los sitios que usan
--  esta base (QA y producción). El código antiguo todavía lee esta columna.
--  El respaldo quedó en usuario_colegio_id_perfil_respaldo (paso 1).
-- =====================================================================
USE `crist668_sistema_panel_central`;

ALTER TABLE usuario_colegio DROP COLUMN id_perfil;

SHOW COLUMNS FROM usuario_colegio;

-- VOLVER ATRÁS (si algo falla):
-- ALTER TABLE usuario_colegio ADD COLUMN id_perfil INT NULL DEFAULT NULL AFTER rol;
-- UPDATE usuario_colegio uc JOIN usuario_colegio_id_perfil_respaldo r ON r.id = uc.id SET uc.id_perfil = r.id_perfil;
