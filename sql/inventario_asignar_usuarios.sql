-- =====================================================================
--  INVENTARIO: asignar usuarios a colegios (tabla usuario_colegio)
--  Ejecutar DESPUÉS de sql/inventario_instalacion.sql.
--
--  El inventario decide qué ve cada persona con usuario_colegio:
--    id_perfil 3 = administrador -> ve TODOS los colegios
--    id_perfil 2 = técnico       -> ve los colegios que tenga asignados
--    id_perfil 1 = usuario       -> ve solo su colegio
--  Un usuario SIN filas aquí no ve ningún equipo.
-- =====================================================================

-- PASO 1. Buscar los IDs (solo lectura)
SELECT id, nombre, apellido_paterno, email FROM usuarios ORDER BY nombre;
SELECT id_colegio, nom_colegio FROM colegio ORDER BY nom_colegio;

-- PASO 2. Asignar un administrador de inventario.
--         Reemplazar 'TU_CORREO@seduc.cl' y el id_colegio de SEDUC (oficina central).
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_perfil, estado)
SELECT u.id, 15, 3, 1
  FROM usuarios u
 WHERE u.email = 'TU_CORREO@seduc.cl'
   AND NOT EXISTS (SELECT 1 FROM usuario_colegio x WHERE x.id_usuario = u.id AND x.id_colegio = 15);

-- PASO 3. Verificar
SELECT uc.id_usuario, u.email, uc.id_colegio, c.nom_colegio, uc.id_perfil, uc.estado
  FROM usuario_colegio uc
  JOIN usuarios u ON u.id = uc.id_usuario
  LEFT JOIN colegio c ON c.id_colegio = uc.id_colegio;
