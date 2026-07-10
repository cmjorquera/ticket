-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-07-2026 a las 09:57:55
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
-- Estructura de tabla para la tabla `equipo_procesador`
--

CREATE TABLE `equipo_procesador` (
  `id_equipo` int(11) NOT NULL,
  `equipo_fabricante` varchar(100) DEFAULT NULL,
  `equipo_modelo` varchar(100) DEFAULT NULL,
  `equipo_velocidad` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipo_procesador`
--

INSERT INTO `equipo_procesador` (`id_equipo`, `equipo_fabricante`, `equipo_modelo`, `equipo_velocidad`) VALUES
(54, 'Intel(R) Corporation', 'Intel(R) Core(TM) i5-6400 CPU @ 2.70GHz', '2700.0'),
(55, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i5-1135G7 @ 2.40GHz', '3800.0'),
(56, 'Intel(R) Corporation', 'Intel(R) Core(TM) i5-8265U CPU @ 1.60GHz', '1485.0'),
(57, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i5-1135G7 @ 2.40GHz', '2400.0'),
(58, 'Advanced Micro Devices Inc.', 'AMD Ryzen 5 4500U with Radeon Graphics', '2375.0'),
(59, 'Intel(R) Corporation', 'Intel(R) Core(TM) i3-10110U CPU @ 2.10GHz', '1980.0'),
(60, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i5-1135G7 @ 2.40GHz', '3800.0'),
(61, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i5-1135G7 @ 2.40GHz', '3800.0'),
(62, 'Advanced Micro Devices Inc.  ****', 'AMD Ryzen 5 3450U with Radeon Vega Mobile Gfx (Procesador)', '2100.0'),
(63, 'Intel(R) Corporation', '12th Gen Intel(R) Core(TM) i5-12500H', '4158.0'),
(64, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i7-11800H @ 2.30GHz', '2178.0'),
(65, 'Advanced Micro Devices Inc.', 'AMD Ryzen 7 4700U with Radeon Graphics', '2000.0'),
(66, 'Intel(R) Corporation', 'Intel(R) Pentium(R) CPU 5405U @ 2.30GHz', '2178.0'),
(67, 'Intel(R) Corporation', '12th Gen Intel(R) Core(TM) i5-1235U', '3960.0'),
(68, 'Intel(R) Corporation', '12th Gen Intel(R) Core(TM) i5-1235U', '3960.0'),
(69, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i5-1135G7 @ 2.40GHz', '2400.0'),
(70, 'Intel(R) Corporation', '13th Gen Intel(R) Core(TM) i7-13620H', '4653.0'),
(71, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i3-1115G4 @ 3.00GHz', '3000.0'),
(72, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i5-1135G7 @ 2.40GHz', '2400.0'),
(73, 'Intel(R) Corporation', 'Intel(R) Core(TM) i5-8265U CPU @ 1.60GHz', '1485.0'),
(74, 'Intel(R) Corporation', '11th Gen Intel(R) Core(TM) i3-1115G4 @ 3.00GHz', '3000.0'),
(75, 'AMD', 'Ryzen 5 7520U', ''),
(76, 'AMD', 'Ryzen 5 7520U', ''),
(77, 'Intel', 'Core i5-10500', '3.1 GHz'),
(78, 'Intel', 'i3-4005', '1.70 Ghz'),
(79, 'Intel', 'i3-4005', '1.70 Ghz'),
(80, 'AMD', 'AMD Ryzen 7 5700U with Radeon Graphics', '1.8 GHz'),
(81, 'Apple', 'M2', '3.49GHz'),
(82, 'Apple', 'M2', '3.49GHz'),
(83, 'Apple', 'M2', '3.49GHz'),
(84, 'Apple', 'M2', '3.49GHz'),
(85, 'Apple', 'M2', '3.49GHz'),
(86, 'Apple', 'M2', '3.49GHz'),
(87, 'Apple', 'M2', '3.49GHz'),
(88, 'Apple', 'M2', '3.49GHz'),
(89, 'Apple', 'A9', '1.85 GHz'),
(90, 'Apple', 'A9', '1.85 GHz'),
(91, 'Apple', 'A9', '1.85 GHz'),
(92, 'Apple', 'A9', '1.85 GHz'),
(93, 'Apple', 'A9', '1.85 GHz'),
(94, 'Apple', 'A9', '1.85 GHz'),
(95, 'Apple', 'A9', '1.85 GHz'),
(96, 'Apple', 'A9', '1.85 GHz'),
(97, 'Apple', 'A9', '1.85 GHz'),
(98, 'Apple', 'A9', '1.85 GHz'),
(99, 'Apple', 'A9', '1.85 GHz'),
(100, 'Apple', 'A9', '1.85 GHz'),
(101, 'Apple', 'A9', '1.85 GHz'),
(102, 'Apple', 'A9', '1.85 GHz'),
(103, 'Apple', 'A9', '1.85 GHz'),
(104, 'Apple', 'A9', '1.85 GHz'),
(105, 'Apple', 'A9', '1.85 GHz'),
(106, 'Apple', 'A9', '1.85 GHz'),
(107, 'Apple', 'A9', '1.85 GHz'),
(108, 'Apple', 'A8X', '1.5GHz'),
(109, 'Apple', 'A9', '1.85 GHz'),
(110, 'Apple', 'A9', '1.85 GHz'),
(111, 'Apple', 'A9', '1.85 GHz'),
(112, 'Apple', 'A9', '1.85 GHz'),
(113, 'Apple', 'A9', '1.85 GHz'),
(114, 'Apple', 'A9', '1.85 GHz'),
(115, 'Apple', 'A9', '1.85 GHz'),
(116, 'Apple', 'A9', '1.85 GHz'),
(117, 'Apple', 'A9', '1.85 GHz'),
(118, 'Apple', 'A9', '1.85 GHz'),
(119, 'Apple', 'A9', '1.85 GHz'),
(120, 'Apple', 'A9', '1.85 GHz'),
(121, 'Apple', 'A9', '1.85 GHz'),
(122, 'Apple', 'A9', '1.85 GHz'),
(123, 'Apple', 'A9', '1.85 GHz'),
(124, 'Apple', 'A7', '1.4GHz'),
(125, 'Apple', 'A7', '1.4GHz'),
(126, 'Apple', 'A7', '1.4GHz'),
(127, 'Apple', 'A7', '1.4GHz'),
(128, 'Apple', 'A8X', '1.5GHz'),
(129, 'Apple', 'A9', '1.85 GHz'),
(130, 'Intel', 'Core i3-5005U', '2GHz'),
(131, 'Intel', 'Core i3-5005U', '2GHz'),
(132, 'Intel', 'Core i3-5005U', '2GHz'),
(133, 'Intel', 'Core i3-5005U', '2GHz'),
(134, 'Intel', 'Core i3-5005U', '2GHz'),
(135, 'Intel', 'Core i3-5005U', '2GHz'),
(136, 'Intel', 'Core i3-5005U', '2GHz'),
(137, 'Intel', 'Core i3-5005U', '2GHz'),
(138, 'Intel', 'Core i3-5005U', '2GHz'),
(139, 'Intel', 'Core i3-5005U', '2GHz'),
(140, 'Intel', 'Core i3-5005U', '2GHz'),
(141, 'Intel', 'Core i3-5005U', '2GHz'),
(142, 'Intel', 'Core i3-5005U', '2GHz'),
(143, 'Intel', 'Core i3-5005U', '2GHz'),
(144, 'Intel', 'Core i3-5005U', '2GHz'),
(145, 'Intel', 'Core i3-5005U', '2GHz'),
(146, 'Intel', 'Core i3-5005U', '2GHz'),
(147, 'Intel', 'Core i3-5005U', '2GHz'),
(148, 'Intel', 'Core i3-5005U', '2GHz'),
(149, 'Intel', 'Core i3-5005U', '2GHz'),
(150, 'Intel', 'Core i3-5005U', '2GHz'),
(151, 'Intel', 'Core i3-5005U', '2GHz'),
(152, 'Intel', 'Core i3-5005U', '2GHz'),
(153, 'Intel', 'Core i3-5005U', '2GHz'),
(154, 'Intel', 'Core i3-5005U', '2GHz'),
(155, 'Intel', 'Core i3-5005U', '2GHz'),
(156, 'Intel', 'Core i3-5005U', '2GHz'),
(157, 'Intel', 'Core i3-5005U', '2GHz'),
(158, 'Intel', 'Core i3-5005U', '2GHz'),
(159, 'Intel', 'Core i3-5005U', '2GHz'),
(160, 'Intel', 'Core i3-5005U', '2GHz'),
(161, 'Intel', 'Core i3-5005U', '2GHz'),
(162, 'Intel', 'Core i3-5005U', '2GHz'),
(163, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(164, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(165, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(166, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(167, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(168, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(169, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(170, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(171, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(172, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(173, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(174, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(175, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(176, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(177, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(178, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(179, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(180, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(181, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(182, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(183, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(184, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(185, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(186, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(187, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(188, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(189, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(190, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(191, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(192, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(193, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(194, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(195, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(196, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(197, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(198, 'MediaTek', 'Kompanio 520', '2.05GHz'),
(199, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(200, 'Intel', 'Celeron N4020', '2.8GHz'),
(201, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(202, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(203, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(204, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(205, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(206, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(207, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(208, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(209, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(210, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(211, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(212, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(213, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(214, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(215, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(216, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(217, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(218, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(219, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(220, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(221, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(222, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(223, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(224, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(225, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(226, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(227, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(228, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(229, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(230, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(231, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(232, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(233, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(234, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(235, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(236, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(237, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(238, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(239, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(240, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(241, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(242, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(243, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(244, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(245, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(246, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(247, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(248, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(249, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(250, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(251, 'Intel', 'Celeron N4020', '2.8GHz'),
(252, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(253, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(254, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(255, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(256, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(257, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(258, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(259, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(260, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(261, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(262, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(263, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(264, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(265, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(266, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(267, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(268, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(269, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(270, 'AMD', 'A4-9120C RADEON R4', '1.6GHz'),
(271, 'Intel', 'i5-1135G7', '2.4GHz'),
(272, '256GB', '2.5\"', 'Intel'),
(273, '480GB', '2.5\"', 'Intel'),
(274, '480GB', '2.5\"', 'Intel'),
(275, '256GB', 'M.2 NVMe', 'Intel'),
(276, '480GB', '2.5\"', 'Intel'),
(277, '480GB', '2.5\"', 'Intel'),
(278, '256 GB', '2.5\"', 'Intel'),
(279, '256 GB', 'M.2 NVMe', 'Intel'),
(280, '256 GB', 'M.2 NVMe', 'Intel');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipo_procesador`
--
ALTER TABLE `equipo_procesador`
  ADD PRIMARY KEY (`id_equipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipo_procesador`
--
ALTER TABLE `equipo_procesador`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
