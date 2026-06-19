-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 19-06-2026 a las 11:33:04
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
-- Estructura de tabla para la tabla `monitores`
--

CREATE TABLE `monitores` (
  `id_monitor` int(11) NOT NULL,
  `id_colegio` int(11) NOT NULL,
  `id_ubicacion` int(11) DEFAULT NULL,
  `id_usuario_asignado` int(11) DEFAULT NULL,
  `id_usuario_registra` int(11) DEFAULT NULL,
  `id_estado` int(11) NOT NULL DEFAULT 1,
  `nombre_monitor` varchar(150) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `modelo` varchar(150) DEFAULT NULL,
  `numero_serie` varchar(150) DEFAULT NULL,
  `codigo_interno` varchar(100) DEFAULT NULL,
  `tamano_monitor` varchar(50) DEFAULT NULL,
  `resolucion_monitor` varchar(100) DEFAULT NULL,
  `tipo_panel` varchar(100) DEFAULT NULL,
  `tipo_conexion` varchar(150) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `fecha_registro` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `monitores`
--
ALTER TABLE `monitores`
  ADD PRIMARY KEY (`id_monitor`),
  ADD KEY `idx_monitor_colegio` (`id_colegio`),
  ADD KEY `idx_monitor_ubicacion` (`id_ubicacion`),
  ADD KEY `idx_monitor_usuario` (`id_usuario_asignado`),
  ADD KEY `idx_monitor_estado` (`id_estado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `monitores`
--
ALTER TABLE `monitores`
  MODIFY `id_monitor` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
