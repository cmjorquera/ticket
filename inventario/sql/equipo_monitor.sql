-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 12-06-2026 a las 09:34:26
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
-- Estructura de tabla para la tabla `equipo_monitor`
--

CREATE TABLE `equipo_monitor` (
  `id_monitor` int(11) NOT NULL,
  `id_equipo` int(11) DEFAULT NULL,
  `modelo_monitor` varchar(255) DEFAULT NULL,
  `codigo_monitor` varchar(255) DEFAULT NULL,
  `serie_monitor` varchar(11) DEFAULT NULL,
  `tamano_monitor` int(10) DEFAULT NULL,
  `resolucion_monitor` varchar(50) DEFAULT NULL,
  `orden_monitor` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipo_monitor`
--

INSERT INTO `equipo_monitor` (`id_monitor`, `id_equipo`, `modelo_monitor`, `codigo_monitor`, `serie_monitor`, `tamano_monitor`, `resolucion_monitor`, `orden_monitor`) VALUES
(269, 55, '()', 'BOE0A23', '', 15, '1366 x 768', 1),
(270, 55, 'SMB2030N (Samsung)', 'SAM0634', 'H9LB201570', 20, '1600 x 900', 2),
(271, 56, '()', 'BOE07FF', '', 15, '1920 x 1080', 1),
(272, 56, 'LED MONITOR ()', 'SAC952D', '', 22, '1920 x 1080', 2),
(273, 57, '831W (AOC International)', 'AOC1831', '', 19, '1366 x 768', 1),
(274, 57, '()', 'BOE097D', '', 15, '1920 x 1080', 2),
(275, 58, '()', 'BOE0920', '', 15, '1366 x 768', 1),
(276, 58, 'E2042 (LG Electronics (GoldStar))', 'GSM4ED7', '206NDSK6U16', 20, '1600 x 900', 2),
(279, 60, '()', 'BOE0A23', '', 15, '1366 x 768', 1),
(280, 60, '', '', '', 0, '', 2),
(281, 61, '831W (AOC International)', 'AOC1831', '', 19, '1366 x 768', 1),
(282, 61, '()', 'BOE0A23', '', 15, '1366 x 768', 2),
(283, 62, '()   ****', 'BOE08CD', '3423424  **', 15, '1366 x 768', 1),
(284, 62, 'E2251 (LG Electronics (GoldStar))', 'GSM586D', '111LTSS3U07', 22, '1920 x 1080', 2),
(285, 63, 'HP', 'AUO2992', '', 15, '1920 x 1080', 1),
(286, 63, 'LG FHD (LG Electronics (GoldStar))', 'GSM5C66', '404TFGC0Y11', 24, '1920 x 1080', 2),
(287, 64, '()', 'CMN161A', '', 16, '1920 x 1080', 1),
(288, 64, 'S24F350 (Samsung)', 'SAM0D20', 'H4ZH800057', 23, '1920 x 1080', 2),
(289, 65, 'SA', 'AUO4799', 'C40H7K', 15, '1920 x 1080', 1),
(290, 65, 'HP 22w', 'HPN342E', 'CNC7440H7K', 22, '1920 x 1080', 2),
(291, 66, '()', 'CMN15DC', '', 15, '1366 x 768', 1),
(292, 66, '', '', '', 0, '', 2),
(293, 67, 'S22A33x (Samsung)', 'SAM7122', 'H4TTC00365', 22, '1920 x 1080', 1),
(294, 67, '()', 'AUO499F', '', 15, '1920 x 1080', 2),
(295, 68, '()', 'BOE0B14', '', 15, '1920 x 1080', 1),
(296, 68, 'S20B300 (Samsung)', 'SAM08A8', 'HTLC401303', 20, '1600 x 900', 2),
(297, 69, '()', 'BOE08E2', '', 15, '1920 x 1080', 1),
(298, 69, '', '', '', 0, '', 2),
(299, 70, '()', 'LEN9156', '', 16, '1920 x 1200', 1),
(300, 70, 'S22A33x (Samsung)', 'SAM7122', 'H4TTC00357', 22, '1920 x 1080', 2),
(301, 71, '()', 'BOE09AE', '', 14, '1920 x 1080', 1),
(302, 71, '', '', '', 0, '', 2),
(303, 72, '()', 'LEN889A', '', 14, '1920 x 1080', 1),
(304, 72, '', '', '', 0, '', 2),
(305, 73, '()', 'BOE07FF', '', 15, '1920 x 1080', 1),
(306, 73, '22B2WG5 (AOC International)', 'AOC2202', 'QNHM9HA0844', 22, '1920 x 1080', 2),
(307, 74, '()', 'BOE09AE', '', 14, '1920 x 1080', 1),
(308, 74, '', '', '', 0, '', 2),
(313, 59, '()', 'CMN1413', '', 14, '1366 x 768', 1),
(314, 59, '', '', '', 0, '', 2),
(333, 54, 'VA2248 SERIES (ViewSonic)', 'VSC0E28', 'SDD11492332', 22, '1920 x 1080', 1),
(334, 54, '', '', '', 0, '', 2),
(339, 75, '', '', '', 0, '', 1),
(341, 76, '', '', '', 0, '', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipo_monitor`
--
ALTER TABLE `equipo_monitor`
  ADD PRIMARY KEY (`id_monitor`),
  ADD KEY `id_equipo` (`id_equipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipo_monitor`
--
ALTER TABLE `equipo_monitor`
  MODIFY `id_monitor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=342;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `equipo_monitor`
--
ALTER TABLE `equipo_monitor`
  ADD CONSTRAINT `equipo_monitor_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
