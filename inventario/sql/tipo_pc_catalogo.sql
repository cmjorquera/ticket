-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 19-06-2026 a las 10:15:26
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
-- Estructura de tabla para la tabla `tipo_pc_catalogo`
--

CREATE TABLE `tipo_pc_catalogo` (
  `id_tipo_pc` int(11) NOT NULL,
  `nombre_tipo` varchar(100) NOT NULL,
  `activo` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_pc_catalogo`
--

INSERT INTO `tipo_pc_catalogo` (`id_tipo_pc`, `nombre_tipo`, `activo`) VALUES
(1, 'Desktop', 1),
(2, 'Notebook', 1),
(3, 'All In One', 1),
(4, 'Mini PC', 1),
(5, 'Servidor', 1),
(6, 'Otro', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `tipo_pc_catalogo`
--
ALTER TABLE `tipo_pc_catalogo`
  ADD PRIMARY KEY (`id_tipo_pc`),
  ADD UNIQUE KEY `uq_tipo_pc_nombre` (`nombre_tipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tipo_pc_catalogo`
--
ALTER TABLE `tipo_pc_catalogo`
  MODIFY `id_tipo_pc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
