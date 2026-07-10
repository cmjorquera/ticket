-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-07-2026 a las 09:57:07
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
(76, 'SSD', '480', ''),
(77, 'Samsung 860 EVO', '256GB', '2.5\"'),
(78, 'SSD', '256GB', '2.5\"'),
(79, 'SSD', '256GB', '2.5\"'),
(80, 'FORESEE 512GB SSD', '477 GB', 'SSD'),
(81, '', '128GB', ''),
(82, '', '128GB', ''),
(83, '', '128GB', ''),
(84, '', '128GB', ''),
(85, '', '128GB', ''),
(86, '', '128GB', ''),
(87, '', '128GB', ''),
(88, '', '128GB', ''),
(89, '', '32GB', ''),
(90, '', '32GB', ''),
(91, '', '32GB', ''),
(92, '', '32GB', ''),
(93, '', '32GB', ''),
(94, '', '32GB', ''),
(95, '', '32GB', ''),
(96, '', '32GB', ''),
(97, '', '32GB', ''),
(98, '', '32GB', ''),
(99, '', '32GB', ''),
(100, '', '32GB', ''),
(101, '', '32GB', ''),
(102, '', '32GB', ''),
(103, '', '32GB', ''),
(104, '', '32GB', ''),
(105, '', '32GB', ''),
(106, '', '32GB', ''),
(107, '', '32GB', ''),
(108, '', '16GB', ''),
(109, '', '32GB', ''),
(110, '', '32GB', ''),
(111, '', '32GB', ''),
(112, '', '32GB', ''),
(113, '', '32GB', ''),
(114, '', '32GB', ''),
(115, '', '32GB', ''),
(116, '', '32GB', ''),
(117, '', '32GB', ''),
(118, '', '32GB', ''),
(119, '', '32GB', ''),
(120, '', '32GB', ''),
(121, '', '32GB', ''),
(122, '', '32GB', ''),
(123, '', '32GB', ''),
(124, '', '16GB', ''),
(125, '', '16GB', ''),
(126, '', '16GB', ''),
(127, '', '16GB', ''),
(128, '', '16GB', ''),
(129, '', '32GB', ''),
(130, '', '112GB', ''),
(131, '', '112GB', ''),
(132, '', '112GB', ''),
(133, '', '112GB', ''),
(134, '', '112GB', ''),
(135, '', '112GB', ''),
(136, '', '112GB', ''),
(137, '', '112GB', ''),
(138, '', '112GB', ''),
(139, '', '112GB', ''),
(140, '', '112GB', ''),
(141, '', '112GB', ''),
(142, '', '112GB', ''),
(143, '', '112GB', ''),
(144, '', '112GB', ''),
(145, '', '112GB', ''),
(146, '', '112GB', ''),
(147, '', '112GB', ''),
(148, '', '112GB', ''),
(149, '', '112GB', ''),
(150, '', '112GB', ''),
(151, '', '112GB', ''),
(152, '', '112GB', ''),
(153, '', '112GB', ''),
(154, '', '112GB', ''),
(155, '', '112GB', ''),
(156, '', '112GB', ''),
(157, '', '112GB', ''),
(158, '', '112GB', ''),
(159, '', '112GB', ''),
(160, '', '112GB', ''),
(161, '', '112GB', ''),
(162, '', '112GB', ''),
(163, '', '32GB', ''),
(164, '', '32GB', ''),
(165, '', '32GB', ''),
(166, '', '32GB', ''),
(167, '', '32GB', ''),
(168, '', '32GB', ''),
(169, '', '32GB', ''),
(170, '', '32GB', ''),
(171, '', '32GB', ''),
(172, '', '32GB', ''),
(173, '', '32GB', ''),
(174, '', '32GB', ''),
(175, '', '32GB', ''),
(176, '', '32GB', ''),
(177, '', '32GB', ''),
(178, '', '32GB', ''),
(179, '', '32GB', ''),
(180, '', '32GB', ''),
(181, '', '32GB', ''),
(182, '', '32GB', ''),
(183, '', '32GB', ''),
(184, '', '32GB', ''),
(185, '', '32GB', ''),
(186, '', '32GB', ''),
(187, '', '32GB', ''),
(188, '', '32GB', ''),
(189, '', '32GB', ''),
(190, '', '32GB', ''),
(191, '', '32GB', ''),
(192, '', '32GB', ''),
(193, '', '32GB', ''),
(194, '', '32GB', ''),
(195, '', '32GB', ''),
(196, '', '32GB', ''),
(197, '', '32GB', ''),
(198, '', '32GB', ''),
(199, '', '32GB', ''),
(200, '', '32GB', ''),
(201, '', '32GB', ''),
(202, '', '32GB', ''),
(203, '', '32GB', ''),
(204, '', '32GB', ''),
(205, '', '32GB', ''),
(206, '', '32GB', ''),
(207, '', '32GB', ''),
(208, '', '32GB', ''),
(209, '', '32GB', ''),
(210, '', '32GB', ''),
(211, '', '32GB', ''),
(212, '', '32GB', ''),
(213, '', '32GB', ''),
(214, '', '32GB', ''),
(215, '', '32GB', ''),
(216, '', '32GB', ''),
(217, '', '32GB', ''),
(218, '', '32GB', ''),
(219, '', '32GB', ''),
(220, '', '32GB', ''),
(221, '', '32GB', ''),
(222, '', '32GB', ''),
(223, '', '32GB', ''),
(224, '', '32GB', ''),
(225, '', '32GB', ''),
(226, '', '32GB', ''),
(227, '', '32GB', ''),
(228, '', '32GB', ''),
(229, '', '32GB', ''),
(230, '', '32GB', ''),
(231, '', '32GB', ''),
(232, '', '32GB', ''),
(233, '', '32GB', ''),
(234, '', '32GB', ''),
(235, '', '32GB', ''),
(236, '', '32GB', ''),
(237, '', '32GB', ''),
(238, '', '32GB', ''),
(239, '', '32GB', ''),
(240, '', '32GB', ''),
(241, '', '32GB', ''),
(242, '', '32GB', ''),
(243, '', '32GB', ''),
(244, '', '32GB', ''),
(245, '', '32GB', ''),
(246, '', '32GB', ''),
(247, '', '32GB', ''),
(248, '', '32GB', ''),
(249, '', '32GB', ''),
(250, '', '32GB', ''),
(251, '', '32GB', ''),
(252, '', '32GB', ''),
(253, '', '32GB', ''),
(254, '', '32GB', ''),
(255, '', '32GB', ''),
(256, '', '32GB', ''),
(257, '', '32GB', ''),
(258, '', '32GB', ''),
(259, '', '32GB', ''),
(260, '', '32GB', ''),
(261, '', '32GB', ''),
(262, '', '32GB', ''),
(263, '', '32GB', ''),
(264, '', '32GB', ''),
(265, '', '32GB', ''),
(266, '', '32GB', ''),
(267, '', '32GB', ''),
(268, '', '32GB', ''),
(269, '', '32GB', ''),
(270, '', '32GB', ''),
(271, '', '256GB', ''),
(272, '', '', 'SSD'),
(273, '', '', 'KINGSTON SA400S37480G'),
(274, '', '', 'KINGSTON SA400S37480G'),
(275, '', '', 'SSD'),
(276, '', '', 'KINGSTON SA400S37480G'),
(277, '', '', 'KINGSTON SA400S37480G'),
(278, '', '', 'WDS240G2G0A-00JH30'),
(279, '', '', 'SSD'),
(280, '', '', 'WDC PC SN530 SDBPMPZ-256G-1101');

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
