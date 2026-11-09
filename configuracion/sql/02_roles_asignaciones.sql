-- =====================================================================
--  CONFIGURACIÓN – 02: asignaciones de cada persona (rol, colegio, departamento)
--  Generado a partir de los datos de QA (09-11-2026) con estas reglas:
--   - colegio  = dominio del correo (@seduc.cl → Seduc, etc.)
--   - departamento = el de su jefatura si es de su colegio; si no, su área
--     (Seduc) o el departamento del mismo nombre en su colegio
--   - admin_colegio = jefatura Admin_Colegio o perfil 4
--   - jefe_departamento = perfil 5 (admin_area); el resto = trabajador
--  Decisiones de Cristian (09-11-2026):
--   - Cordillera: admin solo Samuel Aranda.
--   - Admins: Miguel Ortúzar (Huinganal), Macarena Urrutia (Huelén),
--     Katrin Prendergast (Los Alerces), Gerardo Elgueta (Tabancura),
--     Catalina Irarrázaval (Los Andes).
--   - Finanzas: 2 jefes (Oscar Mercado, Yanelis Reyes). Gerencia: Felipe Guzmán.
--     Periodismo: María José Sánchez.
--   - Área PROFESORAS → departamento PEDAGOGÍA de Seduc.
--   - Técnicos de colegios que apuntaban a Informática de Seduc → Informática
--     de su propio colegio (creada por 01_roles_estructura.sql).
--  * Requiere 01_roles_estructura.sql. Reemplaza las filas de estas 39
--    personas en usuario_colegio (la copia está en usuario_colegio_respaldo).
--  * Se puede ejecutar más de una vez. Si algo sale mal: 99_volver_atras.sql
-- =====================================================================
USE `crist668_sistema_panel_central`;
SET NAMES utf8mb4;
START TRANSACTION;   -- si algo falla, no queda a medias
DELETE FROM usuario_colegio WHERE id_usuario IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12, 14, 15, 16, 17, 18, 20, 24, 25, 26, 31, 33, 35, 39, 41, 42, 43, 44, 45, 46, 48, 49, 2476, 2488, 2492, 2493, 2495, 2496, 2497);

-- id_perfil: se conserva el que tenía (Inventario lo usa para decidir qué colegios mostrar)

