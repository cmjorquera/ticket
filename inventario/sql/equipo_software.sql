-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 12-06-2026 a las 09:35:01
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
(64, 'Microsoft Windows 11  Pro for Workstations (x64), ', '', ''),
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
(76, '11', '', '');

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
