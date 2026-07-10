-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-07-2026 a las 09:59:08
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
-- Estructura de tabla para la tabla `usuario_colegio`
--

CREATE TABLE `usuario_colegio` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_perfil` int(11) NOT NULL,
  `estado` int(11) NOT NULL DEFAULT 1 COMMENT '1 = activo | 0 = inactivo',
  `fecha_asignacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario_colegio`
--

INSERT INTO `usuario_colegio` (`id`, `id_usuario`, `id_colegio`, `id_perfil`, `estado`, `fecha_asignacion`) VALUES
(1, 1, 15, 1, 1, '2026-01-08 10:20:26'),
(2, 2, 15, 1, 1, '2026-01-08 10:20:26'),
(3, 3, 15, 1, 1, '2026-01-08 10:20:26'),
(4, 4, 15, 1, 1, '2026-01-08 10:20:26'),
(5, 5, 15, 1, 1, '2026-01-08 10:20:26'),
(6, 6, 15, 1, 1, '2026-01-08 10:20:26'),
(7, 7, 15, 1, 1, '2026-01-08 10:20:26'),
(8, 8, 11, 1, 1, '2026-01-08 10:20:26'),
(9, 9, 15, 1, 1, '2026-01-08 10:20:26'),
(10, 10, 15, 1, 1, '2026-01-08 10:20:26'),
(11, 11, 15, 1, 1, '2026-01-08 10:20:26'),
(12, 12, 15, 1, 1, '2026-01-08 10:20:26'),
(13, 13, 15, 1, 1, '2026-01-08 10:20:26'),
(14, 14, 15, 1, 1, '2026-01-08 10:20:26'),
(15, 15, 15, 1, 1, '2026-01-08 10:20:26'),
(16, 16, 15, 1, 1, '2026-01-08 10:20:26'),
(17, 17, 15, 1, 1, '2026-01-08 10:20:26'),
(18, 18, 15, 1, 1, '2026-01-08 10:20:26'),
(19, 19, 15, 1, 1, '2026-01-08 10:20:26'),
(20, 20, 15, 1, 1, '2026-01-08 10:20:26'),
(21, 21, 15, 1, 1, '2026-01-08 10:20:26'),
(22, 22, 15, 1, 1, '2026-01-08 10:20:26'),
(23, 23, 15, 1, 1, '2026-01-08 10:20:26'),
(24, 24, 15, 1, 1, '2026-01-08 10:20:26'),
(25, 25, 15, 1, 1, '2026-01-08 10:20:26'),
(26, 26, 15, 1, 1, '2026-01-08 10:20:26'),
(29, 38, 15, 1, 1, '2026-01-08 10:20:26'),
(30, 39, 15, 1, 1, '2026-01-08 10:20:26'),
(31, 40, 15, 1, 1, '2026-01-08 10:20:26'),
(32, 41, 15, 1, 1, '2026-01-08 10:20:26'),
(33, 42, 15, 1, 1, '2026-01-08 10:20:26'),
(34, 43, 15, 1, 1, '2026-01-08 10:20:26'),
(35, 44, 15, 1, 1, '2026-01-08 10:20:26'),
(36, 45, 15, 1, 0, '2026-01-08 10:20:26'),
(37, 46, 15, 1, 1, '2026-01-08 10:20:26'),
(38, 30, 11, 1, 1, '2026-01-08 10:26:11'),
(40, 32, 11, 1, 1, '2026-01-08 10:26:11'),
(41, 48, 1, 1, 1, '2026-04-15 14:38:41'),
(42, 49, 9, 1, 1, '2026-06-11 10:25:43'),
(43, 34, 12, 1, 1, '2026-01-08 10:26:11'),
(44, 32, 1, 1, 1, '2026-01-08 10:26:11'),
(45, 28, 8, 1, 1, '2026-01-08 10:26:11'),
(46, 2476, 1, 1, 0, '2026-03-12 11:02:56'),
(53, 2483, 10, 1, 1, '2026-04-07 19:26:56'),
(54, 2484, 8, 1, 1, '2026-04-07 21:03:02'),
(55, 2485, 15, 1, 1, '2026-04-08 15:46:03'),
(56, 2486, 10, 1, 1, '2026-04-08 15:58:43'),
(57, 2487, 10, 1, 1, '2026-04-08 16:00:28'),
(58, 2488, 1, 1, 1, '2026-04-08 16:04:07'),
(59, 2489, 12, 1, 1, '2026-05-19 09:25:03'),
(60, 35, 15, 1, 1, '2026-04-16 09:38:10'),
(61, 2490, 15, 1, 1, '2026-04-16 10:08:23'),
(62, 2491, 15, 1, 1, '2026-04-16 10:14:18'),
(63, 27, 15, 1, 1, '2026-05-11 12:42:29'),
(64, 33, 13, 1, 1, '2026-05-20 10:12:46'),
(65, 2492, 11, 1, 1, '2026-06-10 08:45:38'),
(66, 2493, 9, 1, 1, '2026-06-10 14:50:39'),
(67, 2494, 15, 1, 1, '2026-06-11 13:11:54'),
(68, 2495, 8, 1, 1, '2026-06-11 16:17:39'),
(69, 2496, 10, 1, 1, '2026-06-18 15:02:21'),
(70, 2497, 10, 1, 1, '2026-06-18 15:03:01'),
(71, 2476, 9, 1, 1, '2026-06-18 15:04:26'),
(72, 45, 8, 1, 1, '2026-06-18 15:05:45');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `usuario_colegio`
--
ALTER TABLE `usuario_colegio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_usuario_colegio` (`id_usuario`,`id_colegio`,`id_perfil`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_colegio` (`id_colegio`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_usuario_colegio_id_usuario` (`id_usuario`),
  ADD KEY `idx_usuario_colegio_id_colegio` (`id_colegio`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuario_colegio`
--
ALTER TABLE `usuario_colegio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `usuario_colegio`
--
ALTER TABLE `usuario_colegio`
  ADD CONSTRAINT `fk_usuario_colegio_colegio` FOREIGN KEY (`id_colegio`) REFERENCES `colegio` (`id_colegio`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
