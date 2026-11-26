-- =====================================================================
--  CONFIGURACIÓN – 04: quitar usuario_colegio.id_perfil  · PASO 1 de 2
--  Se puede ejecutar YA, antes de subir el código nuevo: no rompe nada.
--  Deja la columna opcional (NULL) para que el código nuevo, que ya no la
--  escribe, pueda crear usuarios. El código antiguo sigue funcionando.
--  Luego: subir el código de la rama qa y ejecutar 05_quitar_id_perfil_paso2.sql
-- =====================================================================
-- Se ejecuta en la base seleccionada en phpMyAdmin (QA: crist668_sistema_panel_central · producción: acceso_sistema_panel)

-- Respaldo de la columna (id de fila + valor), por si hay que volver atrás
CREATE TABLE IF NOT EXISTS usuario_colegio_id_perfil_respaldo AS
SELECT id, id_perfil FROM usuario_colegio;

-- La llave única antigua incluía id_perfil (01_roles_estructura.sql ya la
-- reemplaza por uk_uc_asignacion; esto es por si quedó en alguna base)
SET @sql := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE()
                 AND TABLE_NAME = 'usuario_colegio' AND INDEX_NAME = 'uk_usuario_colegio') > 0,
  'ALTER TABLE usuario_colegio DROP INDEX uk_usuario_colegio',
  'SELECT ''uk_usuario_colegio no existe'' AS aviso');
PREPARE st FROM @sql; EXECUTE st; DEALLOCATE PREPARE st;

ALTER TABLE usuario_colegio MODIFY id_perfil INT NULL DEFAULT NULL
  COMMENT 'OBSOLETA: se elimina en 05_quitar_id_perfil_paso2.sql. Usar usuario_perfil y rol.';

SHOW COLUMNS FROM usuario_colegio;
