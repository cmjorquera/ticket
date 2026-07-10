-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-07-2026 a las 09:57:22
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
-- Estructura de tabla para la tabla `equipo_estado_historial`
--

CREATE TABLE `equipo_estado_historial` (
  `id_historial` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `id_estado_anterior` int(11) DEFAULT NULL,
  `id_estado_nuevo` int(11) NOT NULL,
  `id_usuario_accion` int(11) NOT NULL,
  `fecha_accion` datetime NOT NULL DEFAULT current_timestamp(),
  `observacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `equipo_estado_historial`
--
ALTER TABLE `equipo_estado_historial`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `idx_equipo_estado_equipo` (`id_equipo`),
  ADD KEY `idx_equipo_estado_anterior` (`id_estado_anterior`),
  ADD KEY `idx_equipo_estado_nuevo` (`id_estado_nuevo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `equipo_estado_historial`
--
ALTER TABLE `equipo_estado_historial`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
