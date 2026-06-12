-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 12-06-2026 a las 09:30:45
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
-- Estructura de tabla para la tabla `equipo_almacenamiento`
--

CREATE TABLE `equipo_almacenamiento` (
  `id_equipo` int(11) NOT NULL,
  `equipo_modelo` varchar(100) DEFAULT NULL,
  `equipo_capacidad` varchar(50) DEFAULT NULL,
  `equipo_tamano` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipo_almacenamiento`
--

INSERT INTO `equipo_almacenamiento` (`id_equipo`, `equipo_modelo`, `equipo_capacidad`, `equipo_tamano`) VALUES
(54, 'Crucial_CT275MX300SSD1', '256.2', 'Fixed, SSD'),
(55, 'PM991a NVMe Samsung 256GB', '2385', 'Fixed'),
(56, 'KBG30ZMV256G TOSHIBA', '238.5', 'Fixed, SSD'),
(57, 'KBG40ZNS256G NVMe KIOXIA 256GB', '238.5', 'Fixed'),
(58, 'SK hynix BC511 HFM256GDJTNI-82A0A', '238.5', 'Fixed, SSD'),
(59, 'Optane+238GBSSD', '238.5', 'Fixed'),
(60, 'PM991a NVMe Samsung 256GB', '238.5', 'Fixed'),
(61, 'PM991a NVMe Samsung 256GB', '238.5', 'Fixed'),
(62, 'WDC  WDS500G2B0A-00SM50 (Modelo)', '465.8', 'Fixed, SSD'),
(63, 'WD PC SN740 SDDPNQD-512G-2006', '476.9', 'Fixed, SSD'),
(64, 'WD PC SN810 SDCPNRY-512G-1006', '476.9', 'Fixed, SSD'),
(65, 'SAMSUNG MZVLQ512HBLU-00BH1', '476.9', 'Fixed, SSD'),
(66, 'WDC PC SN520 SDAPNUW-256G-1006', '238.5', 'Fixed, SSD'),
(67, 'KBG50ZNV512G KIOXIA', '476.9', 'Fixed, SSD'),
(68, 'WD PC SN740 SDDPNQD-512G-1006', '476.9', 'Fixed, SSD'),
(69, 'SAMSUNG MZALQ512HALU-000L2', '476.9', 'Fixed'),
(70, 'WD PC SN740 SDDPMQD-512G-1101', '476.9', 'Fixed, SSD'),
(71, 'SAMSUNG MZALQ512HBLU-00BL2', '476.9', 'Fixed'),
(72, 'SKHynix_HFS512GD9TNI-L2A0B', '476.9', 'Fixed, SSD'),
(73, 'KBG30ZMV256G TOSHIBA', '238.5', 'Fixed, SSD'),
(74, 'SAMSUNG MZALQ512HBLU-00BL2', '476.9', 'Fixed'),
(75, 'SSD', '480', ''),
(76, 'SSD', '480', '');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipo_almacenamiento`
--
ALTER TABLE `equipo_almacenamiento`
  ADD PRIMARY KEY (`id_equipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipo_almacenamiento`
--
ALTER TABLE `equipo_almacenamiento`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