-- ---------- Seduc SpA (id 15)
-- Alex Espinoza <aespinosa@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 1, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'FINANZAS' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 1), 1), 1, 8, NOW();  -- trabajador · FINANZAS
-- Francisco Chavez <fchavez@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 2, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'CONTABILIDAD' LIMIT 1), 'jefe_departamento', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 2), 1), 1, 8, NOW();  -- jefe · CONTABILIDAD
-- Alvaro Fuentes <afuentes@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 3, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'CONTABILIDAD' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 3), 1), 1, 8, NOW();  -- trabajador · CONTABILIDAD
-- Rodrigo Leon <rleon@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 4, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'CONTABILIDAD' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 4), 1), 1, 8, NOW();  -- trabajador · CONTABILIDAD
-- Victor Perez <vperez@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 5, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'CONTABILIDAD' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 5), 1), 1, 8, NOW();  -- trabajador · CONTABILIDAD
-- Ramon Oliva <roliva@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 6, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 6), 1), 1, 8, NOW();  -- trabajador · INFORMATICA
-- Manuel Gutierrez <mgutierrez@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 7, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 7), 1), 1, 8, NOW();  -- trabajador · INFORMATICA
-- Cristian jorquera <cjorquera@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 8, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 8), 1), 1, 8, NOW();  -- trabajador · INFORMATICA
-- Gonzalo Munoz <gmunoz@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 9, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'GERENCIA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 9), 1), 1, 8, NOW();  -- trabajador · GERENCIA
-- Maria Jose Sanchez <mjsanchez@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 10, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'PERIODISMO' LIMIT 1), 'jefe_departamento', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 10), 1), 1, 8, NOW();  -- jefe · PERIODISMO
-- Francisco Valenzuela <fvalenzuela@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 12, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'RECURSOS HUMANOS' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 12), 1), 1, 8, NOW();  -- trabajador · RECURSOS HUMANOS
-- Javier Ureta <fjureta@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 14, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'GERENCIA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 14), 1), 1, 8, NOW();  -- trabajador · GERENCIA
-- Beatriz Herrera <bherrera@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 15, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'GERENCIA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 15), 1), 1, 8, NOW();  -- trabajador · GERENCIA
-- Gisela Friedl <gfriedl@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 16, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'RECURSOS HUMANOS' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 16), 1), 1, 8, NOW();  -- trabajador · RECURSOS HUMANOS
-- Constanza Nieto <cnieto@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 17, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'RECURSOS HUMANOS' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 17), 1), 1, 8, NOW();  -- trabajador · RECURSOS HUMANOS
-- Veronica Alvarez <valvarez@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 18, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'PEDAGOGIA' LIMIT 1), 'jefe_departamento', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 18), 1), 1, 8, NOW();  -- jefe · PEDAGOGIA
-- Magdalena Ibanez <mibanez@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 20, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'FINANZAS' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 20), 1), 1, 8, NOW();  -- trabajador · FINANZAS
-- Oscar Mercado <omercado@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 24, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'FINANZAS' LIMIT 1), 'jefe_departamento', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 24), 1), 1, 8, NOW();  -- jefe · FINANZAS
-- Yanelis Reyes <yreyes@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 25, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'FINANZAS' LIMIT 1), 'jefe_departamento', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 25), 1), 1, 8, NOW();  -- jefe · FINANZAS
-- Josefina Mackenna <jmackenna@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 26, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'FINANZAS' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 26), 1), 1, 8, NOW();  -- trabajador · FINANZAS
-- Catalina Aspillaga <caspillaga@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 35, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'PERIODISMO' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 35), 1), 1, 8, NOW();  -- trabajador · PERIODISMO
-- Francisco  Gomez <fgomez@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 39, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'CONTABILIDAD' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 39), 1), 1, 8, NOW();  -- trabajador · CONTABILIDAD
-- Trinidad Errazuriz <terrazuriz@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 41, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'PEDAGOGIA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 41), 1), 1, 8, NOW();  -- trabajador · PEDAGOGIA
-- Alejandro  Rojas <arojas@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 42, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 42), 1), 1, 8, NOW();  -- trabajador · INFORMATICA
-- Felipe Guzman <fguzman@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 43, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'GERENCIA' LIMIT 1), 'jefe_departamento', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 43), 1), 1, 8, NOW();  -- jefe · GERENCIA
-- Trinidad Alamos <talamos@seduc.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 44, 15, (SELECT id FROM departamentos_colegio WHERE id_colegio = 15 AND nombre_departamento = 'PEDAGOGIA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 44), 1), 1, 8, NOW();  -- trabajador · PEDAGOGIA

-- ---------- Colegio Cordillera de las Condes (id 1)
-- Javier  Gonzalez <jgonzalez@colegiocordillera.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 31, 1, (SELECT id FROM departamentos_colegio WHERE id_colegio = 1 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 31), 1), 1, 8, NOW();  -- trabajador · INFORMATICA
-- Samuel Aranda <saranda@colegiocordillera.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 48, 1, NULL, 'admin_colegio', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 48), 1), 1, 8, NOW();
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 48, 1, (SELECT id FROM departamentos_colegio WHERE id_colegio = 1 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 48), 1), 1, 8, NOW();  -- trabajador · INFORMATICA
-- Nicolas Bernstein <nbernstein@colegiocordillera.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 2488, 1, (SELECT id FROM departamentos_colegio WHERE id_colegio = 1 AND nombre_departamento = 'LENGUAJE' LIMIT 1), 'jefe_departamento', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 2488), 1), 1, 8, NOW();  -- jefe · LENGUAJE

