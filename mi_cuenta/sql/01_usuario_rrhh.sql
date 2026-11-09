-- =====================================================================
--  MI CUENTA – 01: vínculo de cada usuario con su ficha en Buk (opcional)
--  Sin esta tabla, el sistema busca a la persona en Buk por su correo.
--  Con ella, un ADMINISTRADOR puede fijar el RUT o el id de empleado de Buk
--  (la persona no puede cambiarlo, así nadie ve liquidaciones ajenas).
--  * Se puede ejecutar más de una vez. Compatible con MySQL 5.7.
-- =====================================================================
USE `crist668_sistema_panel_central`;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS usuario_rrhh (
    id_usuario       INT          NOT NULL,              -- usuarios.id
    rut              VARCHAR(12)  NULL,                  -- ej: 12345678-9 (sin puntos)
    id_empleado_buk  VARCHAR(40)  NULL,                  -- id de la ficha en Buk
    actualizado_en   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY ux_usuario_rrhh_rut (rut),
    UNIQUE KEY ux_usuario_rrhh_buk (id_empleado_buk)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ejemplo (lo hace un administrador):
-- INSERT INTO usuario_rrhh (id_usuario, rut) VALUES (8, '12345678-9')
--   ON DUPLICATE KEY UPDATE rut = VALUES(rut);

SELECT u.id, u.nombre, u.email, r.rut, r.id_empleado_buk
  FROM usuarios u LEFT JOIN usuario_rrhh r ON r.id_usuario = u.id
 WHERE LOWER(u.estado) = 'activo'
 ORDER BY u.nombre;
