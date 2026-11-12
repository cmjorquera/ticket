-- =====================================================================
--  INVENTARIO – quién ve qué colegios (rama qa, 2026-11-12)
--  Inventario decide con el modelo de roles (configuracion/logica/LOGICA_ROLES.txt):
--    ve TODOS los colegios        → usuario_perfil.id_perfil = 3 (super_admin)
--    elige entre sus colegios
--      y ve el dashboard          → usuario_perfil 2 (técnico) o rol admin_colegio
--    solo su colegio              → el resto
--  usuario_colegio.id_perfil ya no se usa (se elimina: configuracion/sql/04 y 05).
-- =====================================================================

-- 1) Quiénes son super_admin (deberían ser solo Cristian y 2 personas más)
SELECT u.id, u.nombre, u.apellido_paterno, u.email
  FROM usuario_perfil up JOIN usuarios u ON u.id = up.id_usuario
 WHERE up.id_perfil = 3
 ORDER BY u.nombre;

-- 2) Qué verá cada persona en Inventario
SELECT u.id, u.email,
       CASE WHEN EXISTS(SELECT 1 FROM usuario_perfil x WHERE x.id_usuario = u.id AND x.id_perfil = 3) THEN '3 - todos los colegios'
            WHEN EXISTS(SELECT 1 FROM usuario_perfil x WHERE x.id_usuario = u.id AND x.id_perfil = 2)
              OR EXISTS(SELECT 1 FROM usuario_colegio x WHERE x.id_usuario = u.id AND x.estado = 1 AND x.rol = 'admin_colegio')
                 THEN '2 - sus colegios + dashboard'
            ELSE '1 - solo su colegio' END AS inventario,
       GROUP_CONCAT(DISTINCT CONCAT(c.nom_colegio, ' / ', uc.rol) ORDER BY c.nom_colegio SEPARATOR ' | ') AS asignaciones
  FROM usuarios u
  JOIN usuario_colegio uc ON uc.id_usuario = u.id AND uc.estado = 1
  JOIN colegio c          ON c.id_colegio = uc.id_colegio
 GROUP BY u.id, u.email
 ORDER BY inventario DESC, u.email;
