-- =====================================================================
--  CONFIGURACIÓN – 03: revisar el resultado (solo lectura)
-- =====================================================================
USE `crist668_sistema_panel_central`;
SET NAMES utf8mb4;

-- Cada persona con su rol, colegio y departamento
SELECT c.nom_colegio AS colegio, uc.rol, IFNULL(d.nombre_departamento, '— todo el colegio —') AS departamento,
       u.id, TRIM(CONCAT(u.nombre, ' ', u.apellido_paterno)) AS persona, u.email,
       (SELECT GROUP_CONCAT(p.nombre_perfil ORDER BY p.id_perfil) FROM usuario_perfil up
          JOIN perfiles p ON p.id_perfil = up.id_perfil
         WHERE up.id_usuario = u.id AND up.id_perfil IN (1, 2, 3)) AS puede
  FROM usuario_colegio uc
  JOIN usuarios u ON u.id = uc.id_usuario
  JOIN colegio c  ON c.id_colegio = uc.id_colegio
  LEFT JOIN departamentos_colegio d ON d.id = uc.id_departamento_colegio
 WHERE uc.estado = 1
 ORDER BY c.nom_colegio, FIELD(uc.rol, 'admin_colegio', 'jefe_departamento', 'trabajador'), departamento, persona;

-- Colegios con más de un administrador o departamentos con más de un jefe
SELECT c.nom_colegio, IFNULL(d.nombre_departamento, '(colegio)') AS donde, uc.rol, COUNT(*) AS cuantos,
       GROUP_CONCAT(TRIM(CONCAT(u.nombre, ' ', u.apellido_paterno)) SEPARATOR ', ') AS quienes
  FROM usuario_colegio uc
  JOIN usuarios u ON u.id = uc.id_usuario
  JOIN colegio c  ON c.id_colegio = uc.id_colegio
  LEFT JOIN departamentos_colegio d ON d.id = uc.id_departamento_colegio
 WHERE uc.estado = 1 AND uc.rol IN ('admin_colegio', 'jefe_departamento')
 GROUP BY c.nom_colegio, donde, uc.rol
HAVING cuantos > 1;

-- Colegios (no Seduc) sin administrador
SELECT c.id_colegio, c.nom_colegio FROM colegio c
 WHERE c.estado = 1 AND c.id_colegio <> 15
   AND NOT EXISTS (SELECT 1 FROM usuario_colegio uc WHERE uc.id_colegio = c.id_colegio AND uc.rol = 'admin_colegio' AND uc.estado = 1);
