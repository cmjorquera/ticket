-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-07-2026 a las 09:58:05
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
-- Estructura de tabla para la tabla `equipo_software`
--

CREATE TABLE `equipo_software` (
  `id_equipo` int(11) NOT NULL,
  `windows` varchar(50) DEFAULT NULL,
  `office` varchar(100) DEFAULT NULL,
  `antivirus` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `equipo_software`
--

INSERT INTO `equipo_software` (`id_equipo`, `windows`, `office`, `antivirus`) VALUES
(54, 'Microsoft Windows 10  Professional (x64), Version', 'Microsoft Office Professional Plus 2016', 'Xcitium (Client- Security)'),
(55, 'Microsoft Windows 11  Home Single Language (x64),', 'Microsoft Office Professional Plus 2016', 'Xcitium (Client- Security)'),
(56, 'Microsoft Windows 11  Home (x64), Version 24H2, Bu', '', ''),
(57, 'Microsoft Windows 11  Home Single Language (x64),', '', ''),
(58, 'Microsoft Windows 10  Home Single Language (x64),', 'Microsoft Office Professional Plus 2016', 'Xcitium (Client- Security)'),
(59, 'Microsoft Windows 10  Home Single Language (x64),', '', ''),
(60, 'Microsoft Windows 11  Home Single Language (x64),', 'Microsoft Office Professional Plus 2016', 'Xcitium (Client- Security)'),
(61, 'Microsoft Windows 11  Home Single Language (x64),', 'Microsoft Office Professional Plus 2016', 'Xcitium (Client- Security)'),
(62, 'Microsoft Windows 11  Pro for Workstations (x64),', 'Microsoft Office Professional Plus 2016', 'Xcitium (Client- Security)'),
(63, 'Microsoft Windows 11  Home Single Language (x64),', 'Microsoft Office Professional Plus 2016', 'Xcitium (Client- Security)'),
(64, 'Microsoft Windows 11  Pro for Workstations (x64),', '', ''),
(65, 'Microsoft Windows 11  Home Single Language (x64),', '', ''),
(66, 'Microsoft Windows 10  Professional (x64), Version', 'Microsoft Office Professional Plus 2016', 'Xcitium (Client- Security)'),
(67, 'Microsoft Windows 11  Home Single Language (x64),', 'Microsoft Office Professional Plus 2016', ''),
(68, 'Microsoft Windows 11  Home Single Language (x64),', '', ''),
(69, 'Microsoft Windows 11  Home Single Language (x64),', 'Microsoft Office Professional Plus 2016', 'Xcitium (Client- Security)'),
(70, 'Microsoft Windows 11  Home Single Language (x64), ', '', ''),
(71, 'Microsoft Windows 11  Home Single Language (x64), ', 'Microsoft Office Professional Plus 2016', ''),
(72, 'Microsoft Windows 11  Home Single Language (x64),', 'Microsoft Office Professional Plus 2016', ''),
(73, 'Microsoft Windows 11  Home (x64), Version 23H2, Bu', 'Microsoft Office Professional Plus 2016', ''),
(74, 'Microsoft Windows 11  Home Single Language (x64),', 'Microsoft Office Professional Plus 2016', 'Xcitium (Client- Security)'),
(75, '', '', ''),
(76, '11', '', ''),
(77, 'Windows 10 Pro', 'Office 2021', 'Windows Defender'),
(78, 'Windows 10 Pro', '', 'Windows Defender'),
(79, 'Windows 10 Pro', '', 'Windows Defender'),
(80, 'Microsoft Windows 11 Home Single Language', '', ''),
(81, '', '', ''),
(82, '', '', ''),
(83, '', '', ''),
(84, '', '', ''),
(85, '', '', ''),
(86, '', '', ''),
(87, '', '', ''),
(88, '', '', ''),
(89, '', '', ''),
(90, '', '', ''),
(91, '', '', ''),
(92, '', '', ''),
(93, '', '', ''),
(94, '', '', ''),
(95, '', '', ''),
(96, '', '', ''),
(97, '', '', ''),
(98, '', '', ''),
(99, '', '', ''),
(100, '', '', ''),
(101, '', '', ''),
(102, '', '', ''),
(103, '', '', ''),
(104, '', '', ''),
(105, '', '', ''),
(106, '', '', ''),
(107, '', '', ''),
(108, '', '', ''),
(109, '', '', ''),
(110, '', '', ''),
(111, '', '', ''),
(112, '', '', ''),
(113, '', '', ''),
(114, '', '', ''),
(115, '', '', ''),
(116, '', '', ''),
(117, '', '', ''),
(118, '', '', ''),
(119, '', '', ''),
(120, '', '', ''),
(121, '', '', ''),
(122, '', '', ''),
(123, '', '', ''),
(124, '', '', ''),
(125, '', '', ''),
(126, '', '', ''),
(127, '', '', ''),
(128, '', '', ''),
(129, '', '', ''),
(130, 'Windows 10', '', ''),
(131, 'Windows 10', '', ''),
(132, 'Windows 10', '', ''),
(133, 'Windows 10', '', ''),
(134, 'Windows 10', '', ''),
(135, 'Windows 10', '', ''),
(136, 'Windows 10', '', ''),
(137, 'Windows 10', '', ''),
(138, 'Windows 10', '', ''),
(139, 'Windows 10', '', ''),
(140, 'Windows 10', '', ''),
(141, 'Windows 10', '', ''),
(142, 'Windows 10', '', ''),
(143, 'Windows 10', '', ''),
(144, 'Windows 10', '', ''),
(145, 'Windows 10', '', ''),
(146, 'Windows 10', '', ''),
(147, 'Windows 10', '', ''),
(148, 'Windows 10', '', ''),
(149, 'Windows 10', '', ''),
(150, 'Windows 10', '', ''),
(151, 'Windows 10', '', ''),
(152, 'Windows 10', '', ''),
(153, 'Windows 10', '', ''),
(154, 'Windows 10', '', ''),
(155, 'Windows 10', '', ''),
(156, 'Windows 10', '', ''),
(157, 'Windows 10', '', ''),
(158, 'Windows 10', '', ''),
(159, 'Windows 10', '', ''),
(160, 'Windows 10', '', ''),
(161, 'Windows 10', '', ''),
(162, 'Windows 10', '', ''),
(163, 'ChromeOS', '', ''),
(164, 'ChromeOS', '', ''),
(165, 'ChromeOS', '', ''),
(166, 'ChromeOS', '', ''),
(167, 'ChromeOS', '', ''),
(168, 'ChromeOS', '', ''),
(169, 'ChromeOS', '', ''),
(170, 'ChromeOS', '', ''),
(171, 'ChromeOS', '', ''),
(172, 'ChromeOS', '', ''),
(173, 'ChromeOS', '', ''),
(174, 'ChromeOS', '', ''),
(175, 'ChromeOS', '', ''),
(176, 'ChromeOS', '', ''),
(177, 'ChromeOS', '', ''),
(178, 'ChromeOS', '', ''),
(179, 'ChromeOS', '', ''),
(180, 'ChromeOS', '', ''),
(181, 'ChromeOS', '', ''),
(182, 'ChromeOS', '', ''),
(183, 'ChromeOS', '', ''),
(184, 'ChromeOS', '', ''),
(185, 'ChromeOS', '', ''),
(186, 'ChromeOS', '', ''),
(187, 'ChromeOS', '', ''),
(188, 'ChromeOS', '', ''),
(189, 'ChromeOS', '', ''),
(190, 'ChromeOS', '', ''),
(191, 'ChromeOS', '', ''),
(192, 'ChromeOS', '', ''),
(193, 'ChromeOS', '', ''),
(194, 'ChromeOS', '', ''),
(195, 'ChromeOS', '', ''),
(196, 'ChromeOS', '', ''),
(197, 'ChromeOS', '', ''),
(198, 'ChromeOS', '', ''),
(199, 'ChromeOS', '', ''),
(200, 'ChromeOS', '', ''),
(201, 'ChromeOS', '', ''),
(202, 'ChromeOS', '', ''),
(203, 'ChromeOS', '', ''),
(204, 'ChromeOS', '', ''),
(205, 'ChromeOS', '', ''),
(206, 'ChromeOS', '', ''),
(207, 'ChromeOS', '', ''),
(208, 'ChromeOS', '', ''),
(209, 'ChromeOS', '', ''),
(210, 'ChromeOS', '', ''),
(211, 'ChromeOS', '', ''),
(212, 'ChromeOS', '', ''),
(213, 'ChromeOS', '', ''),
(214, 'ChromeOS', '', ''),
(215, 'ChromeOS', '', ''),
(216, 'ChromeOS', '', ''),
(217, 'ChromeOS', '', ''),
(218, 'ChromeOS', '', ''),
(219, 'ChromeOS', '', ''),
(220, 'ChromeOS', '', ''),
(221, 'ChromeOS', '', ''),
(222, 'ChromeOS', '', ''),
(223, 'ChromeOS', '', ''),
(224, 'ChromeOS', '', ''),
(225, 'ChromeOS', '', ''),
(226, 'ChromeOS', '', ''),
(227, 'ChromeOS', '', ''),
(228, 'ChromeOS', '', ''),
(229, 'ChromeOS', '', ''),
(230, 'ChromeOS', '', ''),
(231, 'ChromeOS', '', ''),
(232, 'ChromeOS', '', ''),
(233, 'ChromeOS', '', ''),
(234, 'ChromeOS', '', ''),
(235, 'ChromeOS', '', ''),
(236, 'ChromeOS', '', ''),
(237, 'ChromeOS', '', ''),
(238, 'ChromeOS', '', ''),
(239, 'ChromeOS', '', ''),
(240, 'ChromeOS', '', ''),
(241, 'ChromeOS', '', ''),
(242, 'ChromeOS', '', ''),
(243, 'ChromeOS', '', ''),
(244, 'ChromeOS', '', ''),
(245, 'ChromeOS', '', ''),
(246, 'ChromeOS', '', ''),
(247, 'ChromeOS', '', ''),
(248, 'ChromeOS', '', ''),
(249, 'ChromeOS', '', ''),
(250, 'ChromeOS', '', ''),
(251, 'ChromeOS', '', ''),
(252, 'ChromeOS', '', ''),
(253, 'ChromeOS', '', ''),
(254, 'ChromeOS', '', ''),
(255, 'ChromeOS', '', ''),
(256, 'ChromeOS', '', ''),
(257, 'ChromeOS', '', ''),
(258, 'ChromeOS', '', ''),
(259, 'ChromeOS', '', ''),
(260, 'ChromeOS', '', ''),
(261, 'ChromeOS', '', ''),
(262, 'ChromeOS', '', ''),
(263, 'ChromeOS', '', ''),
(264, 'ChromeOS', '', ''),
(265, 'ChromeOS', '', ''),
(266, 'ChromeOS', '', ''),
(267, 'ChromeOS', '', ''),
(268, 'ChromeOS', '', ''),
(269, 'ChromeOS', '', ''),
(270, 'ChromeOS', '', ''),
(271, 'Windows 11Pro', '', ''),
(272, 'Core i3-4005', '1.70 Ghz', 'Windows 10 Pro'),
(273, 'Core i3-3110M', '2.40GHz', 'Windows 10 Education'),
(274, 'Core i3-5005U', '2.00 GHz', 'Windows 10 Pro'),
(275, 'Celeron N4500', '1.10 GHz', 'Windows 11 Home Single Language'),
(276, 'Core i3-9100F', '3.60 GHz', 'Windows 10 Pro'),
(277, 'Core i3-3250', '3.50 GHz', 'Windows 10 Education'),
(278, 'Core i3-5005U', '2.00 GHz', 'Windows 10 Education N'),
(279, 'Core i5-1135G7', '2.40GHz', 'Windows 11 Home Single Language'),
(280, 'Core i3-1115G4', '3.00 GHz', 'Windows 11 Home Single Language');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipo_software`
--
ALTER TABLE `equipo_software`
  ADD PRIMARY KEY (`id_equipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipo_software`
--
ALTER TABLE `equipo_software`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
