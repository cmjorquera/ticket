-- =====================================================================
--  INVENTARIO: permiso para TODOS los usuarios (ambiente QA)
--  Da a cada usuario el perfil administrador (3) en SEDUC (colegio 15),
--  así todos ven todo el inventario. Ejecutar DESPUÉS de
--  inventario_instalacion.sql. Se puede ejecutar varias veces sin duplicar.
-- =====================================================================
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_perfil, estado)
SELECT u.id, 15, 3, 1
  FROM usuarios u
 WHERE NOT EXISTS (SELECT 1 FROM usuario_colegio x
                    WHERE x.id_usuario = u.id AND x.id_colegio = 15);

SELECT COUNT(*) AS usuarios_con_inventario FROM usuario_colegio;
