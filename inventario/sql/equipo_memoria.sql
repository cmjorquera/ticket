-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 12-06-2026 a las 09:33:54
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
-- Estructura de tabla para la tabla `equipo_memoria`
--

CREATE TABLE `equipo_memoria` (
  `id_memoria` int(11) NOT NULL,
  `id_equipo` int(11) DEFAULT NULL,
  `designacion_memoria` varchar(255) DEFAULT NULL,
  `formato_memoria` varchar(255) DEFAULT NULL,
  `tipo_memoria` varchar(255) DEFAULT NULL,
  `tamano_memoria` int(11) DEFAULT NULL,
  `frecuencia_memoria` int(11) DEFAULT NULL,
  `marca_memoria` varchar(255) DEFAULT NULL,
  `orden_memoria` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipo_memoria`
--

INSERT INTO `equipo_memoria` (`id_memoria`, `id_equipo`, `designacion_memoria`, `formato_memoria`, `tipo_memoria`, `tamano_memoria`, `frecuencia_memoria`, `marca_memoria`, `orden_memoria`) VALUES
(295, 55, 'DIMM A', 'SODIMM', 'DDR4', 8, 2667, 'Samsung', 1),
(296, 55, 'DIMM B', '', '', 0, 0, '', 2),
(297, 56, 'Bottom-slot 1(left)', 'SODIMM', 'DDR4', 8, 2400, 'Samsung', 1),
(298, 56, 'Bottom-slot 2(right)', 'SODIMM', 'DDR', 0, 0, '', 2),
(299, 57, 'DIMM A', 'SODIMM', 'DDR4', 8, 2667, 'Samsung', 1),
(300, 57, 'DIMM B', '', '', 0, 0, '', 2),
(301, 58, 'Bottom - Slot 1 (left)', 'SODIMM', '', 0, 0, '', 1),
(302, 58, 'Bottom - Slot 2 (right)', 'SODIMM', 'DDR4', 8, 2667, 'SK Hynix', 2),
(305, 60, 'DIMM A', 'SODIMM', 'DDR4', 8, 2667, 'Samsung', 1),
(306, 60, 'DIMM B', '', '', 0, 0, '', 2),
(307, 61, 'DIMM A', 'SODIMM', 'DDR4', 8, 2667, 'Samsung', 1),
(308, 61, 'DIMM B', '', '', 0, 0, '', 2),
(309, 62, 'DIMM 0', 'SODIMM', 'DDR4', 8, 2400, 'Hynix', 1),
(310, 62, 'DIMM 0', 'SODIMM', 'DDR4', 8, 2400, 'Samsung', 2),
(311, 63, 'Bottom - Slot 1 (left)', 'SODIMM', 'DDR4', 8, 3200, 'Samsung', 1),
(312, 63, 'Bottom - Slot 2 (right)', 'SODIMM', 'DDR4', 8, 3200, 'Samsung 2', 2),
(313, 64, 'Bottom - Slot 1 (left)', 'SODIMM', 'DDR4', 8, 3200, 'Micron Technology', 1),
(314, 64, 'Bottom - Slot 2 (right)', 'SODIMM', 'DDR4', 8, 3200, 'Micron Technology', 2),
(315, 65, 'Bottom - Slot 1 (left) MG', 'SODIMM', 'DDR4', 8, 3200, 'Samsung', 1),
(316, 65, 'Bottom - Slot 2 (right)', 'SODIMM', 'DDR4', 8, 3200, 'Samsung', 2),
(317, 66, 'Bottom-Slot 1(left)', 'SODIMM', 'DDR4', 8, 2133, 'Ramaxel Technology', 1),
(318, 66, 'Bottom-Slot 2(right)', 'SODIMM', 'DDR', 0, 0, '', 2),
(319, 67, 'Bottom - Slot 1 (left)', 'SODIMM', 'DDR4', 4, 3200, 'SK Hynix', 1),
(320, 67, 'Bottom - Slot 2 (right)', 'SODIMM', 'DDR4', 4, 3200, 'SK Hynix', 2),
(321, 68, 'Bottom - Slot 1 (left)', 'SODIMM', 'DDR4', 4, 3200, 'Micron Technology', 1),
(322, 68, 'Bottom - Slot 2 (right)', 'SODIMM', 'DDR4', 4, 3200, 'Micron Technology', 2),
(323, 69, 'Controller0-ChannelA', 'Row of chips', 'DDR4', 4, 3200, 'Samsung', 1),
(324, 69, 'Controller1-ChannelA', 'Row of chips', 'DDR4', 4, 3200, 'Samsung', 2),
(325, 70, 'Controller0-ChannelA', 'DIMM', 'LPDDR4', 8, 5200, 'Samsung', 1),
(326, 70, 'Controller1-ChannelA', 'DIMM', 'LPDDR4', 8, 5200, 'Samsung', 2),
(327, 71, 'Controller0-ChannelA-DIMM0', 'SODIMM', 'DDR4', 4, 3200, 'SK Hynix', 1),
(328, 71, 'Controller1-ChannelA', 'Row of chips', 'DDR4', 4, 3200, 'Samsung', 2),
(329, 72, 'Controller0-ChannelA', 'Row of chips', 'DDR4', 4, 3200, 'SK Hynix', 1),
(330, 72, 'Controller1-ChannelA', 'Row of chips', 'DDR4', 4, 3200, 'SK Hynix', 2),
(331, 73, 'Bottom-slot 1(left)', 'SODIMM', 'DDR4', 8, 2400, 'Samsung', 1),
(332, 73, 'Bottom-slot 2(right)', 'SODIMM', 'DDR', 0, 0, '', 2),
(333, 74, 'Controller0-ChannelA-DIMM0', 'SODIMM', 'DDR4', 4, 3200, 'Micron Technology', 1),
(334, 74, 'Controller1-ChannelA', 'Row of chips', 'DDR4', 4, 3200, 'Samsung', 2),
(343, 59, 'Bottom-slot 1(left)', 'SODIMM', 'DDR4', 4, 2667, 'SK Hynix', 1),
(344, 59, 'Bottom-slot 2(right)', 'SODIMM', 'DDR', 0, 0, '', 2),
(381, 54, 'ChannelA-DIMM0', '', '', 0, 0, '', 1),
(382, 54, 'ChannelA-DIMM1', '', '', 0, 0, '', 2),
(383, 54, 'ChannelB-DIMM0', 'DIMM', 'DDR4', 8, 2133, '859B', 3),
(384, 54, 'ChannelB-DIMM1', '', '', 0, 0, '', 4),
(390, 75, '1', 'LPDDR5', 'RAM', 4, 5500, '', 1),
(391, 75, '2', 'LPDDR5', 'RAM', 4, 5500, '', 2),
(394, 76, '1', 'LPDDR5', 'RAM', 4, 5500, '', 1),
(395, 76, '2', '', 'RAM', 4, 5500, '', 2);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipo_memoria`
--
ALTER TABLE `equipo_memoria`
  ADD PRIMARY KEY (`id_memoria`),
  ADD KEY `id_equipo` (`id_equipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipo_memoria`
--
ALTER TABLE `equipo_memoria`
  MODIFY `id_memoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=396;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `equipo_memoria`
--
ALTER TABLE `equipo_memoria`
  ADD CONSTRAINT `equipo_memoria_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
