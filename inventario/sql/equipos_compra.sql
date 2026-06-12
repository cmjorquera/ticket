-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 12-06-2026 a las 09:30:22
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
-- Estructura de tabla para la tabla `equipos_compra`
--

CREATE TABLE `equipos_compra` (
  `id_equipo` int(11) NOT NULL,
  `valor_equipo` int(10) DEFAULT NULL,
  `proveedor` varchar(255) DEFAULT NULL,
  `numero_factura` varchar(255) DEFAULT NULL,
  `fecha_compra` date DEFAULT '0000-00-00',
  `observacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipos_compra`
--

INSERT INTO `equipos_compra` (`id_equipo`, `valor_equipo`, `proveedor`, `numero_factura`, `fecha_compra`, `observacion`) VALUES
(54, 0, 'falabella', '', '0000-00-00', ''),
(55, 0, '', '', '0000-00-00', ''),
(56, 0, '', '', '0000-00-00', ''),
(57, 0, '', '', '0000-00-00', ''),
(58, 0, '', '', '0000-00-00', ''),
(59, 0, '', '', '0000-00-00', ''),
(60, 0, '', '', '0000-00-00', ''),
(61, 0, '', '', '0000-00-00', ''),
(62, 0, '', '', '0000-00-00', ''),
(63, 0, '', '', '0000-00-00', ''),
(64, 0, '', '', '0000-00-00', ''),
(65, 0, '', '', '0000-00-00', ''),
(66, 0, '', '', '0000-00-00', ''),
(67, 0, '', '', '0000-00-00', ''),
(68, 0, '', '', '0000-00-00', ''),
(69, 0, '', '', '0000-00-00', ''),
(70, 0, '', '', '0000-00-00', ''),
(71, 0, '', '', '0000-00-00', ''),
(72, 0, '', '', '0000-00-00', ''),
(73, 0, '', '', '0000-00-00', ''),
(74, 0, '', '', '0000-00-00', ''),
(75, 0, '', '', '0000-00-00', ''),
(76, 0, '', '', '0000-00-00', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipos_compra`
--
ALTER TABLE `equipos_compra`
  ADD PRIMARY KEY (`id_equipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipos_compra`
--
ALTER TABLE `equipos_compra`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `equipos_compra`
--
ALTER TABLE `equipos_compra`
  ADD CONSTRAINT `equipos_compra_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