-- ---------- Colegio Huelén (id 11)
-- Macarena Urrutia <m.urrutia@huelen.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 46, 11, NULL, 'admin_colegio', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 46), 1), 1, 8, NOW();
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 46, 11, (SELECT id FROM departamentos_colegio WHERE id_colegio = 11 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'jefe_departamento', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 46), 1), 1, 8, NOW();  -- jefe · INFORMATICA
-- Carlos Gallardo <c.gallardo@huelen.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 2492, 11, (SELECT id FROM departamentos_colegio WHERE id_colegio = 11 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 2492), 1), 1, 8, NOW();  -- trabajador · INFORMATICA

-- ---------- Colegio Huinganal (id 13)
-- Miguel Ortuzar <mortuzar@colegiohuinganal.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 33, 13, NULL, 'admin_colegio', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 33), 1), 1, 8, NOW();
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 33, 13, (SELECT id FROM departamentos_colegio WHERE id_colegio = 13 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 33), 1), 1, 8, NOW();  -- trabajador · INFORMATICA

-- ---------- Colegio Los Alerces (id 10)
-- katrin Pendergrast <kprendergast@colegiolosalerces.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 2496, 10, NULL, 'admin_colegio', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 2496), 1), 1, 8, NOW();
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 2496, 10, (SELECT id FROM departamentos_colegio WHERE id_colegio = 10 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 2496), 1), 1, 8, NOW();  -- trabajador · INFORMATICA
-- Claudio Ortega <cortega@colegiolosalerces.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 2497, 10, (SELECT id FROM departamentos_colegio WHERE id_colegio = 10 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 2497), 1), 1, 8, NOW();  -- trabajador · INFORMATICA

-- ---------- Colegio Los Andes de Vitacura (id 9)
-- Leandro Barrios <lbarrios@colegiolosandes.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 49, 9, (SELECT id FROM departamentos_colegio WHERE id_colegio = 9 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 49), 1), 1, 8, NOW();  -- trabajador · INFORMATICA
-- Catalina Irarrazaval <cirarrazaval@colegiolosandes.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 2476, 9, NULL, 'admin_colegio', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 2476), 1), 1, 8, NOW();
-- Elvira Claro <eclaso@colegiolosandes.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 2493, 9, (SELECT id FROM departamentos_colegio WHERE id_colegio = 9 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'jefe_departamento', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 2493), 1), 1, 8, NOW();  -- jefe · INFORMATICA

-- ---------- Colegio Tabancura (id 8)
-- Gerardo Elgueta <gelgueta@tabancura.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 45, 8, NULL, 'admin_colegio', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 45), 1), 1, 8, NOW();
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 45, 8, (SELECT id FROM departamentos_colegio WHERE id_colegio = 8 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 45), 1), 1, 8, NOW();  -- trabajador · INFORMATICA
-- Francisco Salazar <fsalazar@tabancura.cl>
INSERT INTO usuario_colegio (id_usuario, id_colegio, id_departamento_colegio, rol, id_perfil, estado, creado_por, fecha_asignacion) SELECT 2495, 8, (SELECT id FROM departamentos_colegio WHERE id_colegio = 8 AND nombre_departamento = 'INFORMATICA' LIMIT 1), 'trabajador', COALESCE((SELECT MAX(r.id_perfil) FROM usuario_colegio_respaldo r WHERE r.id_usuario = 2495), 1), 1, 8, NOW();  -- trabajador · INFORMATICA

COMMIT;

-- Verificación: personas sin ninguna asignación (debería no devolver filas)
SELECT u.id, u.nombre, u.email FROM usuarios u
 WHERE NOT EXISTS (SELECT 1 FROM usuario_colegio uc WHERE uc.id_usuario = u.id AND uc.estado = 1);
