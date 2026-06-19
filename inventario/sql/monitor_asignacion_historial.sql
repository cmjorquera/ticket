-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 19-06-2026 a las 11:53:20
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
-- Estructura de tabla para la tabla `monitor_asignacion_historial`
--

CREATE TABLE `monitor_asignacion_historial` (
  `id_asignacion` int(11) NOT NULL,
  `id_monitor` int(11) NOT NULL,
  `id_usuario_anterior` int(11) DEFAULT NULL,
  `id_usuario_nuevo` int(11) DEFAULT NULL,
  `id_usuario_accion` int(11) NOT NULL,
  `fecha_accion` datetime DEFAULT current_timestamp(),
  `motivo` varchar(255) DEFAULT NULL,
  `observacion` text DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `monitor_asignacion_historial`
--
ALTER TABLE `monitor_asignacion_historial`
  ADD PRIMARY KEY (`id_asignacion`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `monitor_asignacion_historial`
--
ALTER TABLE `monitor_asignacion_historial`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
