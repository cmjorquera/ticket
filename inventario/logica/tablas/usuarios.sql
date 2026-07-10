-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-07-2026 a las 09:59:14
-- Versión del servidor: 10.6.19-MariaDB
-- Versión de PHP: 8.1.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `acceso_sistema_panel`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido_paterno` varchar(100) DEFAULT NULL,
  `apellido_materno` varchar(100) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `sexo` int(11) DEFAULT NULL,
  `clave` varchar(255) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `intentos_fallidos` int(11) DEFAULT 0,
  `id_area_trabajo` int(11) DEFAULT NULL,
  `foto` blob DEFAULT NULL,
  `estado` varchar(50) DEFAULT NULL,
  `anexo` char(10) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `token_reinicio` varchar(100) DEFAULT NULL,
  `identificador` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido_paterno`, `apellido_materno`, `fecha_nacimiento`, `email`, `telefono`, `sexo`, `clave`, `cargo`, `intentos_fallidos`, `id_area_trabajo`, `foto`, `estado`, `anexo`, `fecha_creacion`, `token_reinicio`, `identificador`) VALUES
(1, 'Alex', 'Espinoza', '', '0000-00-00', 'aespinosa@seduc.cl', '5551234567', 1, '$2y$10$bxbSZqUra.5g3O7XXP8NmeJtlRbulv.dgYJAr7pA/jaGUDBjFUTui', 'Analista de Recaudación', 0, 8, NULL, 'Activo', '304', '2024-04-10 13:36:23', '', 'xY8ziBWNWc1jmHg1'),
(2, 'Francisco', 'Chavez', '', '2024-07-03', 'fchavez@seduc.cl', '5551234567', 2, '$2y$10$bxbSZqUra.5g3O7XXP8NmeJtlRbulv.dgYJAr7pA/jaGUDBjFUTui', 'Contador General', 0, 2, NULL, 'Activo', '306', '2024-04-10 14:01:47', '', 'dPjXIubfwJUb2t84'),
(3, 'Alvaro', 'Fuentes', '', '2024-04-26', 'afuentes@seduc.cl', '5551234567', 1, '$2y$10$7vPGbufjJHK2naf22UZ1Zec27jQGFjCkIk.jK.vbIB6x0FCrk/Qjq', 'Analista Contable', 0, 2, NULL, 'Activo', '308', '2024-04-10 14:01:47', '', 'LtW8EHnCO4IeRsxa'),
(4, 'Rodrigo', 'Leon', '', '2024-12-11', 'rleon@seduc.cl', '5551234567', 1, '$2y$10$gS9B7B8GrOmPdmL7rw.EXOyBAbwex93VwZMXL1uMwhLzp9X3wEWem', 'Analista Contable', 0, 2, NULL, 'Activo', '319', '2024-04-10 14:01:47', '', '4ESjNPCq8eCfdb5H'),
(5, 'Victor', 'Perez', '', '2024-08-24', 'vperez@seduc.cl', '5551234567', 1, '$2y$10$OMqYArUZxKRsq4kz.SN9buD1M5Kq7BKX64ikUHTLUd7y5RD.gruMG', 'Analista Contable', 0, 2, NULL, 'Activo', '353', '2024-04-10 14:01:47', '', '6fO83HcKXoUgo0Jr'),
(6, 'Ramon', 'Oliva', 'Zenteno', '2024-05-18', 'roliva@seduc.cl', '5551234567', 1, '$2y$10$wIY1b4bRE1cOFL5wL9kHPOvZTshIZ/qq6yre6DI0wQ5Rpng/sbS9G', 'Analista Programador', 0, 1, NULL, 'Activo', '359', '2024-04-10 14:01:47', '', 'TY26fLT3qMvZinOM'),
(7, 'Manuel', 'Gutierrez', '', '2024-01-09', 'mgutierrez@seduc.cl', '123456', 1, '$2y$10$80CED.Ajy2TSWWFawdMAY.2Q4DtbskPJjr4DLOQUuUSsAPu8wcI3a', 'Jefe de Computación e Informatica', 0, 1, NULL, 'Activo', '322', '2024-04-10 14:01:47', '', 'iZTkQ4CKH6hTstQC'),
(8, 'Cristian', 'jorquera', 'Gonzalezeee', '1986-01-10', 'cjorquera@seduc.cl', '988302735', 1, '$2y$10$0X7mN0UNH9Im0z3y0ELxfe61elN.SsiTeYRIePqeLPEROelnssEZu', 'Analista Programador', 0, 1, NULL, 'Activo', '358', '2024-04-10 14:01:47', '', 'q8ctAnYBSqmlvl20'),
(9, 'Gonzalo', 'Munoz', '', '2024-03-13', 'gmunoz@seduc.cl', '5551234567', 1, '$2y$10$JQno0PyxlgRYoGS.Q.ell.xkealbQiYAGs9xLFORJFPjoHYPF62l.', 'Prevencionista de Riesgos', 0, 5, NULL, 'Activo', '302', '2024-04-10 14:01:47', '', 'JwfvBkHnCO3EVuy7'),
(10, 'Maria Jose', 'Sanchez', '', '2024-07-13', 'mjsanchez@seduc.cl', '5551234567', 2, '$2y$10$i4IvA1eaqjPudl93jgMnvuHi6UAUyrblQfSMUC2lkkhs/lszx71lq', 'Director (a) de Comunicaciones y Marketing', 0, 3, NULL, 'Activo', '337', '2024-04-10 14:01:47', '', 'Lj8AnVmQZe4rTZ4f'),
(11, 'Antonio', 'Valdes', '', '2024-05-02', 'avaldes@seduc.cl', '5551234567', 1, '$2y$10$Hxab9ZB8/BZO45UwdYKhvueMHquT7GPYTGLomQCP1BiZwfE5JdGYC', 'Jefe de Comunicaciones', 0, 9, NULL, 'Bloqueado', '346', '2024-04-10 14:01:47', '', 'Rqot5PK3R0ikzHBL'),
(12, 'Francisco', 'Valenzuela', '', '2024-04-11', 'fvalenzuela@seduc.cl', '5551234567', 1, '$2y$10$U0SUIVSdqO6beNVpwjn9NuWoYWbc7n1Fcf/nm3cFcrUUAd4cNTSpS', 'Administrador General de Remuneraciones', 0, 3, NULL, 'Activo', '314', '2024-04-10 14:01:47', '', 'TZ8x9X9I0Gf3lrYq'),
(13, 'Jose Ignacio', 'Diaz', '', '2024-06-27', 'jidiaz@seduc.cl', '5551234567', 1, '$2y$10$sTbOdH36jRDeoP1ZYVPuPOVgu1E4lGkEyhSAOXMU42PknRnOy8Tqu', 'Director General', 0, 7, NULL, 'Activo', '331', '2024-04-10 14:01:47', '', '2JoDNXd3u81AFqWh'),
(14, 'Javier', 'Ureta', '', '2024-10-12', 'fjureta@seduc.cl', '5551234567', 1, '$2y$10$A/Ew3an5Z6OfxqKn9Hnwm.jjPkBxqSJGE29pvIP049tyG4/40q4bS', 'Director de Personas', 0, 7, NULL, 'Activo', '320', '2024-04-10 14:01:47', '', 'q8fElCWDd1koOHWr'),
(15, 'Beatriz', 'Herrera', '', '2024-04-28', 'bherrera@seduc.cl', '5551234567', 2, '$2y$10$gArFkwxx2/DqKuw.d/swl.B7M.aT57vKZhF4Y79Mz9lqiJSV9rQFG', 'Coordinador General de Proyectos y Gestión', 0, 5, NULL, 'Activo', '354', '2024-04-10 14:01:47', '', 'cxVQhJDOZlCUuBmR'),
(16, 'Gisela', 'Friedl', '', '2024-09-19', 'gfriedl@seduc.cl', '5551234567', 2, '$2y$10$N3KZs9utSofJXLlgkHe4iOTAczF9fw1sszj0AXxyFD32W7z9I1loe', 'Coordinador(a) de Sistemas de Personas', 0, 3, NULL, 'Activo', '356', '2024-04-10 14:01:47', '', '4zuzgo3T9TSyZ5j8'),
(17, 'Constanza', 'Nieto', '', '2024-07-23', 'cnieto@seduc.cl', '5551234567', 2, '$2y$10$hfMAtdY8VJjRkc8RaZGO2ucu.RgXCW47fO4L8XjaKy6vgVGKI393q', 'Coordinador(a) de Personas', 0, 3, NULL, 'Activo', '345', '2024-04-10 14:01:47', '', 'Bp6ajUrk8zhwDq2N'),
(18, 'Veronica', 'Alvarez', '', '2024-10-05', 'valvarez@seduc.cl', '5551234567', 2, '$2y$10$i6DwqMspGndJJ2qxqSbl0.PPJnlzw/nbBD.NYg5WFzHgDb5rflREq', 'Enfermera Coordinador(a)', 0, 4, NULL, 'Activo', '312', '2024-04-10 14:01:47', '', 'FQ0lAHCQa95QMdCe'),
(19, 'Carola', 'Prado', '', '2024-10-06', 'cprado@seduc.cl', '5551234567', 2, '$2y$10$fPeQIJFMHgOjR4aCnNdDKuvPzyqaazqorTROV2yxYVZZa4CAhK5cO', 'Subdirector(a) Académico', 0, 4, NULL, 'Activo', '324', '2024-04-10 14:01:47', '', 'c5H2YCXO1ufDgdb4'),
(20, 'Magdalena', 'Ibanez', '', '2024-01-03', 'mibanez@seduc.cl', '5551234567', 2, '$2y$10$ofrmF6ZXGjJwXEx67xexkeKYYQfPRoE0SJQt8M3aYd5ny3CyujKDG', 'Subdirector(a) de Formación', 0, 4, NULL, 'Activo', '350', '2024-04-10 14:01:47', '', 'DQ6RPpoBGpQPuNj4'),
(21, 'Carola', 'Barros', '', '2024-04-05', 'cbarros@seduc.cl', '5551234567', 2, '$2y$10$.zWmTKsrkzH3jebXz/BcIuw5wnFI7VQOgRgt8QNcSgBkiU2NYVTPy', 'Asesor(a) Equipo Técnico PEIS', 0, 4, NULL, 'Activo', '348', '2024-04-10 14:01:47', '', 'cEwvNhRjNTTGxwNg'),
(22, 'Cecilia', 'Nestler', '', '2024-10-09', 'cnestler@seduc.cl', '5551234567', 2, '$2y$10$DbifWSJHeTJKPgw61UE9puVfVZQeNmMeMis0gxKVaA.kje9V9L4.C', 'Asesora Equipo Técnico de Ingles', 0, 4, NULL, 'Activo', '349', '2024-04-10 14:01:47', '', 'N1xtAmR5FXAVHAGs'),
(23, 'Cecilia', 'Ostornol', '', '2024-04-02', 'costornol@seduc.cl', '5551234567', 2, '$2y$10$.HBo0x.sk1naRaPoY2xNLesYSGWBFSs6cv4V5o./BBhTExtQ6lSey', 'Asesora Equipo Técnico de Ingles', 0, 4, NULL, 'Activo', '313', '2024-04-10 14:01:47', '', '73MxelUkJxlWvzaX'),
(24, 'Oscar', 'Mercado', '', '2024-06-26', 'omercado@seduc.cl', '5551234567', 1, '$2y$10$iTksx0PSjSiFTchicQWiJ.lbXJhaQ39iTN3Bh9IetDmAcWuEAudKe', 'Director de Administración y Finanzas', 0, 7, NULL, 'Activo', '334', '2024-04-10 14:01:47', '', '8DE9DzM2GbFFaHOP'),
(25, 'Yanelis', 'Reyes', '', '2024-08-16', 'yreyes@seduc.cl', '5551234567', 2, '$2y$10$Ww/RV9n..WLGRl46yxzZ3OqnTLGv8MCuz33JwuDINMFsyJeq2oRfC', 'Jefe(a) de Finanzas', 0, 8, NULL, 'Activo', '325', '2024-04-10 14:01:47', '', 'vRGGbKZzJKnvfAZ2'),
(26, 'Josefina', 'Mackenna', '', '2024-04-09', 'jmackenna@seduc.cl', '5551234567', 2, '$2y$10$8yYcZn8gRwbxYywMQLV.MeCcbtS5PYWEV0DymY8SzAJyoY/nJZ2lC', 'Jefe(a) de Administración ', 0, 8, NULL, 'Activo', '321', '2024-04-10 14:01:47', '', '30HjkyEoS3t3F344'),
(27, 'prueba', 'jorquera', 'Gonzalez', '2024-04-24', 'cm.jordddquerag@gmail.com', '988302735', 1, '', 'espia', 0, 1, NULL, 'Activo', '888', '2024-04-24 14:17:44', '1087d9cdce76d4aaf4eeaee53593b409', 'YumbFF8vW4oytx7M'),
(28, 'COLEGIO ', 'TABANCURA', ' ', '0000-00-00', 'contacto@tabancura.cl', '23717450', 2, '$2y$10$RDPiHV4lDp7d0NJnRP1zbuiSCcriCg7AqsZjFPGaXDVNxRX9.Qa4C', 'Secretaria', 0, 10, NULL, 'Activo', '23717450', '2024-05-10 16:27:17', '', 'pzrnmviP7TY38opG'),
(30, 'COLEGIO', ' HUELEN', ' ', '0000-00-00', 'www.colegiohuelen.cl', '23659191', 2, '$2y$10$K2BYoDn7neJL9c28.RQPuudzQ0Sflme44iudfx3B5rvdTjI8Qgida', 'Secretaria', 0, 10, NULL, 'Activo', '23659191', '2024-05-10 16:27:17', '', 'IvfzUHAHzzZ3awVW'),
(31, 'Javier ', 'Gonzalez ', 'COORDILLERA', '0000-00-00', 'jgonzalez@colegiocordillera.cl ', '24295900', 2, '$2y$10$2AAVHIFwEJyOXQ5NNBaxPORRQU2fZwTQia2bBjfYuwaEgF8MzcFxO', 'Secretaria', 0, 10, NULL, 'Activo', '24295900', '2024-05-10 16:27:17', '', 'JF0P1s1BO6RTIJkk'),
(32, 'COLEGIO ', ' LOS ALERCES', ' ', '0000-00-00', 'www.colegiolosalerces.cl', '24322102', 2, '$2y$10$zlW5xFBPvY1ec/Qp3Vzheu9z9VQFnVnMJG8S7piebZVrYU0RbWBY.', 'Secretaria', 0, 10, NULL, 'Activo', '24322102', '2024-05-10 16:27:17', '', 'tcrrH0OUUEmIjimM'),
(33, 'Miguel', 'Ortuzar', 'HUINGANAL', '0000-00-00', 'mortuzar@colegiohuinganal.cl', '225921720', 1, '$2y$10$N9TmkW8KFn5EBj1kwJy3Ve9mfv0N4XwuIChzP2gXcuaOcBYT82R6K', 'Secretaria', 0, 10, NULL, 'Activo', '225921720', '2024-05-10 16:27:17', '', 'EIwgDbQz6FTgsnnx'),
(34, 'COLEGIO', 'CANTAGALLO ', ' ', '0000-00-00', 'www.colegiocantagallo.cl', '22157520', 2, '$2y$10$hpfiDyYi5Vra3JoQLNz.iOcaDEF7H6GGTr9H3Aw7dNRd1BTzJzrLa', 'Secretaria', 0, 10, NULL, 'Activo', '22157520', '2024-05-10 16:27:17', '', 'qlgbUQm5eHGazflQ'),
(35, 'Catalina', 'Aspillaga', 'Finlay', '0000-00-00', 'caspillaga@seduc.cl', '32423423', 2, '', 'Dueña de casa y Secretaria', 0, 10, NULL, 'Activo', '301', '2024-06-10 19:15:56', NULL, NULL),
(38, 'prueba', 'prueba', 'prueba', '2024-12-18', 'prueba@prueba.cl', '988302735', 1, '$2y$10$RDPiHV4lDp7d0NJnRP1zbuiSCcriCg7AqsZjFPGaXDVNxRX9.Qa4C', 'admin', 0, NULL, NULL, 'Activo', NULL, '2024-12-09 18:01:34', NULL, NULL),
(39, 'Francisco ', 'Gomez', 'Gomez', NULL, 'fgomez@seduc.cl', '5551234567', 1, '$2y$10$RDPiHV4lDp7d0NJnRP1zbuiSCcriCg7AqsZjFPGaXDVNxRX9.Qa4C', 'Contador General', 0, 2, NULL, 'Activo', '328', '2024-04-10 14:01:47', NULL, 'TZ8x9X9I0Gf3lJgw'),
(40, 'Jessica', 'Prieto', 'Prieto', '2005-12-05', 'jprieto@seduc.cl', '22157520', 2, '$2y$10$RDPiHV4lDp7d0NJnRP1zbuiSCcriCg7AqsZjFPGaXDVNxRX9.Qa4C', 'Finanzas', 0, 8, NULL, 'Activo', '318', '2024-12-20 12:55:41', NULL, 'TZ8x9X9I0Gf5487A'),
(41, 'Trinidad ', 'Errazuriz', 'Errazuriz', '0000-00-00', 'terrazuriz@seduc.cl', '22157520', 2, '$2y$10$RDPiHV4lDp7d0NJnRP1zbuiSCcriCg7AqsZjFPGaXDVNxRX9.Qa4C', 'Asesor(a) Equipo Técnico PEIS', 0, 4, NULL, 'Activo', '348', '2025-01-02 15:11:48', NULL, 'TZ8x9X9I0Gf3JHGr'),
(42, 'Alejandro ', 'Rojas', 'Schweitzer', '0000-00-00', 'arojas@seduc.cl', '', 1, '$2y$10$RDPiHV4lDp7d0NJnRP1zbuiSCcriCg7AqsZjFPGaXDVNxRX9.Qa4C', 'Jefe de Computación e Informatica', 0, 1, NULL, 'Activo', '332', '2025-02-26 15:02:00', NULL, 'q8ctAnYBSqmgSsQW'),
(43, 'Felipe ', 'Guzman ', '', '0000-00-00', 'fguzman@seduc.cl', NULL, 1, '$2y$10$RDPiHV4lDp7d0NJnRP1zbuiSCcriCg7AqsZjFPGaXDVNxRX9.Qa4C', NULL, 0, 7, NULL, 'Activo', '331', '2025-02-27 15:44:22', NULL, 'dPjXIubfwJUb2ew1'),
(44, 'Trinidad', 'Alamos', '', '0000-00-00', 'talamos@seduc.cl', '22157520', 2, '$2y$10$RDPiHV4lDp7d0NJnRP1zbuiSCcriCg7AqsZjFPGaXDVNxRX9.Qa4C', NULL, 0, 6, NULL, 'Activo', '', '2025-03-10 17:22:20', NULL, 'TZ8339I0Gf54rES'),
(45, 'Gerardo', 'Elgueta', '', '0000-00-00', 'gelgueta@tabancura.cl', '', 1, '', NULL, 0, 11, NULL, 'Activo', '', '2025-05-05 16:36:49', NULL, '123x9X440Gf54rES'),
(46, 'Macarena', 'Urrutia', '', '0000-00-00', 'm.urrutia@huelen.cl', '', 2, '$2y$10$.HBo0x.sk1naRaPoY2xNLesYSGWBFSs6cv4V5o./BBhTExtQ6lSey', NULL, 0, 10, NULL, 'Activo', '', '2025-05-12 18:55:18', NULL, 'TZ8x9X12230Gf54r'),
(48, 'Samuel', 'Aranda', 'CORDILLERA', '2025-10-13', 'saranda@colegiocordillera.cl', '', 1, '$2y$10$xDCqfMdIvuo.PX1QB0cLg.AKddJ5HbjdScAWtey9tiw5Z7YKSJJNi', NULL, 0, 11, NULL, 'Activo', '', '2025-10-13 19:07:35', '', 'xY8ziBWNWc12sHg1'),
(49, 'Leandro', 'Barrios', 'LOS ANDES', '2025-10-13', 'lbarrios@colegiolosandes.cl', '', 0, '$2y$10$KfLrytzDj9u3SF5tnj703.3N81utWz8WESy0dz6j8ZgjpLfBnrcq2', NULL, 0, 1, NULL, 'Activo', '', '2025-10-13 19:45:49', '', NULL),
(2476, 'Catalina', 'Irarrazaval', '', '0000-00-00', 'cirarrazaval@colegiolosandes.cl', '', 2, '', NULL, 0, 10, NULL, 'Pendiente', '', '2026-03-12 14:02:56', '', NULL),
(2485, 'Alejandro', 'Alejandro', 'Rojas', '0000-00-00', 'alejandro.rojas.sch@gmail.com', '', 1, '$2y$10$nLewd5qhvbwFFaRZ8.Uwt.xWdAhmU5.pF0JlhYsSVxqb6RJPGrydC', NULL, 0, 11, NULL, 'Activo', '', '2026-04-08 19:46:03', '', NULL),
(2487, 'Alejandro', 'Perez', 'Rojas', '0000-00-00', 'alejandro.r.s@outlook.com', '', 1, NULL, NULL, 0, 11, NULL, 'Pendiente', '', '2026-04-08 20:00:28', 'b0f9918ea0c42dd63bf3b07751e036db', NULL),
(2488, 'Nicolas', 'Bernstein', '', '0000-00-00', 'nbernstein@colegiocordillera.cl', '', 1, '$2y$10$CLWJtYWMvFREyJG7guim3udhFxz80G7IUZhQ/j/7DZdeGAJoS1EHS', NULL, 0, 4, NULL, 'Activo', '', '2026-04-08 20:04:07', '', NULL),
(2491, 'Guadalupe', 'Jorquera', 'Caicedos', '0000-00-00', 'cm.jorquerag@gmail.com', '', 2, '$2y$10$RHSE3GKmAPQUyF4jdZYnk.Ax0LzlXsHPSAW2/AtQaFhUL2vXEuysm', NULL, 2, 4, NULL, 'Activo', '', '2026-04-16 14:14:18', '', NULL),
(2492, 'Carlos', 'Gallardo', '', '0000-00-00', 'c.gallardo@huelen.cl', '', 1, '$2y$10$.tBOpu1a/jn.wBZYo3azHedZQV81/quzfV7nekTp7k3z/E68aOFuW', NULL, 0, 1, NULL, 'Activo', '', '2026-06-10 12:45:38', '', NULL),
(2493, 'Elvira', 'Claro', '', '0000-00-00', 'eclaso@colegiolosandes.cl', '', 2, NULL, NULL, 0, 1, NULL, 'Activo', '', '2026-06-10 18:50:39', 'edb6deb69d9321be2d5210d9ae4c0f16', NULL),
(2494, 'CRISTIAN', 'jorquera', 'MICHEL', '0000-00-00', 'cr.jorquerag@duocuc.cl', '', 1, '$2y$10$Gce2C.qqFXTTDijKJS6VC.rcb5e6Nx0JgDnsaEdx1jtx4tD2B6OkO', NULL, 0, 1, NULL, 'Activo', '', '2026-06-11 17:11:54', '', NULL),
(2495, 'Francisco', 'Salazar', '', '0000-00-00', 'fsalazar@tabancura.cl', '', 1, NULL, NULL, 0, 4, NULL, 'Pendiente', '', '2026-06-11 20:17:39', '71f4214083a0b4d2593cb962290b8b08', NULL),
(2496, 'katrin', 'Pendergrast', '', '0000-00-00', 'kprendergast@colegiolosalerces.cl', '', 2, '$2y$10$vPUZiIuF4aLVsUbrnrbgc./0Fs8A0PrEBZUYhi8APAZM7vvMFDv9m', NULL, 0, 11, NULL, 'Activo', '', '2026-06-18 19:02:21', '', NULL),
(2497, 'Claudio', 'Ortega', '', '0000-00-00', 'cortega@colegiolosalerces.cl', '', 1, '$2y$10$2Tem0LSh4UUCKCfnGPSEKuiXdZrJf8LCaICapU7KWagRIge9zQOma', NULL, 0, 11, NULL, 'Activo', '', '2026-06-18 19:03:01', '', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_usuarios_id_area_trabajo` (`id_area_trabajo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2498;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_area_trabajo` FOREIGN KEY (`id_area_trabajo`) REFERENCES `area_trabajo` (`id_area`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
