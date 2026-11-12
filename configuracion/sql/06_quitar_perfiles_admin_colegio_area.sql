-- =====================================================================
--  CONFIGURACIÓN – 06: dejar de usar los perfiles 4 (admin_colegio) y
--  5 (admin_area) en usuario_perfil.
--  Quién es administrador de colegio o jefe de departamento ya está en
--  usuario_colegio.rol (LOGICA_ROLES.txt). Desde el código de la rama qa
--  (12-11-2026) Tickets y el Dashboard de tickets lo leen desde ahí.
--  EJECUTAR SOLO DESPUÉS de subir ese código a QA y producción: el código
--  antiguo todavía usa estos perfiles para dar permisos de administrador.
-- =====================================================================
USE `crist668_sistema_panel_central`;

-- 0) Revisar: quién tiene perfil 4 o 5 y si su cargo en usuario_colegio coincide
SELECT u.id, u.email, p.nombre_perfil AS perfil_antiguo,
       GROUP_CONCAT(DISTINCT CONCAT(c.nom_colegio, ' / ', uc.rol) SEPARATOR ' | ') AS cargo_en_usuario_colegio
  FROM usuario_perfil up
  JOIN usuarios u  ON u.id = up.id_usuario
  JOIN perfiles p  ON p.id_perfil = up.id_perfil
  LEFT JOIN usuario_colegio uc ON uc.id_usuario = u.id AND uc.estado = 1
  LEFT JOIN colegio c ON c.id_colegio = uc.id_colegio
 WHERE up.id_perfil IN (4, 5)
 GROUP BY u.id, u.email, p.nombre_perfil
 ORDER BY u.email;
-- Si alguien tiene admin_colegio/admin_area pero NO aparece con rol admin_colegio /
-- jefe_departamento, corrige su cargo en Configuración → Usuarios antes de seguir.

-- 1) Respaldo
CREATE TABLE IF NOT EXISTS usuario_perfil_respaldo_4_5 AS
SELECT * FROM usuario_perfil WHERE id_perfil IN (4, 5);

-- 2) Quitar las asignaciones antiguas
DELETE FROM usuario_perfil WHERE id_perfil IN (4, 5);

-- 3) Desactivar los perfiles en el catálogo (no se borran: el alta de usuarios
--    usa sus nombres para elegir los menús iniciales). Dejan de aparecer en el modal.
UPDATE perfiles SET estado = 0 WHERE id_perfil IN (4, 5);

SELECT * FROM perfiles ORDER BY id_perfil;

-- VOLVER ATRÁS:
-- INSERT INTO usuario_perfil SELECT * FROM usuario_perfil_respaldo_4_5;
-- UPDATE perfiles SET estado = 1 WHERE id_perfil IN (4, 5);
